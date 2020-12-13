<?php
################################################################################
# Copyright 2013-2015 Aerospike, Inc.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
################################################################################
require_once(realpath(__DIR__ . '/../examples_util.php'));

function parse_args() {
    $shortopts  = "";
    $shortopts .= "h::";  /* Optional host */
    $shortopts .= "p::";  /* Optional port */
    $shortopts .= "p::";  /* Optional port */
    $shortopts .= "a";    /* Optionally annotate output with code */
    $shortopts .= "c";    /* Optionally clean up before leaving */

    $longopts  = array(
        "host::",         /* Optional host */
        "port::",         /* Optional port */
        "clean",          /* Optionally clean up before leaving */
        "annotate",       /* Optionally annotate output with code */
        "help",           /* Usage */
    );
    $options = getopt($shortopts, $longopts);
    return $options;
}

$args = parse_args();
if (isset($args["help"])) {
    echo("php background.php [-h<HOST IP ADDRESS>|--host=<HOST IP ADDRESS> -p<HOST PORT NUMBER>|--port=<HOST PORT NUMBER> -a|--annotate -c|--clean]\n");
    exit(1);
}
$HOST_ADDR = (isset($args["h"])) ? (string) $args["h"] : ((isset($args["host"])) ? (string) $args["host"] : "localhost");
$HOST_PORT = (isset($args["p"])) ? (integer) $args["p"] : ((isset($args["port"])) ? (string) $args["port"] : 3000);

echo colorize("Connecting to the host ≻", 'black', true);
$start = __LINE__;
$config = array("hosts" => array(array("addr" => $HOST_ADDR, "port" => $HOST_PORT)));
$db = new Aerospike($config, false);
if (!$db->isConnected()) {
    echo fail("Could not connect to host $HOST_ADDR:$HOST_PORT [{$db->errorno()}]: {$db->error()}");
    exit(1);
}
echo success();
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Inserting a record in test.users with PK=1234 ≻", 'black', true);
$start = __LINE__;
$key = $db->initKey("test", "users", 1234);
$put_vals = array("first_name" => "Peter", "age" => 30);
$status = $db->put($key, $put_vals);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Inserting a record in test.users with PK=2345 ≻", 'black', true);
$start = __LINE__;
$key = $db->initKey("test", "users", 2345);
$put_vals = array("first_name" => "John", "age" => 35);
$status = $db->put($key, $put_vals);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Inserting a record in test.users with PK=3456 ≻", 'black', true);
$start = __LINE__;
$key = $db->initKey("test", "users", 3456);
$put_vals = array("first_name" => "Alex", "age" => 26);
$status = $db->put($key, $put_vals);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Inserting a record in test.users with PK=4567 ≻", 'black', true);
$start = __LINE__;
$key = $db->initKey("test", "users", 4567);
$put_vals = array("first_name" => "Jimmy", "age" => 23);
$status = $db->put($key, $put_vals);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Getting the ages from test.users *before* the UDF is applied ≻", 'black', true);
$start = __LINE__;
$result = array();
$status = $db->scan("test", "users", function ($record) use (&$result) {
    $result[] = $record['bins'];
});
if ($status === Aerospike::OK) {
    echo success();
    var_dump($result);
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Registering the UDF module ≻", 'black', true);
$start = __LINE__;
$copied = copy(__DIR__.'/lua/example_scan_udf.lua', ini_get('aerospike.udf.lua_user_path').'/example_scan_udf.lua');
if (!$copied) {
    echo fail("Could not copy the local lua/example_scan_udf.lua to ". ini_get('aerospike.udf.lua_user_path'));
}
$status = $db->register(ini_get('aerospike.udf.lua_user_path').'/example_scan_udf.lua', "example_scan_udf.lua");
if ($status != Aerospike::OK) {
    echo standard_fail($db);
} else {
    echo success();
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

// display the Lua code of the UDF module
$lua = file_get_contents(ini_get('aerospike.udf.lua_user_path').'/example_scan_udf.lua');
echo colorize($lua, 'purple', false);

echo colorize("Applying a transformation by UDF to all the records of the set in the background ≻", 'black', true);
$start = __LINE__;
$status = $db->scanApply("test", "users", "example_scan_udf", "bin_transform", array(20), $scan_id, array(Aerospike::OPT_WRITE_TIMEOUT=>2000));
if ($status != Aerospike::OK) {
    echo standard_fail($db);
} else {
    echo success();
    echo "\nInitiated a scan with Scan ID:" . $scan_id . "\n";
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Checking the status of the background scan ≻", 'black', true);
$start = __LINE__;
do {
    sleep(1);
    $status = $db->scanInfo($scan_id, $info);
    if ($status != Aerospike::OK) {
        return($db->errorno());
    }
} while($info['status'] != Aerospike::SCAN_STATUS_COMPLETED);
if ($info['progress_pct'] != 100) {
    echo standard_fail($db);
    var_dump($info);
} else {
    echo success();
    var_dump($info);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Getting the ages in test.users *after* the UDF was applied ≻", 'black', true);
$start = __LINE__;
$result = array();
$status = $db->scan("test", "users", function ($record) use (&$result) {
    $result[] = $record['bins'];
});
if ($status === Aerospike::OK) {
    echo success();
    var_dump($result);
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

if (isset($args['c']) || isset($args['clean'])) {
    $start = __LINE__;
    echo colorize("Removing the record ≻", 'black', true);
    $key = $db->initKey("test", "users", 1234);
    $status = $db->remove($key);
    $key = $db->initKey("test", "users", 2345);
    $status = $db->remove($key);
    $key = $db->initKey("test", "users", 3456);
    $status = $db->remove($key);
    $key = $db->initKey("test", "users", 4567);
    $status = $db->remove($key);
    if ($status === Aerospike::OK) {
        echo success();
    } else {
        echo standard_fail($db);
    }
    if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

    echo colorize("De-Registering the UDF module ≻", 'black', true);
    $start = __LINE__;
    $status = $db->deregister("example_scan_udf.lua");
    if ($status === Aerospike::OK) {
        echo success();
    } else {
        echo standard_fail($db);
    }
    if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);
}
$db->close();
?>

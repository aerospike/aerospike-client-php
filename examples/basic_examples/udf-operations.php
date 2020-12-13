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
    echo("php udf-operations.php [-h<HOST IP ADDRESS>|--host=<HOST IP ADDRESS> -p<HOST PORT NUMBER>|--port=<HOST PORT NUMBER> -a|--annotate -c|--clean]\n");
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

echo colorize("Registering the UDF module ≻", 'black', true);
$start = __LINE__;
$copied = copy(__DIR__.'/lua/example_record_udf.lua', ini_get('aerospike.udf.lua_user_path').'/example_record_udf.lua');
if (!$copied) {
    echo fail("Could not copy the local lua/example_record_udf.lua to ". ini_get('aerospike.udf.lua_user_path'));
}
$status = $db->register(ini_get('aerospike.udf.lua_user_path').'/example_record_udf.lua', "example_record_udf.lua");
if ($status === Aerospike::OK) {
    echo success();
} elseif ($status === Aerospike::ERR_UDF_NOT_FOUND) {
    echo fail("Could not find the udf file lua/example_record_udf.lua");
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

// display the Lua code of the UDF module
$lua = file_get_contents('lua/example_record_udf.lua');
echo colorize($lua, 'purple', false);

echo colorize("Writing a record to test.characters at PK=4 with age set to (int) 30 ≻", 'black', true);
$start = __LINE__;
$key = $db->initKey("test", "characters", 4);
$put_vals = array("email" => "freudian.circuits@hal-inst.org", "name" => "Perceptron", "age" => 30);
$status = $db->put($key, $put_vals);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Applying a simple UDF on the record with PK=4 to increment bin 'age' by 10 ≻", 'black', true);
$start = __LINE__;
$status = $db->apply($key, "example_record_udf", "bin_age_add_10");
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Selecting the age from the record ≻", 'black', true);
$start = __LINE__;
$status = $db->get($key, $record, array("age"));
if ($status === Aerospike::OK) {
    echo success();
    echo "The value of age is ".$record['bins']['age']."\n";
} elseif ($status === Aerospike::ERR_RECORD_NOT_FOUND) {
    echo fail("Could not find a user with PK={$key['key']} in the set test.characters");
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Applying a UDF with arguments and return value on the record with PK=4 to update age and return the new age ≻", 'black', true);
$start = __LINE__;
$status = $db->apply($key, "example_record_udf", "bin_transform", array("age", 2, 20), $ret_val);
if ($status === Aerospike::OK) {
    echo success();
    var_dump($ret_val);
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Selecting the age from the record ≻", 'black', true);
$start = __LINE__;
$status = $db->get($key, $record, array("age"));
if ($status === Aerospike::OK) {
    echo success();
    echo "The value of age is ".$record['bins']['age']."\n";
} elseif ($status === Aerospike::ERR_RECORD_NOT_FOUND) {
    echo fail("Could not find a user with PK={$key['key']} in the set test.characters");
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Deregistering the UDF module ≻", 'black', true);
$start = __LINE__;
$status = $db->deregister("example_record_udf.lua");
if ($status === Aerospike::OK) {
    echo success();
} elseif ($status === Aerospike::ERR_UDF_NOT_FOUND) {
    echo fail("Could not find the udf module example_record_udf");
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

if (isset($args['c']) || isset($args['clean'])) {
    $start = __LINE__;
    echo colorize("Removing the record ≻", 'black', true);
    $status = $db->remove($key);
    if ($status === Aerospike::OK) {
        echo success();
    } else {
        echo standard_fail($db);
    }
    if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);
}

$db->close();
?>

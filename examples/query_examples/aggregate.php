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
    echo("php simple.php [-h<HOST IP ADDRESS>|--host=<HOST IP ADDRESS> -p<HOST PORT NUMBER>|--port=<HOST PORT NUMBER> -a|--annotate -c|--clean]\n");
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
$copied = copy(__DIR__.'/lua/example_aggregate_udf.lua', ini_get('aerospike.udf.lua_user_path').'/example_aggregate_udf.lua');
if (!$copied) {
    echo fail("Could not copy the local lua/example_aggregate_udf.lua to ". ini_get('aerospike.udf.lua_user_path'));
}
$status = $db->register(ini_get('aerospike.udf.lua_user_path').'/example_aggregate_udf.lua', "example_aggregate_udf.lua");
if ($status === Aerospike::OK) {
    echo success();
} elseif ($status === Aerospike::ERR_UDF_NOT_FOUND) {
    echo fail("Could not find the udf file ".ini_get('aerospike.udf.lua_user_path').'/example_aggregate_udf.lua');
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

// display the Lua code of the UDF module
$lua = file_get_contents(ini_get('aerospike.udf.lua_user_path').'/example_aggregate_udf.lua');
echo colorize($lua, 'purple', false);

echo colorize("Creating a secondary index on the 'age' bin of test.characters ≻", 'black', true);
$start = __LINE__;
$status = $db->addIndex("test", "characters", "age", "age_index", Aerospike::INDEX_TYPE_DEFAULT, Aerospike::INDEX_NUMERIC);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Writing records in test.characters with PKs=4-10 ≻", 'black', true);
$start = __LINE__;
$characters = array("Dr. Perceptron" => array('HAL Institute', 162),
    "Philip J Fry" => array('Planet Express', 40),
    "Tarunga Leela" => array('Planet Express', 39),
    "Dr. Amy Wong" => array('Planet Express', 34),
    "Prof. Hubert J Farnsworth" => array('Planet Express', 173),
    "Bender Bending Rodriguez" => array('Planet Express', 1055));
$i = 4;
foreach ($characters as $name => $info) {
    $key = $db->initKey("test", "characters", $i++);
    $put_vals = array("name" => $name, "workplace" => $info[0], "age" => $info[1]);
    $status = $db->put($key, $put_vals);
    if ($status === Aerospike::OK) {
        echo success();
    } else {
        echo standard_fail($db);
    }
}

if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Applying a stream UDF to records with ages between 0 and 200 using aggregate()≻", 'black', true);
$start = __LINE__;
$where = $db->predicateBetween("age", 0, 200);
$status = $db->aggregate("test", "characters", $where, "example_aggregate_udf", "group_count", array("workplace"), $result);
if($status !== Aerospike::OK) {
    echo standard_fail($db);
} else {
    echo success();
    echo "Counts of characters in their first two centuries, grouped by workplace:\n";
    var_dump($result);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);


echo colorize("Showing characters WHERE ages between 20 and 200 AND workplace='Planet Express' ≻", 'black', true);
$start = __LINE__;
$where = $db->predicateBetween("age", 20, 200);
$status = $db->aggregate("test", "characters", $where, "example_aggregate_udf", "ages", array('workplace', 'Planet Express'), $result);
if($status !== Aerospike::OK) {
    echo standard_fail($db);
} else {
    echo success();
    echo "The characters expressed as name => age pairs:\n";
    var_dump($result);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

if (isset($args['a']) || isset($args['clean'])) {
    $start = __LINE__;
    echo colorize("Removing the records ≻", 'black', true);
    for ($i = 4; $i <= 10; $i++) {
        $key = $db->initKey("test", "characters", $i);
        $db->remove($key);
    }
    if ($status === Aerospike::OK) {
        echo success();
    } else {
        echo standard_fail($db);
    }
    if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

    $start = __LINE__;
    echo colorize("Dropping the index ≻", 'black', true);
    $status = $db->dropIndex("test", "age_index");
    if ($status === Aerospike::OK) {
        echo success();
    } else {
        echo standard_fail($db);
    }
    if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

    echo colorize("De-Registering the UDF module ≻", 'black', true);
    $start = __LINE__;
    $status = $db->deregister("example_aggregate_udf.lua");
    if ($status === Aerospike::OK) {
        echo success();
    } else {
        echo standard_fail($db);
    }
    if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);
}
$db->close();
?>

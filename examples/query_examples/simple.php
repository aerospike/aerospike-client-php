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

echo colorize("Ensuring that records are put at test.characters with PKs=4-10 ≻", 'black', true);
$start = __LINE__;
$characters = array("freudian.circuits@hal-inst.org" => 162,
    "philip.j.fry@planet-express.com" => 40,
    "tarunga.leela@planet-express.com" => 39,
    "amy.wong@planet-express.com" => 34,
    "hubert.j.farnsworth@planet-express.com" => 173,
    "bender.bending.rodriguez@planet-express.com" => 1055);
$i = 4;
foreach ($characters as $email => $age) {
    $key = $db->initKey("test", "characters", $i++);
    $put_vals = array("email" => $email, "age" => $age);
    $status = $db->put($key, $put_vals);
    if ($status === Aerospike::OK) {
        echo success();
    } else {
        echo standard_fail($db);
    }
}

if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Creating a secondary index on the 'age' bin of test.characters ≻", 'black', true);
$start = __LINE__;
$status = $db->addIndex("test", "characters", "age", "test_character_age_idx", Aerospike::INDEX_TYPE_DEFAULT, Aerospike::INDEX_NUMERIC);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

sleep(1);
echo colorize("Querying records with ages between 0 and 99≻", 'black', true);
$start = __LINE__;
$total = 0;
$not_centenarian = 0;
$where = $db->predicateBetween("age", 0, 99);
$status = $db->query("test", "characters", $where, function ($record) use (&$total, &$not_centenarian) {
    $total += (int) $record['bins']['age'];
    $not_centenarian++;
}, array("email", "age"));
if ($status === Aerospike::OK) {
    echo success();
    echo "\nThe average age of employees who aren't centenarians is ".round($total / $not_centenarian)."\n";
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

if (isset($args['a']) || isset($args['clean'])) {
    $start = __LINE__;
    echo colorize("Removing the records ≻", 'black', true);
    for ($i = 4; $i < 10; $i++) {
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
}
$db->close();
?>

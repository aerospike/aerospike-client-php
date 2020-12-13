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
    echo "php rec-operations.php [-hHOST] [-pPORT] [-a] [-c]\n";
    echo " or\n";
    echo "php rec-operations.php [--host=HOST] [--port=PORT] [--annotate] [--clean]\n";
    exit(1);
}
$HOST_ADDR = (isset($args["h"])) ? (string) $args["h"] : ((isset($args["host"])) ? (string) $args["host"] : "localhost");
$HOST_PORT = (isset($args["p"])) ? (integer) $args["p"] : ((isset($args["port"])) ? (string) $args["port"] : 3000);

echo colorize("Connecting to Aerospike ≻", 'black', true);
$start = __LINE__;
$config = array("hosts" => array(array("addr" => $HOST_ADDR, "port" => $HOST_PORT)));
$db = new Aerospike($config, false);
if (!$db->isConnected()) {
    echo fail("Could not connect to host $HOST_ADDR:$HOST_PORT [{$db->errorno()}]: {$db->error()}");
    exit(1);
}
echo success();
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Creating a new record in test.characters at PK=1, only if none exists ≻", 'black', true);
$start = __LINE__;
$needs_force = false;
$key = $db->initKey("test", "characters", 1);
$put_vals = array(
    "title" => "Professor",
    "name" => "Hubert J. Farnsworth",
    "age" => 150,
    "is_senior" => true,
    "positions" => array("Inventor", "Founder", "Lecturer", "Scientist", "Hyper Yeti"));
$status = $db->put($key, $put_vals, 300, array(Aerospike::OPT_POLICY_EXISTS => Aerospike::POLICY_EXISTS_CREATE));
if ($status === Aerospike::OK) {
    echo success();
} elseif ($status === Aerospike::ERR_RECORD_EXISTS) {
    echo fail("There already is a record at PK={$key['key']} in the set test.characters");
    $needs_force = true;
} else {
    echo standard_fail($db);
    $needs_force = true;
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

if ($needs_force) {
    echo colorize("Retrying to insert a record in test.characters at PK=1 using Aerospike::POLICY_EXISTS_IGNORE ≻", 'black', true);
    $start = __LINE__;
    $status = $db->put($key, $put_vals, 300, array(Aerospike::OPT_POLICY_EXISTS => Aerospike::POLICY_EXISTS_IGNORE));
    if ($status === Aerospike::OK) {
        echo success();
    } else {
        echo standard_fail($db);
        exit(2);
    }
    if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);
}

echo colorize("Retrieving the record metadata ≻", 'black', true);
$start = __LINE__;
$status = $db->exists($key, $meta);
if ($status === Aerospike::OK) {
    echo success();
    var_dump($meta);
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Adding a bin to the record if Aerospike::POLICY_GEN_EQ ≻", 'black', true);
$start = __LINE__;
$current_gen = $meta["generation"];
$new_bin = array("eyesight"=>"bad");
$status = $db->put($key, $new_bin, 0, array(Aerospike::OPT_POLICY_GEN=>array(Aerospike::POLICY_GEN_EQ, $current_gen)));
if ($status === Aerospike::OK) {
    echo success();
} elseif ($status === Aerospike::ERR_RECORD_GENERATION) {
    echo fail("The record at PK={$key['key']} has been altered and has a newer generation");
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Updating the record using Aerospike::POLICY_EXISTS_UPDATE ≻", 'black', true);
$start = __LINE__;
$put_vals = array(
    "eyesight" => "bad",
    "age" => 160,
    "locations" => array(
        "Mars" => "Mars University",
        "Earth" => "Planet Express, New New York",
        "Near Death Star" => "Nobody Knows!"));
$status = $db->put($key, $put_vals, 300, array(Aerospike::OPT_POLICY_EXISTS => Aerospike::POLICY_EXISTS_UPDATE));
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Getting the record ≻", 'black', true);
$start = __LINE__;
$status = $db->get($key, $record);
if ($status === Aerospike::OK) {
    echo success();
    var_dump($record);
} elseif ($status === Aerospike::ERR_RECORD_NOT_FOUND) {
    echo fail("Could not find a user with PK={$key['key']} in the set test.characters");
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Touching the record ≻", 'black', true);
$start = __LINE__;
$status = $db->touch($key, 500);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Retrieving record metadata using its digest ≻", 'black', true);
$start = __LINE__;
$digest_key = $db->initKey($record['key']['ns'], $record['key']['set'], $record['key']['digest'], true);
$status = $db->exists($digest_key, $metadata);
if ($status === Aerospike::OK) {
    echo success();
    var_dump($metadata);
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

if (isset($args['c']) || isset($args['clean'])) {
    echo colorize("Removing the record ≻ ", 'black', true);
    $start = __LINE__;
    $status = $db->remove($key);
    if ($status === Aerospike::OK) {
        echo success();
    } else {
        echo standard_fail($db);
    }
    if ($status === Aerospike::OK) {
        echo success();
    } else {
        echo standard_fail($db);
    }
    if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);
}

$db->close();
?>

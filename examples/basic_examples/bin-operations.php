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
        "annotate",       /* Optionally annotate output with code */
        "clean",          /* Optionally clean up before leaving */
        "help",           /* Usage */
    );
    $options = getopt($shortopts, $longopts);
    return $options;
}

$args = parse_args();
if (isset($args["help"])) {
    echo "php bin-operations.php [-hHOST] [-pPORT] [-a] [-c]\n";
    echo " or\n";
    echo "php bin-operations.php [--host=HOST] [--port=PORT] [--annotate] [--clean]\n";
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

echo colorize("Writing a record in test.characters with PK=1234 ≻", 'black', true);
$start = __LINE__;
$digest = $db->getKeyDigest("test", "characters", 1234);
$key = $db->initKey("test", "characters", $digest, true);
$put_vals = array("email" => "freudian.circuits@hal-inst.org", "name" => "Perceptron");
$status = $db->put($key, $put_vals);
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

echo colorize("Prepending the string value 'Doctor ' value to the 'name' bin ≻", 'black', true);
$start = __LINE__;
$status = $db->prepend($key, 'name', 'Doctor ');
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Appending the string value ' MD' to the 'name' bin ≻", 'black', true);
$start = __LINE__;
$status = $db->append($key, 'name', ' MD');
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Selecting the modified 'name' bin of the record ≻", 'black', true);
$start = __LINE__;
$status = $db->get($key, $record, array('name'));
if ($status === Aerospike::OK) {
    echo success();
    var_dump($record);
} elseif ($status === Aerospike::ERR_RECORD_NOT_FOUND) {
    echo fail("Could not find a user with PK={$key['key']} in the set test.characters");
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Setting a 'patients_cured' bin on the record to (int) 3 using put() ≻", 'black', true);
$start = __LINE__;
$put_vals = array("patients_cured" => 3);
$status = $db->put($key, $put_vals);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Incrementing the 'patients_cured' bin by (int) -1 ≻", 'black', true);
$start = __LINE__;
$status = $db->increment($key, 'patients_cured', -1);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Selecting the bins 'email' and 'patients_cured' from the record ≻", 'black', true);
$start = __LINE__;
$status = $db->get($key, $record, array('email', 'patients_cured'));
if ($status === Aerospike::OK) {
    echo success();
    var_dump($record);
} elseif ($status === Aerospike::ERR_RECORD_NOT_FOUND) {
    echo fail("Could not find a user with PK={$key['key']} in the set test.characters");
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Performing multiple operations:\n", 'black', true);
echo colorize("  Adding a bin 'Occupation'\n", 'black', true);
echo colorize("  Incrementing the 'patients_cured' bin by (int) 4\n", 'black', true);
echo colorize("  Read bins 'Occupation' and 'patients_cured' ≻", 'black', true);
$start = __LINE__;
$operations = array(
    array("op" => Aerospike::OPERATOR_WRITE, "bin" => "Occupation", "val" => "Doctor of Freudian Circuit Analysis"),
    array("op" => Aerospike::OPERATOR_INCR, "bin" => "patients_cured", "val" => 4),
    array("op" => Aerospike::OPERATOR_READ, "bin" => "Occupation"),
    array("op" => Aerospike::OPERATOR_READ, "bin" => "patients_cured"));
$res = $db->operate($key, $operations, $returned);
if ($res === Aerospike::OK) {
    echo success();
    var_dump($returned);
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Removing the 'patients_cured' bin ≻", 'black', true);
$start = __LINE__;
$status = $db->removeBin($key, array('patients_cured'));
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Getting the full record ≻", 'black', true);
$start = __LINE__;
$res = $db->get($key, $record);
if ($res === Aerospike::OK) {
    echo success();
    var_dump($record);
} elseif ($res === Aerospike::ERR_RECORD_NOT_FOUND) {
    echo fail("Could not find a user with PK={$key['key']} in the set test.users");
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

if (isset($args['c']) || isset($args['clean'])) {
    $start = __LINE__;
    echo colorize("Removing the record ≻", 'black', true);
    $res = $db->remove($key);
    if ($res === Aerospike::OK) {
        echo success();
    } else {
        echo standard_fail($db);
    }
    if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);
}

$db->close();
?>

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
    $shortopts .= "w::";  /* Optionally write every Nth operation */
    $shortopts .= "n::";  /* Optionally number of read-write operations */

    $longopts  = array(
        "host::",         /* Optional host */
        "port::",         /* Optional port */
        "write-every::",    /* Optionally write every Nth operation */
        "num-ops::",        /* Optionally number of read-write operations */
        "help",           /* Usage */
    );
    $options = getopt($shortopts, $longopts);
    return $options;
}

$args = parse_args();
if (isset($args["help"])) {
    echo "php read-write-mix.php [-hHOST] [-pPORT] [-wWRITE EVERY Nth RECORD] [-nRECORDS]\n";
    echo " or\n";
    echo "php read-write-mix.php [--host=HOST] [--port=PORT] [--write-every=WRITE EVERY Nth RECORD] [--num-ops=RECORDS]\n";
    exit(1);
}
$addr = (isset($args["h"])) ? (string) $args["h"] : ((isset($args["host"])) ? (string) $args["host"] : "localhost");
$port = (isset($args["p"])) ? (integer) $args["p"] : ((isset($args["port"])) ? (string) $args["port"] : 3000);
$total_ops = (isset($args["n"])) ? (integer) $args["n"] : ((isset($args["num-ops"])) ? (string) $args["num-ops"] : 100000);
$write_every = (isset($args["w"])) ? (integer) $args["w"] : ((isset($args["write-every"])) ? (string) $args["write-every"] : 10);

echo colorize("Connecting to the host ≻", 'black', true);
$config = array("hosts" => array(array("addr" => $addr, "port" => $port)));
$db = new Aerospike($config, false);
if (!$db->isConnected()) {
    echo fail("Could not connect to host $addr:$port [{$db->errorno()}]: {$db->error()}");
    exit(1);
}
echo success();

echo colorize("Clear out the record that may exist at test.performance with PK=1 ≻", 'black', true);
$key = $db->initKey("test", "performance", "read-write");
$res = $db->remove($key);
if ($res === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}

$write_fails = 0;
$writes = 1;
$reads = 0;
$read_fails = 0;
$kv = array("v" => 1);
echo colorize("Initialize the record used for the serial put/get performance test ≻", 'black', true);
$begin = microtime(true);
$res = $db->put($key, $kv);
if ($res === Aerospike::OK) {
    echo success();
} else {
    echo fail("Could not initialize the test bin [{$db->errorno()}]: {$db->error()}");
    exit(2);
}

echo colorize("Run the mixed read/write performance test ≻", 'black', true);
for ($num_ops = 1; $num_ops < $total_ops; $num_ops++) {
    if (($num_ops % $write_every) === 0) {
        $kv['v']++;
        $res = $db->put($key, $kv);
        $writes++;
        if ($res != Aerospike::OK) {
            $write_fails++;
            // roll back the test value
            $kv['v']--;
        }
    } else {
        $res = $db->get($key, $r);
        $reads++;
        if ($res != Aerospike::OK) {
            $read_fails++;
        } elseif ($r['bins']['v'] != $kv['v']) {
            $read_fails++;
        }
    }
}
$end = microtime(true);
if (($read_fails === 0) && ($write_fails === 0)) {
    echo success();
} else {
    echo standard_fail($db);
}

echo colorize("$num_ops serial operations, $reads reads, $writes writes (every $write_every records)\n", 'green', true);
$color = ($read_fails > 0) ? 'red' : 'green';
echo colorize("Failed reads: $read_fails\n", $color, true);
$color = ($write_fails > 0) ? 'red' : 'green';
echo colorize("Failed writes: $write_fails\n", $color, true);
$delta = $end - $begin;
$tps = ($num_ops / $delta);
echo colorize("Total time: {$delta}s TPS:$tps\n", 'purple', true);

$db->close();
?>

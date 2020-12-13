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
    $shortopts .= "n::";  /* Optionally number of read operations */

    $longopts  = array(
        "host::",         /* Optional host */
        "port::",         /* Optional port */
        "num-ops::",        /* Optionally number of read operations */
        "help",           /* Usage */
    );
    $options = getopt($shortopts, $longopts);
    return $options;
}

$args = parse_args();
if (isset($args["help"])) {
    echo "php read.php [-hHOST] [-pPORT] [-nRECORDS]\n";
    echo " or\n";
    echo "php read.php [--host=HOST] [--port=PORT] [--num-ops=RECORDS]\n";
    exit(1);
}
$addr = (isset($args["h"])) ? (string) $args["h"] : ((isset($args["host"])) ? (string) $args["host"] : "localhost");
$port = (isset($args["p"])) ? (integer) $args["p"] : ((isset($args["port"])) ? (string) $args["port"] : 3000);
$total_ops = (isset($args["n"])) ? (integer) $args["n"] : ((isset($args["num-ops"])) ? (string) $args["num-ops"] : 100000);

echo colorize("Connecting to the host ≻", 'black', true);
$config = array("hosts" => array(array("addr" => $addr, "port" => $port)));
$db = new Aerospike($config, false);
if (!$db->isConnected()) {
    echo fail("Could not connect to host $addr:$port [{$db->errorno()}]: {$db->error()}");
    exit(1);
}
echo success();

echo colorize("Assuming that write.php was run before to create $total_ops records in test.performance\n", 'black', false);
$key = $db->initKey("test", "performance", "write");
$v = 0;
$reads = 0;
$read_fails = 0;
$begin = microtime(true);

echo colorize("Read $total_ops records ≻", 'black', true);
for ($num_ops = 1; $num_ops <= $total_ops; $num_ops++) {
    $v++;
    $key['key'] = 'write-'.$v;
    $res = $db->get($key, $record);
    $reads++;
    if ($res !== Aerospike::OK) {
        $read_fails++;
        $v--;
    }
}
$end = microtime(true);
if ($read_fails === 0) {
    echo success();
} else {
    echo standard_fail($db);
}

echo colorize("$reads sequential reads\n", 'green', true);
$color = ($read_fails > 0) ? 'red' : 'green';
echo colorize("Failed reads: $read_fails\n", $color, true);
$delta = $end - $begin;
$tps = ($num_ops / $delta);
echo colorize("Total time: {$delta}s TPS:$tps\n", 'purple', true);

$db->close();
?>

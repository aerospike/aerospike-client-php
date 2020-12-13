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
    global $argv;
    $options = getopt($shortopts, $longopts);
    for ($i = 0; $i <= count($options); $i++) {
        array_shift($argv);
    }
    $options['_args'] = $argv;
    return $options;
}

$args = parse_args();
if (isset($args["help"])) {
    echo "php rw-worker.php [-hHOST] [-pPORT] [-wWRITE EVERY Nth RECORD] [-nRECORDS] LOGFILE\n";
    echo " or\n";
    echo "php rw-worker.php [--host=HOST] [--port=PORT] [--write-every=WRITE EVERY Nth RECORD] [--num-ops=RECORDS] LOGFILE\n";
    exit(1);
}
$addr = (isset($args["h"])) ? (string) $args["h"] : ((isset($args["host"])) ? (string) $args["host"] : "localhost");
$port = (isset($args["p"])) ? (integer) $args["p"] : ((isset($args["port"])) ? (string) $args["port"] : 3000);
$total_ops = (isset($args["n"])) ? (integer) $args["n"] : ((isset($args["num-ops"])) ? (string) $args["num-ops"] : 100000);
$write_every = (isset($args["w"])) ? (integer) $args["w"] : ((isset($args["write-every"])) ? (string) $args["write-every"] : 10);
$log = $args["_args"][0];
$pid = getmypid();
echo "rw-worker.php launched with process ID $pid\n";

$config = array("hosts" => array(array("addr" => $addr, "port" => $port)));
$db = new Aerospike($config, false);
if (!$db->isConnected()) {
    file_put_contents($log, "$pid|0|0|0|0|0|0|0\n", FILE_APPEND);
    exit(1);
}

$key = $db->initKey("test", "rw-performance", $pid);
$writes = 0;
$write_fails = 0;
$reads = 0;
$read_fails = 0;
$begin = microtime(true);
$kv = array('v'=>0);
$res = $db->put($key, $kv);
if ($res !== Aerospike::OK) {
    $end = microtime(true);
    $delta = $end - $begin;
    $tps = (1 / $delta);
    file_put_contents($log, "$pid|1|$delta|$tps|0|0|1|1\n", FILE_APPEND);
    exit(2);
}
$writes++;

for ($num_ops = 1; $num_ops < $total_ops; $num_ops++) {
    if (($num_ops % $write_every) === 0) {
        $kv['v'] = $num_ops;
        $res = $db->put($key, $kv);
        $writes++;
        if ($res !== Aerospike::OK) {
            $write_fails++;
        }
    } else {
        $res = $db->get($key, $r);
        $reads++;
        if ($res !== Aerospike::OK) {
            $read_fails++;
        }
    }
}
$end = microtime(true);
$delta = $end - $begin;
$tps = ($num_ops / $delta);
file_put_contents($log, "$pid|$num_ops|$delta|$tps|$reads|$read_fails|$writes|$write_fails\n", FILE_APPEND);
$db->close();
?>

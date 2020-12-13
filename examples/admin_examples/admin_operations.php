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
$config = array("hosts" => array(array("addr" => $HOST_ADDR, "port" => $HOST_PORT)), "user"=>"admin", "pass"=>"admin");
$db = new Aerospike($config, false);
if (!$db->isConnected()) {
    echo fail("Could not connect to host $HOST_ADDR:$HOST_PORT [{$db->errorno()}]: {$db->error()}");
    exit(1);
}
echo success();
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Creating a user ≻", 'black', true);
$start = __LINE__;
$status = $db->createUser("example_user", "example_pass", array("read"));
sleep(1);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Creating a role ≻", 'black', true);
$start = __LINE__;
$status = $db->createRole("example_role", array(array("code" => Aerospike::PRIV_READ_WRITE, "ns" => "test", "set" => "demo")));
sleep(1);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Query a role before grant privileges ≻", 'black', true);
$start = __LINE__;
$status = $db->queryRole("example_role", $privileges);
sleep(1);
if ($status === Aerospike::OK) {
    echo success();
    var_dump($privileges);
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Grant privileges to a role ≻", 'black', true);
$start = __LINE__;
$status = $db->grantPrivileges("example_role", array(array("code"=>Aerospike::PRIV_READ_WRITE_UDF)));
sleep(1);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Query a role after grant privileges ≻", 'black', true);
$start = __LINE__;
$status = $db->queryRole("example_role", $privileges);
sleep(1);
if ($status === Aerospike::OK) {
    echo success();
    var_dump($privileges);
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Revoke privileges from a role ≻", 'black', true);
$start = __LINE__;
$status = $db->revokePrivileges("example_role", array(array("code"=>Aerospike::PRIV_READ_WRITE_UDF)));
sleep(1);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Query all roles after revoking privileges ≻", 'black', true);
$start = __LINE__;
$status = $db->queryRoles($privileges);
sleep(1);
if ($status === Aerospike::OK) {
    echo success();
    var_dump($privileges);
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Grant the role to a user ≻", 'black', true);
$start = __LINE__;
$status = $db->grantRoles("example_user", array("example_role"));
sleep(1);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Query the user ≻", 'black', true);
$start = __LINE__;
$status = $db->queryUser("example_user", $roles);
sleep(1);
if ($status === Aerospike::OK) {
    echo success();
    var_dump($roles);
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Revoke role from the user ≻", 'black', true);
$start = __LINE__;
$status = $db->revokeRoles("example_user", array("example_role"));
sleep(1);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Query all users ≻", 'black', true);
$start = __LINE__;
$status = $db->queryUsers($roles);
sleep(1);
if ($status === Aerospike::OK) {
    echo success();
    var_dump($roles);
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Drop the role ≻", 'black', true);
$start = __LINE__;
$status = $db->dropRole("example_role");
sleep(1);
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

echo colorize("Dropping the user ≻", 'black', true);
$start = __LINE__;
$status = $db->dropUser("example_user");
if ($status === Aerospike::OK) {
    echo success();
} else {
    echo standard_fail($db);
}
if (isset($args['a']) || isset($args['annotate'])) display_code(__FILE__, $start, __LINE__);

$db->close();
?>

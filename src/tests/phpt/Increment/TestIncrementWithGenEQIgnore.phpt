--TEST--
Increment with generation policy POLICY_GEN_IGNORE

--FILE--
<?php
include dirname(__FILE__)."/../../astestframework/astest-phpt-loader.inc";
aerospike_phpt_runtest("Increment", "testIncrementWithGenEQIgnore");
--EXPECT--
OK


--TEST--
Aggregate - correct arguments with policy

--SKIPIF--
<?php include('skip_zts.inc'); ?>
--FILE--
<?php
include dirname(__FILE__)."/../../astestframework/astest-phpt-loader.inc";
aerospike_phpt_runtest("Aggregate", "testAggregatePositiveWithPolicy");
--EXPECT--
OK

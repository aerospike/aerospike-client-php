--TEST--
 Check if passed a array as parameter, proper geojson object is returned.

--FILE--
<?php
include dirname(__FILE__)."/../../astestframework/astest-phpt-loader.inc";
aerospike_phpt_runtest("Geospatial", "fromArray");
--EXPECT--
OK

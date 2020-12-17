<?php
class Info extends AerospikeTestCommon
{
    private $test_host;

    protected function setUp() {
        $config = get_as_config();
        $this->test_host = $config['hosts'][0];
        $this->db = new Aerospike($config);
        if (!$this->db->isConnected()) {
            return $this->db->errorno();
        }
    }

    /**
     * @test
     * Basic INFO without parameter
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     *
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoNoParameter()
    {
        try {
            return $this->db->info();
        } catch(ErrorException $e) {
            return $this->db->errorno();
        }
    }
    /**
     * @test
     * Basic Info positive for bins
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Info for bins of namespace
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForBins()
    {
        $status = $this->db->info('bins/test', $response, $this->test_host);
        if(!empty($response))
            return $status;
        return Aerospike::ERR_CLIENT;
    }
    /**
     * @test
     * Basic Info positive for build
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Info for build
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForBuild()
    {
        try {
            $status = $this->db->info('build', $response, $this->test_host);
            if(!empty($response))
                return $status;
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
        return Aerospike::ERR_CLIENT;
    }
    /**
     * @test
     * Basic Info positive for config with response an array
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Info for config
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForConfig()
    {
        $response = array();
        try {
            $status = $this->db->info('get-config:context=namespace;id=test', $response, $this->test_host);
            if(!empty($response))
                return $status;
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
        return Aerospike::ERR_CLIENT;
    }

    /**
     * @test
     * Basic Info positive for jobs
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Info for all jobs
     *
     * @remark
     * 
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForJobs()
    {
        try {
            $status = $this->db->info('jobs:', $response, $this->test_host);
            if(!empty($response))
                return $status;
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
        return Aerospike::ERR_CLIENT;
    }
    /**
     * @test
     * Basic Info positive for jobs with module scan and response is string
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Info for scan job
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForJobsScan()
    {
        $response = "";
        try {
            $status = $this->db->info('jobs:module=scan', $response, $this->test_host);
            if(!empty($response))
                return $status;
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
        return Aerospike::ERR_CLIENT;
    }
    /**
     * @test
     * Basic Info positive for latency
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * info for latency
     *
     * @remark
     * 
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForLatency()
    {
        try {
            $status = $this->db->info('latencies:hist=reads;slice=30;back=300;duration:120', $response, $this->test_host);
            if(!empty($response))
                return $status;
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
        return Aerospike::ERR_CLIENT;
    }
    /**
     * @test
     * Basic Info positive for log and response is null
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * info for particular log
     *
     * @remark
     * 
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForLog()
    {
        $response = NULL;
        try {
            $status = $this->db->info('log/0', $response, $this->test_host);
            if(!empty($response))
                return $status;
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
        return Aerospike::ERR_CLIENT;
    }
    /**
     * @test
     * Basic Info positive for all logs
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * info for all log files
     *
     * @remark
     * 
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForAllLogs()
    {
        try {
            $status = $this->db->info('logs', $response, $this->test_host);
            if(!empty($response))
                return $status;
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
        return Aerospike::ERR_CLIENT;
    }
    /**
     * @test
     * Basic Info positive for mcast
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * info for mcast
     *
     * @remark
     * 
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForMcast()
    {
        try {
            $status = $this->db->info('mcast', $response, $this->test_host);
            if(!empty($response))
                return $status;
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
        return Aerospike::ERR_CLIENT;
    }
    /**
     * @test
     * Basic Info positive for mesh
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * info for mesh
     *
     * @remark
     * 
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForMesh()
    {
        try {
            $status = $this->db->info('mesh', $response, $this->test_host);
            return $status;
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
    }
    /**
     * @test
     * Basic Info positive for namespaces
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Information about a particular namespace
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForNamespace()
    {
        try {
            $status = $this->db->info('namespace/test', $response, $this->test_host);
            if(!empty($response))
                return $status;
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
        return Aerospike::ERR_CLIENT;
    }
    /**
     * @test
     * Basic Info positive for nodes
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Info about all nodes in system
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForNode()
    {
        try {
            $status = $this->db->info('node', $response, $this->test_host);
            if(!empty($response))
                return $status;
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
        return Aerospike::ERR_CLIENT;
    }
    /**
     * @test
     * Basic Info negative with incorrect host name
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Error
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoNegativeForIncorrectHost()
    {
        $host = array("addr"=>"localhost2", "port"=>3000);
        try {
            return $this->db->info('node', $response, $host);
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
    }
    /**
     * @test
     * Basic Info negative with incorrect command
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Error
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoNegativeForIncorrectCommand()
    {
        try {
            $status = $this->db->info('abcd', $response, $this->test_host);
            if ($status == Aerospike::OK) {
                if($response==NULL) {
                    return Aerospike::OK;
                }
            }
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
    }
    /**
     * @test
     * Basic Info negative with empty command
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Error
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoNegativeForEmptyCommand()
    {
        $status = $this->db->info('', $response, $this->test_host);
        return $status;
    }
    /**
     * @test
     * Basic Info negative with incorrect port no
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Error
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoNegativeForIncorrectPort()
    {
        $host = array("addr"=>AEROSPIKE_CONFIG_NAME, "port"=>3001);
        try {
            return $this->db->info('node', $response, $host, array(Aerospike::OPT_READ_TIMEOUT=>10));
        } catch (ErrorException $e) {
            return $this->db->errorno();
        }
    }
    /**
     * @test
     * Basic Info positive with 2 arguments
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Error
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForTwoArguments()
    {
        try {
            $status = $this->db->info('build', $response);
            if(!empty($response))
                return $status;
            }  catch (ErrorException $e) {
            return $this->db->errorno();
            }
        return Aerospike::ERR_CLIENT;
    }
    /**
     * @test
     * Basic Info positive with policy argument
     *
     * @pre
     * Connect using aerospike object to the specified node
     *
     * @post
     * Info about build
     *
     * @remark
     *
     *
     * @test_plans{1.1}
     */
    function testInfoPositiveForPolicyArgument()
    {
        try {
            $status = $this->db->info('build', $response, $this->test_host, array(Aerospike::OPT_READ_TIMEOUT => 2000));
            if(!empty($response))
                return $status;
            }
        catch (ErrorException $e) {
            return $this->db->errorno();
        }
        return Aerospike::ERR_CLIENT;
    }
}
?>

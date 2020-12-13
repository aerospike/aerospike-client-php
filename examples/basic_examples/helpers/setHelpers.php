<?php

/*
 * FUNCTION TO PARSE COMMAND LINE ARGS TO SCRIPT
 */

function parse_args() {
    $shortopts  = "";
    $shortopts .= "h::";  /* Optional host */
    $shortopts .= "p::";  /* Optional port */

    $longopts  = array(
        "host::",         /* Optional host */
        "port::",         /* Optional port */
        "help",           /* Usage */
    );

    $options = getopt($shortopts, $longopts);
    return $options;
}


    $args = parse_args();

    if (isset($args["help"])) {
        echo("php setHelpers.php [-h<HOST IP ADDRESS>|--host=<HOST IP ADDRESS> -p<HOST PORT NUMBER>|--port=<HOST PORT NUMBER>]\n");
        exit(1);
    }

    $HOST_ADDR = (isset($args["h"])) ? (string) $args["h"] : ((isset($args["host"])) ? (string) $args["host"] : "localhost");
    $HOST_PORT = (isset($args["p"])) ? (integer) $args["p"] : ((isset($args["port"])) ? (string) $args["port"] : 3000);


    /*----------------------------------------------------------------------------------------------------------------------------
     * SET LOG LEVEL USAGE:
     *      public void Aerospike::setLogLevel ( int $log_level )
     * SET LOG HANDLER USAGE:
     *      public static void Aerospike::setLogHandler ( callback $log_handler )
     *          where callback must follow the signature:
     *              public function log_handler ( int $level, string $file, string $function, int $line )
     * SET SERIALIZER USAGE:
     *      public static void Aerospike::setSerializer ( callback $serialize_cb )
     *          where callback method must follow the signature:
     *              public function string aerodb_serialize ( mixed $value )
     * SET DESERIALIZER USAGE:
     *      public static void Aerospike::setDeserializer ( callback $unserialize_cb )
     *          where callback method must follow the signature:
     *              public function string aerodb_deserialize ( mixed $value )
     *----------------------------------------------------------------------------------------------------------------------------
     */

    /*
     * EXAMPLE 1: SET LOG LEVEL AND LOG HANDLER CALLBACK
     */

    $config = array("hosts"=>array(array("addr"=>$HOST_ADDR, "port"=>$HOST_PORT)));
    $db = new Aerospike($config, 'prod-db');
    if (!$db->isConnected()) {
        echo "Aerospike failed to connect to host $HOST_ADDR:$HOST_PORT [{$db->errorno()}]: {$db->error()}\n";
        $db->close();
        exit(1);
    } else {
        echo "Aerospike connection to host $HOST_ADDR:$HOST_PORT successful\n";
        $db->setLogLevel(Aerospike::LOG_LEVEL_DEBUG);
        $db->setLogHandler(function ($level, $file, $function, $line) {
            switch ($level) {
                case Aerospike::LOG_LEVEL_ERROR:
                    $lvl_str = 'ERROR';
                break;
                case Aerospike::LOG_LEVEL_WARN:
                    $lvl_str = 'WARN';
                break;
                case Aerospike::LOG_LEVEL_INFO:
                    $lvl_str = 'INFO';
                break;
                case Aerospike::LOG_LEVEL_DEBUG:
                    $lvl_str = 'DEBUG';
                break;
                case Aerospike::LOG_LEVEL_TRACE:
                    $lvl_str = 'TRACE';
                break;
                default:
                    $lvl_str = '???';
            }
            error_log("[$lvl_str] in $function at $file:$line");
        });
    }

    /*
     * EXAMPLE 2: SET SERIALIZER AND DESERIALIZER TO STORE A PHP BLOB
     */

    if (!$db->isConnected()) {
        echo "Aerospike failed to connect to host INVALID_ADDR:INVALID_PORT [{$db->errorno()}]: {$db->error()}\n";
        $db->close();
        exit(1);
    } else {
        Aerospike::setDeserializer(function ($val) {
            $prefix = substr($val, 0, 3);
            if ($prefix !== 'r||') {
                return unserialize(substr ($val, 3));
            }
            return unserialize(substr ($val, 3));
        });

        Aerospike::setSerializer(function ($val) {
            if (is_bool ($val)) {
                return "b||". serialize($val);
            }
            if (is_object ($val)) {
                return "o||". serialize($val);
            }
            if (is_double ($val)) {
                return "d||". serialize($val);
            }
            // otherwise, mark it as raw
            return "r||". $val;
        });

        class edu_course {
            public $course_name;
            public $duration;
            
            function __construct($name, $years) {
                $this->course_name = $name;
                $this->duration = $years;
            }
        }

        $BTech = new edu_course("B.Tech", 4);

        $key = $db->initKey("test", "demo", "example_key");
        $put_vals = array("education" => array("degree"=>$BTech, 
                                               "score"=>99.99, 
                                               "passed"=>TRUE));
        // will ensure a record exists at the given key with the specified bins using the registered serializer callback
        $res = $db->put($key, $put_vals, 100, array(Aerospike::OPT_SERIALIZER => Aerospike::SERIALIZER_USER));
        if ($res === Aerospike::OK) {
            echo "Record written.\n";
            // get the record using the registered deserializer callback
            $res = $db->get($key, $record);
            if ($res === Aerospike::OK) {
                var_dump($record);
            } elseif ($res === Aerospike::ERR_RECORD_NOT_FOUND) {
                echo "A user with key ". $key['key']. " does not exist in the database\n";
            } else {
                echo "[{$db->errorno()}] ".$db->error();
            }
        } else {
            echo "[{$db->errorno()}] ".$db->error();
        }
    }
    $db->close();
?>

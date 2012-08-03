<?php
/**
 *
 */

require_once __DIR__ . "/autoload.php";
require_once __DIR__ . "/../vendor/spyc/spyc.php";

$tester = new \DeusTesting\TestDriver(__DIR__."/conf", param_present('-v'));
$tester->run_tests();

function param_present($param_key)
{
    global $argc, $argv;
    return array_search($param_key, $argv)!==false;
}
function get_param_value($param_key)
{
    global $argc, $argv;
    $param_value = null;
    if($param_key_index = array_search($param_key, $argv))
    {
        if(isset($argv[$param_key_index+1]))
        {
            $param_value = escapeshellarg($argv[$param_key_index+1]);
        }
        else
        {
            echo "Usage: No value found for param: '$param_key'\n";
            exit(1);
        }
    }
    return $param_value;
}
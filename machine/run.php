<?php
/**
 *
 */

require_once __DIR__ . "/autoload.php";
require_once __DIR__ . "/../vendor/spyc/spyc.php";

$verbosity_level = get_requested_verbosity();

$tester = new \MachinaTesting\TestDriver(__DIR__."/conf", $verbosity_level);
$tester->run_tests();
/**
 * Determine if the given $param_key exists in the cli command
 *
 * @param $param_key
 * @return bool
 */
function param_present($param_key)
{
    global $argc, $argv;
    return array_search($param_key, $argv)!==false;
}

/**
 * Utility to get the value for a given cli param key
 * i.e.
 * for the cli command:
 *     'php run.php -foo bar -v'
 *     get_param_value('-foo') would return 'bar'
 *
 *
 * @param $param_key
 * @return null|string
 */
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

/**
 * determine the requested verbosity in the CLI command
 *
 * @return int
 */
function get_requested_verbosity()
{
    $verbosity_level = 0;
    if(param_present('-v'))
    {
        $verbosity_level = 1;
    }
    else if(param_present('-vv'))
    {
        $verbosity_level = 2;
    }
    return $verbosity_level;
}
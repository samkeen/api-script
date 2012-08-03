<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sam
 * Date: 8/2/12
 * Time: 8:29 PM
 * To change this template use File | Settings | File Templates.
 */

require_once __DIR__ . "/autoload.php";
require_once __DIR__ . "/../vendor/spyc/spyc.php";

$tester = new \DeusTesting\TestDriver(__DIR__."/conf");
$tester->run_tests();
<?php
/**
 * experimenting with a script to canvert HAR into the DSL for this app's
 * yaml files
 */
$script_name = pathinfo($argv[0], PATHINFO_BASENAME);
if(count($argv)!=3)
{
    echo "Usage: php {$script_name} \"descriptive test comment\" \"`pbpaste`\"".PHP_EOL;
    exit(1);
}
$har_array = json_decode($argv[2], true);
$test_comment = $argv[1];
if(json_last_error()!='00000')
{
    echo "The output of `pbpaste` was not parsable JSON".PHP_EOL;
    echo "Output was: {$argv[2]}".PHP_EOL;
    exit(1);
}

$request  = $har_array['request'];
$response = $har_array['response'];
$resource = pathinfo($request['url'], PATHINFO_BASENAME);

$yaml = <<< YYYY
{$request['method']} {$resource}:
  comment: {$test_comment}

YYYY;

if(strtoupper($request['method'])=='POST')
{
    $post_data = json_decode($request['postData']['text'], true);
    foreach($post_data as $key => &$value)
    {
        $value = "    {$key}: {$value}";
    }
    $yaml .= "  creation_properties:".PHP_EOL.implode(PHP_EOL, $post_data).PHP_EOL;
}


echo ($yaml);


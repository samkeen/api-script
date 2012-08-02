<?php
namespace DeusTesting;

class GoTest extends BaseTestCase
{
    private static $service_manifests = array();

    public static function setUpBeforeClass()
    {
        // load YAML parser
        require __DIR__.'/../../vendor/spyc/spyc.php';
        // gather manifests
        $manifest_files = array_map('realpath', glob(__DIR__.'/../conf/service.*.yaml'));
        foreach($manifest_files as $manifest_file_path)
        {
            $match=null;
            preg_match('/\.(?P<service_name>[^\.]+)\.yaml$/', $manifest_file_path, $match);
            self::emit("Registering service: '{$match['service_name']}' (file: {$manifest_file_path})");
            self::$service_manifests[$match['service_name']] = \Spyc::YAMLLoad($manifest_file_path);
        }
    }


    function testGo()
    {
        foreach(self::$service_manifests as $service_name => $service_manifest)
        {
            $this->execute_tests($service_name, $service_manifest);
        }
    }

    private function execute_tests($service_name, $service_manifest)
    {
        self::emit("Running tests for: '{$service_name}'");
        foreach($service_manifest['tests'] as $test_name => $test_meta)
        {
            switch(strtolower($test_meta['method']))
            {
                case "get":
                    self::emit("Running GET Test: '{$test_name}' ({$test_meta['comment']})");
                    $this->get_all($test_meta['path']);
                    break;
                case "create-get":
                    self::emit("Running Create Then GET Test: '{$test_name}' ({$test_meta['comment']})");
                    $this->create_then_get_resource(
                        $test_meta['path'],
                        $test_meta['creation_properties'],
                        $test_meta['expected_properties']
                    );
                    break;
                case "post":
                    self::emit("Running POST Test: '{$test_name}' ({$test_meta['comment']})");
                    $this->post_resource(
                        $test_meta['path'],
                        $test_meta['creation_properties']
                    );
                    break;
                case "delete":
                    self::emit("Running DELETE Test: '{$test_name}' ({$test_meta['comment']})");
                    $this->delete_resource(
                        $test_meta['path'],
                        $test_meta['creation_properties']
                    );
                    break;
                case "patch":
                    self::emit("Running PATCH Test: '{$test_name}' ({$test_meta['comment']})");
                    $this->patch_resource(
                        $test_meta['path'],
                        $test_meta['creation_properties'],
                        $test_meta['patch_properties']
                    );
                    break;

            }
        }
        print_r($service_manifest);
    }

    private function get_all($path, array $expected_properties=array(), $empty_response_allowed=true)
    {
        $this->service(self::FENPHEN)
            ->assert_api_get(
            $path,
            $expected_properties,
            $empty_response_allowed
        );
    }

    private function create_then_get_resource($path, array $creation_properties, array $expected_properties=array())
    {
        $created_resource = $this->create_resource_for_testing($path, $creation_properties);
        $this->service(self::FENPHEN)
            ->assert_api_get(
            "{$path}/{$created_resource['id']}",
            $expected_properties,
            $empty_response_allowed=true,
            $expected_count = 1
        );
        $this->cleanup_resource($path, $created_resource);
    }

    private function post_resource($path, array $creation_properties)
    {
        $this->service(self::FENPHEN)
            ->assert_api_post(
            $path,
            $this->evaluate_property_values($creation_properties)
        );
    }

    private function delete_resource($path, $creation_properties)
    {
        $created_resource = $this->create_resource_for_testing($path, $creation_properties);
        $this->service(self::FENPHEN)
            ->assert_api_delete(
            "{$path}/{$created_resource['id']}"
        );
    }

    private function patch_resource($path, $creation_properties, $patch_properties)
    {
        $created_resource = $this->create_resource_for_testing($path, $creation_properties);
        $this->service(self::FENPHEN)
            ->assert_api_patch(
            "{$path}/{$created_resource['id']}",
            $this->evaluate_property_values($patch_properties)
        );
    }

    private function create_resource_for_testing($path, $creation_properties)
    {
        return $this->service(self::FENPHEN)
            ->assert_api_post(
            $path,
            $this->evaluate_property_values($creation_properties),
            $clean_up = false
        );
    }

    private static function emit($string)
    {
        echo trim($string).PHP_EOL;
    }
}
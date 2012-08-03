<?php
namespace DeusTesting;

class GoTest extends BaseTestCase
{
    private static $service_manifests = array();

    public static function setUpBeforeClass()
    {
        // gather manifests
        $manifest_files = array_map('realpath', glob(__DIR__.'/../conf/tests.*.yaml'));
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
            $path = $this->get_path_from_name($test_name);
            switch($this->get_method_from_name($test_name))
            {
                case "get":
                    if(isset($test_meta['creation_properties']))
                    {
                        $creation_properties = $this->get_blended_evaluated_creation_properties(
                            $service_manifest, $path, $test_meta
                        );
                        self::emit("Running Create Then GET Test: '{$test_name}' ({$test_meta['comment']})");
                        $this->create_then_get_resource(
                            $path,
                            $creation_properties,
                            $test_meta['expected_properties']
                        );
                    }
                    else
                    {
                        self::emit("Running GET Test: '{$test_name}' ({$test_meta['comment']})");
                        $this->get_all($path);
                    }
                    break;
                case "post":
                    self::emit("Running POST Test: '{$test_name}' ({$test_meta['comment']})");
                    $creation_properties = $this->get_blended_evaluated_creation_properties(
                        $service_manifest, $path, $test_meta
                    );
                    $this->post_resource(
                        $path,
                        $creation_properties
                    );
                    break;
                case "delete":
                    self::emit("Running DELETE Test: '{$test_name}' ({$test_meta['comment']})");
                    $creation_properties = $this->get_blended_evaluated_creation_properties(
                        $service_manifest, $path, $test_meta
                    );
                    $this->delete_resource(
                        $path,
                        $creation_properties
                    );
                    break;
                case "patch":
                    self::emit("Running PATCH Test: '{$test_name}' ({$test_meta['comment']})");
                    $creation_properties = $this->get_blended_evaluated_creation_properties(
                        $service_manifest, $path, $test_meta
                    );
                    $this->patch_resource(
                        $path,
                        $creation_properties,
                        $test_meta['patch_properties']
                    );
                    break;

                case "put":
                    self::emit("Running PUT Test: '{$test_name}' ({$test_meta['comment']})");
                    $creation_properties = $this->get_blended_evaluated_creation_properties(
                        $service_manifest, $path, $test_meta
                    );
                    $this->put_resource(
                        $path,
                        $creation_properties,
                        $test_meta['put_properties']
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

    private function put_resource($path, $creation_properties, $put_properties)
    {
        $created_resource = $this->create_resource_for_testing($path, $creation_properties);
        $put_properties = $this->evaluate_property_values($put_properties);
        $this->service(self::FENPHEN)
            ->assert_api_put(
            "{$path}/{$created_resource['id']}",
            array_merge($created_resource, $put_properties)
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
    private function get_method_from_name($test_name)
    {
        $match = null;
        preg_match('/^(?P<method>head|options|get|post|put|patch|delete)/i', $test_name, $match);
        if( ! isset($match['method']))
        {
            $this->fail("HTTP Method (head|options|get|post|put|patch|delete) not found at the beginning of test name: '{$test_name}'");
        }
        return strtolower($match['method']);
    }
    private function get_path_from_name($test_name)
    {
        $match = null;
        preg_match('/\s(?P<path>[^\s]+)$/i', $test_name, $match);
        if( ! isset($match['path']))
        {
            $this->fail("HTTP path not found at the end of test name: '{$test_name}'");
        }
        return "/".trim($match['path'], '/ ');
    }
    private function get_evaluated_creation_properties($default_creation_properties, $creation_properties)
    {
        return $this->evaluate_property_values(array_merge($default_creation_properties, $creation_properties));
    }
    private function get_blended_evaluated_creation_properties($service_manifest, $path, $test_meta)
    {
        return $this->get_evaluated_creation_properties(
            isset($service_manifest['resource_seeds'][$path])?$service_manifest['resource_seeds'][$path]:array(),
            isset($test_meta['creation_properties'])?$test_meta['creation_properties']:array()
        );
    }
}
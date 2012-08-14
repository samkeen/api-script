<?php
namespace MachinaTesting;
/**
 *
 */
class TestDriver
{
    private static $service_manifests = array();

    /**
     * @var int
     */
    private $start_time;
    private $test_engine = null;

    private $current_test_manifest;
    private $current_test_name;

    public function __construct($conf_files_directory, $verbosity_level=0)
    {
        $this->test_engine = new TestEngine($conf_files_directory, $verbosity_level);
        // gather manifests
        $manifest_files = glob("{$conf_files_directory}/tests.*.yaml");
        foreach($manifest_files as $manifest_file_path)
        {
            $match=null;
            preg_match('/\.(?P<service_name>[^\.]+)\.yaml$/', $manifest_file_path, $match);
            $this->test_engine->emit_comment("Registering service: '{$match['service_name']}' (file: {$manifest_file_path})");
            self::$service_manifests[$match['service_name']] = \Spyc::YAMLLoad($manifest_file_path);
            self::$service_manifests[$match['service_name']]['manifest_filename'] = pathinfo($manifest_file_path, PATHINFO_BASENAME);
        }
    }

    /**
     * Run all configured tests
     */
    function run_tests()
    {
        $this->start_time = time();
        foreach(self::$service_manifests as $service_name => $service_manifest)
        {
            $this->execute_service_tests($service_name, $service_manifest);
        }
        if($failures = $this->test_engine->get_failures())
        {
            $this->test_engine->emit_summary(PHP_EOL . PHP_EOL . "Failure Summary:");
            foreach($failures as $test_context => $failure_msg)
            {
                $failure_msg = $this->format_summary_error($failure_msg);
                $this->test_engine->emit_summary(PHP_EOL . "\t[{$test_context}]\t\"{$failure_msg}\"");
            }
        }
        if($errors = $this->test_engine->get_errors())
        {
            $this->test_engine->emit_summary(PHP_EOL . PHP_EOL . "Error Summary:");
            foreach($errors as $test_context => $error_msg)
            {
                $error_msg = $this->format_summary_error($error_msg);
                $this->test_engine->emit_summary(PHP_EOL . "\t[{$test_context}]\t\t\"{$error_msg}\"");
            }
        }
        $this->test_engine->emit_summary(PHP_EOL . "Run time: ".(time() - $this->start_time) . " seconds");
        $this->test_engine->emit_summary("Memory Usage: ".number_format(memory_get_peak_usage())." bytes");
    }

    private function execute_service_tests($service_name, $service_manifest)
    {
        $this->current_test_manifest = $service_manifest['manifest_filename'];
        $this->test_engine->emit_summary(PHP_EOL . "Running tests for: '{$service_name}'");
        $this->test_engine->service($service_name);
        $this->test_engine->emit_summary(PHP_EOL . "Found ".count($service_manifest['tests'])." tests");
        foreach($service_manifest['tests'] as $test_name => $test_meta)
        {
            $this->current_test_name = $test_name;
            $path = $this->get_path_from_name($test_name);
            switch($this->get_method_from_name($test_name))
            {
                case "get":
                    if(isset($test_meta['creation_properties']))
                    {
                        $creation_properties = $this->get_blended_evaluated_creation_properties(
                            $service_manifest, $path, $test_meta
                        );
                        $this->test_engine->emit_comment("Running Create Then GET Test: '{$test_name}' ({$test_meta['comment']})");
                        $this->create_then_get_resource(
                            $path,
                            $creation_properties,
                            $test_meta['expected_properties']
                        );
                    }
                    else
                    {
                        $this->test_engine->emit_comment("Running GET Test: '{$test_name}' ({$test_meta['comment']})");
                        $this->get_all($path);
                    }
                    break;
                case "post":
                    $this->test_engine->emit_comment("Running POST Test: '{$test_name}' ({$test_meta['comment']})");
                    $creation_properties = $this->get_blended_evaluated_creation_properties(
                        $service_manifest, $path, $test_meta
                    );
                    $this->post_resource(
                        $path,
                        $creation_properties
                    );
                    break;
                case "delete":
                    $this->test_engine->emit_comment("Running DELETE Test: '{$test_name}' ({$test_meta['comment']})");
                    $creation_properties = $this->get_blended_evaluated_creation_properties(
                        $service_manifest, $path, $test_meta
                    );
                    $this->delete_resource(
                        $path,
                        $creation_properties
                    );
                    break;
                case "patch":
                    $this->test_engine->emit_comment("Running PATCH Test: '{$test_name}' ({$test_meta['comment']})");
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
                    $this->test_engine->emit_comment("Running PUT Test: '{$test_name}' ({$test_meta['comment']})");
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
    }

    private function get_test_context()
    {
        return "{$this->current_test_manifest}#{$this->current_test_name}";
    }

    /**
     * @param string $path
     * @param array $expected_properties
     * @param bool $empty_response_allowed
     */
    private function get_all($path, array $expected_properties=array(), $empty_response_allowed=true)
    {
        try
        {
            $this->test_engine->assert_api_get(
                $path,
                $expected_properties,
                $empty_response_allowed
            );
            $this->test_engine->emit_pass("Passed");
        }
        catch(FailException $e)
        {
            $this->test_engine->fail($this->get_test_context(), $e->getMessage());
        }
        catch(\Exception $e)
        {
            $this->test_engine->error($this->get_test_context(), $e->getMessage());
        }
    }

    /**
     * @param string $path
     * @param array $creation_properties
     * @param array $expected_properties
     */
    private function create_then_get_resource($path, array $creation_properties, array $expected_properties=array())
    {
        try
        {
            $created_resource = $this->create_resource_for_testing($path, $creation_properties);
            if($created_resource['id'])
            {
                $this->test_engine->assert_api_get(
                    "{$path}/{$created_resource['id']}",
                    $expected_properties,
                    $empty_response_allowed=true,
                    $expected_count = 1
                );
                $this->test_engine->cleanup_resource($path, $created_resource);
            }
            $this->test_engine->emit_pass("Passed");
        }
        catch(FailException $e)
        {
            $this->test_engine->fail($this->get_test_context(), $e->getMessage());
        }
        catch(\Exception $e)
        {
            $this->test_engine->error($this->get_test_context(), $e->getMessage());
        }
    }

    /**
     * @param string $path
     * @param array $creation_properties
     */
    private function post_resource($path, array $creation_properties)
    {
        try
        {
            $this->test_engine->assert_api_post(
                $path,
                $this->test_engine->evaluate_property_values($creation_properties)
            );
            $this->test_engine->emit_pass("Passed");
        }
        catch(FailException $e)
        {
            $this->test_engine->fail($this->get_test_context(), $e->getMessage());
        }
        catch(\Exception $e)
        {
            $this->test_engine->error($this->get_test_context(), $e->getMessage());
        }

    }

    /**
     * @param string $path
     * @param array $creation_properties
     */
    private function delete_resource($path, array $creation_properties)
    {
        try
        {
            $created_resource = $this->create_resource_for_testing($path, $creation_properties);
            if(isset($created_resource['id']))
            {
                $this->test_engine->assert_api_delete(
                    "{$path}/{$created_resource['id']}"
                );
            }
            $this->test_engine->emit_pass("Passed");
        }
        catch(FailException $e)
        {
            $this->test_engine->fail($this->get_test_context(), $e->getMessage());
        }
        catch(\Exception $e)
        {
            $this->test_engine->error($this->get_test_context(), $e->getMessage());
        }
    }

    /**
     * @param string $path
     * @param array $creation_properties
     * @param array $patch_properties
     */
    private function patch_resource($path, array $creation_properties, array $patch_properties)
    {
        try
        {
            $created_resource = $this->create_resource_for_testing($path, $creation_properties);
            if(isset($created_resource['id']))
            {
                $this->test_engine->assert_api_patch(
                    "{$path}/{$created_resource['id']}",
                    $this->test_engine->evaluate_property_values($patch_properties)
                );
            }
            $this->test_engine->emit_pass("Passed");
        }
        catch(FailException $e)
        {
            $this->test_engine->fail($this->get_test_context(), $e->getMessage());
        }
        catch(\Exception $e)
        {
            $this->test_engine->error($this->get_test_context(), $e->getMessage());
        }
    }

    /**
     * @param string $path
     * @param array $creation_properties
     * @param array $put_properties
     */
    private function put_resource($path, array $creation_properties, array $put_properties)
    {
        try
        {
            $created_resource = $this->create_resource_for_testing($path, $creation_properties);
            if(isset($created_resource['id']))
            {
                $put_properties = $this->test_engine->evaluate_property_values($put_properties);
                $this->test_engine->assert_api_put(
                    "{$path}/{$created_resource['id']}",
                    array_merge($created_resource, $put_properties)
                );
            }
            $this->test_engine->emit_pass("Passed");
        }
        catch(FailException $e)
        {
            $this->test_engine->fail($this->get_test_context(), $e->getMessage());
        }
        catch(\Exception $e)
        {
            $this->test_engine->error($this->get_test_context(), $e->getMessage());
        }
    }

    /**
     * @param string $path
     * @param array $creation_properties
     * @return array
     */
    private function create_resource_for_testing($path, array $creation_properties)
    {
        return $this->test_engine->assert_api_post(
            $path,
            $this->test_engine->evaluate_property_values($creation_properties),
            $clean_up = false
        );
    }

    /**
     * @param string $test_name
     * @return string
     */
    private function get_method_from_name($test_name)
    {
        $match = null;
        preg_match('/^(?P<method>head|options|get|post|put|patch|delete)/i', $test_name, $match);
        if( ! isset($match['method']))
        {
            $this->test_engine->fail(
                $this->get_test_context(),
                "HTTP Method (head|options|get|post|put|patch|delete) not found at the beginning of test name: '{$test_name}'"
            );
        }
        return strtolower($match['method']);
    }

    /**
     * @param string $test_name
     * @return string
     */
    private function get_path_from_name($test_name)
    {
        $match = null;
        preg_match('/\s(?P<path>[^\s]+)$/i', $test_name, $match);
        if( ! isset($match['path']))
        {
            $this->test_engine->fail($this->get_test_context(), "HTTP path not found at the end of test name: '{$test_name}'");
        }
        return "/".trim($match['path'], '/ ');
    }

    /**
     * @param array $default_creation_properties
     * @param array $creation_properties
     * @return array
     */
    private function get_evaluated_creation_properties(array $default_creation_properties, array $creation_properties)
    {
        return $this->test_engine->evaluate_property_values(array_merge($default_creation_properties, $creation_properties));
    }

    /**
     * @param array $service_manifest
     * @param string $path
     * @param array $test_meta
     * @return array
     */
    private function get_blended_evaluated_creation_properties(array $service_manifest, $path, array $test_meta)
    {
        return $this->get_evaluated_creation_properties(
            isset($service_manifest['resource_seeds'][$path])?$service_manifest['resource_seeds'][$path]:array(),
            isset($test_meta['creation_properties'])?$test_meta['creation_properties']:array()
        );
    }
    private function format_summary_error($failure_msg)
    {
        $fail_first_line = $fail_remaining_lines = "";
        $failure_msg = (array)explode(PHP_EOL, $failure_msg);
        $fail_first_line = array_shift($failure_msg);
        if($failure_msg)
        {
            $failure_msg = array_map(
                function($val)
                {
                    return "\t\t\t{$val}";
                },
                $failure_msg
            );
            $fail_remaining_lines = PHP_EOL . implode(PHP_EOL, $failure_msg);
        }
        return "{$fail_first_line} {$fail_remaining_lines}";
    }
}
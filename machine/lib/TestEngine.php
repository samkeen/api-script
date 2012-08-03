<?php
namespace DeusTesting;
/**
 *
 */
class TestEngine
{

    private $requested_service_name = null;
    private $requested_service_response = null;
    private $requested_service_full_uri = null;
    private $services_meta = array();

    private $failures = array();

    /**
     * @var ApiHelper
     */
    private $api_helper = null;

    function __construct($conf_files_directory)
    {
        $services_meta_file_path = "{$conf_files_directory}/services.yaml";
        if( ! file_exists($services_meta_file_path) || ! is_readable($services_meta_file_path))
        {
            throw new \ErrorException("Conf file not found and/or not readable at: {$services_meta_file_path}");
        }
        $this->services_meta = \Spyc::YAMLLoad($services_meta_file_path);;
        $this->validate_conf($this->services_meta);
        $this->api_helper = new ApiHelper();
    }

    function service($service_name)
    {
        $this->requested_service_name = $service_name;
        return $this;
    }

    /**
     * This method
     *   - builds, send, and gathers the response for the cURL POST request
     *   - Asserts the HTTP response code is 201
     *   - Asserts the HTTP response entity body is not empty
     *   - Asserts that the created Resource contains the property keys of the supplied
     *     $payload
     *   - If $cleanup=true, it sends an HTTP DELETE request for the created Resource
     *
     * @param string $request_path
     * @param array $payload The array of property values to be sent with the POST request
     * @param bool $cleanup If true a Delete request is sent after making assertions concerning
     * the POST Resource creation
     * @return array The returned Resource from the web service
     */
    function assert_api_post($request_path, array $payload=array(), $cleanup=true)
    {
        $service_meta = $this->get_service_meta($this->requested_service_name);
        $this->requested_service_full_uri = $this->build_full_path(
            $service_meta['base_domain_path'], $request_path, $service_meta['api_prefix_path']
        );
        $this->requested_service_response = $this->api_helper->api_post(
            $this->requested_service_full_uri, $payload, $service_meta['username'], $service_meta['password']
        );
        $created_resource = json_decode($this->requested_service_response['body'], true);
        $this->assert_response_code(201, 'POST');
        $this->assertNotEmpty($created_resource, "The created Resource was found to be Empty");
        $this->assert_resource_contains_expected_properties($created_resource, array_keys($payload));
        if($cleanup)
        {
            $this->cleanup_resource($request_path, $created_resource);
        }
        return $created_resource;
    }

    /**
     * This Method
     *   - builds, send, and gathers the response for the cURL GET request
     *   - asserts the HTTP response code is 200
     *   - if expecting a response ($empty_response_allowed=false), asserts response is not empty
     *   - if $expected_count supplies, that count is asserted
     *   - for the returned resource, assert that it has the properties of the sent payload
     *
     * @param string $request_path
     * @param array $expected_property_keys
     * @param bool $empty_response_allowed
     * @param null|int $expected_count
     * @return array The Resources returned by the Web Service
     */
    function assert_api_get(
        $request_path, array $expected_property_keys=array(), $empty_response_allowed=true, $expected_count=null)
    {
        $service_meta = $this->get_service_meta($this->requested_service_name);
        $this->requested_service_full_uri = $this->build_full_path(
            $service_meta['base_domain_path'], $request_path, $service_meta['api_prefix_path']
        );
        $this->requested_service_response = $this->api_helper->api_get(
            $this->requested_service_full_uri, $service_meta['username'], $service_meta['password']
        );
        $retrieved_resources = json_decode($this->requested_service_response['body'], true);
        $this->assert_response_code(200, 'GET');
        if( ! $empty_response_allowed && ! $retrieved_resources)
        {
            $this->fail("The param: \$empty_response_allowed was false and the response body was empty");
        }
        if($empty_response_allowed && empty($retrieved_resources) && $expected_count===null)
        {
            $expected_count = 0;
        }
        if($expected_count!==null)
        {
            $actual_count = count($retrieved_resources);
            $this->assertEquals($expected_count, $actual_count,
                "Expected count of returned Resources was: {$expected_count}, actual count was: {$actual_count}"
                ."Retrieved Resources: ".print_r($retrieved_resources, true)
            );
        }
        if($retrieved_resources && $expected_property_keys)
        {
            $this->assert_resource_contains_expected_properties($retrieved_resources[0], $expected_property_keys);
        }
        return $retrieved_resources;
    }

    /**
     * This method
     *   - builds, send, and gathers the response for the cURL PUT request
     *   - asserts the HTTP response code is 204
     *   - assert the response body is empty
     *
     * @param string $request_path
     * @param array $payload
     */
    function assert_api_put($request_path, array $payload=array())
    {
        $service_meta = $this->get_service_meta($this->requested_service_name);
        $this->requested_service_full_uri = $this->build_full_path(
            $service_meta['base_domain_path'], $request_path, $service_meta['api_prefix_path']
        );
        $this->requested_service_response = $this->api_helper->api_patch(
            $this->requested_service_full_uri, $payload, $service_meta['username'], $service_meta['password']
        );
        $this->assert_response_code(204, 'PUT');
        $this->assertEmpty($this->requested_service_response['body']);
    }

    /**
     * This Method
     *   - builds, send, and gathers the response for the cURL PATCH request
     *   - asserts the HTTP response code is 204
     *   - assert the response body is empty
     *
     * @param string $request_path
     * @param array $payload
     */
    function assert_api_patch($request_path, array $payload=array())
    {
        $service_meta = $this->get_service_meta($this->requested_service_name);
        $this->requested_service_full_uri = $this->build_full_path(
            $service_meta['base_domain_path'], $request_path, $service_meta['api_prefix_path']
        );
        $this->requested_service_response = $this->api_helper->api_patch(
            $this->requested_service_full_uri, $payload, $service_meta['username'], $service_meta['password']
        );
        $this->assert_response_code(204, 'PATCH');
        $this->assertEmpty($this->requested_service_response['body']);
    }

    /**
     * - builds, send, and gathers the response for the cURL DELETE request
     *   - asserts the HTTP response code is 204
     *   - assert the response body is empty
     *
     * @param string $request_path
     */
    function assert_api_delete($request_path)
    {
        $service_meta = $this->get_service_meta($this->requested_service_name);
        $this->requested_service_full_uri = $this->build_full_path(
            $service_meta['base_domain_path'], $request_path, $service_meta['api_prefix_path']
        );
        $this->requested_service_response = $this->api_helper->api_delete(
            $this->requested_service_full_uri, $service_meta['username'], $service_meta['password']
        );
        $this->assert_response_code(204, 'DELETE');
        $this->assertEmpty($this->requested_service_response['body']);
    }

    /**
     * @param array $conf
     * @throws \ErrorException
     */
    private function validate_conf(array $conf = array())
    {
        foreach ($conf as $host_name => $host_meta) {
            /*
             * Validate `'base_domain_path'
             */
            if( ! isset($host_meta['base_domain_path']))
            {
                throw new \ErrorException("`base_domain_path` for host [{$host_name}] from conf-blackbox.php must include protocol (http:// or https://)");
            }
            if( ! array_key_exists('api_prefix_path', $host_meta))
            {
                throw new \ErrorException("the key: `api_prefix_path` for host [{$host_name}] from conf-blackbox.php was not found");
            }
            if( ! preg_match('%^https?://.*%', $host_meta['base_domain_path']))
            {
                throw new \ErrorException(
                    "`base_domain_path` for host [{$host_name}] from conf-blackbox.php must include protocol (http:// or https://)\n"
                    ."Value found: [{$host_meta['base_domain_path']}]"
                );
            }
            /*
             * validate `username` & `password`
             */
            if( ! array_key_exists('username', $host_meta) ||  ! array_key_exists('password', $host_meta))
            {
                throw new \ErrorException("`username` and/or `password` key missing for host [{$host_name}] from conf-blackbox.php");
            }
        }
    }
    /**
     * @param string $base_domain_path ex: "http://localhost/fen-phen/?_wrap_array=1&__c=/"
     * @param string $request_path ex: "/product"
     * @param string $api_path_prefix ex: "/api/v1"
     * @return string
     */
    private function build_full_path($base_domain_path, $request_path, $api_path_prefix)
    {
        $request_path = "/".ltrim($request_path, ' /');
        $base_service_path = rtrim($base_domain_path, ' /');
        $api_prefix_path = $api_path_prefix
            ? "/" .ltrim($api_path_prefix, ' /')
            : '';
        return "{$base_service_path}{$api_prefix_path}{$request_path}";
    }
    /**
     * @param string $host_name
     * @return array|null
     * @throws \ErrorException
     */
    private function get_service_meta($host_name)
    {
        $service_meta = isset($this->services_meta[$host_name]) ? $this->services_meta[$host_name] : null;
        if( ! $service_meta)
        {
            throw new \ErrorException(
                "no service meta found for service name: [{$host_name}] in conf-blackbox.php.\n"
                    ."Known service host names: ".implode(",", array_keys($this->services_meta))
            );
        }
        return $service_meta;
    }

    /**
     * Used for testing for error conditions
     *
     * @param $response
     * @return mixed|string
     */
    private function get_expected_response_error($response)
    {
        $error_response = '';
        if( ! strstr($response, '__error'))
        {
            $error_response = "__error key not found in response. Entire response entity body was: ".$response;
        }
        else
        {
            $response_array = json_decode($response, true);
            if(null === $response_array)
            {
                $error_response = "was unable to JSON decode entity body.  Entity Body string: ".$response;
            }
            else if( ! array_key_exists('__error', $response_array))
            {
                $error_response = "After JSON decoding entity body, the array key `__error` was not found.  Entity Body string: ".$response;
            }
            else
            {
                $error_response = $response_array;
            }
        }
        return $error_response;
    }
    /**
     * Issue a DELETE request for the given $created_resource
     *
     * @param string $request_path
     * @param array $created_resource
     */
    function cleanup_resource($request_path, $created_resource)
    {
        $this->assert_api_delete("{$request_path}/{$created_resource['id']}");
    }
    function evaluate_property_values(array $properties)
    {
        foreach ($properties as $property_key => &$property_value) {
            if(substr(strtolower(trim($property_value)), 0, 5) == 'php::')
            {
                $match = null;
                $eval_function = trim(substr($property_value, 5),' ;');
                preg_match('/^(?P<function_name>[^\(]+)/', $eval_function, $match);
                if( ! $match['function_name'])
                {
                    $this->fail("The php eval property value: [{$property_key}] => '{$property_value}' is malformed");
                }
                if( ! function_exists($match['function_name']))
                {
                    $this->fail("The the function name ['{$match['function_name']}'] from the php eval property value: '{$property_key}: {$property_value}' is unknown");
                }
                $property_value = eval("return {$eval_function};");
            }
        }
        return $properties;
    }
    function fail($message)
    {
        self::emit($message);
        $this->add_failure($message);
    }
    private function assert_resource_contains_expected_properties($created_resource, $expected_properties)
    {
        if($missing = array_diff($expected_properties, array_keys($created_resource)))
        {
            $this->fail("Of the expected properties [".implode(',', $expected_properties)."]"
                . " these properties were not found [".implode(',', $missing)."]"
                . " in the created resource: ".print_r($created_resource, true)
            );
        }
    }
    private function assert_response_code($code, $request_type)
    {
        $this->assertEquals(
            $code,
            $this->requested_service_response['code'],
            "The HTTP response code for this {$request_type} request [{$this->requested_service_full_uri}] should have been {$code}, "
                ."was instead: [{$this->requested_service_response['code']}]\n"
                ."Response __error: ".print_r($this->get_expected_response_error($this->requested_service_response['body']), true)
        );
    }

    function emit($string)
    {
        echo trim($string).PHP_EOL;
    }

    private function assertNotEmpty($value, $message=null)
    {
        if(empty($value))
        {
            $message = $message ?: "The value was empty";
            $this->fail($message);
        }
    }
    private function assertEmpty($value, $message=null)
    {
        if( ! empty($value))
        {
            $message = $message ?: "The value [$value] was NOT empty";
            $this->fail($message);
        }
    }
    private function assertEquals($value1, $value2, $message=null)
    {
        if( ! $value1 == $value2)
        {
            $message = $message ?: "Failed asserting the {$value1} was equal to {$value2}";
            $this->fail($message);
        }
    }

    private function add_failure($message)
    {
        $this->failures[] = $message;
    }

}
<?php
/**
 *
 */
namespace DeusTesting;

require_once __DIR__ . "/autoload.php";

class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    const FENPHEN = 'fenphen';

    private $requested_service_name = null;
    private $requested_service_http_method = null;
    private $requested_service_response = null;
    private $requested_service_full_uri = null;
    private $conf = array();

    /**
     * After a POST the returned resource set here.
     * @var array
     */
    private $created_resource = null;
    /**
     * @var ApiHelper
     */
    private $api_helper = null;

    protected function setUp()
    {
        $conf_file_path = __DIR__."/conf/conf.php";
        if( ! file_exists($conf_file_path) || ! is_readable($conf_file_path))
        {
            throw new \ErrorException("Conf file not found and/or not readable at: {$conf_file_path}");
        }
        $this->conf = require $conf_file_path;
        $this->validate_conf($this->conf);
        $this->api_helper = new ApiHelper();
    }

    protected function service($service_name)
    {
        $this->requested_service_name = $service_name;
        return $this;
    }

    protected function assert_api_post($request_path, array $payload=array())
    {
        $this->requested_service_http_method = 'POST';
        $service_meta = $this->get_service_meta($this->requested_service_name);
        $this->requested_service_full_uri = $this->build_full_path(
            $service_meta['base_domain_path'], $request_path, $service_meta['api_prefix_path']
        );
        $this->requested_service_response = $this->api_helper->api_post(
            $this->requested_service_full_uri, $payload, $service_meta['username'], $service_meta['password']
        );
        $this->created_resource = json_decode($this->requested_service_response['body'], true);
        $this->assert_response_code(201);
        // perform POST
        // assert 201 response
        // assert response not empty
        // assert response array has keys of payload
        // assert response array values match $payload values
        // return generated identifier (id)
    }
    protected function assert_api_get($request_path)
    {
        $this->requested_service_http_method = 'GET';
        $service_meta = $this->get_service_meta($this->requested_service_name);
        $this->requested_service_full_uri = $this->build_full_path(
            $service_meta['base_domain_path'], $request_path, $service_meta['api_prefix_path']
        );
        $this->requested_service_response = $this->api_helper->api_get(
            $this->requested_service_full_uri, $service_meta['username'], $service_meta['password']
        );
        $this->assert_response_code(200);
        // perform GET
        // assert 200 response
        // assert not empty response
        // assert has $expected_keys
    }
    protected function assert_api_put($request_path, array $payload=array())
    {
        $this->requested_service_http_method = 'PUT';
        $service_meta = $this->get_service_meta($this->requested_service_name);
        $this->requested_service_full_uri = $this->build_full_path(
            $service_meta['base_domain_path'], $request_path, $service_meta['api_prefix_path']
        );
        $this->requested_service_response = $this->api_helper->api_patch(
            $this->requested_service_full_uri, $payload, $service_meta['username'], $service_meta['password']
        );
        $this->assert_response_code(204);
        // perform PUT
        // A. assert 204 response
        //    assert empty response
        //    assert has $expected_keys
        // B. perform GET
        //    assert 200 response
        //    assert field has expected mutation
    }
    protected function assert_api_patch($request_path, array $payload=array())
    {
        $this->requested_service_http_method = 'PATCH';
        $service_meta = $this->get_service_meta($this->requested_service_name);
        $this->requested_service_full_uri = $this->build_full_path(
            $service_meta['base_domain_path'], $request_path, $service_meta['api_prefix_path']
        );
        $this->requested_service_response = $this->api_helper->api_patch(
            $this->requested_service_full_uri, $payload, $service_meta['username'], $service_meta['password']
        );
        $this->assert_response_code(204);
        // perform PATCH
        // A. assert 204 response
        //    assert empty response
        //    assert has $expected_keys
        // B. perform GET
        //    assert 200 response
        //    assert field has expected mutation
    }
    protected function assert_api_delete($request_path)
    {
        $this->requested_service_http_method = 'DELETE';
        $service_meta = $this->get_service_meta($this->requested_service_name);
        $this->requested_service_full_uri = $this->build_full_path(
            $service_meta['base_domain_path'], $request_path, $service_meta['api_prefix_path']
        );
        $this->requested_service_response = $this->api_helper->api_delete(
            $this->requested_service_full_uri, $service_meta['username'], $service_meta['password']
        );
        $this->assert_response_code(204);
        $this->assertEmpty($this->requested_service_response['body']);
        // perform DELETE
        // A. assert 204 response
        //    assert empty response
        // B. perform GET
        //    assert 404
    }
    protected function get_created_resource()
    {
        return $this->created_resource;
    }
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
    private function build_full_path($base_domain_path, $request_path, $api_path_prefix)
    {
        $request_path = "/".ltrim($request_path, ' /');
        $base_service_path = rtrim($base_domain_path, ' /');
        $api_prefix_path = $api_path_prefix
            ? "/" .ltrim($api_path_prefix, ' /')
            : '';
        return "{$base_service_path}{$api_prefix_path}{$request_path}";
    }
    private function get_service_meta($host_name)
    {
        $service_meta = isset($this->conf[$host_name]) ? $this->conf[$host_name] : null;
        if( ! $service_meta)
        {
            throw new \ErrorException(
                "no service meta found for service name: [{$host_name}] in conf-blackbox.php.\n"
                    ."Known service host names: ".implode(",", array_keys($this->conf))
            );
        }
        return $service_meta;
    }
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
    private function assert_response_code($code)
    {
        $this->assertEquals(
            $code,
            $this->requested_service_response['code'],
            "The HTTP response code for this {$this->requested_service_http_method} request [{$this->requested_service_full_uri}] should have been {$code}, "
                ."was instead: [{$this->requested_service_response['code']}]\n"
                ."Response __error: ".print_r($this->get_expected_response_error($this->requested_service_response['body']), true)
        );
    }
}
<?php
namespace DeusTesting;

class ProductTest extends BaseTestCase
{

//    function cycleProductTest()
//    {
//        $this
//            ->service(self::FENPHEN)
//            ->cycle('/product')
//            ->post()
//            ->get()
//            ->patch()
//            ->delete();
//    }

    function testGetAll()
    {
        $resources = $this->service(self::FENPHEN)
            ->assert_api_get(
                '/product',
                array('name'),
                $empty_response_allowed=true
            );
    }
    function testGetCreatedResource()
    {
        $created_resource = $this->service(self::FENPHEN)
            ->assert_api_post(
            '/product',
            array('name' => uniqid('bob-')),
            $clean_up = false
        );
        $resources = $this->service(self::FENPHEN)
            ->assert_api_get(
                "/product/{$created_resource['id']}",
                array('name'),
                $empty_response_allowed=true,
                $expected_count = 1
            );
    }
    function testPost()
    {
        $created_resource = $this->service(self::FENPHEN)
            ->assert_api_post(
                '/product',
                array('name' => uniqid('bob-'))
            );
    }
    function testDelete()
    {
        $created_resource = $this->service(self::FENPHEN)
            ->assert_api_post(
                '/product',
                array('name' => uniqid('bob-'))
            );
        $this->service(self::FENPHEN)
            ->assert_api_delete(
                "/product/{$created_resource['id']}"
        );
    }
    function testPatch()
    {
        $created_resource = $this->service(self::FENPHEN)
            ->assert_api_post(
            '/product',
            array('name' => uniqid('bob-')),
            $clean_up = false
        );
        $this->service(self::FENPHEN)
            ->assert_api_patch(
                "/product/{$created_resource['id']}",
                array('name' => uniqid('bob-new-'))
        );
    }
    function testPut()
    {
        $created_resource = $this->service(self::FENPHEN)
            ->assert_api_post(
                '/product',
                array('name' => uniqid('bob-')),
                $clean_up = false
            );
        $created_resource['name'] = uniqid('bob-new-');
        $this->service(self::FENPHEN)
            ->assert_api_put(
                "/product/{$created_resource['id']}",
                $created_resource
        );
    }
}
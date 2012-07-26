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
        $this
            ->service(self::FENPHEN)
            ->assert_api_get('/product');
    }
    function testPost()
    {
        $this->service(self::FENPHEN)
            ->assert_api_post(
                '/product',
                array('name' => uniqid('bob-'))
            );
    }
    function testDelete()
    {
        $this->service(self::FENPHEN)
            ->assert_api_post(
                '/product',
                array('name' => uniqid('bob-'))
            );
        $created_resource = $this->get_created_resource();
        $this->service(self::FENPHEN)
            ->assert_api_delete(
                "/product/{$created_resource['id']}"
        );
    }
    function testPatch()
    {
        $this->service(self::FENPHEN)
            ->assert_api_post(
            '/product',
            array('name' => uniqid('bob-'))
        );
        $created_resource = $this->get_created_resource();
        $this->service(self::FENPHEN)
            ->assert_api_patch(
                "/product/{$created_resource['id']}",
                array('name' => uniqid('bob-new-'))
        );
    }
    function testPut()
    {
        $this->service(self::FENPHEN)
            ->assert_api_post(
            '/product',
            array('name' => uniqid('bob-'))
        );
        $created_resource = $this->get_created_resource();
        $created_resource['name'] = uniqid('bob-new-');
        $this->service(self::FENPHEN)
            ->assert_api_put(
                "/product/{$created_resource['id']}",
                $created_resource
        );
    }
}
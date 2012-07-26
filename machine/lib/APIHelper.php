<?php
namespace DeusTesting;

class ApiHelper
{
    /**
     * Function that uses cURL to perform basic API get
     *
     * @param string $url full URL to get
     * @param string|null $identifier http auth identifier - if this is NULL (default) no authentication will be used
     * @param string|null $shared_secret http auth secret
     * @return array
     */
    function api_get($url, $identifier = null, $shared_secret = null)
    {
        $curl_auth = $identifier . ":" . $shared_secret;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $curl_auth);

        return $this->gather_response($ch);
    }

    /**
     * Function that uses cURL to perform basic API post
     *
     * @param string $url full URL to post to
     * @param array $post_contents
     * @param string|null $username http auth identifier - if this is NULL (default) no authentication will be used
     * @param string|null $password
     * @return array
     */
    function api_post($url, array $post_contents, $username = null, $password = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_contents));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        if ($username != NULL) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        }
        return $this->gather_response($ch);
    }

    /**
     * @param string $url
     * @param array $post_contents
     * @param string|null $username
     * @param string|null $password
     * @return array
     */
    function api_put($url, array $post_contents, $username = null, $password = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        if ($post_contents != NULL) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_contents));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if ($username != NULL) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        return $this->gather_response($ch);
    }

    /**
     * @param string $url
     * @param array $post_contents
     * @param string|null $username
     * @param string|null $password
     * @return array
     */
    function api_patch($url, array $post_contents, $username = null, $password = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        if ($post_contents != NULL) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_contents));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if ($username != NULL) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        return $this->gather_response($ch);
    }

    /**
     * @param string $url
     * @param string|null $identifier
     * @param string|null $sharedSecret
     * @return array
     */
    function api_delete($url, $identifier = NULL, $sharedSecret = NULL)
    {
        $curlAuth = $identifier . ":" . $sharedSecret;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $curlAuth);
        return $this->gather_response($ch);
    }

    /**
     * @param $ch
     * @return array
     */
    private function gather_response($ch)
    {
        $api_response = curl_exec($ch);
        $api_http_response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $api_full_response = array(
            "body" => $api_response,
            "code" => $api_http_response
        );
        curl_close($ch);
        return $api_full_response;
    }
}
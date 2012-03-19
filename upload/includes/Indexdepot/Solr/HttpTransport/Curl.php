<?php

require_once (dirname(__FILE__) . '/../../../Apache/Solr/HttpTransport/Abstract.php');
require_once (dirname(__FILE__) . '/../../../Apache/Solr/HttpTransport/Response.php');

class Indexdepot_Solr_HttpTransport_Curl extends Apache_Solr_HttpTransport_Abstract
{

    private $_curl;
    
    function __construct($username = '', $password = '', $ssl = 'http://')
    {
        $this->_curl = curl_init();
        curl_setopt_array($this->_curl, 
                          array(CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_BINARYTRANSFER => true,
                                CURLOPT_HEADER => false));
        if (! empty($username) && ! empty($password)) {
            $this->setHttpAuthOptions($username, $password);
        }
        if ($ssl == 'https://') {
            $this->setSSLOptions();
        }
    }
    
    function __destruct()
    {
        curl_close($this->_curl);
    }
    
    function setSSLOptions(){
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    
    function setHttpAuthOptions($username, $password){
        define('HTTP_AUTH_USER', $username);
        define('HTTP_AUTH_PASS', $password);
        
        curl_setopt($this->_curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ); 
        curl_setopt($this->_curl, CURLOPT_USERPWD, HTTP_AUTH_USER . ':' . HTTP_AUTH_PASS );  
    }

    public function performGetRequest($url, $timeout = false)
    {
        // check the timeout value
        if ($timeout === false || $timeout <= 0.0) {
            // use the default timeout
            $timeout = $this->getDefaultTimeout();
        }

        // set curl GET options
        curl_setopt_array($this->_curl, array(
            // make sure we're returning the body
            CURLOPT_NOBODY => false,
            // make sure we're GET
            CURLOPT_HTTPGET => true,
            // set the URL
            CURLOPT_URL => $url,
            // set the timeout
            CURLOPT_TIMEOUT => $timeout
        ));

        // make the request
        $responseBody = curl_exec($this->_curl);

        // get info from the transfer
        $statusCode = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($this->_curl, CURLINFO_CONTENT_TYPE);

        return new Apache_Solr_HttpTransport_Response($statusCode, $contentType, $responseBody);
    }

    public function performHeadRequest($url, $timeout = false)
    {
        // check the timeout value
        if ($timeout === false || $timeout <= 0.0) {
            // use the default timeout
            $timeout = $this->getDefaultTimeout();
        }

        // set curl HEAD options
        curl_setopt_array($this->_curl, array(
            // this both sets the method to HEAD and says not to return a body
            CURLOPT_NOBODY => true,
            // set the URL
            CURLOPT_URL => $url,
            // set the timeout
            CURLOPT_TIMEOUT => $timeout
        ));

        // make the request
        $responseBody = curl_exec($this->_curl);

        // get info from the transfer
        $statusCode = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($this->_curl, CURLINFO_CONTENT_TYPE);

        return new Apache_Solr_HttpTransport_Response($statusCode, $contentType, $responseBody);
    }

    public function performPostRequest($url, $postData, $contentType, $timeout = false)
    {
        // check the timeout value
        if ($timeout === false || $timeout <= 0.0) {
            // use the default timeout
            $timeout = $this->getDefaultTimeout();
        }

        // set curl POST options
        curl_setopt_array($this->_curl, array(
            // make sure we're returning the body
            CURLOPT_NOBODY => false,
            // make sure we're POST
            CURLOPT_POST => true,
            // set the URL
            CURLOPT_URL => $url,
            // set the post data
            CURLOPT_POSTFIELDS => $postData,
            // set the content type
            CURLOPT_HTTPHEADER => array("Content-Type: {$contentType}"),
            // set the timeout
            CURLOPT_TIMEOUT => $timeout
        ));

        // make the request
        $responseBody = curl_exec($this->_curl);

        // get info from the transfer
        $statusCode = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($this->_curl, CURLINFO_CONTENT_TYPE);

        return new Apache_Solr_HttpTransport_Response($statusCode, $contentType, $responseBody);
    }
    
}

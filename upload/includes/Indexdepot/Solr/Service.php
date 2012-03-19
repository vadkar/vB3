<?php

require_once (dirname(__FILE__) . '/../../Apache/Solr/Service.php');
require_once (dirname(__FILE__) . '/../../Apache/Solr/Exception.php');
require_once (dirname(__FILE__) . '/../../Apache/Solr/HttpTransport/Curl.php');
require_once (dirname(__FILE__) . '/../../Indexdepot/Solr/HttpTransport/Curl.php');

/**
 * http://www.indexdepot.com
 *
 * @author Vadims Karpuschkins
 */
class Indexdepot_Solr_Service extends Apache_Solr_Service
{
    /**
     * Server identification strings
     *
     * @var string
     */
    protected $_protocol = null;
            
    protected $_username = null;
            
    protected $_password = null;

    public function __construct($protocol = 'http://', $host = 'localhost', $port = 8180, $path = '/solr/', $username = '', $password = '', $httpTransport = false)
    {
        parent::__construct($host, $port, $path, new Indexdepot_Solr_HttpTransport_Curl($username, $password, $protocol) );        
        $this->setProtocol($protocol);
//        $this->setUsername($username);
//        $this->setPassword($password);
    }
    
    public function setProtocol($protocol)
    {
        if (!$protocol == 'http://' && !$protocol == 'https://') {
            throw new Apache_Solr_InvalidArgumentException('Not a valid Protocol please choose http:// or https://');
        } else {
            $this->_protocol = $protocol;
        }

        if ($this->_urlsInited) {
            $this->_initUrls();
        }
    }

    public function setUsername($username)
    {
        $this->_username = $username;

        if ($this->_urlsInited) {
            $this->_initUrls();
        }
    }

    public function setPassword($password)
    {
        $this->_password = $password;

        if ($this->_urlsInited) {
            $this->_initUrls();
        }
    }

    protected function _constructUrl($servlet, $params = array())
    {
        if (count($params)) {
            //escape all parameters appropriately for inclusion in the query string
            $escapedParams = array();

            foreach ($params as $key => $value) {
                $escapedParams[] = urlencode($key) . '=' . urlencode($value);
            }

            $queryString = $this->_queryDelimiter . implode($this->_queryStringDelimiter, $escapedParams);
        } else {
            $queryString = '';
        }

//        if (! empty($this->_username) && ! empty($this->_password)) {
//            return $this->_protocol . $this->_username . ':' . $this->_password . '@' . $this->_host . ':' . $this->_port . $this->_path . $servlet . $queryString;
//        } else {
            return $this->_protocol . $this->_host . ':' . $this->_port . $this->_path . $servlet . $queryString;
//        }
    }    
}
<?php
/**
 * ------------------------------------------------------------------------
 * JA Twitter Module for J25 & J30
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die('Restricted access');

/**
 * JATwitter class.
 */
class JATwitter
{

    /**
     * @var array $apiMethods Twitter API Methods Service
     *
     * @access protected;
     */
    var $_apiMethods = array('user_timeline' => 'http://api.twitter.com/1/statuses/user_timeline.%s?count=%s', 'friend_timeline' => 'http://api.twitter.com/1/statuses/friends_timeline.%s?count=%s', 'followers' => 'http://api.twitter.com/1/statuses/followers.%s?count=%s', 'friends' => 'http://api.twitter.com/1/statuses/friends.%s?count=%s', 'show' => 'http://api.twitter.com/1/users/show.%s?screen_name=%s');

    /**
     * @var array $_apiOtherMethods contain method nonOAuth
     *
     * @access protected.
     */
    var $_apiOtherMethods = array('show' => 'http://api.twitter.com/1/users/show.%s?screen_name=%s', 'user_timeline' => 'http://api.twitter.com/1/statuses/user_timeline.%s?screen_name=%s&count=%s', 'friends' => 'http://api.twitter.com/1/statuses/friends.%s?screen_name=%s&count=%s', 'friend_timeline' => 'http://api.twitter.com/1/statuses/friends_timeline.%s?screen_name=%s&count=%s');

    /**
     * @var string $_screenName
     *
     * @access public.
     */
    var $_screenName = '';

    /**
     * @var string $_format format of return data;
     *
     * @access protected
     */
    var $_format = 'json';

    /**
     * @var string $_auth;
     *
     * @access protected
     */
    var $_auth = '';

    /**
     * @var integer $_status status of response
     *
     * @access protected
     */
    var $_status = '';

    /**
     * @var stream $_output  data of response
     *
     * @access protected.
     */
    var $_output = '';

    /**
     * @var string $_message message of reponse.
     *
     * @access protected.
     */
    var $_message = '';


    /**
     * set username and password which using for authencate
     *
     * @param string $username
     * @param string $password
     * @return JATwitter.
     */
    function setAuth($username, $password)
    {
        if (trim($username) == '' || trim($password) == '')
            return $this;

        $this->_auth = sprintf("%s:%s", $username, $password);
        return $this;
    }


    /**
     * set sreen name same as twitter username
     *
     * @param string $screenName
     * @return JATwitter
     */
    function setScreenName($screenName)
    {
        $this->_screenName = $screenName;
        return $this;
    }


    /**
     * set format of return data
     *
     * @param string $format
     * @return JATwitter
     */
    function setFormat($format)
    {
        $this->_format = $format;
        return $this;
    }


    /**
     * get tweets base on method request
     *
     * @param string $method
     * @param integer $count default equal 10 item
     * @return boolean if have problem with request service, else return string.
     */
    function getTweets($method, $count = 10)
    {
        // find url request
        if ($this->_auth == '') {
            if (array_key_exists($method, $this->_apiOtherMethods)) {
                $url = sprintf($this->_apiOtherMethods[$method], $this->_format, $this->_screenName, $count);
            } else {
                return;
            }
        } else if ($this->_auth != '' && array_key_exists($method, $this->_apiMethods)) {
            if ($method == 'show') {
                $url = sprintf($this->_apiMethods[$method], $this->_format, $this->_screenName);
            } else {
                $url = sprintf($this->_apiMethods[$method], $this->_format, $count);
            }
        }
        // marke request twitter api;
        if (isset($url)) {
            $this->makeRequest($url);
            if ($this->_status == 200) {
                switch ($this->_format) {
                    case 'xml':
                        return $this->_parserDataXML($method);
                        break;
                    case 'json':
                        return $this->_parserDataJSON($method);
                        break;
                }
            } else {
                $this->_message = JText::_('ERROR_SERVER_RESPONSE') . ' ' . $this->_status;
                return false;
            }
        }
        return null;
    }


    /**
     * parser data of xml which 've responsed from Twitter service.
     *
     * @param string $method twitter api method
     * @return array contain twittes, return null if not found method request.
     */
    function _parserDataXML($method)
    {
        require ('class.xmlparser.php');
        if ($this->_output != '') {
            $obj = new T3XmlParser();
            $obj->loadString($this->_output);
            $xml = $obj->toObject();
            // parser data base on api method.
            return $this->callMethod("parser" . ucfirst($method), $xml);
        }
        return null;
    }


    /**
     * only parser json which response from api method.
     */
    function _parserDataJSON($method)
    {
        include ('json.php');
        if ($this->_output != '') {
            $xml = json_decode($this->_output);
            return $this->callMethod("parser" . ucfirst($method), $xml);
        }
        return null;
    }


    /**
     * get data for element's 'attribute
     *
     * @param XML Attribute of XML
     * @return string
     */
    function getData($obj)
    {
        return @(string) $obj;
    }


    /**
     * only parser xml which response from api method "show", it contain user's data
     *
     * @param JSimpleXML $xml
     * @return stdClas
     */
    function parserShow($xml)
    {

        $obj = new stdClass();
        if (!isset($xml->id))
            return null;
        $obj->id = $this->getData($xml->id);
        $obj->name = $this->getData($xml->name);
        $obj->location = $this->getData($xml->location);
        $obj->description = $this->getData($xml->description);
        $obj->profile_image_url = $this->getData($xml->profile_image_url);
        $obj->url = $this->getData($xml->url);
        $obj->followers_count = $this->getData($xml->followers_count);
        $obj->friends_count = $this->getData($xml->friends_count);
        $obj->favourites_count = $this->getData($xml->favourites_count);
        $obj->statuses_count = $this->getData($xml->statuses_count);
        $obj->screen_name = $this->getData($xml->screen_name);
        unset($xml);
        return $obj;
    }


    /**
     * only parser xml which response from api method "user_timeline", it contain twitters
     *
     * @param JSimpleXML $xml
     * @return array.
     */
    function parserUser_timeline($items)
    {
        $out = array();
        foreach ($items as $item) {
            if (!isset($item->id))
                continue;
            $obj = new stdClass();
            $user = $item->user;
            $obj->id = $this->getData($item->id);
            $obj->source = $this->getData($item->source);
            $obj->created_at = $this->getData($item->created_at);
            $obj->text = $this->getData($item->text);
            $obj->name = $this->getData($user->name);
            $obj->screen_name = $this->getData($user->screen_name);
            $obj->profile_image_url = $this->getData($user->profile_image_url);
            $out[] = $obj;
        }
        return $out;
    }


    /**
     * only parser xml which response from api method "friend_timeline", it contain twitters
     *
     * @param JSimpleXML $xml
     * @return array.
     */
    function parserFriend_timeline($xml)
    {
        print_r($xml);
        die();
        return $this->parserUser_timeline($xml);
    }


    function _getXMLChildren($parentNode)
    {
        return;
    }


    /**
     * only parser xml which response from api method "friend", it contain friends' data
     *
     * @param JSimpleXML $xml
     * @return array.
     */
    function parserFriends($friends)
    {

        $out = array();
        foreach ($friends as $friend) {
            $out[] = $this->parserShow($friend);
        }

        return $out;
    }


    /**
     * only parser xml which response from api method "followers", it contain friends' data
     *
     * @param JSimpleXML $xml
     * @return array.
     */
    function parserFollowers($friends)
    {
        $out = array();
        foreach ($friends as $friend) {
            $out[] = $this->parserShow($friend);
        }
        return $out;
    }


    /**
     * magic method
     *
     * @param string method  method is calling
     * @param string $params.
     * @return unknown
     */
    function callMethod($method, $params)
    {
        if (method_exists($this, $method)) {
            if (is_callable(array($this, $method))) {
                return call_user_func(array($this, $method), $params);
            }
        }
        return false;
    }


    /**
     * get message of reponse
     *
     * @return string;
     */
    function getMessage()
    {
        return $this->_message;
    }


    /**
     * check curl installed
     *
     * @return void.
     */
    function _iscurlinstalled()
    {
        if (function_exists('curl_version') == "Enabled") {
            return true;
        } else {
            if (in_array('curl', get_loaded_extensions())) {
                return true;
            } else {
                return false;
            }
        }
    }


    /**
     * make request service
     *
     * @param string $url
     * @return void.
     */

    function makeRequest($url, $post = array())
    {
        if ($this->_iscurlinstalled()) {
            //use curl to get content from url
            $handle = curl_init();

            curl_setopt($handle, CURLOPT_URL, $url);

            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($handle, CURLOPT_TIMEOUT, 400);

            if ($this->_auth != '') {
                curl_setopt($handle, CURLOPT_USERPWD, $this->_auth);
            }
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_HTTPHEADER, array('Expect:'));
            $response = curl_exec($handle);
            $this->_status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);
            if ($this->_format == 'json') {
                $this->_output = strstr($response, "[{");
                if (empty($this->_output)) {
                    $this->_output = strstr($response, "{");
                }
            }
            $pos = strpos($response, '<?xml');
            if ($pos) {
                $this->_output = substr($response, $pos, strlen($response));
            }
            return;
        } else {
            $response = '';
            $out = parse_url($url);
            $errno = $errstr = '';
            $host = $out['host'];
            $path = $out['path'] . '?' . $out['query'];
            $header = "GET $path HTTP/1.1\r\n";
            $header .= "Host: $host\r\n";
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $header .= "Accept-Encoding: none\r\n";

            if ($this->_auth != '') {
                $header .= "Authorization: Basic " . base64_encode("$this->_auth") . "\r\n";
            }
            $header .= "Connection: Close\r\n\r\n";

            $sock = fsockopen($host, 80, $errno, $errstr, 400);
            if (!$sock) {
                return null;
            } else {
                fwrite($sock, $header);
                while (!feof($sock)) {
                    $response .= fgets($sock, 128);
                }
                fclose($sock);
                if ($this->_format == 'json') {
                    $this->_output = strstr($response, "[{");
                    if (empty($this->_output)) {
                        $this->_output = strstr($response, "{");
                    }
                    $this->_status = 200;
                }
                $pos = strpos($response, '<?xml');
                if ($pos) {
                    $this->_output = substr($response, $pos, strlen($response));
                    $this->_status = 200;
                }
                return true;
            }
        }
    }
}
?>

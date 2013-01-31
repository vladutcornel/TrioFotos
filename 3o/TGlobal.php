<?php

require_once TRIO_DIR . '/framework-core.php';

/**
 * Wrapper for the PHP's global vars
 * If any of the requested params are not set, the methods will return an empty
 * string, or, for the Get, Post, Cookie and Session, the programmer can specify 
 * the default return value.
 *
 * For the GET parameters, the real browser sent parameters are used, since PHP
 * may get confused if a redirect script is used (eg. using mod_rewrite in Apache)
 * 
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oFramework
 * @subpackage Core
 */
class TGlobal {

    /**
     * The actual request URL components (not confused by a redirect)
     * @static
     * @var array the url components (see http://php.net/manual/en/function.parse-url.php)
     */
    private static $request;

    /**
     * Real GET parameters, usefull if a redirect script (e.g. htaccess+mod_rewrite in Apache) is used
     * @static
     * @var array
     */
    private static $get;

    /**
     * The request variables, cached by self::request() method
     * @see TGlobal::request()
     * @static
     * @var array
     */
    private static $request_vars = array();

    /**
     * Registers the get parameter if we're in a redirect
     */
    public static function init() {
        // register real request
        self::$request = parse_url($_SERVER['REQUEST_URI']);

        if (isset(self::$request['query']))
            parse_str(self::$request['query'], self::$get);
    }

    /**
     * Real GET parameters (as seen on browser's address bar)
     * Reason: Some server URL redirects (e.g. using htaccess in Apache) confuse PHP, so $_GET doesn't always work
     * If you need the params from a redirect, use TGlobal::script()
     * @param string $param
     * @param string $default What should be returned if there param was not found
     * @return string
     */
    public static function get($param, $default = '') {
        if (!isset(self::$get[$param]))
            return $default;
        return self::$get[$param];
    }

    /**
     * Append new GET parameters to the requested query
     * @param string|array $param
     * @param mixed $value
     * @return string The new get query (e.g. param1=value&param2=other_value)
     * @throws BadMethodCallException - for development, if the second parameter is missing
     */
    public static function buildQuery($param, $value = NULL) {
        // get a new copy of the GET array
        $get = self::$get;

        // behaviour 1: if it is given only one array parameter, use that to update the
        if (is_array($param)) {
            return http_build_query(array_merge($get, $param));
        }
        if (is_object($param)) {
            // treat the param as an array
            return http_build_query(array_merge($get, (array) $param));
        }

        if (is_null($value)) {
            throw new BadMethodCallException('TGlobal::buildQuery should be called with eather an array, an object or two values');
        }

        // behaviour 2: if we get two parameters...
        $get[$param] = $value;
        return http_build_query($get);
    }

    /**
     * Retrive the real query
     * @return string
     */
    public static function getQuery() {
        if (isset(self::$request['query']))
            return self::$request['query'];
        return '';
    }

    /**
     * GET parameters recived by PHP. Most times, it's the same as TGlobal::get()
     * @see TGlobal::get
     * @param string $param
     * @param string $default What should be returned if there param was not found
     * @return string
     */
    public static function script($param, $default = '') {
        if (!isset($_GET[$param]))
            return $default;
        return $_GET[$param];
    }

    /**
     * Get POST parameters ($_POST)
     * @param string $param
     * @param string $default What should be returned if there param was not found
     * @return string
     */
    public static function post($param, $default = '') {
        if (!isset($_POST[$param]))
            return $default;
        return $_POST[$param];
    }

    /**
     * Get the cookie vars ($_COOKIE)
     * @param string $param
     * @param string $default What should be returned if there param was not found
     * @return string
     */
    public static function cookie($param, $default = '') {
        if (!isset($_COOKIE[$param]))
            return $default;
        return $_COOKIE[$param];
    }

    /**
     * Set a cookie value. The difference between this and PHP's setcookie()
     * is that you can throw in almost any time-related value as the expiration time.
     * The 4th parameter should be an associative array to set the other
     * setcookie() options and are the same as the defaults from PHP, except 
     * that the path is set to '/'
     * 
     * @param string $param
     * @param mixed $value
     * @param mixed $expire expiration time
     * @param array $extra extra parameters for setcookie function
     */
    public static function setCookie($param, $value = 1, $expire = 0, $extra = array()) {
        // try to find the perfect expiration timestamp
        $expire_timestamp = 0;
        if (!empty($expire)) {
            try {
                if ('session' == $expire) {
                    // the user may only want to set the extra params
                    $expire_timestamp = 0;
                    throw new Exception('found');
                }

                if (is_numeric($expire)) {
                    $now = time();
                    if ($expire < $now) {
                        // asume the user wants a relative timestamp
                        $expire_timestamp = time() + (int) $expire;
                        throw new Exception('found');
                    } else {
                        // this is old-school
                        $expire_timestamp = (int) $expire;
                        throw new Exception('found');
                    }
                }

                // this will throw an exception (that probably doesn't have 
                // 'found' as a message) if the expire can't be evaluated as a time
                $date = TUtil::string2date($expire);

                $expire_timestamp = $date->getTimestamp();
            } catch (Exception $e) {
                if ('found' != $e->getMessage()) {
                    $expire_timestamp = 0;
                }
            }
        }


        $extra = (array) TUtil::populate($extra, array(
                    'path' => '/',
                    'domain' => null,
                    'secure' => false,
                    'httponly' => false
                ));
        $success = setcookie($param, $value, $expire_timestamp, $extra['path'], $extra['domain'], $extra['secure'], $extra['httponly']);
        // in case you disperately need the cookie value during the same
        // script - you should not rely on that...
        $_COOKIE[$param] = $value;
    }

    public static function unsetCookie($param, $extra = array()) {
        // if, by any chance, get an object or something else...
        $extra = (array) $extra;
        TUtil::populate($extra, array(
            'path' => '/',
            'domain' => self::server('HTTP_HOST'),
            'secure' => false,
            'httponly' => false
        ));
        // set the cookie to 25 hours ago 
        setcookie($param, '', time() - 90000, $extra['path'], $extra['domain'], $extra['secure'], $extra['httponly']);
    }

    /**
     * Get session vars ($_SESSION)
     * @param string $param
     * @return string
     */
    public static function session($param, $default = '') {
        // start the session if it is not started
        if (session_id() === "")
            session_start();

        if (!isset($_SESSION[$param])) {

            return $default;
        } else {
            if (is_array($default) || is_object($default)) {
                $_SESSION[$param] = TUtil::populate($_SESSION[$param], $default);
            }
            return $_SESSION[$param];
        }
    }

    /**
     * Set a session variable
     * @param type $param
     * @param type $value
     */
    public static function setSession($param, $value) {
        // start the session if it is not started
        if (session_id() === '')
            session_start();

        $_SESSION[$param] = $value;
    }

    public static function unsetSession($param) {
        // start the session if it is not started
        if (session_id() === '')
            session_start();

        unset($_SESSION[$param]);
    }

    /**
     * Get enviroment variables ($_ENV)
     * @param string $param
     * @return string
     */
    public static function env($param, $default = '') {
        if (!isset($_ENV[$param]))
            return $default;
        else
            return $_ENV[$param];
    }

    /**
     * Get Server variables ($_SERVER)
     * @param string $param
     * @return string
     */
    public static function server($param, $default = '') {
        if (!isset($_SERVER[$param]))
            return $default;
        else
            return $_SERVER[$param];
    }

    /**
     * Get uploaded file data
     * @param string $param
     * @return boolean|array false if there is no file
     */
    public static function files($param) {
        if (!isset($_FILES[$param]))
            return false;
        $out = array();
        if (!is_array($_FILES[$param]['tmp_name'])){
            $out[]= array(
                'tmp_name'=>$_FILES[$param]['tmp_name'],
                'name'=>$_FILES[$param]['name'],
                'type'=>$_FILES[$param]['type'],
                'size'=>$_FILES[$param]['size'],
                'error'=>$_FILES[$param]['error'],
            );
        } else {
            foreach ($_FILES[$param]['tmp_name'] as $key=>$temp){
                $out[]= array(
                    'tmp_name'=>$_FILES[$param]['tmp_name'][$key],
                    'name'=>$_FILES[$param]['name'][$key],
                    'type'=>$_FILES[$param]['type'][$key],
                    'size'=>$_FILES[$param]['size'][$key],
                    'error'=>$_FILES[$param]['error'][$key],
                );    
            }
        }
        
        return $out;
    }

    /**
     * Get a request parameter.
     * The order is compatible with php/ini's 'variables_order'. The only 
     * addition is the x option that represents the provided $_GET  @see TGlobal::script
     * @param string $param the searched param
     * @param mixed $default The value to be returned in case nothing was found
     * @param string $order the order of the searched (compatible with php/ini's 'variables_order' - http://php.net/manual/en/ini.core.php#ini.variables-order)
     * @return string
     */
    public static function request($param, $default = '', $order = 'escxgp') {
        // if we already searched for the param, we won't do it again..
        if (isset(self::$request_vars[$param]))
            return self::$request_vars[$param];

        // search for the param in the provided order
        $toreturn = $default;
        $characters = array_reverse(str_split(strtolower($order)));
        foreach ($characters as $char) {
            switch ($char) {
                case 'p':
                    if ('' != self::post($param)) {
                        $toreturn = self::post($param);
                        break 2; // exit foreach
                    }
                    break;
                case 'g':
                    if ('' != self::get($param)) {
                        $toreturn = self::get($param);
                        break 2; // exit foreach
                    }
                    break;
                case 'x':
                    if ('' != self::script($param)) {
                        $toreturn = self::script($param);
                        break 2; // exit foreach
                    }
                    break;
                case 'c':
                    if ('' != self::cookie($param)) {
                        $toreturn = self::cookie($param);
                        break 2; // exit foreach
                    }
                    break;
                case 's':
                    if ('' != self::server($param)) {
                        $toreturn = self::server($param);
                        break 2; // exit foreach
                    }
                    break;
                case 'e':
                    if ('' != self::env($param)) {
                        $toreturn = self::env($param);
                        break 2; // exit foreach
                    }
                    break;
            }
        }

        // save the result since this is quite expensive operation
        self::$request_vars[$param] = $toreturn;

        return $toreturn;
    }

}

TGlobal::init();
<?php
require_once TRIO_DIR.'/whereis.php';
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
 * @package 3oScript
 */
if(!class_exists('TGlobal'))
{
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

           if(isset(self::$request['query']))
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
        public static function get($param, $default='') {
               if(!isset(self::$get[$param])) return $default;
               return self::$get[$param];
        }
        
        /**
         * Append new GET parameters to the requested query
         * @param string|array $param
         * @param mixed $value
         * @return string The new get query (e.g. param1=value&param2=other_value)
         * @throws BadMethodCallException - for development, if the second parameter is missing
         */
        public static function buildQuery($param, $value = NULL)
        {
            // get a new copy of the GET array
            $get = self::$get;
            
            // behaviour 1: if it is given only one array parameter, use that to update the
            if(is_array($param))
            {
                return http_build_query(array_merge($get,$param));
            }
            if (is_object($param))
            {
                // treat the param as an array
                return http_build_query(array_merge($get,(array)$param));
            }
            
            if (is_null($value))
            {
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
        public static function getQuery(){
            if(isset(self::$request['query']))
                return self::$request['query'] ;
            return '';
        }

        /**
         * GET parameters recived by PHP. Most times, it's the same as TGlobal::get()
         * @see TGlobal::get
         * @param string $param
         * @param string $default What should be returned if there param was not found
         * @return string
         */
        public static function script($param, $default='') {
               if(!isset($_GET[$param])) return $default;
               return $_GET[$param];
        }

        /**
         * Get POST parameters ($_POST)
         * @param string $param
         * @param string $default What should be returned if there param was not found
         * @return string
         */
        public static function post($param, $default = '') {
               if (! isset($_POST[$param])) return $default;
               return $_POST[$param];
        }

        /**
         * Get the cookie vars ($_COOKIE)
         * @param string $param
         * @param string $default What should be returned if there param was not found
         * @return string
         */
        public static function cookie($param, $default='') {
               if (! isset($_COOKIE[$param])) return $default;
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
        public static function setCookie($param, $value = 1, $expire = 0, $extra = array())
        {
            // try to find the perfect expiration timestamp
            $expire_timestamp = 0;
            if (!empty($expire))
            {
                try{
                    if ('session' == $expire)
                    {
                        // the user may only want to set the extra params
                        $expire_timestamp = 0;
                        throw new Exception('found');
                    }
                    
                    if (is_numeric($expire))
                    {
                        $now = time();
                        if ($expire < $now){
                            // asume the user wants a relative timestamp
                            $expire_timestamp = time() + (int)$expire;
                            throw new Exception('found');
                        } else {
                            // this is old-school
                            $expire_timestamp = (int)$expire;
                            throw new Exception('found');
                        }
                    }
                    
                    // this will throw an exception (that probably doesn't have 
                    // 'found' as a message) if the expire can't be evaluated as a time
                    $date = string2date($expire);
                    
                    $expire_timestamp = $date->getTimestamp();
                    
                } catch (Exception $e)
                {
                    if ('found' != $e->getMessage())
                    {
                        $expire_timestamp = 0;
                    }
                }
            }
            
            
            $extra = (array) self::populate($extra, array(
                'path'=>'/',
                'domain'=>  null,
                'secure' => false, 
                'httponly' => false 
            ));
            $success = setcookie($param, $value, $expire_timestamp, $extra['path'], $extra['domain'], $extra['secure'], $extra['httponly']);
            // in case you disperately need the cookie value during the same
            // script - you should not rely on that...
            $_COOKIE[$param] = $value;
        }
        
        public static function unsetCookie($param, $extra = array())
        {
            // if, by any chance, get an object or something else...
            $extra = (array) $extra;
            self::populate($extra, array(
                'path'=>'/',
                'domain'=>  TGlobal::server('HTTP_HOST'),
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
        public static function session($param, $default = '')
        {
            // start the session if it is not started
            if (session_id() === "") session_start ();

            if (!isset($_SESSION[$param])) {
                
                return $default;
            }
            else {
                if (is_array($default) || is_object($default))
                {
                    $_SESSION[$param] = TGlobal::populate($_SESSION[$param], $default);
                }
                return $_SESSION[$param];
            }
        }

        /**
         * Set a session variable
         * @param type $param
         * @param type $value
         */
        public static function setSession($param, $value)
        {
            // start the session if it is not started
            if (session_id() === '') session_start ();

            $_SESSION[$param] = $value;
        }

        public static function unsetSession($param)
        {
            // start the session if it is not started
            if (session_id() === '') session_start ();

            unset($_SESSION[$param]);
        }

        /**
         * get enviroment variables ($_ENV)
         * @param string $param
         * @return string
         */
        public static function env($param)
        {
            if (!isset($_ENV[$param])) return "";
            else return $_ENV[$param];
        }

        /**
         * Get Server variables ($_SERVER)
         * @param type $param
         * @return string
         */
        public static function server($param)
        {
            if (!isset($_SERVER[$param])) return "";
            else return $_SERVER[$param];
        }

        /**
         * Get uploaded file data
         * @param string $param
         * @return boolean|array false if there is no file
         */
        public static function file($param)
        {
            if (!isset($_FILES[$param])) return false;
            else return $_FILES[$param];
        }

        /**
         * Get a request parameter
         * @param string $param the searched param
         * @param string $order the order of the searched (compatible with php/ini's 'variables_order' - http://php.net/manual/en/ini.core.php#ini.variables-order)
         * @return string
         */
        public static function request($param, $order = 'escgp')
        {
            // if we already searched for the param, we won't do it again..
            if (isset (self::$request_vars[$param]))
                return self::$request_vars[$param];

            // search for the param in the provided order
            $toreturn = "";
            $characters = array_reverse(str_split(strtolower($order)));
            foreach ($characters as $char)
            {
                switch ($char)
                {
                    case 'e':
                        if ('' != self::env($param))
                        {
                            $toreturn = self::env($param);
                            break 2;// exit foreach
                        }
                        break;
                    case 'g':
                        if ('' != self::get($param))
                        {
                            $toreturn = self::get($param);
                            break 2;// exit foreach
                        }
                        break;
                    case 'p':
                        if ('' != self::post($param))
                        {
                            $toreturn = self::post($param);
                            break 2;// exit foreach
                        }
                        break;
                    case 'c':
                        if ('' != self::cookie($param))
                        {
                            $toreturn = self::cookie($param);
                            break 2;// exit foreach
                        }
                        break;
                    case 's':
                        if ('' != self::server($param))
                        {
                            $toreturn = self::server($param);
                            break 2;// exit foreach
                        }
                        break;
                }
            }

            // save the result since this is quite expensive operation
            self::$request_vars[$param] = $toreturn;

            return $toreturn;
        }
        
        /**
         * Populate an array or an object with the specified default values if those are not set already
         * @param object|array $original Theoriginal object/array
         * @param object|array $defaults An object/array containing the default values
         * @return object|array The original array or object with the default values set
         * @throws UnexpectedValueException
         */
        public static function populate($original, $defaults)
        {
            $using_array = true;
            if (is_object($original))
            {
                $using_array = false;
            } elseif (!is_array($original))
            {
                // I don't know what to do with this...
                throw new UnexpectedValueException('the first parameter from TGlobal::populate should be eather an array or an object.'.  gettype($original).' was given');
            }
            
            if (!is_array($defaults) && !is_object($defaults))
            {
                // foreach will complain with this
                throw new UnexpectedValueException('the second parameter from TGlobal::populate should be eather an array or an object.'.  gettype($original).' was given');
            }
            
            foreach($defaults as $key=>$value)
            {
                if ($using_array && !isset($original[$key]))
                {
                    $original[$key] = $value;
                } elseif (!$using_array && !isset ($original->$key)){
                    $original->$key = $value;
                }
            }
            
            return $original;
        }

    }
    
}
TGlobal::init();

if (!function_exists('string2date'))
{
    /**
     * Creates a DateTime object form a string, a DateInterval or a ISO8601 interval (P<date>T<time>, used by DateInterval Constructor)
     * @param mixed $string
     * @return \DateTime
     * @throws DomainException  when the string can not be evaluated eather way.
     * @todo This may be better in a separate class
     */
    function string2date($string)
    {
        if ($string instanceof DateTime) return $string;
        if ($string instanceof DateInterval) return date_create ()->add($string);
        try
        {
            // for relative format
            $time = new DateTime($string);
            return $time;
        }  catch (Exception $e){}
        // for P...T... format (ISO 8601)
        $interval = new DateInterval($string);
        return date_create()->add($interval);
    }
    
    
}
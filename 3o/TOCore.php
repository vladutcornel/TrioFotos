<?php

if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT']);
}

if (!defined('TRIO_DIR'))
    define('TRIO_DIR', __DIR__);
require_once TRIO_DIR . '/framework-core.php';

/**
 * The center of 3OScript redirect mechanism
 * It determines the php file that should be loaded.
 * For non-php files, it just dumpes the contents and sets the Mime type
 * accordinglly
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oFramework
 * @subpackage Core
 */
class TOCore {

    /**
     * @staticvar array The extra params of the array
     */
    public static $params = array();


    /**
     * The file name for the file loaded by the current script
     * @var string
     */
    public static $file = '';

    /**
     * The file path (relative to SITE_ROOT) loaded by the current script
     * @var string
     */
    public static $file_path = '';

    /**
     * The class loaded by the current script
     * @var string
     */
    public static $main_class = '';
    
    /**
     * The main object used to render the page
     * @var object
     */
    public static $root = null;
    
    /**
     * List of prefixes for the main class.
     * @var array
     */
    public static $prefixes = array('Page', 'Page_', 'P');
    
    /**
     * Set if an exception should be thrown if there is no class in the loaded file.
     * @var boolean
     */
    public static $throw_class_exception = true;
    
    /**
     * Sets if an exception should be thrown if there is no method to be loaded
     * Note that this doesn't matter if TOCore::$throw_class_exception is set to false
     * @var boolean 
     */
    public static $throw_method_exception = true;

    /**
     * This loads the requested page from a .php file
     * @param $page string the requested URI
     * @return The loaded filename, without extension
     */
    public static function load($page = '') {
        // if there is no page, we use the index
        if ($page == '')
            $page = 'index';

        // get the virtual directories
        $parts = explode('/', $page);

        if (is_dir(SITE_ROOT . '/' . $page) && file_exists(SITE_ROOT . '/' . $page . '/index.php')) {
            // a directory with index.php was requested
            $page.= '/index.php';
            $parts[] = 'index';
            include_once $page;
        } elseif (file_exists(SITE_ROOT . '/' . $page) && is_file(SITE_ROOT . '/' . $page)) {
            // load the requested file
            $fileinfo = pathinfo(SITE_ROOT . '/' . $page);

            $last = count($parts) - 1;
            $parts[$last] = $fileinfo['filename'];



            if ($fileinfo['extension'] != 'php') {
                // it's not a php script
                header("Content-Type: " . TUtil::getMimeType(SITE_ROOT . '/' . $page));
                echo file_get_contents(SITE_ROOT . '/' . $page);

                die();
            }

            include_once SITE_ROOT . '/' . $page;
        } elseif (file_exists(SITE_ROOT . '/' . $page . '.php')) {
            // the file was requested without .php extension
            include_once SITE_ROOT . '/' . $page . '.php';
        } else {
            // no luck so far, we try loading the parent directory
            $parent_dir = implode('/', array_slice($parts, 0, -1));
            $slice = array_slice($parts, -1);
            if ('' != $slice[0])
                array_unshift(self::$params, $slice[0]);
            if ($parent_dir != '') {
                return self::load($parent_dir);
            }

            if (file_exists(SITE_ROOT . '/index.php')) {
                // try to load the homepage.
                return self::load("index");
            }

            // we still couldn't find a file to load
            throw new TOCoreException('',TOCoreException::NO_FILE);
        }

        // save the loaded file path
        static::$file_path = implode('/', $parts).'.php';
        
        // return the filename, so we can figure the class to load
        return $parts[count($parts) - 1];
    }
    
    /**
     * Sets and returns the main class for the requested file.
     * To avoid name conflicts with Library's classes, the main class can have a prefix
     * @return boolean|string
     */
    public static function find_main_class()
    {
        
        // The class name should only contain letters, numbers or underscores ("_")
        static::$main_class = preg_replace('/[^a-z0-9_]+/i', '_', static::$file);
        
        if (!TUtil::isIterable(static::$prefixes))
        {
            throw new UnexpectedValueException('Please provide an array or an object for main class prefix');
        }
        
        foreach (static::$prefixes as $prefix) {
            if (class_exists($prefix.static::$main_class)){
                return (static::$main_class = $prefix.static::$main_class);
            }
        }
        
        if (class_exists(static::$main_class))
        {
            return static::$main_class;
        }
        
        return false;
    }

    /**
     * Tells weather or not this is a AJAX request.
     * The request is tought to be AJAX if HTTP_X_REQUESTED_WITH header is set to 'XMLHttpRequest'
     * or, more reliably, the request parameter(eather via post, or via get) is 'ajax'
     * @return boolean
     */
    public static function isAjax() {
        return
                (TGlobal::server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest')
                ||
                (TGlobal::request('request','','pgx') == 'ajax');
    }

    /**
     * Tells weather or not this is a CSS request.
     * The request is tought to be CSS if the 'request' parameter(eather via post, or via get) is 'css'
     * @return boolean
     */
    public static function isCss() {
        return (TGlobal::request('request','','pgx') == 'css');
    }

    /**
     * Tells weather or not this is a JavaScript request.
     * The request is tought to be JavaScript if the 'request' parameter(eather via post, or via get) is 'js'
     * @return boolean
     */
    public static function isJavascript() {
        return (TGlobal::request('request','','pgx') == 'js');
    }
    
    /**
     * Load the appropriate method from the given class.
     * This assumes the class file is loaded or loadable via the whereis mechanism.
     * This is used by TOCore to load the main class, but can be used load other
     * class as if it was the main class
     * @param string|object $class
     * @throws TOCoreException when there is no main method in the class
     */
    public static function parse($class){
        
        if(!is_object($class)){
            $page = new $class(self::$params);
        } else {
            $page = $class;
        }
        
        
        if (self::isAjax() && method_exists($page, 'ajax')) {
            //run the main AJAX method
            $page->ajax(self::$params);
        } else {

            if ('POST' == TGlobal::server('REQUEST_METHOD') && method_exists($page, 'post_request')) {
                $page->post_request(self::$params);
            } elseif (method_exists($page, 'get_request')) {
                $page->get_request(self::$params);
            }
            if (self::isJavascript() && method_exists($page, 'javascript')) {
                // run Javascript method
                $page->javascript(self::$params);
            } elseif (self::isCss() && method_exists($page, 'css')) {
                // run CSS method
                $page->css(self::$params);
            } elseif (method_exists($page, 'main')) {
                // run the main method
                $page->main(self::$params);
            } else {
                // No main method. The fun is over
                if(static::$throw_method_exception)
                    throw new TOCoreException(self::$file, TOCoreException::NO_MAIN);
            }
        }
        
        return $page;

    }

    /**
     * This should only be called once on a page request.
     * Use TOCore::parse to share functionality between web pages.
     * 
     * It loads the appropriate file and an object of the main class
     * (that should have the same name as the file). A custom URi can be provided,
     * but if it's not, 
     *
     * Unless it's a AJAX request and the ajax() method is provided, based on 
     * the request type (POST or GET), it will try to fire get_request() or post_request()
     * 
     * Then it tries to invoke the appropriate method for the request
     * (ajax, javascript, css) or the main() method that should be in all the
     * class files ment for display.
     * 
     * @param string $uri the custom uri to load
     */
    public static function main($uri = null) {

        // turn on output buffering so nothing is isplayed unless everything is OK
        ob_start();

        //
        $queryArray = array();

        static::$file = "";
        if (!is_null($uri)){
            // this is a custom request
            static::$file = self::load($uri);
        } elseif ('' != TGlobal::server('REDIRECT_QUERY_STRING')) {
            // We are here probably by a redirect and the server fed us something like "?page=<<something here>>" (the default Trio Core suggestion), so load the page
            parse_str(TGlobal::server('REDIRECT_QUERY_STRING'), $queryArray);
            $page = $queryArray['page'];
            static::$file = self::load($page);
        } else {
            // We are here organic (via include or require),
            // so we should figure the name of the file
            static::$file = basename(TGlobal::server('PHP_SELF'), ".php");
        }


        // We have the name, let's run the script...

        if (static::find_main_class()) {
            
            self::$root = self::parse(new static::$main_class(static::$params));
            
        } else {
            // The class is not declared
            if (static::$throw_class_exception)
                throw new TOCoreException(static::$file,  TOCoreException::NO_CLASS);
        }

        ob_end_flush();
    }

}

/**
 * Exceptions related to TrioCore file and class loading
 */
class TOCoreException extends Exception{
    const NO_CLASS = 1; //001
    const NO_MAIN = 2;  //010
    const NO_FILE = 4;  //100
    public $request_file = '';
    
    public function __construct($file = '', $code = 0, $previous = null) {
        switch($code){
            case self::NO_FILE:
                $message = 'No file could be loaded.
                    Please create a index.php file in the root directory of your site ('.SITE_ROOT.')';
                break;
            case self::NO_CLASS:
                $message = 'Can\'t find the main class.
                    Please create a ' . $file . ' class in ' . $file . '.php';
                break;
            case self::NO_MAIN:
                $message = 'There is no method main() in the ' . $file . '.php class file.
                    The script can not be loaded';
                break;
            default:
                $message = 'Undefined TOCore exception';
        }
        parent::__construct($message, $code, $previous);
        $this->request_file = $file;
    }
}

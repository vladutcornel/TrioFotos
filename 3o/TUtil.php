<?php

require_once TRIO_DIR.'/framework-core.php';

/**
 * Some utility functions wrapped in a class.
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oFramework
 * @subpackage Core
 */
abstract class TUtil{
    /**
     * Trick to make this class static (can not be instantiated)
     */
    final function __construct(){}
    
    /**
     * The URL used to fetch the known mime types - if this ever changes
     * @see TUtil::getMimeType
     */
    const MIME_TYPES_URL = 'http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';
    

    /**
     * Extracts a portion of the text from the word at the starting position
     * @param string $input the input text
     * @param int $start Witch word to start from (0 = first)
     * @param int $chunk The number of words to return
     * @return string
     */
    public static function word_slice($input, $start = 0, $words = 80)
    {
        $arr = preg_split("/[\s]+/", $input,$words+1);
	return join(' ',array_slice($arr,$start,$words));
    }
    
    /**
     * Save data as cache into a file using the serialize function.
     * 
     * @param mixed $data the data to be saved
     * @param file $file where to save it
     * @param time $cache_interval
     */
    public static function saveSerializedCache($data, $file, $cache_interval) {
        $interval = self::string2date($cache_interval);
        $expire = $interval->format(DateTime::ATOM);
        
        $obj = (object) array(
            'timeout'=>$expire,
            'data'=> serialize($data)
        );
        file_put_contents($file, serialize($obj));
    }
    
    /**
     * Read the contents of Cache file.
     * If the data is to old, false is returned
     * @param string $cache_file the path to the cache file
     * @return mixed the cached data or false if it is too old or for some reason it's not accesible
     */
    public static function readSerializedCache($cache_file)
    {
        try{
            if (!file_exists($cache_file))
            {
                throw new Exception;
            }
            $contents = file_get_contents($cache_file);
            if ($contents == false)
            {
                // == works here insted of === because empty string is equaly bad
                throw new Exception;
            }
            $json = unserialize($contents);
            if (false == $json)
            {
                throw new Exception;
            }

            if (!isset($json->timeout) || !isset($json->data))
            {
                throw new Exception;
            }

            $expire = new DateTime($json->timeout);
            if ($expire->getTimestamp() <= time())
            {
                throw new Exception;
            }
            return unserialize($json->data);
        }  catch (Exception $e){
            return false;
        }
    }
    
    /**
     * Change the class of an object
     * Warning! The original object is changed
     * @param object $obj the object to be converted
     * @param string $class_type the new class name
     * @author toma at smartsemantics dot com
     * @see http://www.php.net/manual/en/language.types.type-juggling.php#50791
     */
    public static function changeClass(&$obj, $class_type) {
        if (class_exists($class_type, true)) {
            $obj =
                unserialize(
                    preg_replace(
                            "/^O:[0-9]+:\"[^\"]+\":/i", "O:" . strlen($class_type) . ":\"" . $class_type . "\":", serialize($obj)
                    )
                );// unserialize
        }// if class exists
    }// changeClass()
    
    /**
     * Helper function to determine if the provided variable can be iterated 
     * by foreach (it's an object or an array).
     * 
     * @param mixed $var the tested variable
     * @return boolean true if it can be used in a foreach
     */
    public static function isIterable($var) {
        return (is_array($var) || is_object($var));
    }
    
    /**
     * Creates a DateTime object form a string, a DateInterval or a ISO8601 
     * interval (P<date>T<time>, used by DateInterval Constructor)
     * @param mixed $string
     * @return \DateTime
     * @throws DomainException  when the string can not be evaluated eather way.
     * @todo This may be better in a separate class
     */
    public static function string2date($string) {
        if ($string instanceof DateTime)
            // we've got a DateTime
            return $string;
        if ($string instanceof DateInterval)
            // add the interval to current date
            return date_create()->add($string);
        try {
            // for relative format - will throw an Exception if the time is in the wrong format
            $time = new DateTime($string);
            return $time;
        } catch (Exception $e) {
            
        }
        // for P...T... format (ISO 8601)
        $interval = new DateInterval($string);
        return date_create()->add($interval);
    }
    
    
    /**
     * Populate an array or an object with the specified default values if those 
     * are not set already.
     * @param object|array $original Theoriginal object/array
     * @param object|array $defaults An object/array containing the default values
     * @return object|array The original array or object with the default values set
     * @throws UnexpectedValueException
     */
    public static function populate($original, $defaults) {
        // determine if the original is an object or an array
        $using_array = true;
        if (is_object($original)) {
            $using_array = false;
        } elseif (!is_array($original)) {
            // I don't know what to do with this...
            throw new UnexpectedValueException('the first parameter from TUtil::populate should be eather an array or an object.' . gettype($original) . ' was given');
        }

        // determine if the default is an array or object
        if (!self::isIterable($defaults)) {
            // foreach will complain with this
            throw new UnexpectedValueException('the second parameter from TUtil::populate should be eather an array or an object.' . gettype($original) . ' was given');
        }

        // populate all values that are not already set
        foreach ($defaults as $key => $value) {
            if ($using_array && !isset($original[$key])) {
                $original[$key] = $value;
            } elseif (!$using_array && !isset($original->$key)) {
                $original->$key = $value;
            }
        }

        return $original;
    }
    
    /**
     * Get Client's IP address
     * if not otherwise specified and available, the address within the LAN will 
     * be returned. As a fallback or if the global IP is specified, the client's 
     * global accesible IP will be returned ($_SERVER[REMOTE_ADDR])
     * @param boolean $global set to true if $_SERVER[RENOTE_ADDR] should be returned
     * @return string 
     */
    public static function getIP($global = false){
        if ($global){
            return TGlobal::server('REMOTE_ADDR','UNKNOWN');
        }
        
        return TGlobal::server('HTTP_CLIENT_IP',
                TGlobal::server('HTTP_X_FORWARDED_FOR',
                        TGlobal::server('HTTP_X_FORWARDED',
                                TGlobal::server('HTTP_FORWARDED',
                                        TGlobal::server('REMOTE_ADDR',
                                            'UNKNOWN'
                                        )
                                )
                        )
                )
               );
    }
    
    /**
     * Return the Mime Type of a file using the best method available.
     * it is based on UNIX file command, but will fallback to a 
     * extension-based guessing on Windows or other systems
     * @param string $file the file (it must exist)
     * @param bool $strict On Linux servers, this will return a more strict mime type
     * @return string a mime type like "image/png"
     */
    public static function getMimeType($file, $strict = FALSE){
        // for invalid files, guess based on the extension
        if (!file_exists($file)){
            return self::guessMimeType($file);
        }
        
        //try the UNIX file command to get something like "text/html; charset=utf-8"
        if ($strict)
            $file_data = exec('file -bi "'.addcslashes ($file,'"').'"');
        else
            $file_data = exec('mimetype "'.addcslashes ($file,'"').'"');
        
        if (!$file_data){
            // the sistem does not have the file comand (it's not UNIX)
            return self::guessMimeType($file);
        }
        // $file_data should be something like "text/html; charset=utf-8" in strict mode
        $components = explode($strict?';':':', $file_data);
        $usefull = $strict? array_shift($components)
            // in normal mode, it's something like "/file/path: mime/type"
            : array_pop($components);
        $valid = preg_match('#(?P<type>[a-z\-\+]+/[a-z\-\+]+)#i', $usefull, $matches);
        if (!$valid){
            // for some reason, the mime could not be determined, so we guess it
            return self::guessMimeType($file);
        }
        
        return $matches['type'];
    }
    
    /**
     * Try to guess the file mime type based on the extension.
     * You should use getMimeType instead, unless you are absolutely certain that
     * your script will always run ONLY on a system that will fallback to this (like Windows)
     * @param string $file
     * @return string a mime type like "image/png"
     */
    public static function guessMimeType($file){
        $tempfile = sys_get_temp_dir().'/3oMimes';
        $mime_array = self::readSerializedCache($tempfile);
        if (!is_array($mime_array) || count($mime_array) < 1){
            $mime_array = array();
            foreach(@explode("\n",@file_get_contents(self::MIME_TYPES_URL))as $x)
                if(isset($x[0])&&$x[0]!=='#'&&preg_match_all('#([^\s]+)#',$x,$out)&&isset($out[1])&&($c=count($out[1]))>1)
                    for($i=1;$i<$c;$i++)
                        $mime_array[$out[1][$i]] = $out[1][0];
             // cache everything for one day       
             self::saveSerializedCache($mime_array, $tempfile, '1 day');
        }
        $fileext = substr(strrchr($file, '.'), 1); 
        
        // if the file type can not be determined, it will use "text/plain"
        $mime_array = self::populate($mime_array, array($fileext => 'text/plain'));
        return $mime_array[$fileext];
    }
    
}
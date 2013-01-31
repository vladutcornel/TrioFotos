<?php  
/**  
 * Utility to locate class files. 
 * @package 3oFramework
 * @subpackage Core
 * @author Cornel Borina <cornel@scoalaweb.com>  
 */  

class Whereis{
    /**
     * Associative array where the key represents the class name 
     * (including namespace if any) and the value is the file path where that 
     * class is located
     * @var array
     */
    private static $whereis = array();
    
    public static function init(){
        spl_autoload_register (__CLASS__.'::_autoload', true, true); 
    }
    
    public static function _autoload($class_name){
        // try to load class file
        if (isset(self::$whereis[$class_name]))  
        {  
            include self::$whereis[$class_name];  
        }
    }
    
    public static function register(){
        $first_arg = func_get_arg(0);  
        if (!is_array($first_arg))  
        {
            // we got a list  
            $nr_args = func_num_args();  
            $args = func_get_args();  
            $new_args = array();  
            for ($i = 1; $i < $nr_args; $i+=2)  
            {  
                $new_args[$args[$i-1]] = $args[$i];  
            }  
            trio_whereis($new_args);  
            return;  
        }
        
        foreach($first_arg as $class=>$file)  
        {
            self::$whereis[$class] = $file;  
        }
    }
}

Whereis::init();
<?php
/**
 * Generate Whereis file - for 3oScript only
 * FOR DEVELOPMENT OF 3oFramework ONLY
 * @author cornel
 */
class GenerateWhereis {
    var $whereis = array();
    var $basedir = '/';

    function main(){
        $this->basedir = __DIR__;
        $this->read_dir();
        
        $file = '<?php 
/** 
 * Helper file to locate all the framework classes - Auto generated 
 * @package 3oScript 
 * @author Cornel Borina <cornel@scoalaweb.com> 
 */ 

/** 
 * User defined classes 
 */ 
global $WHEREIS_USER;
if (!is_array($WHEREIS_USER))
    $WHEREIS_USER = array(); 

/**
 * TriO classes
 */
$WHEREIS = '.var_export($this->whereis, true).';


/**
 * For Trio Framework internal use only!!!
 * Tries to load the class file for the given class. 
 * The user can add extra non-TriO classes by registering the class names and 
 * file paths to trio_whereis.
 * @global array $WHEREIS
 * @global array $WHEREIS_USER
 * @param string $class_name
 * @see trio_whereis
 */
function trio_autoload($class_name){ 
    global $WHEREIS; 
    global $WHEREIS_USER; 
     
    if (!defined("TRIO_DIR")) 
    { 
        define("TRIO_DIR", __DIR__); 
    } 
    // try to load TriO script class 
    if (isset($WHEREIS[$class_name])) 
    { 
        include TRIO_DIR.\'/\'.$WHEREIS[$class_name]; 
    } 
     
    // try to load User-defined class 
    if (isset($WHEREIS_USER[$class_name])) 
    { 
        include $WHEREIS_USER[$class_name]; 
    } 
} 

/* 
 * Register autoload function and set it to prepand (3rd param) so other autoload functions can be declared 
 */ 
spl_autoload_register (\'trio_autoload\', true, true); 

/** 
 * Tell the script where to look for the invoked class 
 * You can provide a parameters list with the odd index (1st param, 3rd...)  
 * being the class names andd the even parameters being the file path. 
 * Or you can directly provide an associative array with the keys being the  
 * class names and the values the path 
 * @param array $whereis array(class_name=>file_path) 
 */ 
function trio_whereis() 
{ 
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
    global $WHEREIS_USER; 
    foreach($first_arg as $class=>$file) 
    { 
        $WHEREIS_USER[$class] = $file; 
    } 
}';
        /*
         * Only dump the file contents so we won't accidentaly overwrite important things
         */
        //echo '<pre>';
        highlight_string($file);
        //echo '</pre>';
    }

    /**
     * Recurseve method to generate $whereis
     * @param type $dir
     */
    function read_dir($dir = '') {
        // from PHP Manual
        if ($handle = opendir($this->basedir.'/'.$dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry == "." || $entry == "..") {
                    continue;
                }
                if (\in_array($entry, array(
                    'whereis.php'
                )))
                {
                    continue;
                }

                if (\is_dir($this->basedir.'/'.$dir.'/'.$entry)){
                    $this->read_dir($dir.'/'.$entry);
                    continue;
                }

                $class_name = \basename($entry,'.php');
                $this->whereis[$class_name] = $dir.'/'.$entry;
            }
            closedir($handle);
        }
    }
}
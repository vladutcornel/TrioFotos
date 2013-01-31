<?php
require_once TRIO_DIR.'/framework-core.php';
/**
 * The basic library. implements the methods that should be loaded by any other 
 * classes
 * Pus some data that should be available anywhere
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oFramework
 * @subpackage Core
 */
class TObject{
    /**
     * Method that should be present in all class files
     */
    public function main()
    {
        // if there is not Main method implemented, it's probably a library class
        echo "This file is not for view";
    }

    /**
     * Convert the current element to a HTML code. This should be allways 
     * overridden
     * @return string The HTML representation of the current object
     */
    public function toHtml(){
        return __CLASS__;
    }

    /**
     * General getter
     * @param string $var_name
     * @return mixed
     */
    public function getVar($var_name){
        $var_name = strtolower($var_name);
        if(isset($this->$var_name))
            return $this->$var_name;
        return FALSE;
    }

    /**
     * General setter
     * @param string $var_name
     * @param mixed $new_value
     * @return \TObject for method chaining (if wanted)
     */
    public function setVar($var_name, $new_value){
        $var_name = strtolower($var_name);
        $this->$var_name = $new_value;
        return $this;
    }

    /**
     * Add support for unimplemented setters and getters
     * @param string $function
     * @param array $args
     * @return type
     * @throws BadMethodCallException
     */
    public function __call($function, $args)
    {
        // test a getter
        $is_getter = preg_match("/^get(?P<varname>[a-z_]+)$/i",$function, $matches);
        if ($is_getter){
            return $this->getVar(strtolower($matches['varname']));
        }

        // test a boolean getter
        $is_boolean_getter = preg_match("/^is(?P<varname>[a-z_]+)$/i",$function, $matches);
        if ($is_boolean_getter){
            return $this->getVar(strtolower($matches['varname']))?true:false;
        }

        // test a setter
        $is_setter = preg_match("/^set(?P<varname>[a-z_]+)$/i",$function, $matches);
        if ($is_setter){
            return $this->setVar(strtolower($matches['varname']), $args[0]);
        }

        // Nothing worked. May be a misspell...
        throw new BadMethodCallException;
    }

    /**
     * Getter for private members.
     * The object properties shoud have eather getVarname() or get_varname() method
     * implemented or else a LogicException exception is thrown
     * @param string $name
     * @return mixed
     * @throws LogicException
     */
    public function __get($name) {
        // camel case getter
        $method = 'get'.ucfirst($name);
        if (method_exists($this, $method))
        {
            return call_user_func(array($this, $method));
        }
        // underline method
        $method = 'get_'.$name;
        if (method_exists($this, $method))
        {
            return call_user_func(array($this, $method));
        }

        // last chance: if the property was set, return the value
        if (isset($this->$name))
        {
            return $this->$name;
        }

        throw new LogicException;
    }

    /**
     * Setter for private members
     * The object properties shoud have eather a setVarname() or a set_varname()
     * method implemented or else a LogicException exception is thrown
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @throws LogicException
     */
    public function __set($name, $value) {
        // camel case getter
        $method = 'set'.ucfirst($name);
        if (method_exists($this, $method))
        {
            return call_user_func(array($this, $method), $value);
        }
        // underline method
        $method = 'set_'.$name;
        if (method_exists($this, $method))
        {
            return call_user_func(array($this, $method), $value);
        }

        // if the property doesn't exist, create one and give it the value
        if (!isset($this->$name))
        {
            $this->$name = $value;
            return;// so we won't throw the exception
        }

        throw new LogicException;
    }
    
    /**
     * No Operation. 
     * This can be used to take advantage of the whereis mechanism and load
     * classes without actually using them just yet .
     * You probably shouldn't abuse this feature.
     */
    public static function noop(){}
}
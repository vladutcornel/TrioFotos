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
     * Map of accessed proiperties
     * Used to store data accessed from outside (that triggered magic methods), 
     * without accidentally accessiong protected methods.
     * @var array
     */
    private $_prop = array();
    
    /**
     * Convert the current element to a text. 
     * @return string The string representation of the current object
     */
    public function __toString() {
        return get_called_class();
    }

    /**
     * General getter
     * @param string $var_name
     * @return mixed
     */
    public function getVar($var_name){
        $var_name = strtolower($var_name);
        if(isset($this->_prop[$var_name]))
            return $this->_prop[$var_name];
        return NULL;
    }

    /**
     * General setter
     * @param string $var_name
     * @param mixed $new_value
     * @return \TObject $this for method chaining
     */
    public function setVar($var_name, $new_value){
        $var_name = strtolower($var_name);
        $this->_prop[$var_name] = $new_value;
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
        // test a getter (get...), boolean getter (is...) or setter (set...)
        $is_func = preg_match("/^(?P<functype>get|is|set)(?P<varname>[a-z_]+)$/i",$function, $matches);
        if ($is_func){
            switch($matches['functype']){
                case 'get':
                    return $this->getVar(strtolower($matches['varname']));
                case 'is':
                    return $this->getVar(strtolower($matches['varname']))?true:false;
                case 'set':
                    return $this->setVar(strtolower($matches['varname']), $args[0]);
            }
            
        }

        // Nothing worked. May be a misspell...
        throw new BadMethodCallException($function.' method is not implemented');
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
        if (isset($this->_prop[$name]))
        {
            return $this->_prop[$name];
        }

        throw new LogicException($name.' was not set and does nott have a getter defined');
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
        if (!isset($this->_prop[$name]))
        {
            $this->_prop[$name] = $value;
            return;// so we won't throw the exception
        }

        throw new LogicException;
    }
    
    public function __isset($name) {
        return isset($this->_prop[$name]);
    }

        /**
     * No Operation. 
     * This can be used to take advantage of the whereis mechanism and load
     * classes without actually using them just yet.
     * You probably shouldn't abuse this feature.
     * 
     * This can be usefull if a file contains more than one class and you only 
     * need the one that is not registered with Whereis
     */
    public static function noop(){}
    
    /**
     * Create a new object of the current class and return it. Parameters can be 
     * passed to the constructor.
     * This only exists because constructions like "(new class)->method()" 
     * (new object dereference) are illegal before PHP 5.4.
     * 
     * @deprecated since version PHP 5.4
     */
    public static function create(){
        $class = new ReflectionClass(get_called_class());
        return $class->newInstanceArgs(func_get_args());
        
    }
}
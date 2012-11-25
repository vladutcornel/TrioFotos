<?php
/**
 * A HTML Toggleable field (checkbox/radio).
 *
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class ToggleField extends Input{
    /**
     * @param string $name the name of the field
     * @param mixed $value the value of the checkbox field
     * @param string $id
     */
    public function __construct($type,$name, $default = 1, $id = '') {
        parent::__construct($type, $name, $default, $id);
        $this->setAttribute('value', $default);
        $this->guess_value();
    }
    
    protected function guess_value(){
        // find the array basename
        $has_base = preg_match('/^\s*(?P<var_base>[^\[]+)/i', $this->getName(),$matches);
        if (! $has_base || !isset($matches['var_base']))
        {
            return;
        }
        $request = TGlobal::request($matches['var_base']);
        if (!is_array($request))
        {
            if ( '' != $request)
                // the request param is not an array
                $this->check();
            return;
        }
        
        // find sub-arrays in the name
        $has_subarray = preg_match_all('/\[(?P<index>[^\]]*)\]/i', $this->getName(),$subarrays);
        if (!$has_subarray || !isset($subarrays['index']) || count ($subarrays['index']) < 1)
        {
            // probably the name is just malformated
            return;
        }
        
        foreach ($subarrays['index'] as $index)
        {
            if ('' === $index)
            {
                if (in_array($this->getAttribute('value'), $request))
                    $this->check ();
                return;
            }
            if (is_array($request[$index]))
            {
                // move to the subarray
                $request = $request[$index];
            } else if ($request[$index] == $this->getAttribute('value')){
                $this->check();
            }
        }
        
    }
    
    public function check($check = true){
        if ($check)
            $this->setAttribute ('checked', 'checked');
        else
            $this->removeAttribute ('checked');
    }
    
    /**
     * Marks the checkbox as checked if the provided value matches the field value
     * Otherwise, unchecks it.
     * To really set the value, use Checkbox::setAttribute(...)
     * @param type $new_value
     * @return \Checkbox
     */
    public function setValue($new_value) {
        if ($new_value == $this->getAttribute('value'))
        {
            $this->setAttribute('checked', 'checked');
        } else{
            $this->removeAttribute('checked');
        }
        
        return $this;
    }
    
    /**
     * Returns the value of the field if checked or false if not
     * @return boolean|string
     */
    public function getValue() {
        if ('' != $this->getAttribute('checked'))
        {
            return $this->getAttribute('value');
        } else {
            return false;
        }
    }
}
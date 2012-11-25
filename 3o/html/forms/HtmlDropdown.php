<?php

require_once TRIO_DIR.'/whereis.php';

/**
 * A Html Dropdown (<select>)
 *
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class HtmlDropdown extends FormElement {
    /**
     * @var array The Option elements
     */
    private $options = array();
    
    /**
     * @var array The different group (optgroup) elements
     */
    private $groups = array();
    
    /**
     * 
     * @param string $name the field name
     * @param string $id
     */
    public function __construct($name = '', $id = '') {
        parent::__construct('select', $name, $id);
    }
    
    /**
     * Ads an option to the dropdown list and returns the HtmlElement created
     * @param string $value the value of the option
     * @param string $text the text to display. If it's an empty string or not specified, the value is used instead
     * @param string $group The group of options. If there is no group yet, one is created
     * @return \HtmlDropdownOption The option element created
     */
    public function addOption($value, $text = '', $group = null)
    {
        
        if (is_null($group)){
            $option = new HtmlDropdownOption($value, $text);
            $this->addChild($option);
            $this->options[]= $option;
            return $option;
        }
        
        if (!isset($this->groups[$group]))
        {
            $this->groups[$group] = new HtmlDropdownGroup($group);
            $this->addChild($this->groups[$group]);
        }
        $option = new HtmlDropdownOption($value, $text);
        $this->groups[$group]->addChild($option);
        return $option;
    }
    
    /**
     * Selects one or more elements from the list.
     * The values to be selected can be one or more string parameters or an array of values
     * @param string|array $new_value
     */
    public function setValue($new_value) {
        if (!is_array($new_value) && func_num_args() > 1)
        {
            $new_value = func_get_args();
        }
        $values = (array)$new_value;
        foreach ($this->options as $option)
        {
            foreach ($values as $value)
                if ($option->getValue() == $value)
                {
                    $option->select();
                }
        }
        
        foreach ($this->groups as $group)
        {
            $group->select($new_value);
        }
        
        return $this;
    }
    
    
}

/**
 * HtmlDropdownGroup - a group of options (wrapper for <optgroup>)
 * This class should not be instantiated by the user script
 */
class HtmlDropdownGroup extends HtmlElement
{
    public function __construct($label, $id = '') {
        parent::__construct('optgroup', $id);
        $this->setAttribute('label', $label);
    }
    
    public function select($new_value) {
        $values = (array)$new_value;
        foreach ($this->childs as $child)
        {
            $option = $child['element'];
            foreach ($values as $value)
                if ($option->getValue() == $value)
                {
                    $option->select();
                }
        }
        
        foreach ($this->groups as $group)
        {
            $group->select($new_value);
        }
    }
}

/**
 * HtmlDropdownOption - An option of the dropdown (wrapper for <option>)
 * This class should not be instantiated by the user script
 */
class HtmlDropdownOption extends HtmlElement
{
    public function __construct($value, $text = '', $id = '') {
        parent::__construct('option', $id);
        $this->setAttribute('value', $value);
        if ('' == $text) $text = $value;
        $this->setText($text);
    }
    
    public function select(){
        $this->setAttribute('selected', '1');
    }
    
    public function unselect(){
        $this->removeAttribute('selected');
    }
    
    public function getValue(){
        return $this->getAttribute('value');
    }
}
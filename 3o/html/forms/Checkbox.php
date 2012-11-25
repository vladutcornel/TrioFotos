<?php
/**
 * A HTML Checkbox.
 *
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class Checkbox extends ToggleField{
    /**
     * @param string $name the name of the field
     * @param mixed $value the value of the checkbox field
     * @param string $id
     */
    public function __construct($name, $default = 1, $id = '') {
        parent::__construct('checkbox', $name, $default, $id);
    }
}
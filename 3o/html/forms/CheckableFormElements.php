<?php

namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

/**
 * A pseudo form element to hold Checkbox and Radio Groups.
 * By default, this does not display anything, but rather holds the objects for 
 * the Input elements that can be displayed somewhere else.
 * You mus call ::show() for this to become displayable element
 *
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
abstract class CheckableFormElements extends FormElement {

    const CHECKBOX = 0;
    const RADIO = 1;

    private $type = self::CHECKBOX;
    private $inputs = array();
    private $display = false;

    public function __construct($type, $name, $id = '') {
        parent::__construct('input', $name, $id);

        $this->type = $type;
        
        if (self::CHECKBOX == $type) {
            $real_type = 'checkbox';
        } else {
            $real_type = 'radio';
        }

        $this->setAttribute('type', $real_type);
    }

    /**
     * Create a new Input FormElement and register it to the group.
     * @param text $value The element's value
     * @return \Input The FormElement created
     */
    public function addOption($value) {
        $input = new Input(
                        self::CHECKBOX == $this->type ? 'checkbox' : 'radio',
                        $this->getName(),
                        $value, preg_replace('/[^a-zA-Z\-_]/', '', $this->getName())
        );
        
        $input->setFixed(true);
        
        $this->inputs[] = $input;
        
        return $input;
    }
    
    /**
     * Checks the elements that have the specified value(s)
     * @param mixed $new_value the value of the element or an array of values
     */
    public function setValue($new_value) {
        /* @var $input Input */
        foreach ($this->inputs as $input)
        {
            if (\in_array($input->getValue(), (array)$new_value))
            {
                $input->setAttribute('checked', 'checked');
                if (self::RADIO == $this->type) break;
            } else {
                $input->deleteAttribute('checked');
            }
        }
    }

    public function getChildren() {
        return array();
    }

    /**
     * Force this group to act as a real container
     */
    public function show() {
        $this->display = true;
    }

    /**
     * Make this form element hidden
     */
    public function hide() {
        $this->display = false;
    }

    public function toHtml($echo = TRUE) {
        if (FALSE == $this->display)
            return '';
        $out = '';
        foreach ($this->inputs as $input) {
            $out.=$input->toHtml(false);
        }

        if ($echo)
            echo $out;

        return $out;
    }
    
    public function toCSS($echo = true) {
        if (FALSE == $this->display)
            return '';
        $out = '';
        foreach ($this->inputs as $input) {
            $out.=$input->toCSS(false);
        }

        if ($echo)
            echo $out;

        return $out;
    }
}
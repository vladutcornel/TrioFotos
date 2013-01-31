<?php

namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

/**
 * A multi-line text field (aka. textarea)
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class Textarea extends FormElement{
    public function __construct($name = '', $default = '', $id = ''){
        parent::__construct('textarea', $name, $id);
        
        $this->setValue($default);

        $this->setSingleTag(false);
    }

    public function setValue($newValue) {
        $this->setText($newValue);
    }
    
    public function setText($newText) {
        return parent::setText(\htmlentities($newText));
    }
    
    public function getText() {
        return html_entity_decode(parent::getText());
    }
}
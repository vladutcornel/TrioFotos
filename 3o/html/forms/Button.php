<?php

namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

define('BUTTON_SUBMIT', 'submit');
define('BUTTON_STANDARD', 'button');

/**
 * A form button
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class Button extends Input{
    
    const SUBMIT = 'submit';
    const STANDARD = 'button';
    const RESET = 'reset';
    public function __construct($type = self::STANDARD, $default = '',$name = '', $id = ''){
        parent::__construct($type, $name, $default, $id);

    }

    public function setLabel($text) {
        if($this->isSingletag())
            $this->setValue($text);
        else $this->setText ($text);
        
        return $this;
    }

    /**
     * Add a child to the button. If it's the first child, the button is transformed
     * from <input> to <button>
     * @param HtmlElement $child
     * @param boolean $before
     * @param int $position
     */
    public function addChild($child, $before = true, $position = -1) {
        $this->setSingleTag(false);
        $this->setTag('button');
        $this->setText($this->getValue());
        return parent::addChild($child, $before, $position);
    }
    /**
     * bypass the FormElement's Label+Info HTML
     */
    public function toHtml($echo = TRUE){
        return HtmlElement::toHtml($echo);
    }
    /**
     * bypass the FormElement's Label+Info CSS
     */
    public function toCss($echo = TRUE){
        return HtmlElement::toCss($echo);
    }
}
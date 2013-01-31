<?php

namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

/**
 * A hidden form field
 *
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class Hidden extends Input{
    public function __construct($name, $default='', $id=''){
        parent::__construct('hidden', $name, $default, $id);
        $this->setFixed(TRUE);
    }

    public function toHtml($echo = TRUE){
        return HtmlElement::toHtml($echo);
    }

    public function toCSS($echo = null){
        return '';
    }
}
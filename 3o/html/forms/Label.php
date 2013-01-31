<?php

namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

/**
 * A HTML Label element
 *
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class Label extends HtmlElement {
    
    /**
     * @param string $text The text content of the label
     * @param string|FormElement $for The associated field (the id of the element or the element itself)
     * @param string $id
     */
    public function __construct($text = '', $for = '', $id = '') {
        parent::__construct('label', $id);
        
        $this->setFor($for);
        $this->setText($text);
    }
    
    /**
     * Set the associated field
     * @param FormElement|string $element the element (FormElement) or element's ID
     * @return \FormLabel $this for method chaining
     */
    public function setFor($element)
    {
        if ($element instanceof FormElement)
        {
            // get the id of the form element
            $this->setAttribute('for', $element->getId());
        } else
        {
            // we assume we got some string (or something that can be converted to string)
            $this->setAttribute('for', "$element");
        }
        
        return $this;
    }
}
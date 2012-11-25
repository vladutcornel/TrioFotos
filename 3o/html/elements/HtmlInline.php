<?php

require_once TRIO_DIR.'/whereis.php';

/**
 * Utility for generic HTML inline elements
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage html
 */
class HtmlInline extends HtmlElement {
    private $href = null;
    public function __construct($tag = 'span', $id = '') {
        parent::__construct($tag, $id);
    }
    
    /**
     * Adds a Hyperlink REFference to the element, so the element will be clickable
     * @param type $destination
     * @return HtmlInline $this
     */
    public function href ($destination)
    {
        $this->href = $destination;
        if ($this->getTag() == 'a')
        {
            $this->setAttribute('href', $destination);
        }
        return $this;
    }
    
    /**
     * Renders the element. If there is a href, an link tag will suround this element
     * @param type $echo
     * @return type
     */
    public function toHtml($echo = true) {
        if (is_null($this->href) || $this->getTag() == 'a')
            return parent::toHtml($echo);
        
        $out = '<a href="'.htmlentities($this->href).'">';
        $out.= parent::toHtml(false);
        $out.= '</a>';
        
        if ($echo)
            echo $out;
        return $out;
    }
}

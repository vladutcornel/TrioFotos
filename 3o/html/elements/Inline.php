<?php
namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

/**
 * Utility for generic HTML inline elements.
 * Inline elements usually don't require sub-elements except text and can be 
 * wrapped in an anchor (link) tag
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage html
 */
class Inline extends HtmlElement {
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
     * @param boolean $echo
     * @return string
     */
    public function toHtml($echo = true) {
        if (\is_null($this->href) || $this->getTag() == 'a')
            return parent::toHtml($echo);
        
        $out = '<a href="'.\htmlentities($this->href).'">';
        $out.= parent::toHtml(false);
        $out.= '</a>';
        
        if ($echo)
            echo $out;
        return $out;
    }
}

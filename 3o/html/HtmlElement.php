<?php
namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

use trio\css\Style as Style;

/**
 * A generic HTML Element.
 * In most casses, you should not use this directly
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class HtmlElement extends Element{

    /**
     * @var Style The element's style
     */
    private $style;

    /**
     * @var array Element's classes (class atribute)
     */
    private $classes = array();

    /**
     * This automatically detects HTML5 single tags.
     * The $id parameter can be a selector in the form of "#id.class1.class2"
     * The id and classes will be extracted automatically
     * @param string $tagName
     * @param string $elementId the id or a CSS selector that this element will match
     */
    public function __construct($tag = '', $id = ''){
        parent::__construct($tag,$id);

        $singleTags = array('base','br','hr','img','input','param','source','track','wbr');
        if(\in_array($tag, $singleTags)){
            $this->setSingletag(true);
        }else{
            $this->setSingletag(false);
        }
        
        $this->style = new Style("#".$this->getId());
    }
    
    /**
     * Givest te abillity to set both the id and classes of the element
     * @param string $id
     * @return HtmlElement $this
     */
    public function setId($id) {
        // generate attributes based on the parameter
        $found = \preg_match_all('/(?P<type>#|\.)(?P<name>[a-z0-9_\-]+)/i', $id, $matches);
        if ($found)
        {
            // find the initial part of the string (may be the id)
            $found = \preg_match('/^.+[#.]/', $id, $start_matches);
            if ($found)
            {
                $newid = $start_matches[0];
            } else {
                $newid = '';
            }
            
            foreach ($matches['type'] as $i=>$type)
            {
                switch ($type)
                {
                    case '#':
                        $newid = $matches['name'][$i];
                        break;
                    case '.':
                        $this->addClass($matches['name'][$i]);
                        break;
                }
            }
        } else {
            $newid = $id;
        }
        if ($this->style)
            $this->style->setSelector('#'.$newid);
        return parent::setId($newid);
        
    }

    /**
     * Registers a new Class for this element
     * @param string $class_name
     * @return HtmlElement $this
     */
    public function addClass($class_name){
        $args = \func_get_args();
        foreach ($args as $class_name){
            $this->classes["$class_name"] = true;
        }
        $this->updateClasses();
        return $this;
    }

    /**
     * Remove a previously registered class
     * @param string $class_name
     * @return HtmlElement $this
     */
    public function removeClass($class_name){
        $args = \func_get_args();
        foreach ($args as $class_name){
            if(isset($this->classes["$class_name"]))
            {
                unset($this->classes["$class_name"]);

            }
        }
        $this->updateClasses();
        return $this;
    }

    /**
     * Reset the "class" attribute from the html tag
     */
    private function updateClasses(){
        $this->setAttribute('class', \implode( ' ',\array_keys($this->classes) ));
    }

    /**
     * Get the element's style
     * @return Style
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Fetch or just return the HTML code for this element
     * @see Element::toCode
     */
    public function toHtml($echo = true){
        return parent::toCode($echo);
    }
    
    /**
     * Because most of the HTMLElements overwrite the toHtml method,
     * this will make Element::toCode to do the right thing for the children 
     * @param boolean $echo
     * @return string
     */
    public function toCode($echo = true)
    {
        return $this->toHtml($echo);
    }

    /**
     * Fetch or just return the associated CSS styles for this element and it's children
     * @param boolean $echo true if he code should be printed
     */
    public function toCSS($echo = TRUE){
        if (! $this->canDisplay())
            return;
        
        $css = $this->getStyle()->get(true);
        foreach($this->childs as $child){
            if (!$child['element'] instanceof HtmlElement)
                continue;
            $css.= $child['element']->toCSS(false);
        }

        if ($echo) {
            echo $css;
        }

        return $css;
    }

    /**
     * Overload the addChild so the added child would be HtmlElement
     * @see Element::addChild
     */
    public function addChild($child, $before = true, $position = -1) {
        if (! $child instanceof HtmlElement) {
            throw new \DomainException($this);
        }
        parent::addChild($child, $before, $position);
        return $this;
    }

    /**
     * Provides a CSS-compatible #Selector
     */
    public function __toString(){
        return "#".$this->getId();
    }
}
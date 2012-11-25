<?php

require_once TRIO_DIR.'/whereis.php';

/**
 * Utility for manipulating HTML lists
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage html
 */
class HtmlList extends HtmlElement {
    /**
     * Simple unordered list (square bullets)
     */
    const UNORDERED = self::SQUARE;
    /**
     * Unordered list with square bullets
     */
    const SQUARE = 0;
    
    /**
     * Simple ordered list (Decimal numbers)
     */
    const ORDERED = self::DECIMAL;
    /**
     * Ordered list with decimal numbers
     */
    const DECIMAL = 1;
    
    /**
     * Unordered list with circle bullets
     */
    const CIRCLE = 2;
    
    const DISC = 3;
    
    
    const UPPER_ALPHA = 4;
    const ALPHA = self::UPPER_ALPHA;
    const UPPER_LATIN = self::UPPER_ALPHA;
    
    const LOWER_ALPHA = 5;
    const LOWER_LATIN = self::LOWER_ALPHA;
    
    const UPPER_ROMAN = 6;
    const ROMAN = self::UPPER_ROMAN;
    
    const LOWER_ROMAN = 7;
    
    const LOWER_GREEK = 8;
    const GREEK = self::LOWER_GREEK;
    
    const HEBREW = 9;
    
    const LEADNG_ZERO = 10;
    const LEADING = self::LEADNG_ZERO;
    const ADD0 = self::LEADNG_ZERO;
    const DECIMAL_LEADING_ZERO = self::LEADNG_ZERO;

    /**
     * Nothing displayed
     */
    const BLANK = 99;
    const NONE = self::BLANK;
    
    private $list_items = array();
    
    public function __construct($type = self::UNORDERED, $id = '') {
        parent::__construct('ul', $id);
        $this->setType($type);
    }
    
    public function setType($type)
    {
        switch ($type)
        {
            case HtmlList::SQUARE:
                    $this->setTag('ul');
                    $this->getStyle()->setMultipleProperties(array(
                        'list-style-type'=>'square'
                    ));
                break;
            case HtmlList::DECIMAL:
                    $this->setTag('ol');
                    $this->getStyle()->setMultipleProperties(array(
                        'list-style-type'=>'decimal'
                    ));
                break;
            case HtmlList::CIRCLE:
                    $this->setTag('ul');
                    $this->getStyle()->setMultipleProperties(array(
                        'list-style-type'=>'circle'
                    ));
                break;
            case HtmlList::DISC:
                    $this->setTag('ul');
                    $this->getStyle()->setMultipleProperties(array(
                        'list-style-type'=>'disc'
                    ));
                break;
            case HtmlList::LEADNG_ZERO:
                    $this->setTag('ol');
                    $this->getStyle()->setMultipleProperties(array(
                        'list-style-type'=>'decimal-leading-zero'
                    ));
                break;
            case HtmlList::UPPER_ALPHA:
                    $this->setTag('ol');
                    $this->getStyle()->setMultipleProperties(array(
                        'list-style-type'=>'upper-alpha'
                    ));
                break;
            case HtmlList::LOWER_ALPHA:
                    $this->setTag('ol');
                    $this->getStyle()->setMultipleProperties(array(
                        'list-style-type'=>'lower-alpha'
                    ));
                break;
            case HtmlList::UPPER_ROMAN:
                    $this->setTag('ol');
                    $this->getStyle()->setMultipleProperties(array(
                        'list-style-type'=>'upper-roman'
                    ));
                break;
            case HtmlList::LOWER_ROMAN:
                    $this->setTag('ol');
                    $this->getStyle()->setMultipleProperties(array(
                        'list-style-type'=>'lower-roman'
                    ));
                break;
            case HtmlList::LOWER_GREEK:
                    $this->setTag('ol');
                    $this->getStyle()->setMultipleProperties(array(
                        'list-style-type'=>'lower-greek'
                    ));
                break;
            case HtmlList::HEBREW:
                    $this->setTag('ol');
                    $this->getStyle()->setMultipleProperties(array(
                        'list-style-type'=>'hebrew'
                    ));
                break;
            case HtmlList::BLANK:
                    $this->setTag('ul');
                    $this->getStyle()->setMultipleProperties(array(
                        'list-style-type'=>'none'
                    ));
                break;
            default:
                // by default we assume it is an image URL for the bullets
                $this->setTag('ul');
                $this->getStyle()->setMultipleProperties(array(
                     'list-style-type'=>'square'
                    ,'list-style-image'=>'url(\''.$type.'\')'
                ));
        }
    }
    
    /**
     * Insert a list item at the specified position
     * @param int $index
     * @param mixed $value A Html Element or a string
     * @return \HtmlList $this
     */
    public function set($index, $value)
    {
        $nr_items = count ($this->list_items);
        
        for ($i = $nr_items; $i > $index; $i--)
        {
            $this->list_items[$i] = $this->list_items[$i-1];
        }
        $this->list_items[$index] = new HtmlListElement($value);
        $this->addChild($this->list_items[$index], true, $index);
        return $this;
    }
    
    /**
     * Shorthand method for appending an element to the end of the list
     * @param mixed $value
     * @return HtmlList
     * @see HtmlList::set
     */
    public function add($value)
    {
        return $this->set(count($this->list_items), $value);
    }
    
    /**
     * Get the List element at the specified position
     * @param int $index
     * @return HtmlListElement
     */
    public function get($index)
    {
        return $this->list_items[$index];
    }
}

/**
 * TriO Library internal class for an element in the list
 */
class HtmlListElement extends HtmlElement{
    private $contents = '';
    public function __construct($contents, $id = '') {
        parent::__construct('li', $id);
        
        $this->contents = $contents;
    }
    
    public function toHtml($echo = true) {
        if ($this->contents instanceof HtmlElement)
            $this->setText ($this->contents->toHtml(false));
        else
            $this->setText ("{$this->contents}");
            
        return parent::toHtml($echo);
    }
}
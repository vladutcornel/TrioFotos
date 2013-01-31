<?php
namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

/**
 * A generic SGML (XML or HTML) element
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class Element extends \TObject
{
    /**
     * @var string The tag name
     */
    private $tag;

    /**
     * @var array the descendents
     */
    protected $childs = array();

    /**
     * @var string The string content of the element
     */
    private $text;

    /**
     * @var array tag atributes
     */
    private $attributes = array();

    /**
     * @var string unique identifier for this element ("id" atribute)
     */
    private $htmlId;

    /**
     * @staticvar array A list with all ids used by the page (so there won't be duplicates)
     */
    private static $usedIds = array();

    /**
     * @var boolean true if the tag should be rendered without closing tag (no children)
     */
    private $singleTag = false;

    /**
     * @var int The next child that should be retrived by Element::eachChild()
     * @see Element::eachChild()
     */
    private $position = 0;
    
    /**
     * Weather or not the element should be displayed
     * @var boolean
     */
    private $displayable = true;

    /**
     * @param string $tagName The name of the element (e.g. "div" for <div>)
     * @param string $tagId The unique identifier for this element
     */
    public function __construct($tag = 'div', $id = '')
    {
        $this->tag = $tag;

        $this->setId($id);
    }

    /**
     * Mark this element as single so no children will be displayed and 
     * the format will be <tag_name />
     * @param boolean $single
     * @return Element $this for method chaining
     */
    public function setSingleTag($single)
    {
        // make sure it's boolean
        $this->singleTag = $single?true:false;
        return $this;
    }

    /**
     * Check if this element won't display children
     * @return bool
     */
    public function isSingletag()
    {
        return $this->singleTag;
    }

    /**
    * Set a new ID for this element
    * @param string $newid
    * @return Element $this
    */
    public function setId($newid)
    {
        // if there is no ID, create one from the tag
        if ('' == \trim($newid)) $newid = $this->getTag ();
        
        // if the tag was not set, generate one random ID
        if ('' == \trim($newid)) $newid = \md5(\rand());
        
        // make sure it's a unique id
        $element_nr = 1;
        $id = $newid;
        while (\in_array($id, self::$usedIds))
        {
            $id = $newid . $element_nr;
            $element_nr ++;
        }

        // remove old id from the list
        if (false !== ($position = \array_search($this->htmlId, self::$usedIds)))
        {
            // modify in place
            self::$usedIds[$position] = $id;
        }else {
            // append the new id
            self::$usedIds[] = $id;
        }

        // save the id
        $this->htmlId = $id;

        // update the element attributes
        $this->setAttribute('id', $this->getId());


        return $this;
    }

    /**
     * Get the element identifier (ID element)
     * @return string
     */
    public function getId()
    {
        return $this->htmlId;
    }

    /**
     * Get the name of the tag
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set a new tag/element type
     * @param string $newname the new tag name
     * @return Element $this
     */
    public function setTag($newname)
    {
        $newname = \trim($newname);
        // cherck if it's a valid XML tag name
        if (!\preg_match("/^[a-z_][a-z0-9\:_\-\.]*$/i", $newname))
        {
            throw new \DomainException("Invalid tag name");
        }
        $this->tag = $newname;
        return $this;
    }

    /**
     * Get a attribute value
     * @param string $attrName the index of the atribute
     * @return string the atribute's value
     */
    public function getAttribute($attrName)
    {
        if (!isset($this->attributes[$attrName])) return '';
        return $this->attributes[$attrName];
    }

    /**
     * Set an attribute value
     * @param string $attrName the index of the attribute
     * @param string $attrValue the new value for the attribute
     * @return Element $this
     */
    public function setAttribute($attrName, $attrValue)
    {
        $attrName = \trim($attrName);
        if (!\preg_match("/^[a-z_][a-z0-9\:_\-\.]*$/i", $attrName))
        {
            throw new \DomainException("Invalid attribute name");
        }
        $this->attributes[$attrName] = $attrValue;
        return $this;
    }
    
    /**
     * Unset a node attribute. If there is no attribute, nothing is done
     * @param string $attrName
     * @return Element $this
     */
    public function deleteAttribute($attrName)
    {
        if (isset($this->attributes[$attrName]))
        {
            unset($this->attributes[$attrName]);
        }
        return $this;
    }
    
    /**
     * Alias of Element::deleteAttribute
     * @param string $attrName
     * @return Element $this
     * @see Element::deleteAttribute
     */
    public function removeAttribute($attrName)
    {
        return $this->deleteAttribute($attrName);
    }

    /**
     * Set the inner text of the element. This can contain markup code too
     * @param string $newText the new inner text
     * @return Element $this
     */
    public function setText($newText)
    {
        $this->text = $newText;
        return $this;
    }

    /**
     * Get the current inner text
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
    
    /**
     * Tells wether or not this element can be displayed
     * @return boolean
     */
    public function canDisplay()
    {
        return $this->displayable;
    }
    
    /**
     * Set the displayable flag
     * @param boolean $state
     * @return Element $this for method chaining
     */
    public function setDisplayable($state)
    {
        $this->displayable = $state?true:false;
        return $this;
    }
    
    /**
     * Set the displayable flag to true
     * @return \Element $this for method chaining
     */
    public function show(){
        return $this->setDisplayable(true);
    }
    
    /**
     * Set the displayable flag to false
     * @return \Element $this for method chaining
     */
    public function hide(){
        return $this->setDisplayable(false);
    }

    /**
     * addChild - add a child node to the element
     *
     * @param Element $child the Element to be added
     * @param bool $before true if the element should be before the text
     * @param int $position the position in the children array. -1 to add it to the end of the array
     * @return Element $this
     */
    public function addChild($child, $before = true, $position = -1)
    {
        if ($child instanceof Element)
        {
            $toAdd = array(
                'element' => $child,
                'before' => $before // before internal text?
                );
            $nrChilds = \count($this->childs);
            if ($position == - 1 || $position >= $nrChilds)
            {
                // the inserted position is the end of the array
                $position = $nrChilds;

            }
            else
            {
                // we need to make room for the new Element
                for($i = $nrChilds; $i > $position; $i--)
                {
                    $this->childs[$i] = $this->childs[$i - 1];
                }
            }

            $toAdd['position'] = $position;
            $this->childs[$position] = $toAdd;
        }

        return $this;
    }
    
    /**
     * Appends this element to the specified parent
     * @param Element $parent
     * @param boolean $before
     * @param int $position
     */
    public function addTo($parent, $before = true, $position = -1)
    {
        if ($parent instanceof Element)
        {
            $parent->addChild($this, $before, $position);
            return $this;
        }
        
        throw new \BadMethodCallException('The parent of an element must also be an element');
    }

    /**
     * Retrives the Child information (index position, Element object and 
     * possition relative to the text - before = true/false)
     * @param string $id the element's ID
     * @return array|null An associative array( 'position'=>int, 'before'=>bool, 'element'=>Element ) or NULL if there is no child
     */
    public function getChildById($id){
        $elements = $this->childs;
        foreach ($elements as $element){
            if ($element['element']->getId() == $id){
                return $element;
            }
        }

        return null;
    }

    /**
     * Retrives the Child information (index position, Element object and 
     * possition relative to the text - before = true/false) based on the element type/tag
     * @param string $tag the element's type name
     * @return array|null An associative array( 'position'=>int, 'before'=>bool, 'element'=>Element ) or NULL if there is no child
     */
    public function getFirstOf($tag){
        $elements = $this->childs;
        foreach ($elements as $element){
            if ($element['element']->getTag() == $tag){
                return $element;
            }
        }

        return null;
    }

    /**
     * Retrives the next Child information, based on an internal pointer. 
     * After the list child, NULL is returned and the pointer repositions on 
     * the first element
     * @param string $tag the element's type name
     * @return array|null An associative array( 'position'=>int, 'before'=>bool, 'element'=>Element ) or NULL if there is no child
     */
    public function eachChild(){
        if ($this->isSingletag() || \count($this->childs) < 1)
            return null;
        if ($this->position == \count ($this->childs) ){
            $this->position = 0;
            return NULL;
        }
        return $this->childs[ $this->position++ ];
    }
    
    /**
     * Retrieves all known children of this element.
     * If it's a single tag, no element will be returned no mater if any child 
     * was added
     * @return array
     */
    public function getChildren()
    {
        $children = array();
        
        if ($this->isSingletag() || \count($this->childs) < 1)
            return $children;
        
        while(($child = $this->eachChild()) != NULL)
        {
            $children[]= $child['element'];
        }
        
        return $children;
    }
    

    /**
     * Display or fetch the Element's code
     * @param bool $echo Display and fetch(true) or just fetch(false)
     * @return string the code for this element, including child nodes
     */
    public function toCode($echo = true)
    {
        if (! $this->canDisplay())
            return '';
        /*Register start-tag attributes*/
        $tag = $this->startTag(false);
        if ($this->isSingletag()){
            if ($echo)
                echo $tag;
            return $tag;
        }
        // print a closing-tag element
        /* Register child elements */
        $htmlBefore = '';
        $htmlAfter = '';
        $nrChilds = count($this->childs);
        for($i = 0; $i < $nrChilds; $i++)
        {
            // skip non-elements
            if (!$this->childs[$i]['element'] instanceof Element)
                continue;
            // get the code before and after the text
            if ($this->childs[$i]['before'])
            {
                $htmlBefore .= $this->childs[$i]['element']->toCode(false);
            }
            else
            {
                $htmlAfter .= $this->childs[$i]['element']->toCode(false);
            }
        }
        /* Echo if necessary */
        if ($echo)
        {
            echo "{$tag}" , $htmlBefore, $this->text, $htmlAfter , "</{$this->tag}>";
        }
        return "{$tag}" . $htmlBefore . $this->text . $htmlAfter . "</{$this->tag}>";
    }

    /**
     * Fetch or display only the start tag of the element
     * @param boolean $echo true if you want to print the tag
     * @return string the start tag code
     */
    public function startTag($echo = true){
        /*Register start-tag attributes*/
        $tag = $this->tag;
        foreach($this->attributes as $attr => $value)
        {
            $tag .= " $attr=\"" . htmlentities("$value") . "\"";
        }
        // print a single tag
        if ($this->isSingletag())
        {
            if ($echo)
            {
                echo "<{$tag} />";
            }
            return "<{$tag} />";
        }

        /* Echo if necessary */
        if ($echo)
        {
            echo "<{$tag}>";
        }
        return "<{$tag}>";
    }

    /**
     * Fetch or display the element's ending tag. 
     * If this is a single-tag element, an empty string will be returned
     * @param boolean $echo true if the tag should be printed
     * @return string seomething like "</tag_name>"
     */
    public function endTag($echo = true){
        if ($this->isSingletag()) return "";
        if ($echo){
            echo '</',$this->getTag(),'>';
        }
    }
}
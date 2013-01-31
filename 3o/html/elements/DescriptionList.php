<?php
namespace trio\html;
require_once \TRIO_DIR.'/framework.php';
/**
 * DescriptionList - a list of definitions / descriptions(<dl>)
 *
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class DescriptionList extends HtmlElement {
    /**
     * 
     * @param string $id 
     */
    public function __construct($id = '') {
        parent::__construct('dl', $id);
    }
    
    /**
     * 
     * @param type $title
     * @param type $description
     * @return \DescriptionList $this for method chaining
     */
    public function add($title = '', $description = ''){
        $this->addTitle($title);
        $this->addDescription($description);
        
        
        return $this;
    }
    
    public function addTitle($title){
        $dt = new DescriptionListTitle($this->getId().'_title');
        if ($title instanceof HtmlElement){
            $dt->addChild($title);
        } else {
            $dt->setText($title);
        }
        $this->addChild($dt);
        return $dt;
    }
    
    public function addDescription($description){
        $dd = new DescriptionListDefinition($this->getId().'_definition');
        if ($description instanceof HtmlElement){
            $dd->addChild($description);
        } else {
            $dd->setText($description);
        }
        
        $this->addChild($dd);
        return $dd;
    }
    
    public function addDefinition($description){
        return $this->addDescription($description);
    }
}

class DescriptionListTitle extends HtmlElement{
    public function __construct($id = '') {
        parent::__construct('dt', $id);
    }
}

class DescriptionListDefinition extends HtmlElement{
    public function __construct($id = '') {
        parent::__construct('dd', $id);
    }
}
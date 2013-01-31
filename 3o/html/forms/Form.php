<?php
namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

/**
 * A HTML form element.
 * It can set the value of a inner element. If the element does not exist,
 * a hidden element with that name and value is created
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class Form extends Block{
    private $form_fields = array();
    public function __construct($action, $method = 'get', $id=''){
        parent::__construct('form',$id);
        $this->setAttribute('action', $action);
        $this->setAttribute('method', $method);
    }

    /**
     * Sets the value for the FormElement identified by field name.
     * If there is no such field, one Hidden field is created
     * @param string $field tag's "name" value of the field
     * @param string $newValue
     */
    public function setValue($field, $newValue) {
        $found = false;

        foreach ($this->form_fields as $child){
            if (! $child instanceof FormElement) continue;

            if ($child->getName() == $field && ! $child->isFixed()){
//                if ($child instanceof RadioGroup)
//                    var_dump ($child);
                $child->setValue($newValue);
                $found = true;
            }//if
        }//foreach
        
        if (!$found) {
            $hidden = new Hidden($field, $newValue);
            $this->addChild($hidden);
        }

        return $this;
    }
    
    public function addChild($child, $before = true, $position = -1) {
        parent::addChild($child, $before, $position);
        
        // find a form field and register it
        if ($child instanceof HtmlElement)
        {
            $elements = self::find_form_elements($child);
            foreach ($elements as $element)
            {
                if (!is_null($element))
                {
                    $this->form_fields[]= $element;
                }
            }// foreach form element from the child
        }// if the child is a form field
    }
    
    
    private static function find_form_elements(HtmlElement $e)
    {
        $find_queue = new \SplQueue();
        $find_queue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE);
        
        // the return array
        $elements = array();
        
        // add the element to the queue
        $find_queue->enqueue($e);
        
        // move the queue pointer to the first position
        $find_queue->rewind();
        
        // walk the queue
        while ($find_queue->valid())
        {
            // get the current element
            $element = $find_queue->current();
                        
            // return it if it's a Form Element
            if ($element instanceof FormElement)
            {
                // add the element to the list and
                $elements[] = $element;
                
                // move to the next element because a form element never has a child Form Element
                $find_queue->next();
                continue;
            }
            
            // add the child nodes to the queue
            $children = $element->getChildren();
            foreach ($children as $child)
            {
                $find_queue->enqueue($child);
            }
            
            $find_queue->next();
        }
        
        unset($find_queue);
        
        return $elements;
    }
}
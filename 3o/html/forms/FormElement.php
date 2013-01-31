<?php

namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

/**
 * An element designed to be included in a form
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class FormElement extends HtmlElement{
    
    /**
     * @var HtmlLabel
     */
    private $label = NULL;
    
    /**
     * 
     * @var HtmlElement 
     */
    private $tip = NULL;
    
    /**
     * 
     * @var boolean
     */
    private $external_label;
    
    private $fixed = false;

    public function __construct($type, $name, $id = ''){
        parent::__construct($type, $id);

        $this->setAttribute('name', $name);

        $this->guess_value();
    }
    
    protected function guess_value(){
        // find the array basename
        $has_base = \preg_match('/^\s*(?P<var_base>[^\[]+)/i', $this->getName(),$matches);
        if (! $has_base || !isset($matches['var_base']))
        {
            return;
        }
        $request = TGlobal::request($matches['var_base']);
        if (!is_array($request))
        {
            // the request param is not an array
            $this->setValue($request);
            return;
        }
        
        // find sub-arrays in the name
        $has_subarray = \preg_match_all('/\[(?P<index>[^\]]*)\]/i', $this->getName(),$subarrays);
        if (!$has_subarray || !isset($subarrays['index']) || \count ($subarrays['index']) < 1)
        {
            // probably the name is just malformated
            return;
        }
        
        foreach ($subarrays['index'] as $index)
        {
            if ('' === $index)
            {
                // the last part is []
                return;
            }
            if (\is_array($request[$index]))
            {
                // move to the subarray
                $request = $request[$index];
            } else {
                $this->setValue($request[$index]);
            }
        }
        
    }
    
    public function setFixed($fixed = true){
        $this->fixed = $fixed;
    }
    
    public function isFixed()
    {
        return $this->fixed;
    }
    
    /**
    * Access the <label> element from outside
    */
    public function getLabel(){
        return $this->label;
    }

    /**
     * Access the infobar tip element from outside. The infobar is intended to
     * be a information text after the actual element
     */
    public function getInfobar(){
        return $this->tip;
    }

    public function setLabel($text){
        if ($text instanceof Label)
        {
            $this->label = $text;
            $this->external_label = true;
        }
        if (!$this->label instanceof Label)
        {
            $this->create_label();
        }
        
        if ($text instanceof HtmlElement && ! $text instanceof Label) {
            $this->label->addChild($text);
        } else {
            $this->label->setText($text);
        }
        
        return $this;
    }
    
    private function create_label($text = ''){
        $this->label = new Label($text, $this);
        $this->external_label = false;
    }
    
    public function setInfotext($text){
        if (!$this->tip instanceof HtmlElement)
        {
            $this->create_tip();
        }
        $this->tip->setText($text);
        return $this;
    }
    
    public function create_tip()
    {
        $this->tip = new HtmlElement('span','#'.$this->getId().'tip.help-inline');
    }

    public function setValue($new_value) {
//        if ($new_value == '')
//            var_dump (debug_backtrace ());
        $this->setAttribute('value', $new_value);
        return $this;
    }
    
    public function getValue(){
        return $this->getAttribute('value');
    }
    
    public function setName($name)
    {
        $this->setAttribute('name', $name);
        return $this;
    }
    
    public function getName() {
        return $this->getAttribute('name');
    }

    /**
    * prints and/or returns the html code for this form element.
    * The label is printed before the element while the infobar is printed after
    */
    public function toHtml($echo = TRUE){
        $html = '';
        if ($this->external_label === false)
            $html.= $this->label->toHtml(FALSE);
        $html.= parent::toHtml(false);
        if ($this->tip instanceof HtmlElement)
            $html.= $this->tip->toHtml(false);

        if($echo){
            echo $html;
        }
        return $html;
    }


    public function toCSS($echo = true){
        $css = '';
        if ($this->external_label === false)
            $css = $this->label->toCSS(false);
        $css.=parent::toCSS(false);
        if ($this->tip instanceof HtmlElement)
            $css.= $this->tip->toCSS(false);

        if($echo) {
            echo $css;
        }

        return $css;
    }
}
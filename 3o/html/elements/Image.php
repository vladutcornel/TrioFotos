<?php
namespace trio\html;
use trio\css\Unit as CSSUnit;
require_once \TRIO_DIR.'/framework.php';

/**
 * A web page image (<img>)
 *
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class Image extends Inline{
    /**
     * @param string $src
     * @param string $alt
     * @param string $id
     */
    public function __construct($src = '', $alt = '', $id = '') {
        parent::__construct('img', $id);
        
        // set the source of the image
        $this->setAttribute('src', $src);
        
        // set the alternative text - required by HTML specs
        $this->setAttribute('alt', $alt);
    }
    
    /**
     * Set the width of the image
     * @param numeric $width
     * @param bool $only_css only update the CSS property
     * @return \HtmlImage $this
     */
    public function setWidth($width, $only_css = true) {
        // prepare the unit
        $unit = new CSSUnit($width);
        
        // set the CSS attribute
        $this->getStyle()->setProperty('width', $unit);
        
        // delete the HTML attribute if there is one
        $this->deleteAttribute('width');
        
        // return if we don't need to update the HTML attrbute
        if ($only_css) return $this;
        
        // set teh HTML attribute
        if ($unit->getUnitGroup() == CSSUnit::PIXEL)
        {
            $this->setAttribute('width', (int)$unit->getSize());
        } elseif ($unit->getUnitGroup() == CSSUnit::PERCENT) {
            $this->setAttribute('width', (int)$unit->getSize().'%');
        }
        
        return $this;
    }
    
    /**
     * Set the height of the image
     * @param numeric $height
     * @param bool $only_css only update the CSS property
     * @return \HtmlImage $this
     */
    public function setHeight($height, $only_css = true) {
        // prepare the unit
        $unit = new CSSUnit($height);
        
        // set the CSS attribute
        $this->getStyle()->setProperty('height', $unit);
        
        // delete the HTML attribute if there is one
        $this->deleteAttribute('height');
        
        // return if we don't need to update the HTML attrbute
        if ($only_css) return $this;
        
        // set teh HTML attribute
        if ($unit->getUnitGroup() == CSSUnit::PIXEL)
        {
            $this->setAttribute('height', (int)$unit->getSize());
        } elseif ($unit->getUnitGroup() == CSSUnit::PERCENT) {
            $this->setAttribute('height', (int)$unit->getSize().'%');
        }
        
        return $this;
    }
}
<?php

require_once TRIO_DIR.'/whereis.php';

/**
 * A helper class to store a valid CSS size
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage CSS
 */
class CSSUnit {
    // unit groups
    const PERCENT = 0;
    const EM = 1;
    const EX = 2;
    const PIXEL = 4; // Pixel based 
    const METRIC = 5;// includes inches because they can be converted
    
    /**
     * @var int The size
     */
    private $size = 0;

    /**
     * @var string the measurement unit
     */
    private $unit = 'px';
    
    private static $unit_groups = array(
        'px'=> CSSUnit::PIXEL,
        '%'=> CSSUnit::PERCENT,
        'em'=> CSSUnit::EM,
        'ex'=> CSSUnit::EX,
        'cm'=> CSSUnit::METRIC,
        'mm'=> CSSUnit::METRIC,
        'in'=> CSSUnit::METRIC,
        'pt'=> CSSUnit::METRIC,
        'pc'=> CSSUnit::METRIC
    );

    public function __construct($unit = '') {
        if (is_numeric($unit))
        {
            $unit = $unit.'px';
        }
        static $css_units;
        if (!isset($css_units) || empty($css_units))
            $css_units = implode('|', array(
                '%',
                'px',
                'pixels?',
                'em',   // font size
                'ex',   // x letter size
                // metric system
                'cm',
                'centimeters?',
                'mm',
                'milimeters?',
                // american system
                'in(ch)?',
                'pt',   // point = 1/72 inch
                'points?',  //
                'pc',   // pica = 12 points
                'pica(s)?'
            ));

        $valid = preg_match('/(?P<size>[0-9]+((.|,)[0-9]+)?)\s*(?P<unit>'.$css_units.')?/', $unit, $matches);
        //$valid = preg_match('/(?P<size>[0-9]+)/i', $unit, $matches);
        var_dump($unit, $valid, $matches);
        if ($valid)
        {
            $this->size = (float) str_replace(',', '.', $matches['size'] );
            if (isset($matches['unit']))
            {
                switch ($matches['unit']) {
                    case '%': 
                        $this->unit = '%'; 
                        break;
                    case 'px':
                    case 'pixel':
                    case 'pixels':
                        $this->unit  = 'px';
                        break;
                    case 'em':   // font size
                        $this->unit = 'em';
                        break;
                    case 'ex':   // x letter size
                        $this->unit = 'ex';
                    // metric system
                    case 'cm':
                    case 'centimeter':
                    case 'centimeters':
                        $this->unit = 'cm';
                        break;
                    case 'mm':
                    case 'milimeter':
                    case 'milimeters':
                        $this->unit = 'mm';
                        break;
                    case 'in':
                    case 'inch':
                        $this->unit = 'in';
                        break;
                    case 'pt':case 'point': case 'points':
                        $this->unit = 'pt';
                        break;
                    case 'pc':case 'pica': case 'picas':
                        $this->unit = 'pc';
                        break;
                    default:
                        $this->unit = 'px';
                        break;
                }
            }
        }
    }
    
    /**
     * Get the unit group for this unit
     */
    public function getUnitGroup()
    {
        return self::$unit_groups[$this->unit];
    }
    
    /**
     * Retrve the size of this unit
     * @return float
     */
    public function getSize()
    {
        return $this->size;
    }
    
    /**
     * Get the size in Centimeters (if the unit is metric)
     * @return float
     */
    public function getCentimeters()
    {
        switch ($this->unit)
        {
            case 'cm':
                return $this->size;
            case 'mm':
                return $this->size * 10;
            case 'in':
                return $this->size / 2.54;
        }
    }
    
    /**
     * return a CSS-compatible string
     * @return string
     */
    public function __toString() {
        return $this->size.$this->unit;
    }
}

<?php

require_once TRIO_DIR.'/whereis.php';

/**
 * A CSS style sheet
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage CSS
 * @todo Add support for & subclasses (like LESS)
 * @todo ADD support for CSSAtribute interface
 */
class Style extends TObject{

    /**
     * @var string the CSS selector for this style
     */
    private $selector;

    /**
     * @var array An associative array for each style property
     */
    private $properties = array();

    /**
     * @var array all the styles that descend from this
     */
    private $substyles = array();

    /**
     * @param string $selector
     * @param array|string $properties The initial properties for this style
     */
    public function __construct($selector = NULL, $properties = NULL) {
        $this->selector = $selector;
        if (is_array($properties)){
            $this->setMultipleProperties($properties);
        }
    }

    /**
     * Fetch all the properties in a string
     * @param boolean $use_selector if set to false, only the properties are return (without selector or child styles)
     * @param string $parent_selector the selector that should be added before the style's own selector
     * @return string
     */
    public function get($use_selector = true, $parent_selector = ''){
        // the output
        $style = '';

        // only print if there is something to print
        if(count($this->properties))
        {
            // prepare selector for this style
            if($use_selector){
                // break selector into peaces
                $selector_parts = explode(',', $this->getSelector());

                // and add every peace to the selector
                $hasComa = false;
                foreach($selector_parts as $part){
                    if($hasComa) $style.=',';
                    else $hasComa = true;
                    $style.=$parent_selector.' '.$part;
                }


                $style.='{';
            }

            // print every property
            $comma = FALSE;// use or not a semicolon (;)
            foreach($this->properties as $key=>$value){
                if($comma) $style.=';';
                else $comma = true;

                $style.=$key.':'.$value;
            }

            // prepare the closing brace of the selector
            if($use_selector){
                $style.='}';
            }
        }


        // prepare selectors for substyles
        if($use_selector) {
            foreach($this->substyles as $substyle){

                $style.= $substyle->get(true, $parent_selector . ' ' . $this->getSelector());
            }
        }
        return $style;
    }

    /**
     * Print the full style
     * @param boolean $use_selector false if you only want the properties
     */
    public function flush($use_selector = TRUE){
        echo $this->get($use_selector);
    }

    /**
     * Getter for the selector
     * @return string
     */
    public function getSelector(){
        return $this->selector;
    }

    /**
     * Reset the style selector
     * @param string $new_selector
     * @return Style $this for method chaining
     */
    public function setSelector($new_selector) {
        $this->selector = "$new_selector";
        return $this;
    }

    /**
     * Set a custom CSS property-value
     * @param string $property_name The property name to be set
     * @param mixed $property_value
     * @return Style $this for method chaining
     */
    public function setProperty($property_name, $property_value) {
        // determine setter name
        $setter = self::getPropertyMethod($property_name);

        if (method_exists($this,$setter)){
            // use a specialised setter
            $this->$setter($property_value);
        }else {
            // use a generic setter
            $this->properties[$property_name] = trim("$property_value");
        }

        return $this;
    }

    /**
     * Get the value for the specified property
     * @return string|int
     */
    public function getProperty($property_name) {
        $getter = self::getPropertyMethod($property_name,"get");
        if (method_exists(this, $getter)){
            return $this->$getter();
        }

        return $this->properties[$property_name];
    }

    public static function getPropertyMethod($property_name, $prefix = "set"){//eg. margin left
        $temp = str_replace("-"," ",$property_name); // e.g. "margin left"
        $temp = ucwords($temp); // eg. "Margin Left"
        return $prefix.str_replace(" ","",$temp);// eg. "setMarginLeft"
    }

    /**
     * Set multiple properties at once
     */
    public function setMultipleProperties(array $properties){
        foreach($properties as $key=>$property){
            $this->setProperty($key, $property );
        }
    }

    public function addSubStyle(Style $new_style){
        $this->substyles[$new_style->getSelector()] = $new_style;
    }

    /**
     * remove a substyle. The substyle can be a Style object or a string
     * @param Style|string $substyle
     */
    public function removeSubStyle($style){
        if($style instanceof Style){
            if(isset($this->substyles[$style->getSelector()])){
                unset($this->substyles[$style->getSelector()]);
            }
        }else{
            if (isset($this->substyles["$style"])) {
                unset($this->substyles["$style"]);
            }
        }
    }

    // Costumised setters and getters /////////////////////////////////////////

    /**
     * Set all background properties in on shot
     */
//    public function setBackground($background_properties) {
//        // split the background properties by a whitespace
//        $bg_props = preg_split("/[\s,]+/", $background_properties);
//        $props_array = array();
//
//        $size_values = 0;
//
//        foreach ($bg_props as $prop){
//            // check if it's a color and set the color $props_array['color'] if so and continue
//            try{
//                $color = new CSSColor($prop);
//                $props_array['color'] = $color;
//                continue;
//            } catch(NotAColor $e){}
//
//            //todo: check all belo
//            // check if it's in {top,left,bottom,right, center} and set the position
//            $align = preg_match("/^(top|left|bottom|right|center)$/i",strtolower($prop));
//            if ($align){
//                if (!isset($props_array['position'])){
//                    $props_array['position'] = array(
//                        'vertical'=>'center',
//                        'horizontal'=>'center'
//                    );
//                }
//                switch ($prop){
//                    case "top":
//                    case "bottom":
//                        $props_array['position']['vertical'] = $prop;
//                        break;
//                    case "left":
//                    case "right":
//                        $props_array['position']['horizontal'] = $prop;
//                }
//                echo $size_values++;
//                continue;
//            }
//
//
//            // TODO: check if it's a size property and set it
//            $size = preg_match("/^[0-9]+(.[0-9]+)?(p[xct]|%|[cem]m|in)$/i", $prop);
//            if ($size){
//                // check if position wa set
//                switch($size_values)
//                {
//                    case 0:
//                        $props_array['position'] = array(
//                            'vertical'=>'center',
//                            'horizontal'=>$prop
//                        );
//                        break;
//                    case 1:
//                        if ("center" == $props_array['position']['vertical'])
//                            $props_array['position']['vertical'] = $prop;
//                        else
//                            $props_array['position']['horizontal'] = $prop;
//                        break;
//                    case 2:
//                        if (!isset($props_array['size']))
//                            $props_array['size'] = array(
//                                'height'=> 'auto',
//                                'width' => $prop
//                            );
//                        break;
//                    case 3:
//                        if (is_array($props_array['size'])){
//                            $props_array['size']['height'] = $prop;
//                        }
//
//                }
//
//                echo $size_values ++;
//                continue;
//            }
//
//            $size = preg_match("/^(cover|contain)$/i", $prop);
//            if ($size){
//                $props_array['size'] = $prop;
//                continue;
//            }
//
//            // check if is in {repeat-x, repeat-y, no-reapet} and set repeat property
//            $repeat = preg_match("/^(repeat\-[xy]|(no\-)?repeat)$/i", $prop);
//            if ($repeat)
//            {
//                $props_array['repeat'] = $prop;
//                continue;
//            }
//
//            $origin = preg_match("/^(padding|border|content)\-box$/i",$prop);
//            if ($origin){
//                if (!isset($props_array['origin']))
//                    $props_array['origin'] = $prop;
//                else
//                    $props_array['clip'] = $prop;
//                continue;
//            }
//
//            $attachment = preg_match("/^(fixed|scroll)$/i", $prop);
//            if ($attachment){
//                $props_array['attachment'] = $prop;
//                continue;
//            }
//
//
//            // todo: check it may be a image (url, linear-gradient) and set the image
//            $image = preg_match("/^url\\(['\"]?(?P<url>[^'\"]+)(['\"]?)\\)/i",$prop,$matches);
//            if ($image){
//                if (!isset($props_array['images']))
//                    $props_array['images'] = array();
//                $props_array['images'][] = $matches['url'];
//            }
//        }// foreach bg property
//
//        return $props_array;
//    }
}

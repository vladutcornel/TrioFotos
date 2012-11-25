<?php
require_once TRIO_DIR.'/whereis.php';
/**
 * Defines a CSS-ready color.
 * @package 3oLibrary
 * @subpackage CSS
 * @todo add Color manipulation methods (see LESS)
 */
class CSSColor
{
    /**
     * @var int
     */
    private $red = 0;
    /**
     * @var int
     */
    private $green = 0;
    /**
     * @var int
     */
    private $blue = 0;

    /**
     * @var int
     */
    private $hue = 0;
    /**
     * @var int
     */
    private $saturation;
    /**
     * @var int
     */
    private $lightnes;


    /**
     * @var int transparency
     */
    private $alpha = 1;

    /**
     * @param string The initial CSS color in valid format (#RRGGBB,#RGB,rbg[a] or hsl[a])
     */
    public function __construct($init="transparent")
    {
        // strip whitespace
        $init = preg_replace("/\s+/","",strtolower($init));
        if (isset (self::$standard_colors[$init]))
        {
            $init = self::$standard_colors[$init];
        }
        $format_not_found = true;

        if ("transparent" == $init){
            $this->alpha = 0;
            $format_not_found = false;
        }

        if ($format_not_found)
        {   // long hex format #RRGGBB
            $hex = preg_match("/^#([0-9a-f]{6})$/", $init, $matches);
            if ($hex)
            {

                $this->red = hexdec($init[1].$init[2]);
                $this->green = hexdec($init[3].$init[4]);
                $this->blue = hexdec($init[5].$init[6]);

                $this->alpha = 1;
                $this->update_hsl();
                $format_not_found = false;
            }
        }

        if ($format_not_found)
        {   // short hex format #RGB
            $hex = preg_match("/^#([0-9a-f]{3})$/", $init, $matches);
            if ($hex)
            {
                // #RGB
                $this->red = hexdec($init[1].$init[1]);
                $this->green = hexdec($init[2].$init[2]);
                $this->blue = hexdec($init[3].$init[3]);

                $this->alpha = 1;
                $this->update_hsl();
                $format_not_found = false;
            }
        }

        if ($format_not_found)
        {
            // rgb([0..255],[0..255],[0..255])
            $rgb = preg_match("/^rgb\((?P<red>[0-9]{1,3}),(?P<green>[0-9]{1,3}),(?P<blue>[0-9]{1,3})\)$/", $init, $matches);

            if ($rgb)
            {
                $this->red = intval($matches['red']);
                $this->green = intval($matches['green']);
                $this->blue = intval($matches['blue']);

                $this->alpha = 1;
                $this->update_hsl();
                $format_not_found = false;
            }
        }

        if ($format_not_found)
        {
            // rgba([0..255],[0..255],[0..255],[0..1])
            $rgba = preg_match("/rgba\((?P<red>[0-9]{1,3}),(?P<green>[0-9]{1,3}),(?P<blue>[0-9]{1,3}),(?P<alpha>[01](.[0-9]*)?)\)/", $init, $matches);
            if ($rgba)
            {
                $this->red = intval($matches['red']);
                $this->green = intval($matches['green']);
                $this->blue = intval($matches['blue']);

                $this->alpha = floatval($matches['alpha']);
                $this->update_hsl();
                $format_not_found = false;
            }
        }





        if ($format_not_found)
        {
            //hsl([0..360],[1..100]%,[1..100]%)
            $hsl = preg_match("/^hsl\((?P<hue>[0-9]{1,3}),(?P<saturation>[0-9]{1,3})%,(?P<light>[0-9]{1,3})%\)$/", $init, $matches);
            if ($hsl)
            {
                $this->hue = intval($matches['hue']);
                $this->saturation = intval($matches['saturation']);
                $this->lightnes = intval($matches['light']);

                $this->alpha = 1;
                $this->update_rgb();
                $format_not_found = false;
            }
        }

        if ($format_not_found)
        {
            //hsla([0..360],[1..100]%,[1..100]%,[0..1])
            $hsla = preg_match("/hsla\((?P<hue>[0-9]{1,3}),(?P<saturation>[0-9]{1,3})%,(?P<light>[0-9]{1,3})%,(?P<alpha>[01](.[0-9]*)?)\)/", $init, $matches);

            if ($hsla)
            {
                $this->hue = intval($matches['hue']);
                $this->saturation = intval($matches['saturation']);
                $this->lightnes = intval($matches['light']);

                $this->alpha = floatval($matches['alpha']);
                $this->update_rgb();
                $format_not_found = false;
            }
        }

        // throw a exception if there is no valid color
        if ($format_not_found)
        {
            throw new NotAColor($init);
        }
    }

    /**
     * Update Hue-Saturation-Lightnes values after the RGB changed
     */
    private function update_hsl(){
	$r = $this->red / 255.0;
	$g = $this->green / 255.0;
	$b = $this->blue / 255.0;
	$H = 0;
	$S = 0;
        $L = 0;

	$min = min(min($r, $g),$b);
	$max = max(max($r, $g),$b);
	$delta = $max - $min;

	$L = ($max + $min)/2;

	if($delta == 0)
	{
		$H = 0;
		$S = 0;
	}
	else
	{
		$S = $delta / $max;

		$dR = ((($max - $r) / 6) + ($delta / 2)) / $delta;
		$dG = ((($max - $g) / 6) + ($delta / 2)) / $delta;
		$dB = ((($max - $b) / 6) + ($delta / 2)) / $delta;

		if ($r == $max)
			$H = $dB - $dG;
		else if($g == $max)
			$H = (1/3) + $dR - $dB;
		else
			$H = (2/3) + $dG - $dR;

		if ($H < 0)
			$H += 1;
		if ($H > 1)
			$H -= 1;
	}

        $this->hue = $H*360;
        $this->saturation = $S*100;
	$this->lightnes = $L*100;
    }

    /**
     * Update rgb values based on hsl values
     * based on http://mjijackson.com/2008/02/rgb-to-hsl-and-rgb-to-hsv-color-model-conversion-algorithms-in-javascript
     */
    private function update_rgb(){
        $h = $this->hue / 360;
        $s = $this->saturation / 100;
        $l = $this->lightnes / 100;

        if($s == 0){
            $r = $g = $b = $l; // achromatic
        }else{


            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = hue2rgb($p, $q, $h + 1/3);
            $g = hue2rgb($p, $q, $h);
            $b = hue2rgb($p, $q, $h - 1/3);
        }

        $this->red =    intval($r*255);
        $this->green =  intval($g*255);
        $this->blue =   intval($b*255);
    }

    /**
     * Return the hsl CSS function
     *
     */
    public function getHSL()
    {
       return "hsl({$this->hue},{$this->saturation}%,{$this->lightnes}%)";
    }

    /**
     * Return the rgb CSS function
     *
     */
    public function getRGB()
    {
       return "rgb({$this->red},{$this->green},{$this->blue})";
    }

    /**
     * Return the rgba CSS function
     *
     */
    public function getRGBA()
    {
       return "rgba({$this->red},{$this->green},{$this->blue},{$this->alpha})";
    }

    /**
     * Get the simple hex (#RGB) representation of the color
     */
    public function getSimpleHex()
    {
        // SR = (R-R%16)/16

       $simple_red = intval($this->red/16,16);
       $hex = dechex($simple_red);
       $simple_green = intval($this->green/16,16);
       $hex.= dechex($simple_green);
       $simple_blue = intval($this->blue/16,16);
       $hex.=dechex($simple_blue);
       return "#$hex";
    }

    /**
     * Get the long hex (#RRGGBB) representation of the color
     */
    public function getLongHex()
    {
        // SR = (R-R%16)/16

       $red = dechex($this->red);
       if (strlen($red) == 1){
            $red = '0'.$red;
       }

       $green = dechex($this->green);
       if (strlen($green) == 1){
            $green = '0'.$green;
       }

       $blue = dechex($this->blue);
       if (strlen($blue) == 1){
            $blue = '0'.$blue;
       }
       return "#{$red}{$green}{$blue}";
    }

    /**
     * Return a IE-filter compatible color
     * @return string
     */
    public function getARGB()
    {
        // SR = (R-R%16)/16

       $red = dechex($this->red);
       if (strlen($red) == 1){
            $red = '0'.$red;
       }

       $green = dechex($this->green);
       if (strlen($green) == 1){
            $green = '0'.$green;
       }

       $blue = dechex($this->blue);
       if (strlen($blue) == 1){
            $blue = '0'.$blue;
       }

       $alpha = dechex(intval($this->alpha * 255));
       if (strlen($alpha) == 1)
       {
           $alpha = '0'.$alpha;
       }
       return "#{$alpha}{$red}{$green}{$blue}";
    }

    /**
     * Invert this color
     */
    public function invert(){
        $this->red = 255 - $this->red;
        $this->green = 255 - $this->green;
        $this->blue = 255 - $this->blue;
        $this->update_hsl();
    }


    // good ol` setters and getters
    /**
     * @param int $new_red_value
     */
    public function setRed($new_value)
    {
        $new_value = intval($new_value);
        if ($new_value > 255)
            $new_value = 255;
        $this->red = $new_value;
        $this->update_hsl();
    }
    /**
     * @return int Red value in [0,255]
     */
    public function getRed(){
        return $this->red;
    }

    /**
     * @param int $new_green_value
     */
    public function setGreen($new_value)
    {
        $new_value = intval($new_value);
        if ($new_value > 255)
            $new_value = 255;
        $this->green = $new_value;
        $this->update_hsl();
    }
    /**
     * @return int Green value in [0,255]
     */
    public function getGreen(){
        return $this->green;
    }

    /**
     * @param int $new_blue_value
     */
    public function setBlue($new_value)
    {
        $new_value = intval($new_value);
        if ($new_value > 255)
            $new_value = 255;
        $this->blue = $new_value;
        $this->update_hsl();
    }
    /**
     * @return int Blue value in [0,255]
     */
    public function getBlue(){
        return $this->blue;
    }

    /**
     * Set the color hue. Specify $method = "fractional" if you use a [0,1] range
     * @param int|float $new_hue_value
     * @param string $method "deg"|"fractional"
     */
    public function setHue($new_value, $method = "deg")
    {
        $new_value = intval($new_value);
        if ($method == "fractional"){
            $new_value*=360;
        }
        $this->hue = $new_value;
        $this->update_rgb();
    }
    /**
     * Get the color hue. $method = "fractional" if you want a [0,1] range
     * @return float Hue value in [0,360] or [0,1]
     */
    public function getHue($method = "deg"){
        if ($method == "fractional")
            return $this->hue/360;
        return $this->hue;
    }

    /**
     * Set the color saturation. Specify $method = "fractional" if you use a [0,1] range
     * or "%" for percentage value
     * @param int|float $new_saturation_value
     * @param string $method "%"|"fractional"
     */
    public function setSaturation($new_value, $method = "%")
    {
        $new_value = intval($new_value);
        if ($method == "fractional"){
            $new_value*=100;
        }
        $this->saturation = $new_value;
        $this->update_rgb();
    }
    /**
     * Get the color saturation. $method = "fractional" if you want a [0,1] range or "%" for percentage value
     * @return float Saturation value in [0,100] or [0,1]
     */
    public function getSaturation($method = "%"){
        if ($method == "fractional")
            return $this->saturation/100;
        return $this->saturation;
    }

    /**
     * Set the color luminsance(light). Specify $method = "fractional" if you use a [0,1] rangeor "%" for percentage value
     * @param int|float $new_saturation_value
     * @param string $method "%"|"fractional"
     */
    public function setLight($new_value, $method = "%")
    {
        $new_value = intval($new_value);
        if ($method == "fractional"){
            $new_value*=100;
        }
        $this->lightnes = $new_value;
        $this->update_rgb();
    }
    /**
     * Get the color luminsance (light). $method = "fractional" if you want a [0,1] range or "%" for percentage value
     * @return float Light value in [0,100] or [0,1]
     */
    public function getLight($method = "%"){
        if ($method == "fractional")
            return $this->lightnes/100;
        return $this->lightnes;
    }

    /**
     * Set the value for the alpha chanel
     * @param float $value the new value for the alpha chanel
     * @param string $method input method: percent(%) or fractional. Default fractional
     */
    public function setAlpha($value, $method = "fractional"){
        if ('%' == $method){
            $value/=100;
        }

        $this->alpha = $value;
    }

    /**
     * Get the alpha chanel value
     * @param string $method input method: percent(%) or fractional. Default fractional
     * @return float
     */

    public function getAlpha($method){
        if ('%' == $method)
        {
            return intval($this->alpha*100);
        }

        return $this->alpha;
    }

    /**
     * Get the best CSS representation for this color
     * @return string
     */
    public function getCSS(){
        // the color is completly transparent
        if ($this->alpha == 0) return "transparent";

        // the color is opaque (no transparency)
        if ($this->alpha == 1){
            if ($this->red%16 == intval($this->red/16) &&
                $this->green%16 == intval($this->green/16) &&
                $this->blue%16 == intval($this->blue/16))
            {
                // the color can be represented by 3 hex digits
                return $this->getSimpleHex();
            }

            // the color can only acurately be reprezented by 6 hex-digits
            return $this->getLongHex();
        }

        // the color is semi-transparent
        return $this->getRGBA();
    }

    public function __toString(){
        return $this->getCSS();
    }

    public static $standard_colors = array(
    'aliceblue'=>'#f0f8ff',
    'antiquewhite'=>'#faebd7',
    'aqua'=>'#00ffff',
    'aquamarine'=>'#7fffd4',
    'azure'=>'#f0ffff',
    'beige'=>'#f5f5dc',
    'bisque'=>'#ffe4c4',
    'black'=>'#000000',
    'blanchedalmond'=>'#ffebcd',
    'blue'=>'#0000ff',
    'blueviolet'=>'#8a2be2',
    'brown'=>'#a52a2a',
    'burlywood'=>'#deb887',
    'cadetblue'=>'#5f9ea0',
    'chartreuse'=>'#7fff00',
    'chocolate'=>'#d2691e',
    'coral'=>'#ff7f50',
    'cornflowerblue'=>'#6495ed',
    'cornsilk'=>'#fff8dc',
    'crimson'=>'#dc143c',
    'cyan'=>'#00ffff',
    'darkblue'=>'#00008b',
    'darkcyan'=>'#008b8b',
    'darkgoldenrod'=>'#b8860b',
    'darkgray'=>'#a9a9a9',
    'darkgrey'=>'#a9a9a9',
    'darkgreen'=>'#006400',
    'darkkhaki'=>'#bdb76b',
    'darkmagenta'=>'#8b008b',
    'darkolivegreen'=>'#556b2f',
    'darkorange'=>'#ff8c00',
    'darkorchid'=>'#9932cc',
    'darkred'=>'#8b0000',
    'darksalmon'=>'#e9967a',
    'darkseagreen'=>'#8fbc8f',
    'darkslateblue'=>'#483d8b',
    'darkslategray'=>'#2f4f4f',
    'darkslategrey'=>'#2f4f4f',
    'darkturquoise'=>'#00ced1',
    'darkviolet'=>'#9400d3',
    'deeppink'=>'#ff1493',
    'deepskyblue'=>'#00bfff',
    'dimgray'=>'#696969',
    'dimgrey'=>'#696969',
    'dodgerblue'=>'#1e90ff',
    'firebrick'=>'#b22222',
    'floralwhite'=>'#fffaf0',
    'forestgreen'=>'#228b22',
    'fuchsia'=>'#ff00ff',
    'gainsboro'=>'#dcdcdc',
    'ghostwhite'=>'#f8f8ff',
    'gold'=>'#ffd700',
    'goldenrod'=>'#daa520',
    'gray'=>'#808080',
    'grey'=>'#808080',
    'green'=>'#008000',
    'greenyellow'=>'#adff2f',
    'honeydew'=>'#f0fff0',
    'hotpink'=>'#ff69b4',
    'indianred'=>'#cd5c5c',
    'indigo'=>'#4b0082',
    'ivory'=>'#fffff0',
    'khaki'=>'#f0e68c',
    'lavender'=>'#e6e6fa',
    'lavenderblush'=>'#fff0f5',
    'lawngreen'=>'#7cfc00',
    'lemonchiffon'=>'#fffacd',
    'lightblue'=>'#add8e6',
    'lightcoral'=>'#f08080',
    'lightcyan'=>'#e0ffff',
    'lightgoldenrodyellow'=>'#fafad2',
    'lightgray'=>'#d3d3d3',
    'lightgrey'=>'#d3d3d3',
    'lightgreen'=>'#90ee90',
    'lightpink'=>'#ffb6c1',
    'lightsalmon'=>'#ffa07a',
    'lightseagreen'=>'#20b2aa',
    'lightskyblue'=>'#87cefa',
    'lightslategray'=>'#778899',
    'lightslategrey'=>'#778899',
    'lightsteelblue'=>'#b0c4de',
    'lightyellow'=>'#ffffe0',
    'lime'=>'#00ff00',
    'limegreen'=>'#32cd32',
    'linen'=>'#faf0e6',
    'magenta'=>'#ff00ff',
    'maroon'=>'#800000',
    'mediumaquamarine'=>'#66cdaa',
    'mediumblue'=>'#0000cd',
    'mediumorchid'=>'#ba55d3',
    'mediumpurple'=>'#9370d8',
    'mediumseagreen'=>'#3cb371',
    'mediumslateblue'=>'#7b68ee',
    'mediumspringgreen'=>'#00fa9a',
    'mediumturquoise'=>'#48d1cc',
    'mediumvioletred'=>'#c71585',
    'midnightblue'=>'#191970',
    'mintcream'=>'#f5fffa',
    'mistyrose'=>'#ffe4e1',
    'moccasin'=>'#ffe4b5',
    'navajowhite'=>'#ffdead',
    'navy'=>'#000080',
    'oldlace'=>'#fdf5e6',
    'olive'=>'#808000',
    'olivedrab'=>'#6b8e23',
    'orange'=>'#ffa500',
    'orangered'=>'#ff4500',
    'orchid'=>'#da70d6',
    'palegoldenrod'=>'#eee8aa',
    'palegreen'=>'#98fb98',
    'paleturquoise'=>'#afeeee',
    'palevioletred'=>'#d87093',
    'papayawhip'=>'#ffefd5',
    'peachpuff'=>'#ffdab9',
    'peru'=>'#cd853f',
    'pink'=>'#ffc0cb',
    'plum'=>'#dda0dd',
    'powderblue'=>'#b0e0e6',
    'purple'=>'#800080',
    'red'=>'#ff0000',
    'rosybrown'=>'#bc8f8f',
    'royalblue'=>'#4169e1',
    'saddlebrown'=>'#8b4513',
    'salmon'=>'#fa8072',
    'sandybrown'=>'#f4a460',
    'seagreen'=>'#2e8b57',
    'seashell'=>'#fff5ee',
    'sienna'=>'#a0522d',
    'silver'=>'#c0c0c0',
    'skyblue'=>'#87ceeb',
    'slateblue'=>'#6a5acd',
    'slategray'=>'#708090',
    'slategrey'=>'#708090',
    'snow'=>'#fffafa',
    'springgreen'=>'#00ff7f',
    'steelblue'=>'#4682b4',
    'tan'=>'#d2b48c',
    'teal'=>'#008080',
    'thistle'=>'#d8bfd8',
    'tomato'=>'#ff6347',
    'turquoise'=>'#40e0d0',
    'violet'=>'#ee82ee',
    'wheat'=>'#f5deb3',
    'white'=>'#ffffff',
    'whitesmoke'=>'#f5f5f5',
    'yellow'=>'#ffff00',
    'yellowgreen'=>'#9acd32'
    );
}

function hue2rgb($p, $q, $t){
                if($t < 0) $t += 1;
                if($t > 1) $t -= 1;
                if($t < 1/6) return $p + ($q - $p) * 6 * $t;
                if($t < 1/2) return $q;
                if($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
                return $p;
            }

class NotAColor extends Exception{
    public function __construct($colorname){
        parent::__construct($colorname." is not a color");
    }
}
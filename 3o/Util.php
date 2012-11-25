<?php

require_once TRIO_DIR.'/whereis.php';

/**
 * Some utility functions wrapped in a class
 * @author Cornel Borina  <corne@scoalaweb.com>
 */
abstract class Util{
    final function __construct(){
        
    }

    /**
     * Extracts a portion of the text from the word at the starting position
     * @param string $input the input text
     * @param int $start Witch word to start from (0 = first)
     * @param int $chunk The number of words to return
     * @return string
     */
    static function word_slice($input, $start = 0, $words = 80)
    {
        $arr = preg_split("/[\s]+/", $str,$words+1);
	return join(' ',array_slice($arr,$start,$words));
    }
}
<?php
require_once TRIO_DIR.'/whereis.php';
/**
 * A CSSAttribute is expected to return one or more CSS-compatible attributes.
 * The attribute list shoud be an associative array with the key representing
 * the name of the CSS attribute (e.g. "background-color") and the value, the 
 * CSS value of that property (e.g. "blue")
 * @author Cornel Borina <cornel@scoalaweb.com>
 */
interface CSSAtribute {
    /**
     * Return in the form [attribute_name => attribute_value]
     * @return array The CSS attribute array
     */
    public function cssArray();
}
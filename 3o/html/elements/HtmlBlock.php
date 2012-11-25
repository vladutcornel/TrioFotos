<?php

require_once TRIO_DIR.'/whereis.php';

/**
 * A page Section (div)
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class HtmlBlock extends HtmlElement{

    private static $allowed_types = array(
        'div',
        'form',
        // HTML 5
        'header',
        'nav',
        'footer',
        'article',
        'section',
        'aside',
        'details',
        'figcaption',
        'figure',
        'hgroup'
        
    );
    public function __construct($type = 'div', $id = '') {
        if (!in_array($type, self::$allowed_types))
        {
            throw new BadMethodCallException('Check the documentation or the TriO source code for alowed block level elements');
        }
        parent::__construct($type, $id);
    }
}
<?php

require_once TRIO_DIR.'/whereis.php';

/**
 * An input text field
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class TextField extends Input{
    public function __construct($name, $default='', $id=''){
        parent::__construct('text', $name, $default, $id);
    }

}
<?php

namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

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
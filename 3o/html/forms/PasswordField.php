<?php

require_once TRIO_DIR.'/whereis.php';

/**
 * A password form field
 *
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class PasswordField extends Input{
    public function __construct($name, $default='', $id=''){
        parent::__construct('password', $name, $default, $id);
    }

}
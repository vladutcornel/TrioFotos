<?php

namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

/**
 * A group of check boxes that are not necesary for direct display
 *
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class CheckboxGroup extends CheckableFormElements {
    public function __construct($name, $id = '') {
        parent::__construct(self::CHECKBOX, $name, $id);
    }
}
<?php

require_once TRIO_DIR.'/whereis.php';

/**
 * A page paragraph
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 * @todo add Wrning when non-standard elements are added (like <div>)
 */
class Paragraph extends HtmlElement{

    public function __construct($content = '', $id = '') {
        parent::__construct('p',$id);
        $this->setText($content);
    }
}
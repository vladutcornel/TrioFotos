<?php
require_once TRIO_DIR.'/whereis.php';

/**
 * A HTML heading (wrapper for h1,h2,...h6 tags)
 *
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class HtmlHeading extends HtmlElement{
    public function __construct($level = 1, $content = '', $id = '') {
        if (!is_numeric($level) || !preg_match('/^[1-6]$/', $level))
        {
            $id = $content;
            $content = $level;
            $level = 1;
        }
        parent::__construct('h'.$level, $id);
        
        $this->setText($content);
    }
}
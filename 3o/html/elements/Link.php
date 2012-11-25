<?php

require_once TRIO_DIR.'/whereis.php';

/**
 * A page anchor
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class Link extends HtmlInline{

    public function __construct($destination, $content = NULL, $id = ''){
        parent::__construct('a',$id);

        $this->setAttribute('href', $destination);
        if ($content === NULL) {
            $this->setText($destination);
        }else {
            $this->setText($content);
        }

    }

    public function getHref(){
        return $this->getAttribute('href');
    }
}
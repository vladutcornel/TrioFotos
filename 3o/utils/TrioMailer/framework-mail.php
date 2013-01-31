<?php
/**
 * Helper file for loading TrioMail
 * Use this ONLY if you plan on using TrioMail as a stand-alone mail library
 * @package 3oFramework
 * @subpackage Core
 * @author Cornel Borină <cornel@scoalaweb.com>
 */
if (!defined("TRIO_DIR"))  
{  
    define("TRIO_DIR", __DIR__);  
}

define('TRIO_MAILER_STANDALONE', 1);

include_once TRIO_DIR.'/whereis.php';

Whereis::register(array (
    //'TGlobal' => TRIO_DIR.'/TGlobal.php',
    'TObject' => TRIO_DIR.'/TObject.php',
    'TUtil' => TRIO_DIR.'/TUtil.php',
    'trio\mail\Mailer'=> __DIR__.'/class.php,aoler.php'
));

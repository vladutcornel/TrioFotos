<?php
/**
 * Helper file for loading TrioFramework classes
 * @package 3oFramework
 * @subpackage Core
 * @author Cornel Borină <cornel@scoalaweb.com>
 */
if (!defined("TRIO_DIR"))  
{  
    define("TRIO_DIR", __DIR__);  
} 

include_once TRIO_DIR.'/whereis.php';

Whereis::register(array (
    'TOCore' => TRIO_DIR.'/TOCore.php',
    'TGlobal' => TRIO_DIR.'/TGlobal.php',
    'TObject' => TRIO_DIR.'/TObject.php',
    'TUtil' => TRIO_DIR.'/TUtil.php',
));

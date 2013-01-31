<?php

// Where the script files are located
define ('SCRIPT_ROOT',__DIR__);
// the visible portion of the site is set in a subfolder (for safety)
define ('SITE_ROOT',SCRIPT_ROOT.'/public');

require_once __DIR__.'/3o/framework.php';
require_once __DIR__.'/my/dbconnect.php';
require_once __DIR__.'/my/PageData.php';



PageData::$site_url = '/triofotos';

list ($tempuri) = explode('?', TGlobal::server('REDIRECT_QUERY_STRING'), 2);

$parts = explode('/', $tempuri, 2);

// extract the language and URI from the URL
if (($nr_parts = count ($parts)) > 0 ){
    
    if ('' != $parts[0])
        PageData::$lang = $parts[0];
    
    if ($nr_parts > 1){
        PageData::$uri = $parts[1];
    }
}

PageData::$site_url.='/'.PageData::$lang;

// The class based mechanism will not always be used
TOCore::$throw_class_exception = false;

ob_start();

// Run the file and class loader with our custom URi
TOCore::main(PageData::$uri);

PageData::$content = ob_get_clean();

PageData::renderTemplate();


<?php
Whereis::register('lessc', SCRIPT_ROOT.'/lib/lessphp/lessc.inc.php');
define ('MY_LESS_ROOT', SCRIPT_ROOT.'/my/less/');
/**
 * Description of less
 *
 * @author cornel
 */
class Page_less {
    
    public function main($params){
        PageData::$template = 'ajax.php';
        header('Content-Type: text/css');
        
        $file = preg_replace('#(/|.)css(/|$)#i', '$1less$2', implode ('/', $params)) ;
        if (file_exists(MY_LESS_ROOT.$file))
        {
            $less = new lessc(MY_LESS_ROOT.$file);
            $less->setVariables(array(
                'site_root'=> "'".  PageData::site_address() . "'"
            ));
            echo $less->parse();
        }
    }
    
}
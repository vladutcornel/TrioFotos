<?php

/**
 * Description of login
 *
 * @author cornel
 */
class Page_login {
    function __construct($params) {
        if (isset($params[0]) && $params[0] == 'out'){
            TGlobal::unsetSession('logdata');
            header('location: '.PageData::site_address());
        }
    }
    function post_request(){
        try{
            $email = TGlobal::post('email');
            if (!preg_match('/[a-z0-9\-_.]+@[a-z0-9\-_.]+/i', $email)) {
                throw new Exception(1);
            }
            $pass = TGlobal::post('password');
            if (trim($pass) == '')
                throw new Exception(2);
          
            
            $old = UserModel::search(array('email'=>$email,'password'=>  md5($pass)));
            if (count($old) < 1){
                throw new Exception(4);
            }
            if ($old[0]->real != 0 || $old[0]->active == 0)
                throw new Exception(5);
            // associate pseudo user
            if ( ( $pseudo_user = PageData::getUser() ) != false && $pseudo_user->real == 0){
                $pseudo_user->real = $old[0]->id;
                $pseudo_user->save();
            }
            
            TGlobal::setSession('logdata', $old[0]); 
            header('location: '.PageData::site_address());
       } catch (Exception $e){
            ?>
<div class="alert alert-error"><?php PageData::write('Invalid login') ?></div>
            <?php
        }
    }
    
    function main(){
        TOCore::main('404');
    }
}

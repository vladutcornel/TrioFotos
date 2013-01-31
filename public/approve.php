<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of approve
 *
 * @author cornel
 */
class Page_approve {
    function ajax(){
        PageData::$template = 'ajax.php';
        header('Vary: Accept');
        if ((strpos(TGlobal::server('HTTP_ACCEPT'), 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        $response = array('success'=>false);
        try{
            if(!isset($params[0]))
                throw new Exception ();
            $search = array('path'=>$params[0]);
            if (!PageData::getUser()->admin)
                throw new Exception;
            $fotos = PhotoModel::search($search);
            
            if (count($fotos) < 1)
                throw new Exception;
            
            $foto = $fotos[0];
            
            $foto->approved = 1;
            $foto->save();
            $response['success'] = true;
        } catch (Exception $e){
            $response['success'] = false;
        }
        echo json_encode($response);
    }
}

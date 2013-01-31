<?php

/**
 * Description of delete
 *
 * @author cornel
 */
class PageDelete {
    function ajax($params){
        PageData::$template = 'ajax.php';
        header('Vary: Accept');
        if ((strpos(TGlobal::server('HTTP_ACCEPT'), 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        PageData::$template = 'ajax.php';
        $response = array('success'=>false);
        try{
            if(!isset($params[0]))
                throw new Exception ();
            $search = array('path'=>$params[0]);
            if (!PageData::getUser()->admin)
                $search['user'] = PageData::getUser ()->id;
            $fotos = PhotoModel::search($search);
            
            if (count($fotos) < 1)
                throw new Exception;
            
            $foto = $fotos[0];
            
            // delete main file
            unlink(SCRIPT_ROOT.'/uploads/'.$foto->path);
            
            // delete thumbnails
            $files = glob(SCRIPT_ROOT.'/uploads/*x*-'.$foto->path);
            
            foreach ($files as $file)
                unlink ($file);
            $query = sprintf('DELETE FROM photos WHERE id=%d', $foto->id);
            trio\db\Model::$db->query($query);
            $response['success'] = true;
        } catch (Exception $e){
            $response['success'] = false;
        }
        echo json_encode($response);
    }
    
    function main(){}
}
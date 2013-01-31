<?php

class PageRate {

    public function ajax($params) {
        PageData::$template = 'ajax.php';
        header('Vary: Accept');
        if ((strpos(TGlobal::server('HTTP_ACCEPT'), 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        $response = array('success' => false);
        try {
            $vote = TGlobal::request('vote');
            if (!is_numeric($vote) || $vote < 1 || $vote > 10)
                throw new Exception (2);
            
            $foto = PhotoModel::load(TGlobal::request('photo'));
            
            if (!$foto)
                throw new Exception (3);
            
            $query = sprintf(
                    'REPLACE INTO votes(user, photo, vote) VALUES(%d,%d,%d)', 
                    PageData::getUser()->id, 
                    $foto->id, 
                    $vote
            );
            trio\db\Model::$db->query($query);
            $response['success'] = true;
        } catch (Exception $e) {
            $response['success'] = $e->getMessage();
        }
        echo json_encode($response);
    }

}
<?php

Whereis::register('CustomFotoUploader', SCRIPT_ROOT . '/my/CustomFotoUploader.php');

class Page_upload {

    private function jqupload_forward() {
        new CustomFotoUploader(array(
            'upload_dir' => SCRIPT_ROOT . '/uploads/'
                ));
    }

    function get_request() {
        if (TGlobal::get('download')) {
            $this->jqupload_forward();
        }
    }

    function ajax() {
        PageData::$template = 'ajax.php';
        header('Vary: Accept');
        if ((strpos(TGlobal::server('HTTP_ACCEPT'), 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        $response = new stdClass();
        if (TGlobal::server('REQUEST_METHOD') == 'POST') {
            $response->files = array();
            $files = TGlobal::files('files');
            foreach ($files as $file) {
                $file_data = array(
                    'name' => $file['name']
                );
                try {
                    if ($file['error'] != UPLOAD_ERR_OK) {
                        throw new Exception('Error', $file['error']);
                    }

                    $mime = TUtil::getMimeType($file['tmp_name'], true);
                    $valid = preg_match('#^image/(?P<type>[a-z\-_]+)$#i', $mime, $match);
                    if (!$valid) {
                        throw new Exception('Invalid File Type', 'MIME');
                    }

                    do{
                        $filename = uniqid('image') . '.' . $match['type'];
                    } while(file_exists(SCRIPT_ROOT.'/uploads/'.$filename));

                    $file_data['name'] = $filename;

                    $success = move_uploaded_file($file['tmp_name'], SCRIPT_ROOT . '/uploads/' . $filename);

                    if (!$success) {
                        throw new Exception('Server Error', 'MOVE');
                    }

                    PhotoModel::create(array(
                        'path' => $filename,
                        'user' => PageData::getUser()->id,
                        'approved' => PageData::getUser()->admin? 1 : 0,
                        'added' => date('Y-m-d H:i:s')
                    ))->save();
                    $file_data['size'] = $file['size'];
                    $file_data['url'] = PageData::site_address('view/' . $filename);
                    $file_data['thumbnail_url'] = PageData::site_address('view/80xauto-' . $filename);
                    $file_data['delete_url'] = PageData::site_address('delete/' . $filename);
                    $file_data['delete_type'] = 'POST';
                    $file_data['pending'] = PageData::getUser()->admin? 1 : 0;
                } catch (Exception $e) {
                    $file_data['error'] = PageData::translate('UPLOAD_ERROR_' . $e->getCode(), $e->getMessage());
                }

                $response->files[] = $file_data;
            }
        }
        
        echo json_encode($response);
    }

    function main() {
        header('location: /');
    }

}

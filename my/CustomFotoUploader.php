<?php
require_once __DIR__.'/UploadHandler.php';
/**
 * Description of CustomFotoUploader
 *
 * @author cornel
 */
class CustomFotoUploader extends jquery_fileupload\UploadHandler{
    public function __construct($options = null, $initialize = true) {
        
        parent::__construct($options, $initialize);
    }
    
    /**
     * @todo: get the user id of the loggedin user
     */
    protected function get_user_id() {
        return parent::get_user_id();   
    }
    
    protected function get_full_url() {
        parent::get_full_url();
    }
    
    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null) {
        $data = parent::handle_file_upload($uploaded_file, $name, $size, $type, $error, $index, $content_range);
        
        if (!isset($data->error)){
            $foto = new PhotoModel(array(
                'path'=> $data->name,
                'user' => $this->get_user_id()
            ));
            
            $foto->save();
        }
        
        return $data;
    }
}
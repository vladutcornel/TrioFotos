<?php

class PageView {
    function __construct($param) {
        if (TGlobal::request('width') && TGlobal::request('height') && TGlobal::request('name')){
            header('location: '.PageData::site_address('view/'.TGlobal::request('width').'x'.TGlobal::request('height').'-'.TGlobal::request('name')));
            die();
        };
    }
    function ajax(){
        PageData::$template = 'ajax.php';
        header('Vary: Accept');
        if ((strpos(TGlobal::server('HTTP_ACCEPT'), 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        
        PageData::$template = 'ajax.php';
        $response = new stdClass();
        $response->files = array();
        if (PageData::getUser()->admin)
            $fotos = PhotoModel::loadByQuery ('SELECT * FROM photos ORDER BY added DESC');
        elseif (!PageData::isLogged())
            $fotos = PhotoModel::loadByQuery ('SELECT * FROM photos WHERE approved ORDER BY added DESC');
        else 
            $fotos = PhotoModel::search(array(
                'user'=>  PageData::getUser()->id));
        $base_dir = SCRIPT_ROOT.'/uploads/';
        $base_url = PageData::site_address('view/');
        foreach ($fotos as $foto) {
            if (!file_exists($base_dir.$foto->path))
                continue;
            $tags = array();
            foreach ($foto->getTags() as $tag)
                $tags[]= $tag->name;
            $response->files[]= array(
                'name'=>$foto->path,
                'url'=> $base_url . $foto->path,
                'thumbnail_url'=>PageData::site_address('view/80xauto-' . $foto->path),
                'delete_url'=>PageData::site_address('delete/' . $foto->path),
                'delete_type'=>'POST',
                'pending'=> ! $foto->approved,
                'tags'=> implode(' ', $tags)
            );
        }
        
        echo json_encode($response);
    }
    
    function main($params)
    {
        if (!isset($params[0])){
            return;// no param
        }
        list($basename, $ext) = explode('.', $params[0]);
        
        $source = SCRIPT_ROOT.'/uploads/'.$params[0];
        if (!file_exists($source)){
            $valid = preg_match('/^(?P<width>([0-9]+)|(auto))x(?P<height>([0-9]+)|(auto))\-(?P<file>.+)$/', $basename, $match);
            
            if (!$valid)
                return;
            $source = $this->resize($match['file'], $ext, $match['width'], $match['height']);
        }
        PageData::$template = 'ajax.php';
        header('Content-Type: image/'.$ext);
        echo file_get_contents($source);
        
    }
    
    private function resize($image, $type, $width, $height){
        $file_path = SCRIPT_ROOT.'/uploads/'.$image.'.'.$type;
        list($img_width, $img_height) = 
                @getimagesize($file_path);
        if ($img_width < 1)
            $img_width = 1;
        if ($img_height < 1)
            $img_height = 1;
        if ('auto' == $width){
            if ($height != 'auto')
                $width = $height * $img_width/$img_height;
            else{
                $width = $img_width;
                $height = $img_height;
            }
        }
        
        if ('auto' == $height){
            $height = $width * $img_height/$img_width;
        }
        
        if ($width > $img_width)
            $width = $img_width;
        if ($height > $img_height)
            $height = $img_height;
        $new_file_path = SCRIPT_ROOT.'/uploads/'.$width.'x'.$height.'-'.$image.'.'.$type;
        $new_img = @imagecreatetruecolor($width, $height);
        switch ($type) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                $image_quality = 75;
                break;
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                $image_quality = 9;
                break;
            default:
                $src_img = null;
        }
        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, 0, 0,
            $width,
            $height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $new_file_path, $image_quality);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        
        return $new_file_path;
    }
}

<?php

namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

/**
 * An Form ready for uploading files
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class UploadForm extends Form{

    public function __construct($action, $id=''){
        parent::__construct($action, "POST", $id);

        $this->setAttribute("enctype", "multipart/form-data");
        $this->setValue("MAX_FILE_SIZE", self::getSystemMaxFileSize());
    }

    public static function getSystemMaxFileSize() {
        // based on http://hu2.php.net/manual/en/function.ini-get.php
        $val = \trim(\ini_get("upload_max_filesize"));
        $last = \strtolower($val[\strlen($val)-1]);
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
}
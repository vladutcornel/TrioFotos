<?php

class PageIndex{
    public static function main($params){
        if (count ($params) > 0){
            include __DIR__.'/404.php';
        } elseif (PageData::isLogged()) {
            include __DIR__.'/home.php';
        } else {
            include __DIR__.'/default.php';
        }
    }
}
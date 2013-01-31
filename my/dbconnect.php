<?php

use trio\db\Model as DBModel;
use trio\db\Mysql as TMysql;

DBModel::$db = new TMysql('triofotos2', 'root', '', 'localhost');
DBModel::$cache_dir = sys_get_temp_dir() . '/trio-photos';

DBModel::$special_classes['photos'] = 'PhotoModel';
DBModel::$special_classes['users'] = 'UserModel';
DBModel::$special_classes['tags'] = 'TagModel';


Whereis::register(array(
    'PhotoModel' => SCRIPT_ROOT . '/my/db/PhotoModel.php',
    'UserModel' => SCRIPT_ROOT . '/my/db/UserModel.php',
    'TagModel' => SCRIPT_ROOT . '/my/db/TagModel.php',
));

// build cache folders
$cache_folders = array(
    DBModel::$cache_dir,
    DBModel::$cache_dir . '/photos',
    DBModel::$cache_dir . '/users',
    DBModel::$cache_dir . '/tags',
    DBModel::$cache_dir . '/query',
);

foreach ($cache_folders as $folder) {
    if (!is_dir($folder)) {
        if (is_file($folder))
            unlink($folder);
        mkdir($folder);
    }
}

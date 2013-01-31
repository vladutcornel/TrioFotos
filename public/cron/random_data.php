<?php

// create random users
$nr_users = mt_rand(1, 10);
$timestamp = time();
for ($i = 0; $i < $nr_users; $i++) {
    UserModel::create(array(
        'email' => 'rand' . $i . '@' . $timestamp . '.com',
        'active' => 1,
        'real' => 1
    ))->save();
}

// duplicate some photos
$query = 'SELECT * FROM photos ORDER BY RAND() LIMIT ' . mt_rand(1, 10);
$photos = PhotoModel::loadByQuery($query);
foreach ($photos as $photo) {
    
    list($basename, $ext) = explode('.', $photo->path);
    do {
        $filename = uniqid('image') . '.' . $ext;
    } while (file_exists(SCRIPT_ROOT . '/uploads/' . $filename));
    copy(SCRIPT_ROOT.'/uploads/'.$photo->path, SCRIPT_ROOT.'/uploads/'.$filename);
    $insert = sprintf('INSERT INTO photos(path,user) (SELECT \'%s\', id FROM users ORDER BY RAND() LIMIT 1 )', $filename);
    trio\db\Model::$db->query($insert);
}

// insert votes
//$query = 'INSERT INTO votes(photo, user, vote) (SELECT p.id, u.id, IF(ROUND(RAND()*100)%1, 10, 1) FROM photos AS p,users AS u ORDER BY RAND() LIMIT 100)';
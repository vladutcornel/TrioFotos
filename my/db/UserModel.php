<?php

use trio\db\Model as DBModel;

/**
 * UserModel
 *
 * @author cornel
 */
class UserModel extends DBModel {

    protected static $table_name = 'users';
    protected static $fields = array('id', 'email', 'password', 'admin', 'active' , 'real');
    
    /**
     * Get user's primary key - if he doesn't have one, he will be registered 
     * into the database
     * @return int
     */
    public function getId(){
        if (!$this->getDBVar('id'))
            $this->save ();
        return intval($this->getDBVar('id'));
    }

    /**
     * Save the current model to the database
     */
    public function save() {
        parent::generic_save($this->id == FALSE);
    }

    public function saveJSON() {
        $this->generic_saveJSON(array($this->getDBVar("id")));
    }

    /**
     * Load a model based on the primary key
     * @return PhotosModel a model object
     */
    public static function load($key, $cache = "10 minutes") {
        return parent::generic_load(array("id" => true), $key, $cache);
    }

    /**
     * Load multiple instances into memory at once
     */
    public static function prepare($keys) {
        if (!is_array($keys)) {
            $keys = func_get_args();
        }
        return parent::generic_prepare(array("id" => true), $keys);
    }

    /**
     * Performes a simple search
     * 
     */
    public static function search($params, $op = "and", $type = "strict") {
        return parent::search($params, $op, $type);
    }

    /**
     * Load instances of the model based on a query
     * @return array all the instances found
     */
    public static function loadByQuery($query, $cache = false, $skip_cache = false) {
        // load cached results
        $query = str_replace(array("\n", "\r"), " ", $query);
        $query = preg_replace("/\s+/", " ", $query);
        $cache_file = static::$cache_dir . "/" . md5(trim($query)) . ".json";
        try {
            if ($cache === false || $skip_cache) {
                throw new Exception;
            }
            $data = TUtil::readSerializedCache($cache_file);
            if (false == $data)
                throw new Exception();

            $results = array();
            foreach ($data->results as $pk) {
                if (isset(static::$loaded[$pk])) {
                    $results[] = static::$loaded[$pk];
                    ;
                    continue;
                }
                $obj = PhotosModel::load($pk);
                if ($obj != false && $obj != null) {
                    self::$loaded[$pk] = $obj;
                    $results[] = $obj;
                }
            }
            return $results;
        } catch (Exception $e) {
            // load normaly from database
            $db = DBModel::$db;

            $results = $db->get_results($query);
            $objects = array();
            $pks = array();
            $table_model = self::getClassForTable("users");
            if ($results)
                foreach ($results as $row) {
                    $object = new $table_model($row);
                    $objects[] = $object;

                    self::$loaded[$row->id] = $object;
                    $object->saveJSON(array($row->id));
                    $pks[] = $row->id;
                }

            if ($cache !== false) {
                $data = (object) array(
                            "query" => $query,
                            "results" => $pks
                );

                TUtil::saveSerializedCache($data, $cache_file, $cache);
            }

            return $objects;
        }
    }
    
    /**
     * Get a new user from the database based on the email address
     * @param string $email
     * @return boolean
     */
    public static function loadByEmail($email){
        $users = static::search(array('email'=> $email));
        if (count($users))
            return $users[0];
        return false;
    }

    /**
     * Fetch current user object (UserModel)
     * If nobody is logged in, one pseudo-user will be created
     * @return boolean
     */
    public static function getCurrentUser(){
        $autoemail = TUtil::getIP().'@'.TUtil::getIP(true);
        $callbacks = array(
            function(){
                // logged in user
                return TGlobal::session('logdata', FALSE);
            },
            function() use ($autoemail){
                // returning pseudo-user
                return UserModel::loadByEmail($autoemail);
            }, 
            function() use ($autoemail){
                // new user - may or may not have to register him to the database
                $user = new UserModel(array(
                    'email'=> $autoemail, 
                    'password'=>'', 
                    'admin'=>0, 
                    'active'=>0, 
                    'real'=>0
                ));
                
                return $user;
            }, 
                    
        );
        foreach ($callbacks as $call){
            if (($user = $call()) != false){
                TUtil::changeClass($user, get_called_class());
                return $user;
            }
        }
        
        // just in case something went wrong
        return false;
        
    }
}

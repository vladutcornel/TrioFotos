<?php

use trio\db\Model as DBModel;
use trio\Timestamp as Timestamp;

/**
 * Description of TrioFoto
 *
 * @author cornel
 */
class PhotoModel extends DBModel {

    protected static $table_name = 'photos';
    protected static $fields = array('id', 'path', 'user', 'approved', 'added');
    
    private $user = null;
    private $tags = array();

    public function getUploader() {
        if (is_null($this->user)) {
            $this->user = UserModel::load(parent::getUser());
        }
        
        return $this->user;
    }
    
    public function getTags(){
        if (empty($this->tags)){
            $query = sprintf("SELECT t.* FROM photo_tags AS p INNER JOIN tags AS t ON p.tag = t.id
                WHERE p.photo = %d", $this->id);
            $this->tags = TagModel::loadByQuery($query);
        }
        
        return $this->tags;
    }
    
    public function addTag($new){
        if ($this->hasTag($new) || $new == '')
            return ;
        
        $tag = new TagModel();
        $tag->name = $new;
        $tag->save();
        $this->tags[]= $tag;
        
        $query = sprintf('INSERT INTO photo_tags(photo,tag) VALUES(%d,%d)', $this->id, $tag->id);
        static::$db->query($query);
    }
    
    public function removeTag($todelete){
        if (! $this->hasTag($todelete))
            return;
        $tag = TagModel::load($todelete);
        $query = sprintf('DELETE FROM tags WHERE id = \'%s\' ', $tag->id);
        static::$db->query($query);
        
        $key = array_search($tag, $this->getTags());
        array_splice($this->tags, $key, 1);
    }

        public function hasTag($search){
        $tags = $this->getTags();
        foreach ($tags as $tag){
            if ($tag->name == $search)
                return true;
        }
        
        return false;
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
     * @return PhotoModel a model object
     */
    public static function load($key, $cache = "10 minutes") {
        return parent::generic_load(array("id" => true, 'path'), $key, $cache);
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
                $obj = PhotoModel::load($pk);
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
            $table_model = self::getClassForTable("photos");
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

}

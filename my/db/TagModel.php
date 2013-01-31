<?php

use trio\db\Model as DBModel;

/**
 * Description of TrioFoto
 *
 * @author cornel
 */
class TagModel extends DBModel {

    protected static $table_name = 'tags';
    protected static $fields = array('id', 'name');
    
    private $photos = null;

    public function getPhotos() {
        if (is_null($this->photos)) {
            $query = sprintf("SELECT p.* FROM photo_tags AS t 
                INNER JOIN photos AS p ON t.photo = p.id
                WHERE t.tag = %d", $this->id);
            $this->photos = PhotoModel::loadByQuery($query);
        }
        
        return $this->photos;
    }

    /**
     * Save the current model to the database
     */
    public function save() {
        parent::generic_save($this->getId() == FALSE);
    }

    public function saveJSON() {
        $this->generic_saveJSON(array($this->getDBVar("id")));
    }

    /**
     * Load a model based on the primary key
     * @return PhotosModel a model object
     */
    public static function load($key, $cache = "10 minutes") {
        return parent::generic_load(array("id" => true,'name'), $key, $cache);
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
            $table_model = self::getClassForTable("tags");
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

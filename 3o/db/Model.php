<?php

namespace trio\db;
require_once \TRIO_DIR.'/framework.php';

/**
 * Template for a database table model
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage Database
 */
class Model extends \TObject {
    /**
     * Database object.
     * @var trio\db\Mysql The database
     * @todo Change this into a generic driver
     */
    public static $db;

    /**
     * The name of the corespunding table in the database
     */
    protected static $table_name;

    /**
     * Special designed classes for some tables from the database
     * @var type
     */
    public static $special_classes = array();

    /**
     * The cache folder - must have a special folder for every table
     * @var type
     */
    public static $cache_dir = __DIR__;

    /**
     * @staticvar array The table fields. Should be set for every child class
     */
    protected static $fields = array();
    
    protected $db_data = array();


    /**
     * Track if any property was changed
     * @var boolean
     */
    private $properties_changed = false;

    /**
     * @param array|object $init the initial field values
     */
    public function __construct($init = array()){

        if (!\is_array($init) && !\is_object($init)) {
            \parse_str($init,$init);
        }

        if (\is_array($init) || \is_object($init))
            $this->multiSet($init);
    }

    /**
     * General database getter
     * @param string $var_name the field name
     * @return mixed
     */
    public function getDBVar($var_name){
        $var_name = \strtolower($var_name);
        if(isset($this->db_data[$var_name]))
            return $this->db_data[$var_name];
        return FALSE;
    }

    /**
     * General database setter
     * @param string $var_name the field name
     * @param mixed $new_value the new value
     * @return \DBModel $this for method chaining
     */
    public function setDBVar($var_name, $new_value){
            $var_name = \strtolower($var_name);
            $original = isset($this->db_data[$var_name])?
                $this->db_data[$var_name] :
                '';
            $this->db_data[$var_name] = $new_value;
            
            if ($original != $this->db_data[$var_name])
            {
                $this->properties_changed = true;
            }
        return $this;
    }
    
    /**
     * Give other models the option to advertise changing properties
     */
    protected function markPropertyChange(){
        $this->properties_changed = TRUE;
    }

    /**
     * Bypas the TObject default mechanism of throwing an exception when no setter was defined
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value) {
        try{
            parent::__set($name, $value);
        } catch(\LogicException $e)
        {
            $this->setDBVar($name, $value);
        }
    }

    /**
     * Bypas the TObject default mechanism of throwing an exception when a 
     * object property is not set and no getter was defined
     * @param string $name
     * @param string $value
     */
    public function __get($name) {
        try{
            return parent::__get($name);
        } catch(\LogicException $e)
        {
            return $this->getDBVar($name);
        }
    }
    
    /**
     * Set multiple model properties
     * @param array|object $fields
     * @throws BadMethodCallException when the parameter is not an array or an object
     * @return DBModel $this For method chaining
     */
    public function multiSet($fields)
    {
        if (!\TUtil::isIterable($fields))
        {
            throw new \BadMethodCallException('DBModel::multiSet only accepts associative arrays or objects');
        }
        
        foreach ($fields as $key => $value) {
            $this->setDBVar($key, $value);
        }
        
        return $this;
    }
    
    /**
     * Checks if the current table model has the requested field
     */
    public static function hasField($field)
    {
        return \in_array(\trim($field),static::$fields);
    }

    /**
     * Get all table fields
     */
    public static function getFields(){
        return static::$fields;
    }

    /**
     * Get suggested class for given table
     * @param string $$table_name
     * @return string The class name
     */
    public static function getClassForTable($table_name)
    {
        if (isset(self::$special_classes[$table_name]))
            return self::$special_classes[$table_name];

        $temp = \preg_replace("/[^a-z0-9]+/"," ",$table_name); // e.g. "margin left"
        if (\is_numeric($temp[0]))
        {
            $temp = "C$temp"; // Add a C before database tables that start with a digit
        }
        $temp = \ucwords($temp); // eg. "Margin Left"
        $table_model =  \str_replace(" ","",$temp);// eg. "setMarginLeft"

        if (\class_exists($table_model."Model")){
            return $table_model."Model";
        }

        return __CLASS__;
    }

    /**
     * Save the current model to the database
     */
    public function generic_save($insert_contition){
        $save_fields = array();
        $save_values = array();

        foreach(static::$fields as $field){
            if (($value = $this->getDBVar($field)) !== FALSE){
                $save_fields[] = $field;
                $save_values[] = static::$db->escape($value);
            }
        }
        if ($insert_contition){
            $this->insert($save_fields,$save_values);
            // save the model after insertion, when we have auto-incremented primary keys
            $this->saveJSON();
        } elseif ($this->properties_changed) {
            // save the model file before update because database updates are slow and we want other entities to have access to the new data
            $this->saveJSON();
            $this->update($save_fields,$save_values);
            $this->properties_changed = FALSE;
        }

    }

    /**
     *
     */
    private function insert($save_fields,$save_values){
        $table_name = static::$table_name;
        $query = "INSERT INTO `$table_name` (`".\implode("`,`",$save_fields)."`) VALUES ('".\implode("','", $save_values)."')";
        
        static::$db->query($query);

        if (\in_array('id', static::getFields()))
        {
            $this->setId(static::$db->insert_id);
        }
    }

    /**
     *
     */
    private function update($save_fields, $save_values){
        $table_name = static::$table_name;

        $query = "UPDATE `$table_name` SET ";
        $not_first = false;
        foreach($save_fields as $key=>$field){
            if ($not_first) {
                $query.= " , ";
            }
            $not_first = true;

            $query.= " `{$field}` = '{$save_values[$key]}' ";
        }

        $query.=" WHERE id = ".$this->getId();

        static::$db->query($query);
    }

    /**
     * Load a model based on the primary key
     * @return DBModel a model object
     */
    public static function generic_load($pk_fields, $key, $cache = false){
        //echo "<hr> load model generic ";
        //var_dump(func_get_args());
        //debug_print_backtrace();
        if (isset (static::$loaded[$key]))
        {
            return static::$loaded[$key];
        }

        $from_xml = static::loadXML($key);
        if (false !== $from_xml)
        {
            return $from_xml;
        }
        $array = static::generic_prepare($pk_fields, $key, $cache);
        if (\count ($array) > 0)
            return $array[0];
        return NULL;
    }

    /**
     * Performes a simple search
     *
     */
    public static function search($params, $op = 'and', $type = 'strict'){
        $table_name = static::$table_name;

        $sql = "SELECT * FROM `$table_name`";
        $conditions = array();
        foreach($params as $key=>$value){
            if (! \in_array($key, static::$fields)) continue;
            switch ($type){
                case 'reg':
                    $conditions[]= " `{$key}` REGEX '". static::$db->escape($value) ."' ";
                    break;
                case 'contains':
                    $conditions[]= " `{$key}` LIKE '%". static::$db->escape($value) ."%' ";
                    break;
                case 'start':
                    $conditions[]= " `{$key}` LIKE '". static::$db->escape($value) ."%' ";
                    break;
                case 'end':
                    $conditions[]= " `{$key}` LIKE '%". static::$db->escape($value) ."' ";
                    break;
                case 'strict':
                    $conditions[]= " `{$key}` = '". static::$db->escape($value) ."' ";
                    break;
                case '>':
                case '<':
                case '>=':
                case '<=':
                    $conditions[]= " `{$key}` $type '". static::$db->escape($value) ."' ";
                    break;
                case 'in':
                    if(!\is_array($value)){
                        $value = preg_split("/\s*,\s*/",$value);
                    }
                    foreach($value as &$elem){
                        $elem = static::$db->escape($elem);
                    }

                    $conditions[]= " `{$key}` IN( '".\implode("','",$value)."' )";
                    break;
                case 'direct':
                default:
                    $conditions[]= " `{$key}` ". $value ." ";
            }
        }

        if (count($conditions > 0))
            $sql.="WHERE ". \implode(" {$op} ",$conditions);
        return static::loadByQuery($sql);
    }


    /**
     * Load multiple instances into memory at once
     */
    protected static function generic_prepare($pk_fields, $keys, $cache = false){

        //echo "<hr> load generic prepare ";
        //var_dump(func_get_args());
        //debug_print_backtrace();

        $call_class = \get_called_class();

        $table_name = static::$table_name;

        $numeric_keys = array();
        $all_keys = array();

        if (!\is_array($keys)){
            $keys = \array_slice(\func_get_args(),1);
        }
        $preloaded = array();
        foreach($keys as $key){
            if (isset(static::$loaded[$key])){
                $preloaded[] = static::$loaded[$key];
                //echo "<p>Model is loaded</p>";
                continue;
            }
            $from_xml = static::loadXML($key);
            if ($from_xml !== false)
            {
                $preloaded[] = $from_xml;
                //echo "<p>Model is in xml</p>";
                continue;
            }
            if (\is_numeric($key)) $numeric_keys[]= $key;
            $all_keys[]= static::$db->escape($key);
        }
        $loaded_now = array();

        //var_dump($all_keys);

        if (count($all_keys) > 0){
            $query = "SELECT * FROM `{$table_name}` ";

            $conditions = array();
            foreach ($pk_fields as $field=>$numeric){
                $use_field = $numeric;
                $is_numeric = false;
                if (\is_bool($numeric)){
                    $use_field = $field;
                    $is_numeric = $numeric;
                }

                if ($is_numeric){
                    $conditions[] = " `$use_field` IN('".\implode("','", $numeric_keys)."') ";
                } else {
                    $conditions[] = " `$use_field` IN('".\implode("','", $keys)."') ";
                }

            }

            if (count($conditions) > 0){
                $query.=" WHERE ".\implode(" OR ", $conditions);
            }

            //var_dump($query);

            $loaded_now = static::loadByQuery($query,$cache);
        }

        return \array_merge($preloaded, $loaded_now);
    }

    /**
     * Load instances of the model based on a query
     */
    public static function loadByQuery($query){
        //echo "<hr> load model by query ";
        //var_dump(func_get_args());
        //debug_print_backtrace();

        $call_class = static::getClassForTable(static::$table_name);

        $results = static::$db->get_results($query, ARRAY_A);
        $elements = array();

        if ($results)
        foreach($results as $row){
            $element = new $call_class($row);
            $elements[] = $element;
            static::$loaded[$row['id']] = $element;
            $element->saveJSON(array($row['id']));
        }

        return $elements;
    }
    
    /**
     * Load the first element found in the givven query
     * @param type $query
     */
    public static function loadFirst($query)
    {
        $elements = static::loadByQuery($query);
        if (count($elements) > 0)
            return $elements[0];
        return null;
    }

    protected function generic_saveJSON($keys){
        //echo "<hr> generic save xml ";
        //var_dump($keys);
        //debug_print_backtrace();

        $fields = static::$fields;

        $file = static::$cache_dir."/".static::$table_name."/".\implode('-',$keys).".json";

        $array = array();
        foreach ($fields as $field)
        {
            $array[$field] = \utf8_encode( $this->getDBVar($field));
        }
        return \file_put_contents($file, \json_encode((object) $array));

    }

    public function saveJSON()
    {
        // this will work in most cases
        $this->generic_saveJSON(array($this->getDBVar('id')));
    }

    protected static function generic_loadXML($keys){

        $fields = static::$fields;

        $file = static::$cache_dir."/".static::$table_name."/".\implode('-',$keys).".json";

        if (! \file_exists($file) || !\is_readable($file)) return false;

        $model = static::getClassForTable(static::$table_name);

        $object = new $model;

        $contents = \file_get_contents($file);
        if ($contents === false) return false;

        $json = \json_decode($contents);
        if ($json === NULL) return false;
        //if (!is_object($json))            return FALSE;
        //var_dump($json);
        foreach ($json as $key=>$value)
        {
            $field_name = $key;
            $field_value = \utf8_decode($value);
            $object->setDBVar($field_name, $field_value);
        }

        return $object;
    }

    protected static function loadXML($id)
    {
        $from_xml = static::generic_loadXML(array($id));
        if ($from_xml !== false)
        {
            static::$loaded[$id] = $from_xml;
        }

        return $from_xml;

    }

    /**
     * List of loaded models
     */
    protected static $loaded = array();
}
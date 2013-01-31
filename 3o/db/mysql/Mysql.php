<?php

namespace trio\db;

use TUtil;
use mysqli_result;
use DomainException;
require_once \TRIO_DIR.'/framework.php';

/**
 * MySQL driver based on mysqli.
 * This driver is designed to be compatible with ezsql library 
 * (that doesn't use mysqli) while also providing extra functionality (like caching)
 * EZ SQL Compatibility methods
     * Implemented
     * $db->get_results -- get multiple row result set from the database (or previously cached results)
     * $db->get_row -- get one row from the database (or previously cached results)
     * $db->get_col -- get one column from query (or previously cached results) based on column offset
     * $db->get_var -- get one variable, from one row, from the database (or previously cached results)
     * $db->query -- send a query to the database (and if any results, cache them)
     * $db->get_col_info -- get information about one or all columns such as column name or type
     * $db->escape -- Format a string correctly to stop accidental mal formed queries under all PHP conditions
 * Not Implemented EZ SQL methods and reasons
     * $db->debug -- print last sql query and returned results (if any)
        * Why? TrioMysql usses a completely different aproach to debugging
     * $db->vardump -- print the contents and structure of any variable
        * Why? use PHP's var_dump and something like Xdebug
     * $db->select -- select a new database to work with 
        * Why? Trio MySQL's setDB method
     * $db->hide_errors -- turn ezSQL error output to browser off
     * $db->show_errors -- turn ezSQL error output to browser on
        * Why? TrioMysql doesn't generate errors, just exceptions that 
        * you should catch (Bad things may happen if you don't)
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage Database
 */

// ezSql compatibility constants
define('OBJECT','OBJECT',true);
define('ARRAY_A','ARRAY_A',true);
define('ARRAY_N','ARRAY_N',true);
if(!defined('TMYSQL_CACHE_INTERVAL'))
    define ('TMYSQL_CACHE_INTERVAL', false, true);
class Mysql {
    
    /**
     *
     * @var string Database name
     */
    private $db = false; 
    /**
     *
     * @var string Database User
     */
    private $user = 'root'; 
    
    /**
     *
     * @var string Database User's password
     */
    private $password = '';
    
    /**
     *
     * @var the database host name or IP (in most cases, this should not be changed)
     */
    private $host = 'localhost';
    
    /**
     * @var string the cache directory (where to save the server cache)
     */
    public $cache_dir = 'cache';
    
    /**
     *
     * @var mysqli
     */
    private $mysqli;
    
    /**
     * The rows affected by the last database operation
     * @var type 
     */
    private $affected_rows = 0;
    
    /**
     * The last inserted ID in Auto-Increment field
     * @var type 
     */
    private $last_id;
    
    /**
     * The last submited query
     * @var type 
     */
    private $last_query;
    
    /**
     *
     * @var mysqli_result Last result set
     */
    private $last_result;
    
    /**
     * Total query count so far
     * @var type 
     */
    private $query_count = 0;
    
    /**
     * Only in development mode
     * @var array All the queries executed in the current session.
     */
    private $query_log = array();
    
    /**
     * Should all queries be saved for debugging?
     * @var boolean 
     */
    public $development_mode = false;
    
    /**
     * 
     * @param type $db
     * @param type $user
     * @param type $password
     * @param type $host
     */
    public function __construct($db = false, $user = 'root', $password = '', $host = 'localhost') 
    {
        if ($db !== false)
        {
            $this->connect($db, $user, $password, $host);
        }
    }
    
    public function __destruct() {
        $this->run_queue();
    }


    /**
     * Private variable getter
     * @param type $name
     */
    public function __get($name) {
        switch (\strtolower(\trim($name)))
        {
            case 'insert_id':
            case 'inserted_id':
            case 'last_id':
            case 'auto_increment':
                return $this->getLastId();
            case 'affected_rows':
            case 'rows_affected':
                return $this->getAffectedRows();
            case 'total_queries':
            case 'query_count':
                return $this->query_count;
        }
        
        // try to determine a sugestion
        
        // array of words to check against
        $words  = array('insert_id','inserted_id','last_id','affected_rows','rows_affected', 'auto_increment');

        // no shortest distance found, yet
        $shortest = -1;
        $closest = '';
        // loop through words to find the closest
        foreach ($words as $word) {

            // calculate the distance between the input word,
            // and the current word
            $lev = \levenshtein($name, $word);

            // if this distance is less than the next found shortest
            // distance, OR if a next shortest word has not yet been found
            if ($lev <= $shortest || $shortest < 0) {
                // set the closest match, and shortest distance
                $closest  = $word;
                $shortest = $lev;
            }
        }
        
        // throw an exception because the property was not found
        throw new \UnexpectedValueException ("Unrecognized Property Name (".__CLASS__."::{$name}). Did you meen {$closest}?");
    }
    
    /**
     * Connect to the database given the parameters
     * @param type $db
     * @param type $user
     * @param type $password
     * @param type $host
     */
    public function connect($db = 'trio', $user = 'root', $password = '', $host = 'localhost') 
    {
        $this->setDB($db, false);
        $this->setUser($user, false);
        $this->setPassword($password, false);
        $this->setHost($host, false);
        
        if ($this->mysqli instanceof \mysqli)
        {
            $this->mysqli->close();
        }
        
        $this->mysqli = new \mysqli($host, $user, $password, $db);
    }
    
    /**
     * Set the database name 
     * @param type $db
     * @param type $reconect
     */
    public function setDB($db, $reconect = true)
    {
        $this->db = $db;
        if ($reconect)
        {
            $this->connect($this->db, $this->user, $this->password, $this->host);
        }
    }
    
    /**
     * 
     * @param type $db
     * @param type $reconect
     */
    public function setUser($db, $reconect = true)
    {
        $this->user = $db;
        if ($reconect)
        {
            $this->connect($this->db, $this->user, $this->password, $this->host);
        }
    }
    
    /**
     * 
     * @param type $db
     * @param type $reconect
     */
    public function setPassword($db, $reconect = true)
    {
        $this->password = $db;
        if ($reconect)
        {
            $this->connect($this->db, $this->user, $this->password, $this->host);
        }
    }
    
    /**
     * 
     * @param type $db
     * @param type $reconect
     */
    public function setHost($db, $reconect = true)
    {
        $this->host = $db;
        if ($reconect)
        {
            $this->connect($this->db, $this->user, $this->password, $this->host);
        }
    }
    
    /**
     * Run a query, return the results and maybe cache.
     * Database Update querys (anything but SELECT) are not cached, but are queued for as long as posible
     * @param string $query The SQL Query
     * @param mixed $cache the interval (string or DateInterval) to keep the results or false to skip caching
     * @return mixed
     */
    public function query($query, $cache = TMYSQL_CACHE_INTERVAL) {
        $query = $this->minimize_query($query);
        if (\preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)/i', $query))
        {
            // this query expects results
            return $this->run_select($query, $cache);
        } else 
        {
            // this will not actually return anything ...
            return $this->run_update($query);
        }
    }
    
    private function minimize_query($query)
    {
        return \trim (\str_replace(array("\n", "\r"), ' ', $query));
    }
    
    /**
     * Run a Result-retriving statement and return the results as an Object array
     * @param type $query
     * @param type $cache
     */
    private function run_select($query, $cache = TMYSQL_CACHE_INTERVAL)
    {
        // return last results if the same query is submited
        if ($query == $this->last_query)
        {
            return $this->decodeResults($this->last_result);
        }
        
        // try to fetch data from cache file
        $cache_file = $this->cache_dir.'/'.\md5($query).'.json';
        if (\count ($this->query_queue) == 0 && $cache != false)
        {
            $cache_data = TUtil::readSerializedCache($cache_file);
            if ($cache_data !== false)
            {
                return $cache_data;
            }
        }
        
        
        // Run unattended updates so the data retrived is as fresh as possible
        $this->run_queue();
        
        // free some memory
        if ($this->last_result instanceof mysqli_result)
        {
            $this->last_result->free();
        }
        
        // get the results
        $this->last_result = $this->mysqli->query($query);
        $this->query_count ++;
        $this->addQuery($query);
        if (!$this->last_result instanceof mysqli_result)
        {
            throw new \Exception('Invalid Query:'.$this->mysqli->error.' Query:'.$query);
        }
        
        // populate results meta-data
        $results = $this->decodeResults($this->last_result);
        
        // save cache
        if (false != $cache)
        {
            TUtil::saveSerializedCache($results, $cache_file, $cache);
        }
        
        // save last query
        $this->last_query = $query;
        
        return $results;
    }
    
    /**
     * 
     * @param mysqli_result $mysqli_result
     */
    private function decodeResults($mysqli_result) {
        $data = array();
        while ($row = $mysqli_result->fetch_object())
        {
            $data[]= $row;
        }
        
        // reset the data pointer
        $mysqli_result->data_seek(0);
        
        return $data;
    }
    
    /**
     * Run a query that is expected to update the database in some sort
     * @param type $query
     */
    private function run_update($query)
    {
        $this->query_queue[]=$query;
    }
    
    /**
     * @var array The Queries that havn't been executed yet
     */
    private $query_queue = array();
    
    /**
     * Run the current statement queue
     * @return type
     */
    private function run_queue(){
        if (count ($this->query_queue) == 0)
            return;
        
        // run query in mysql
        $this->mysqli->multi_query(\implode (';', $this->query_queue));
        
        $this->query_count+= count($this->query_queue);
        $this->addQuery($this->query_queue);
        
        // free mysqli results (for some reason, we have to do that...)
        do{
            $this->mysqli->store_result();
        }while($this->mysqli->more_results() && $this->mysqli->next_result());
        
        // populate object properties
        $this->affected_rows = $this->mysqli->affected_rows;
        $this->last_id = $this->mysqli->insert_id;
        $this->query_queue = array();
    }
    
    public function getLastId()
    {
        // run unattended querys
        $this->run_queue();
        
        // return the last result
        return $this->last_id;
    }
    
    public function getAffectedRows()
    {
        // run unattended querys
        $this->run_queue();
        
        // return the last result
        return $this->affected_rows;
    }
    
    public function getQueryCount(){
        return $this->query_count;
    }
    
    protected function addQuery($query){
        if (!$this->development_mode) return;
        $query = array($query);
        $this->query_log = \array_merge($this->query_log, $query);
    }
    
    public function getLog()
    {
        return $this->query_log;
    }
    
    /**
     * Get the results of a query (or the last executed result-query)
     * @param string $query
     * @param mixed $cache
     * @return mixed
     */
    public function get_results($query = '', $format = OBJECT, $cache = TMYSQL_CACHE_INTERVAL)
    {
        $results = $this->query($query != ''?$query:$this->last_query, $cache);
        switch ($format)
        {
            case ARRAY_A:
                $return_results = array();
                foreach ($results as $row)
                {
                    $row_data = array();
                    foreach ($row as $key => $value) {
                        $row_data[$key] = $value;
                    }
                    $return_results[]= $row_data;
                }
                return $return_results;
            case ARRAY_N:
                $return_results = array();
                foreach ($results as $row)
                {
                    $row_data = array();
                    foreach ($row as $key => $value) {
                        $row_data[] = $value;
                    }
                    $return_results[]= $row_data;
                }
                return $return_results;
            case OBJECT:
            default :
            return $results;
        }
    }
    
    /**
     * Get a row from the result
     * @param string $query
     * @param int $offset
     * @param mixed $cache
     * @return Object[]
     * @throws DomainException
     */
    public function get_row($query = '', $offset = 0, $cache = TMYSQL_CACHE_INTERVAL) 
    {
        if ($query == '' || $query == 'last')
        {
            $query = $this->last_query;
        }
        
        if (\func_num_args() == 2 && !\is_numeric(\func_get_arg(1)))
        {
            $offset = 0;
            $cache = func_get_arg(1);
        }
        
        $results = $this->get_results($query,OBJECT, TMYSQL_CACHE_INTERVAL);
        if (isset($results[$offset]))
        {
            return $results[$offset];
        }
        
        throw new DomainException ('Invalid Offset');
    }
    
    /**
     * Get one column from the query result or previous
     * @param type $query
     * @param type $offset
     * @param type $cache
     * @return type
     * @throws DomainException 
     */
    public function get_col($query = '', $offset = 0, $cache = TMYSQL_CACHE_INTERVAL)
    {
        if ($query == '' || $query == 'last')
        {
            $query = $this->last_query;
        }
        
        if (\func_num_args() == 2 && !\is_numeric(\func_get_arg(1)))
        {
            $offset = 0;
            $cache = \func_get_arg(1);
        }
        
        // try to read cache
        $cache_file = $this->cache_dir.'/column-'.$offset.'-'.  \md5($this->minimize_query($query)).'.json';
        if (($data = TUtil::readSerializedCache($cache_file)) !== false)
        {
            return $data;
        }
        
        // determine the new array
        $this->get_results($query, OBJECT, TMYSQL_CACHE_INTERVAL);
        if (\is_numeric($offset) && ($this->last_result->num_rows < $offset || $offset < 0))
        {
            throw new DomainException ('Invalid Offset');
        }
        
        if (!\is_numeric($offset))
        {
            // search the result fields for the column name
            $field_found = false;
            while ($field = $this->last_result->fetch_field())
            {
                if ($field->name == $offset)
                {
                    $field_found = true;
                }
            }
            
            //TODO: search the original names of the field?
            
            // reset the field pointer
            $this->last_result->field_seek(0);
            
            // throw exception if nothing was found
            if (! $field_found)
            {
                throw new DomainException ('Invalid offset');
            }
        }
        
        $data = array();
        while ($row = $this->last_result->fetch_array())
        {
            $data[]= $row[$offset];
        }
        
        // reset stuff
        $this->last_result->data_seek(0);
        
        if (false != $cache)
        {
            // save data to cache
            TUtil::saveSerializedCache($data, $cache_file, $cache);
        }
        
        return $data;
    }
    
    /**
     * Get a single-cell value from the result set
     * @param type $query
     * @param type $row
     * @param type $column
     * @param type $cache
     * @return type
     * @throws DomainException 
     */
    public function get_var(
            $query = '', 
            $row = 0, 
            $column = 0, 
            $cache = TMYSQL_CACHE_INTERVAL)
    {
        if ($query == '' || $query == 'last')
        {
            $query = $this->last_query;
        }
        
        
        if (\func_num_args() == 2 && !\is_numeric(\func_get_arg(1)))
        {
            $row = 0;
            $cache = func_get_arg(1);
        }
        
        if (func_num_args() == 3 && !is_numeric(func_get_arg(2)))
        {
            $column = 0;
            $cache = func_get_arg(2);
        }
        
        // try to read cache
        $cache_file = $this->cache_dir.'/var-'.$row.'-'.$column.'-'.  \md5($this->minimize_query($query)).'.json';
        if (($data = TUtil::readSerializedCache($cache_file)) !== false)
        {
            return $data;
        }
        
        $results = $this->get_results($query, OBJECT, $cache);
        if (false == $this->last_result->data_seek($row))
        {
            throw new DomainException ('Invalid row offset ('.$row.')');
        }
        
        if (false == $this->last_result->field_seek($column))
        {
            throw new DomainException ('Invalid column offset');
        }
        
        $row = $this->last_result->fetch_row();
        $data = $row[$column];
        // reset stuff
        $this->last_result->field_seek(0);
        $this->last_result->data_seek(0);
        
        if (false != $cache)
        {
            // save data to cache
            TUtil::saveSerializedCache($data, $cache_file, $cache);
        }
        
        return $data;
    }
    
    /**
     * Get column information based on the given result query or table name
     * @param type $query
     * @return type
     */
    public function get_col_info($query = '')
    {
        if ($query == '' || $query == 'last')
        {
            $query = $this->last_query;
        }
        // easyest scenario: we need col info just for the last query
        if ($query == $this->last_query)
        {
            return $this->last_result->fetch_fields();
        }
        
        // we have a query
        $query = $this->minimize_query($query);
        if (\preg_match('/^(SELECT|SHOW|DESCRIBE|EXPLAIN)\s+/i', $query))
        {
            $results = $this->mysqli->query($query);
            $this->query_count++;
            $this->addQuery($query);
            if (!$results instanceof mysqli_result)
            {
                throw new \UnexpectedValueException('Invalid Query');
            }
            $fields = $results->fetch_fields();
            $results->free();
            return $fields;
        }
        
        // this most likely is a table name
        return $this->get_table_info($query);
    }
    
    /**
     * Get information about a table's fields
     * @param type $table
     * @return type
     */
    public function get_table_info($table)
    {
        $table = \str_replace('`', '', $table);
        $query = "SELECT * FROM `{$table}` LIMIT 1";
        return $this->get_col_info($query);
    }
    
    /**
     * Escape the given string to work with an sql statement
     * @param type $string
     */
    function escape ($string)
    {
        return $this->mysqli->real_escape_string(\stripslashes($string));
    }
}
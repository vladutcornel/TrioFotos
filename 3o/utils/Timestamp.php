<?php
namespace trio;

use TUtil;
use DateTime;
require_once TRIO_DIR.'/framework.php';

/**
 * TrioTimestamp - Extends the native DateTime with the ability to be used 
 * in a string context
 *
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage Utils
 */
class Timestamp extends \DateTime{
    /**
     * The format suggested by TrioFramework is the Unix Timestamp because it 
     * can be converted to all the other formats. You should probbably extend
     * this Class if you need another default format
     * @var string The default date format
     * @see http://php.net/manual/en/function.date.php
     */
    public static $default_format = 'U';
    
    /**
     * Return this date in the default format
     * @return string
     */
    public function __toString() {
        return $this->format(static::$default_format);
    }
    
    /**
     * Create a new timestamp based on the provided SQL Time string
     * @param string $sql_date_string Y-m-d H:i:s
     * @return TrioTimestamp
     */
    public static function createFromSQL($sql_date_string)
    {
        try{
            $date = static::createFromFormat('*Y*m*d*H*i*s*', $sql_date_string);
            if (! $date instanceof DateTime)
                throw new \Exception;
            TUtil::changeClass($date, __CLASS__);
            return $date;
        } catch (Exception $e){
            return new Timestamp($sql_date_string);
        }
    }
}

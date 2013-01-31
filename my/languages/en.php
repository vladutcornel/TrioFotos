<?php
/**
 * English translation and generic language will be the same.
 */
class SiteTranslationEn{
    protected static $phrases = array(
        // no translation needed for English
    );
    
    public static function translate($phrase, $default = ''){
        if (isset(static::$phrases[$phrase])){
            return static::$phrases[$phrase];
        }
        
        // no translation found;
        if ($default)
            return $default;
        return $phrase;
    }
}
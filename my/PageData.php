<?php
Whereis::register('SiteTranslationEn', __DIR__.'/languages/en.php');
/**
 * HTML page data
 *
 * @author cornel
 */
class PageData {
    /**
     * Language
     * @var string
     */
    public static $lang = 'ro';
    
    /**
     * Load the translation for the given phrase.
     * The default parameter allows the use of keywords instead of phrases
     * @param string $phrase
     * @param string $default
     * @return string
     */
    public static function translate($phrase, $default = ''){
        $class = 'SiteTranslation'.self::$lang;
        if (!class_exists($class)){
            if (file_exists(__DIR__.'/languages/'.self::$lang.'.php')) {
                include_once __DIR__.'/languages/'.self::$lang.'.php';
            } else {
                include_once __DIR__.'/languages/en.php';
                $class = 'SiteTranslationEn';
            }
        }
        
        return $class::translate($phrase, $default);
    }
    
    /**
     * Output the translation for the given phrase
     * @param string $phrase text or keywords (if using the second param.) to be translated
     * @param string $default allows the use of keywords for the phrase
     * @see PageData::translate()
     */
    public static function write($phrase, $default = ''){
        echo self::translate($phrase, $default);
    }
    
    /**
     * requested URi
     * @var string
     */
    public static $uri = 'index.php';
    
    /**
     * Site url or uri
     * @var string
     */
    public static $site_url = '/';
    
    /**
     * Get the site root URI or build an address based on that root
     * @param string $uri uri to be appended
     * @return string fuill URi
     */
    public static function site_address($uri = ''){
        return self::$site_url.'/'.$uri;
    }
    
    public static function lang_address($lang, $uri = null){
        if (is_null($uri))
            $uri = self::$uri;
        $parts = explode('/', static::site_address());
        $parts[count($parts) - 2] = $lang;
        
        return implode('/', $parts).$uri;
    }


    /**
     * template to be loaded
     * @var string
     */
    public static $template = 'default.php';
    
    /**
     * Output a template file.
     * @param string $template
     */
    public static function renderTemplate($template = null){
        $base = __DIR__.'/templates/';
        if (is_null($template))
            $template = $base.self::$template;
        if (file_exists($template)){
            include $template;
        } elseif(file_exists($base.$template)){
            include $base.$template;
        } else {
            echo '<!-- Template file not found -->';
            self::renderStyles();
            echo self::$content;
            self::renderScripts();
            echo '<!-- Template file not found -->';
        }
    }
    /**
     * JavaScript Source files
     * @var array
     */
    public static $scripts = array();
    
    /**
     * Register a new JavaScript URL
     * @param string $file
     */
    public static function addScript($file){
        if (!in_array($file, self::$scripts))
            self::$scripts[]= $file;
    }
    /**
     * Render HTML source to include all registered scripts
     */
    public static function renderScripts(){
        foreach (self::$scripts as $file){
            echo '<script src="',$file,'"></script>';
        }
    }
    
    /**
     * CSS files to load
     * @var array
     */
    public static $styles = array();
    
    /**
     * Register a new CSS script url
     * @param type $file
     */
    public static function addStyle($file){
        
        if (!in_array($file, self::$styles))
        self::$styles[]= $file;
    }
    
    /**
     * Render HTML source to include all registered styles
     * Can be used in the head section of the template
     */
    public static function renderStyles(){
        foreach (self::$styles as $file){
            echo '<link rel="stylesheet" href="',$file,'" />';
        }
    }
    
    /**
     * Page Title
     * @var string
     */
    public static $title = 'Trio Foto Host';
    
    /**
     * Page SEO description
     * @var string
     */
    public static $description = '';
    
    /**
     * SEO Page keywords
     * @var array
     */
    public static $keywords = array();

    /**
     * Page generated content
     * @var string
     */
    public static $content = '';
    
    public static function getUser(){
        $log = UserModel::getCurrentUser();
        TGlobal::setSession('logdata', $log);
        return $log;
    }

    public static function isLogged(){
        $user = static::getUser();
        
        if ($user->real != 0 || $user->active == 0)
            return false;
        return true;
    }
    
    /**
     * Redirect to an internal URi. 
     * By default, the script will be imediatly ended
     * @param string $uri
     * @param boolean $end
     */
    public static function redirect($uri, $end = TRUE){
        header('location: '.static::site_address($uri));
        if ($end) exit;
    }

    /**
     * Map of values that need to be passed around
     * @var array
     */
    private static $data = null;
    
    public static function get($key){
        return self::$data->getVar($key);
    }
    
    public static function set($key, $value) {
        self::$data[$key] = $value;
    }
    
    /**
     * Actions to take before anything is done
     */
    public static function init(){
        self::$data = new TObject;
    }
}

PageData::init();
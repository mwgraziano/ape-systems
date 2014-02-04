<?php



define("DEFAULT_CONTROLLER","PubController");
define("DEFAULT_FUNCTION","index");

if(getenv('environment') == 'production' || (isset($env) && $env=='production')) {
    error_reporting(E_ALL ^ E_NOTICE);
    
}
else {
    error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
    define("ENV","dev");
    define("DBHOST","localhost");
    define("DBUSER","apesystem");
    define("DBPASS","###########");
    define("DBNAME","apesystem");
    define("MEMCACHE_SERVERS","127.0.0.1");
    define("SITE_URL","http://dev.ape-system.com");
    define("IMG_URL","/img/");
    define("CSS_URL","/css/");
    define("JS_URL","/js/");
}

define("USER_IMG_URL", "/img/users/");
define("S3_USER_IMG_DIR","");
define("S3_USER_FILE_DIR","");
define("S3_BUCKET","");
define("S3_ACCESS_KEY","");
define("S3_ACCESS_SECRET","");

define("APP_PATH","/var/www/ape/app/");
define("MODEL_PATH","/var/www/ape/app/models/");
define("OBJECT_PATH","/var/www/ape/app/objects/");
define("SERVICE_PATH","/var/www/ape/app/services/");
define("VIEW_PATH","/var/www/ape/app/views/");
define("CONTROLLER_PATH","/var/www/ape/app/controllers/");

define("USER_IMG_PATH","/var/www/ape/html/img/users/");
        
define("GLOBAL_SECRET","#########");
        
define("AUTH_LEVEL_LOGGEDOUT",0);
define("AUTH_LEVEL_LOGGEDIN",1);
define("AUTH_LEVEL_ATHLETE",2);
define("AUTH_LEVEL_COACH",5);
define("AUTH_LEVEL_COACHADMIN",6);
define("AUTH_LEVEL_ADMIN",9);
define("AUTH_LEVEL_SUPERADMIN",10);
        
define("PERMISSION_READ",1);
define("PERMISSION_WRITE",2);
define("PERMISSION_DELETE",4);
define("PERMISSION_PERMIT",8);

define("MAX_FAILURE_SESSION",5);
define("MAX_FAILURE_USER",100);

define("SITE_CACHE_PREFIX","ape");
define("URL_BLOCKED","/auth/blocked/");

define("DEFAULT_ERROR_MESSAGE","An error has occurred");

define("ERROR_FIELD_MISSING",100);
define("ERROR_FIELD_INVALID",101);
define("ERROR_PERMISSION_DENIED",102);

define("SUCCESS","OK");

date_default_timezone_set("UTC");

//Setup Memcache and sessions
$mc_servers = explode(",", MEMCACHE_SERVERS);

foreach($mc_servers as $server) {
    $session_save_paths[] = "tcp://". $server .":11211";
}

$session_save_path = implode(",", $session_save_paths);

unset($session_save_paths);

ini_set('session.save_handler', 'memcache');
ini_set('session.save_path', $session_save_path);


//Include Paths
set_include_path(APP_PATH . PATH_SEPARATOR . 
                 CONTROLLER_PATH . PATH_SEPARATOR . 
                 MODEL_PATH . PATH_SEPARATOR .
                 SERVICE_PATH . PATH_SEPARATOR . 
                 OBJECT_PATH . PATH_SEPARATOR . 
                 get_include_path());


//Class Autoloading
spl_autoload_register("autoload");

function autoload($class) {
    include_once($class . ".php");
}


//Utility functions
function dump($var, $exit = true) {
    echo "<pre>";
    print_r($var);
    echo "</pre>";

    if ($exit) {
        $bt = debug_backtrace();
        dump($bt[0]['file'] . ":" . $bt[0]['line'], false);
        exit();
    }
}

function uncamel($fn) {
    $fns = explode("_",$fn);

    $rfn = array();
    
    foreach($fns as $fn) {
        $fn = preg_split('/(?<=\\w)(?=[A-Z])/', $fn);
        $fn = array_filter($fn);
        $rfn[] = strtolower(implode('_', $fn));
    }

    if(count($rfn) == 1) return $rfn[0];
    else return $rfn;
}

function camel($fn) {
    $my_ucfirst = function (&$w, $i) {
        $w = ucfirst($w);
    };

    $fn = preg_split("/[_\-]/", $fn);
    array_walk($fn, $my_ucfirst);

    return lcfirst(implode('', $fn));
}

function is_md5($str) {
    return (preg_match("/^[a-f0-9]{32}$/", strtolower($str)) == 1);
}

function is_password($str) {
    return (preg_match("/^[a-z0-9\!\@\#\$\%\^\&\*\(\)\-\_\=\+\`\~\?\<\>\,\.\;\:]{6,}$/i", $str) == 1);
}

function is_phone($str) {
    
    $phone = preg_replace("/[^\d]/", "", $str);
    
    $phone = preg_replace("/^1/","", $phone);
    
    if(strlen($phone) == 10) return $phone;
    
    return false;
}

function tz_offset($offset, $ts = "now") {
    $tz = new DateTime("now", new DateTimeZone(date_default_timezone_get()));
    $tz = date_offset_get($tz);
    
    $os = ($tz + $offset);
    
    return strtotime($ts ." ". $os ." hours");
}

function tz_offset_reverse($offset, $ts = "now") {
    
    $tz = new DateTime("now", new DateTimeZone(date_default_timezone_get()));
    $tz = date_offset_get($tz);
    
    $tz *= -1;
    $offset *= -1;
    
    $os = ($tz + $offset);
    
    return strtotime($ts ." ". $os ." hours");
    
}

?>

<?php

class Session
{
    public static function __callStatic($fn, $args) {
        $s = substr($fn, 0,3);
        
        switch($s) {
            case 'set':
                $_SESSION[uncamel(substr($fn,3))] = $args[0];
                break;
            case 'get':
                return $_SESSION[uncamel(substr($fn,3))];
                break;
        }
    }
    
	public static function beginSession(User $user)
	{
	    $_SESSION['user_id'] = $user->id;
		App::init()->initUser($user);
	}
	
	public static function hasSession()
	{
		return isset($_SESSION['user_id']);
	}
	
	public static function endSession()
	{
		self::end();
	}
	
	public static function start()
	{
	    session_start();
		if(isset($_SESSION['user_id']))
		{
		    App::init()->initUser(User::load($_SESSION['user_id']));
		}
	}
	
	public static function setRequestKey($key)
	{
		$_SESSION['request_key'] = $key;
	}
	
	public static function getRequestKey()
	{
		return @$_SESSION['request_key'];
	}
	
	private static function end()
	{
		session_destroy();
	}
    
    public static function getSessionId() {
        return session_id();
    }

    public static function addNonce($key, $obj, $id) {
    	$_SESSION['nonce'][$key] = array("object"=>strtoupper($obj), "id"=>$id, "expires"=>(time() + 1800));
    }

    public static function checkNonce($key, $obj, $id) {
    	$o = false;

    	if(array_key_exists($key, $_SESSION['nonce'])) {

    		$o = $_SESSION['nonce'][$key];
    		//unset($_SESSION['nonce'][$key]);

    		if($o['object'] == strtoupper($obj) && $o['id'] == $id) return true;
    	}

    	return false;
    }

    public static function generateNonce($obj, $id) {
    	$key = md5(strtoupper($obj) .'|'. $id .'|'. microtime());
    	self::addNonce($key, $obj, $id);
    	self::cleanNonce();
    	return $key;
    }

    public static function cleanNonce() {
    	foreach($_SESSION['nonce'] as $key=>$val) {
    		if($val['expires'] < time()) unset($_SESSION['nonce'][$key]);
    	}
    }
    
}

?>
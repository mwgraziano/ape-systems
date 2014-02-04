<?php

/*
 * This is our application core
 */

class App
{
	private static $instance;
    public $user;
    public $account;
    public $profile_user;
	
	private function __construct()
	{
		
        //Initialize Cache
        $c = Cache::init();
        $srvrs = explode(',',MEMCACHE_SERVERS);
        foreach($srvrs as $srvr)
            $c->addServer($srvr, 11211);
        
	}
	
    public static function init()
	{
		if(is_null(self::$instance))
		{
			self::$instance = new self();
            Session::start();
		}
		
		return self::$instance;
	}
    
    public static function run() {
        $app = self::init();
        
        switch($app->getRunType()) {
            case "cli":
                $app->processCLI();
                break;
            default:
                $app->processWeb();
                break;
        }
    }
    
    private function getRunType() {
        if(defined("STDIN")) return 'cli';
        
        return "web";
    }
    
    private function processWeb() {
        
        $this->auth();
        
        $this->request = Request::init();
        
        $this->controller = Controller::init();
        
        $this->controller->route();
        
    }
    
    public function initUser($user) {
        
        if(!is_a($user, "User")) return false;
        
        $this->user = $user;
        
        if(Session::getAccountId())
            $this->account = Account::load(Session::getAccountId());
        else
            $this->account = Account::load($this->user->getAccountId());
    }
	
	
	public function redirect($url)
	{
		header("location: $url");
		exit;
	}
    
    public function hredirect($hash)
    {
        header("Content-Type: application/json");
        $ret = array("api"=>array("redirect"=>$hash));
        
        echo json_encode($ret);
        
        exit;
    }
	
	public function generateRequestKey()
	{
		$key = md5(microtime() ."|". rand(10000,100000));
		
		Session::setRequestKey($key);
		
		return $key;
	}
    
    
    public function authObject($obj, $permissions) {
        $u = $this->user;
        
        if(empty($u)) $success = false;
        else $success = Permission::isAuth($u->getId(), $obj, $permissions);
        
        if(!$success) {
            $this->incrementPermissionDenied();
        }
        
        return $success;
    }
    
    public function isAuth() {
        return (($this->user && is_a($this->user, "User")) ? $this->user->getAuthLevel() : 0);
    }
    
    public function incrementPermissionDenied() {
        $c = Cache::init();
        
        if($this->user) $id = $this->user->getId();
        else $id = Session::getSessionId();
        
        $permission_denied_key = $c->generateKey("user_blocked[". $id ."]");
        
        
    }
    
    public function auth() {
        
        
        return;
        
    }
    
    public function buildSecret($owner_id = false) {
        if(!empty($owner_id)) {
            $v = $owner_id ."|";
        } else {
            $u = $this->user;
            if(!empty($u)) $v = $u->getId() ."|";
        }
        
        return $v . GLOBAL_SECRET;
    }
    
    public function unauthorized(){
        // header('HTTP/1.1 401 Unauthorized');
        // include(VIEW_PATH ."unauthorized.php");
        $this->redirect("/");
        exit;
    }
    
    public function fire($event, $context) {
        
    }
    
    public function getUserId() {
        if($this->user) return $this->user->getId();
        
        return false;
    }
    
    public function getStyle() {
        $styles = array(
            "{back-color-1}"=>"#6e6e6e",
            "{back-color-2}"=>"#eee",
            "{back-color-3}"=>"#AEE239",
            "{back-color-4}"=>"#fff",
            "{font-color-1}"=>"#fff",
            "{back-color-error}"=>"#333",
            "{font-color-2}"=>"#888"
        );
        
        if(!empty($this->account)) {
            $styles = array_merge($styles,$this->account->getStyle());
        }
        
        return $styles;
    }

    /*
     * 
     * Pass server time and get the current user's local time
     * 
     */
    public function userTime(DateTime $time = null) {
        if(empty($this->user) or !is_a($time, "DateTime")) return $time;
        
        $offset = $this->user->getTimezone();
        
        $di = new DateInterval('PT'. abs($offset) ."H");
        
        if($offset < 0) $di->invert = 1;
        
        $time->add($di);
        
        return $time;
    }
    
    /*
     * pass the current user's local time and get the server's time
     */
     public function serverTime(DateTime $time = null) {
        if(empty($this->user) or !is_a($time, "DateTime")) return $time;
        
        $offset = $this->user->getTimezone();
        
        $di = new DateInterval('PT'. abs($offset) ."H");
        
        if($offset > 0) $di->invert = 1;
        
        $time->add($di);
        
        return $time;
     }
}

?>

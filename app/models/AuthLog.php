<?php

class AuthLog extends Model {
    
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "auth_log";
        $this->row_identifier = "id";
    }
    
    public static function log($user_id, $via = "web") {
        
        $a = new AuthLog();
        $a->setUserId($user_id);
        $a->setLoginTime(date('Y-m-d H:i:s'));
        $a->setBrowserSignature("");
        $a->setIpAddress(ip2long($_SERVER['REMOTE_ADDR']));
        $a->setPhpSessionId(session_id());
        $a->setLoginVia($via);
        return $a->save();
        
    }
}

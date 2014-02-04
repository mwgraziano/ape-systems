<?php

class User extends Model
{
	protected $_version_ = 1.0;
	
	public function __construct()
	{
		parent::__construct();
		$this->table = "users";
		$this->row_identifier = "id";
	}
    
    public static function encryptPassword($pass) {

        return md5($pass);
        
    }
    
    public static function load($id, $is_active = 1)
    {
        
        if(!is_numeric($id) || empty($id)) return false;
        
        $sql = sprintf("SELECT id, account_id, AES_DECRYPT(email, '%s') as `email`, `timezone`, auth_level FROM users WHERE id = %d AND active = %d", GLOBAL_SECRET, $id, $is_active);
        
        $user = array_shift(DB::connect()->getLoaded($sql, "User"));
        
        return $user;
    }
    
    public static function loadByEmail($email) {
        
        $sql = sprintf("SELECT id FROM users WHERE email = AES_ENCRYPT('%s','%s') AND active = 1", $email, GLOBAL_SECRET);
        
        $user_id = DB::connect()->getOne($sql);
        
        return self::load($user_id);
        
    }
    
    public function setLastUpdate($t = false) {
        if(!$t) $t = date('Y-m-d H:i:s');
        
        $sql = sprintf("UPDATE users SET last_update = '%s' WHERE id = %d", $t, $this->getId());
        
        return DB::connect()->update($sql);
    }
    
    //Don't use save for user
    public function save($id = null)
    {
        return false;
    }
    
    public function changePassword($new_passwd) {
        $db = DB::connect();
        
        $new_passwd = self::encryptPassword($new_passwd);
        
        $sql = sprintf("UPDATE users SET passwd = '%s', last_update = NOW() WHERE id = %d", $new_passwd, $this->getId());
        
        return $db->update($sql);
    }
    
    public function changeEmail($new_email) {
        $db = DB::connect();
        
        $sql = sprintf("UPDATE users SET email = AES_ENCRYPT('%s','%s'), last_update = NOW() WHERE id = %d", $new_email, GLOBAL_SECRET, $this->getId());
        
        $this->setEmail($new_email);
        $this->cache();
        
        return $db->update($sql);
    }
    
    public static function createUser($email, $passwd, $acct_id, $bypass_encrypt = false) {
        
        $db = DB::connect();
        
        if(!$bypass_encrypt) $passwd = self::encryptPassword($passwd);
        
        $sql = sprintf("INSERT INTO users SET email = AES_ENCRYPT('%s','%s'), passwd = '%s', account_id = %d, last_update = NOW()", $email, GLOBAL_SECRET, $passwd, $acct_id);
        
        return $db->insert($sql);
        
    }
    
    public function saveAuthLevel($level) {
        $db = DB::connect();
        
        $sql = sprintf("UPDATE users SET auth_level = %d WHERE id = %d", $level, $this->getId());
        
        error_log($sql);
        $this->uncache();
        
        return $db->update($sql);
    }
    
    
    public static function authenticate($email, $password)
	{
		$db = DB::connect();
		
        $sql = sprintf("SELECT id FROM users WHERE email = AES_ENCRYPT('%s', '%s') AND passwd = '%s' AND active = 1", $email, GLOBAL_SECRET, self::encryptPassword($password));
		
		return $db->getOne($sql);
	}
    
    public static function buildEncryptionKey($user_id) {
        return $user_id ."|". GLOBAL_SECRET;
    }
    
    
    /**
     * @param $offset int Minutes offset
     */
    public function saveTimezone($offset, $name) {
        
        $sql = sprintf("UPDATE users SET `timezone` = %d, `timezone_text` = '%s' WHERE id = %d", $offset, $name, $this->getId());
        
        $this->uncache();
        
        return DB::connect()->update($sql);
    }
    
    public static function searchByEmail($email) {
        
        $sql = sprintf("SELECT id, AES_DECRYPT(email, '%s') as `email`, auth_level, active FROM users WHERE AES_DECRYPT(email, '%s') LIKE '%%%s%%'", GLOBAL_SECRET, GLOBAL_SECRET, $email);
        
        return DB::connect()->getAll($sql);
        
    }
    
    public static function undelete($id) {
        
        if(empty($id)) return;
        
        $sql = sprintf("UPDATE users SET active = 1 WHERE id = %d", $id);
        
        return DB::connect()->update($sql);
        
    }

}

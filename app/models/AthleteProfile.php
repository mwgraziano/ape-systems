<?php

class AthleteProfile extends KVModel {
    
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "athlete_profile";
        $this->row_identifier = "athlete_id";
    }
    
    
    public function search($owner_id, $query = "") {
        if(empty($query)) return array();
        
        $secret = App::init()->buildSecret($owner_id);
        
        $sql = sprintf("SELECT ap.athlete_id as id, 
             AES_DECRYPT(ap.prop_value, '%s') as `name`, 
             AES_DECRYPT(ap2.prop_value, '%s') as `photo`
            FROM athlete_profile ap 
            JOIN athletes a ON ap.athlete_id = a.id
            LEFT JOIN athlete_profile ap2 ON ap2.athlete_id = ap.athlete_id and ap2.prop_name = 'photo' 
            WHERE a.account_id = %d
             AND a.active = 1
             AND ap.prop_name = 'name'
             AND CAST(AES_DECRYPT(ap.prop_value, '%s') as CHAR) LIKE '%%%s%%'", $secret, $secret, $owner_id, $secret, $query);
             
        $db = DB::connect();
        return $db->getAll($sql);
    }
}

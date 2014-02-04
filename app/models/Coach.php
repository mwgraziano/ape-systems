<?php

class Coach extends EncryptedModel {
    
    protected $_version_ = 1.0;
    
    protected $my_params = array("id","account_id","status");
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "coaches";
        $this->row_identifier = "id";
    }

    public static function load($id, $acct_id = false) {
    	$ret = parent::load($id);

    	if(!empty($ret)) {
    		if(is_array($ret)) {
    			foreach($ret as &$c) {
    				$c->loadProfile($acct_id);
    			}
    		}
    		else if(is_a($ret, "Coach")) {
    			$ret->loadProfile($acct_id);
    		}
    	}

    	return $ret;
    }

    public function loadProfile($acct_id = false) {
    	$this->setProfile(CoachProfile::load($this->getId(), $acct_id));
    }
    
    public function delete() {
        $p = $this->getProfile();
        if(is_a($p, "CoachProfile")) {
            $p->setCoachId($this->getId());
            $p->delete();
        }
        return parent::delete();
    }
    
}

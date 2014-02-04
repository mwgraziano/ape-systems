<?php

class CoachUser extends HashModel {
	protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "coach_users";
        $this->row_identifier = array("coach_id","user_id");
    }
}
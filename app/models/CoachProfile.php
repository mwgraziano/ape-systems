<?php

class CoachProfile extends KVModel {
    
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "coach_profile";
        $this->row_identifier = "coach_id";
    }
    
}

<?php

class TeamCoach extends HashModel {
        
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "team_coaches";
        $this->row_identifier = array('team_id','coach_id');
    }
}

<?php

class TeamAthlete extends HashModel {
    
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "team_athletes";
        $this->row_identifier = array("team_id","athlete_id");
    }
    
}

<?php

class Unit extends Model {
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "units";
        $this->row_identifier = "id";
    }
}

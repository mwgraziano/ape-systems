<?php

class Sport extends Model {
	protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "sports";
        $this->row_identifier = "id";
    }

}
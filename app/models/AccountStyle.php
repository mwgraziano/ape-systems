<?php

class AccountStyle extends Model {
	protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "account_style";
        $this->row_identifier = "account_id";
    }
}
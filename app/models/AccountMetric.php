<?php

class AccountMetric extends Model {
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "account_metrics";
        $this->row_identifier = "id";
    }
}

<?php

class AccountMetricCategory extends Model {
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "account_metric_categories";
        $this->row_identifier = "id";
    }
    
    
}

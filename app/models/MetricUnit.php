<?php

class MetricUnit extends HashModel {
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "metric_units";
        $this->row_identifier = array("metric_id", "unit_id");
    }
}

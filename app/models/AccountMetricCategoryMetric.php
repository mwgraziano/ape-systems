<?php

class AccountMetricCategoryMetric extends HashModel {
    
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "account_metric_categories_metrics";
        $this->row_identifier = array('account_id','account_metric_category_id','metric_category_id','account_metric_id','metric_id');
    }
    
    public static function loadByAccountMetric($account_id, $account_metric_id = 0, $metric_id = 0) {
        
        $sql = sprintf("SELECT * FROM account_metric_categories_metrics_view WHERE account_id = %d AND account_metric_id = %d and metric_id = %d", $account_id, $account_metric_id, $metric_id);
        
        $data = DB::connect()->getFirst($sql);
        
        if(!empty($data)) {
            $c = new self();
            $c->addData($data);
            
            return $c;
        }
        
        return false;
        
    }
    
    public function updateByMetricId() {
        
        $this->verifyRowIdentifiers();
        
        $sql = "UPDATE ". $this->table ." SET ";
        
        foreach($this->data as $key=>$val)
        {
            if(substr($key,0,1) == '_') continue;
            if($val === false || $val == 'NULL')
                $save_parts[] = "`$key` = NULL";
            else
                $save_parts[] = "`$key` = '$val'";
        }
        
        $sql .= implode(',',$save_parts);
        
        $sql .= " WHERE account_id = ". $this->getAccountId() ." AND account_metric_id = ". $this->getAccountMetricId() ." AND metric_id = ". $this->getMetricId();
        
        $db = DB::connect();
        $db->update($sql);
        
        if(!$db->errno)
        {
            return true;
        }
        else
        {
            throw new Exception(get_class($this)."::updateByMetricId() - Cannot save record: ". $db->error);
            return false;
        }
        
    }
    
    public static function findByInfo($info) {
        
        $sql = self::getView();
        
        foreach((array)$info as $key=>$val) {
            $where_parts[] = "sq.`". $key ."` = '". $val ."'";
        }
        
        if(empty($where_parts)) $where_parts = array("1");
        
        $rsql = sprintf("SELECT * FROM (%s) sq WHERE %s GROUP BY account_id, account_metric_id, metric_id", $sql, implode(" AND ", $where_parts));
        
        return DB::connect()->getAll($rsql);
    }
    
    public static function getView() {
        return "select 
        `account_metric_categories_metrics`.`account_id` AS `account_id`,
        `account_metric_categories_metrics`.`metric_category_id` AS `metric_category_id`,
        `account_metric_categories_metrics`.`account_metric_category_id` AS `account_metric_category_id`,
        `account_metric_categories_metrics`.`metric_id` AS `metric_id`,
        `account_metric_categories_metrics`.`account_metric_id` AS `account_metric_id`,
        `account_metric_categories_metrics`.`unit_id` AS `unit_id`
    from
        (`account_metric_categories_metrics`
        join `accounts` `a` ON ((`a`.`id` = `account_metric_categories_metrics`.`account_id`)))
    where
        (`a`.`active` = 1) 
    union all select 
        `a`.`id` AS `account_id`,
        `m`.`metric_category_id` AS `metric_category_id`,
        t.account_metric_category_id AS `account_metric_category_id`,
        `m`.`metric_id` AS `metric_id`,
        0 AS `account_metric_id`,
        `m`.`unit_id` AS `unit_id`
    from
        (`metric_categories_metrics` `m`
        join `accounts` `a`)
JOIN (
 select 
        `a`.`id` AS `account_id`,
        ifnull(`amc`.`id`, 0) AS `account_metric_category_id`,
        ifnull(`amc`.`metric_category_id`, `mc`.`id`) AS `metric_category_id`,
        ifnull(`amc`.`name`, `mc`.`name`) AS `name`
    from
        ((`metric_categories` `mc`
        join `accounts` `a`)
        left join `account_metric_categories` `amc` ON (((`amc`.`metric_category_id` = `mc`.`id`)
            and (`amc`.`active` = 1)
            and (`amc`.`account_id` = `a`.`id`)
            and (`a`.`active` = 1)))) 
    union select 
        `a`.`id` AS `account_id`,
        `amc`.`id` AS `account_metric_category_id`,
        ifnull(`amc`.`metric_category_id`, 0) AS `metric_category_id`,
        `amc`.`name` AS `name`
    from
        (`account_metric_categories` `amc`
        join `accounts` `a` ON ((`a`.`id` = `amc`.`account_id`)))
    where
        ((`a`.`active` = 1)
            and (`amc`.`active` = 1))
) as t ON t.account_id = a.id
    where
        (`a`.`active` = 1)";
    }
    
}

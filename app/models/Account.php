<?php

class Account extends Model {
    
    protected $_version_ = 1.0;
    
    const DEFAULT_IMAGE = "footer_logo.gif";
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "accounts";
        $this->row_identifier = "id";
    }
    
    public function getLogo() {
        
        $img = $this->getImage();
        if(empty($img)) return IMG_URL . self::DEFAULT_IMAGE;
        else return $img;
        
    }

    public function getMascot() {
        return "Apes";
    }
    
    public function getStyle() {
        $style = AccountStyle::load($this->getId());
        if($style) return json_decode($style->style,true);
        else return array();
    }

    public function getCoaches() {

        $coaches = Coach::findId(array("account_id"=>$this->getId(),"active"=>1));

        if(empty($coaches)) return array();
        else {
            $ret = array();
            foreach($coaches as $coach) {
                $c = Coach::load($coach['id'], $this->getId());
                if($c) $ret[] = $c;
            }
            return $ret;
        }

    }
    
    public function getTeamsToCreate() {
        $sql = sprintf("SELECT s.id, s.name, s.gender FROM (select s.id, s.name, g.gender from sports s, genders g) s
                        LEFT JOIN teams t ON t.sport_id = s.id AND t.gender = s.gender AND t.account_id = %d
                        WHERE t.id IS NULL
                        ORDER BY s.name ASC, s.gender DESC", $this->getId());
                
        $db = DB::connect();
        $sports = $db->getAll($sql);
        
        $ret = array();
        
        foreach($sports as $sport) {
            $ret[$sport['name']]['genders'][] = $sport['gender'];
            $ret[$sport['name']]['id'] = $sport['id'];
        }
        
        unset($sports);
        
        return $ret;
    }
    
    /*
     * Metrics Functions
     */
    
    public function getMetrics() {
        
        $c = Cache::init();
        
        $k = $this->generateCacheKey("metrics");
        
        if(ENV != 'dev') $data = $c->get($k);
        
        if(empty($data)) {
            $sql = sprintf("SELECT * FROM account_metrics_view
                            WHERE account_id = %d
                            ORDER BY name ASC", $this->getId());
                            
            $data = DB::connect()->getAll($sql);
            
            $c->set($k, $data);
        }
        
        return $data;
    }
    
    
    
    public function getMetricCategories() {
        
        $c = Cache::init();
        
        $k = $this->generateCacheKey("metric_categories");
        
        if(ENV != 'dev') $data = $c->get($k);
        
        if(empty($data)) {
            $sql = sprintf("SELECT * FROM account_metric_categories_view
                            WHERE account_id = %d
                            ORDER BY name ASC", $this->getId());
                            
            $data = DB::connect()->getAll($sql);
            
            $c->set($k, $data);
        }
        
        return $data;
    }
    
    public function getMetricCategoryMetrics($account_metric_category = false, $metric_category = false, $data_only = false) {
        if(empty($account_metric_category) && empty($metric_category)) return false;
        
        $c = Cache::init();
        
        $k = $this->generateCacheKey("metric_categories_metrics[". $account_metric_category .",". $metric_category ."]");
        
        if(ENV != 'dev') $data = $c->get($k);
        
        if(empty($account_metric_category)) $account_metric_category = "0";
        // else $account_metric_category = "= ". $account_metric_category;
//         
        if(empty($metric_category)) $metric_category = "0";
        // else $metric_category = "= ". $metric_category;
        
        if(empty($data)) {
            
            $sql = sprintf("SELECT * FROM (
                            SELECT * FROM account_metric_categories_metrics_view
                            GROUP BY account_id, account_metric_id, metric_id) t
                            WHERE metric_category_id = %s 
                             AND account_metric_category_id = %s
                             AND account_id = %d", (string)$metric_category, (string)$account_metric_category, $this->getId());

            $res = DB::connect()->getAll($sql);
            
            $data = array();
            
            foreach($res as $d){
                if(!empty($d['account_metric_id'])) {
                    $m = AccountMetric::load($d['account_metric_id']);
                    $m->setUnit(Unit::load($d['unit_id']));
                    $data[] = $m;
                }
                else {
                    $m = Metric::load($d['metric_id']);
                    $m->setUnit(Unit::load($d['unit_id']));
                    $data[] = $m;
                }
            }
            
            $c->set($k, $data);
        }
        
        if($data_only) {
            $ret = array();
            foreach($data as $dt) {
                if(is_object($dt)) {
                    $u = $dt->getUnit()->getData();
                    $d = $dt->getData();
                    $d['unit_id'] = $u['id'];
                    $d['unit_name'] = $u['name'];
                    $d['unit_label'] = $u['label'];
                    unset($d['unit']);
                    $ret[] = $d;
                }
            }
            
            return $ret;
        }
        
        return $data;
    }
    
    
    public function getMetricCategoryById($account_metric_category = false, $metric_category = false) {
        
        if(empty($account_metric_category) && empty($metric_category)) return false;
        
        if(empty($account_metric_category)) $account_metric_category = "0";
        //else $account_metric_category = "= ". $account_metric_category;
        
        if(empty($metric_category)) $metric_category = "0";
        //else $metric_category = "= ". $metric_category;
        
        $sql = sprintf("SELECT * FROM account_metric_categories_view
                        WHERE account_id = %d
                         AND account_metric_category_id = %s
                         AND metric_category_id = %s
                        ORDER BY name ASC", $this->getId(), $account_metric_category, $metric_category);
                        
        return DB::connect()->getAll($sql);
    }
    
    public function getMetricById($account_metric = false, $metric = false) {
        
        if(empty($account_metric) && empty($metric)) return false;
        
        if(empty($account_metric)) $account_metric = "0";
        
        if(empty($metric)) $metric = "0";
        
        $sql = sprintf("SELECT * FROM 
                        (SELECT * FROM account_metric_categories_metrics_view WHERE account_id = %d GROUP BY account_metric_id, metric_id) amc
                        JOIN account_metrics_view amv ON (amv.account_id = amc.account_id
                         AND amc.account_metric_id = amv.account_metric_id
                         AND amc.metric_id = amv.metric_id)
                        WHERE amc.account_id = %d
                         AND amc.account_metric_id = %s
                         AND amc.metric_id = %s
                        ORDER BY name ASC", $this->getId(), $this->getId(), $account_metric, $metric);
                        
        return array_shift(DB::connect()->getAll($sql));
    }
    
    public function loadMetrics($metrics) {
        $mets = array();
        foreach($metrics as $metric) {
            $mets[] = $this->getMetricById($metric['account_metric_id'], $metric['metric_id']);
        }
        
        return $mets;
    }
}

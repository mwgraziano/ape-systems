<?php

class Team extends Model {
    
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "teams";
        $this->row_identifier = "id";
    }

    public function save($id = null) {
    	$name = $this->getName();
    	
    	if(empty($name)) {
    		$s = Sport::load($this->getSportId());
    		if(!$s) return parent::save($id);

    		switch(strtoupper($this->getGender()))
    		{
    			case 'M':
    				$name = "Men\'s ";
    				break;
				case 'F':
    				$name = "Women\'s ";
    				break;
				case 'I':
    				$name = "Intramural ";
    				break;
    		}

    		$this->setName($name . $s->getName());	
    	}

    	return parent::save($id);
    }
    
    
    public function getAthletes() {
        
        $athlete_ids = TeamAthlete::findAthleteId(array("team_id"=>$this->getId()));
        
        $aids = array();
        
        foreach($athlete_ids as $aid) {
            $aids[] = Athlete::load($aid['athlete_id'], $this->getAccountId());
        }
        
        unset($athele_ids);
        
        return $aids;
    }
    
    public function getAvailableMetrics($account_metric_category = false, $metric_category = false) {
        
        $available = TeamMetric::findMetricsNotAssocWithTeam($this->id);
        
        if(empty($available)) return array();
        
        $account = Account::load($this->getAccountId());
        
        $all = $account->getMetricCategoryMetrics($account_metric_category, $metric_category);
        
        $ret = array();
        
        foreach($all as $m) {
            foreach($available as $av) {
                if((is_a($m, 'AccountMetric') && $av['account_metric_id'] == $m->getId() && $av['metric_id'] == $m->getMetricId()) ||
                   (is_a($m, 'Metric') && $av['metric_id'] == $m->getId() && $av['account_metric_id'] == 0)) {
                    $ret[] = $av;
                    continue;
                }
            }
        }
        
        return $ret;
    }
    
    public function getMetrics() {
        $metrics = sprintf("SELECT tm.metric_id, tm.account_metric_id, m.name FROM team_metrics tm
                            JOIN account_metrics_view m ON m.metric_id = tm.metric_id AND m.account_metric_id = tm.account_metric_id AND m.account_id = %d
                            WHERE tm.team_id = %d
                            ORDER BY m.name ASC", $this->getAccountId(), $this->getId());
        return DB::connect()->getAll($metrics);
    }
    
    public function getKeyMetrics() {
        $metrics = sprintf("SELECT tm.account_metric_id, tm.metric_id, tm.key_metric, amc.name FROM team_metrics tm
                            JOIN account_metrics_view amc ON amc.account_metric_id = tm.account_metric_id AND amc.metric_id = tm.metric_id AND amc.account_id = %d
                            WHERE team_id = %d AND key_metric IS NOT NULL
                            ORDER BY key_metric ASC", $this->getAccountId(), $this->getId());
        return DB::connect()->getAll($metrics);
    }
    
    public function updateKeyMetrics($m1, $m2, $m3, $m4) {
        
        $db = DB::connect()->transact();
        
        try {
            $db->query("UPDATE team_metrics SET key_metric = NULL WHERE team_id = ". $this->getId());
            
            if($m1) $db->update("UPDATE team_metrics SET key_metric = 0 WHERE team_id = ". $this->getId() ." AND account_metric_id = ". $m1[0] ." AND metric_id = ". $m1[1] ." LIMIT 1");
            if($m2) $db->update("UPDATE team_metrics SET key_metric = 1 WHERE team_id = ". $this->getId() ." AND account_metric_id = ". $m2[0] ." AND metric_id = ". $m2[1] ." LIMIT 1");
            if($m3) $db->update("UPDATE team_metrics SET key_metric = 2 WHERE team_id = ". $this->getId() ." AND account_metric_id = ". $m3[0] ." AND metric_id = ". $m3[1] ." LIMIT 1");
            if($m4) $db->update("UPDATE team_metrics SET key_metric = 3 WHERE team_id = ". $this->getId() ." AND account_metric_id = ". $m4[0] ." AND metric_id = ". $m4[1] ." LIMIT 1");
            
            $db->commit();
            return true;
        }
        catch (Exception $e) {
            $db->rollback();
            
            return false;
        }
        
        return true;
    }
}

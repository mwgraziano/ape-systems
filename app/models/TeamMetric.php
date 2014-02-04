<?php


class TeamMetric extends HashModel {
        
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "team_metrics";
        $this->row_identifier = array('team_id','account_metric_id','metric_id');
    }
    
    public static function load($id = false) {
        if(empty($id)) return false;
        
        if(empty($id[1])) $id[1] = '0';
        
        if(empty($id[2])) $id[2] = '0';
        
        
        $sql = sprintf("SELECT * FROM team_metrics WHERE team_id = %d AND account_metric_id = %s AND metric_id = %s", $id[0], $id[1], $id[2]);
        
        $data = DB::connect()->getFirst($sql);
            
        if(empty($data)) return false;
        
        $c = new self();
        
        $c->addData($data);
        
        return $c;
    }
    
    public function delete() {
        
        $amid = $this->getAccountMetricId();
        $mid = $this->getMetricId();
        
        if(empty($amid)) $amid = "0";
        
        if(empty($mid)) $mid = "0";
        
        $team_id = $this->getTeamId();
        
        if(empty($team_id)) return false;
        
        $sql = sprintf("DELETE FROM team_metrics WHERE team_id = %d AND account_metric_id = %s AND metric_id = %s", $team_id, $amid, $mid);
        
        return DB::connect()->update($sql);
        
    }
    
    public function save() {
        
        $amid = $this->getAccountMetricId();
        $mid = $this->getMetricId();
        
        if(empty($amid)) $amid = "0";
        
        if(empty($mid)) $mid = "0";
        
        $team_id = $this->getTeamId();
        
        if(empty($team_id)) return false;
        
        $sql = sprintf("INSERT IGNORE INTO team_metrics (team_id, account_metric_id, metric_id) VALUES (%d,%s,%s)", $team_id, $amid, $mid);
        
        return DB::connect()->update($sql);
    }
    
    public static function findMetricsNotAssocWithTeam($team_id) {
        
        if(!$t = Team::load($team_id)) return false;
        
        $account_id = $t->getAccountId();
        
        unset($t);
        
        $sql = sprintf("SELECT m.account_metric_id, m.metric_id, m.name FROM account_metrics_view m
                        LEFT JOIN team_metrics tm 
                         ON tm.account_metric_id = m.account_metric_id
                         AND tm.metric_id = m.metric_id
                         AND tm.team_id = %d
                        WHERE m.account_id = %d AND tm.team_id IS NULL", $team_id, $account_id);
        
        return DB::connect()->getAll($sql);
    }
    
    public static function updateAccountMetricId($account_id, $metric_id = 0, $account_metric_id = 0) {
        if(empty($account_id)) return false;
        if(empty($metric_id)) return false;
        $sql = sprintf("UPDATE team_metrics tm JOIN teams t ON t.id = tm.team_id AND t.account_id = %d AND t.active = 1
                        SET tm.account_metric_id = %d WHERE metric_id = %d", $account_id, $account_metric_id, $metric_id);
        
        return DB::connect()->update($sql);
    }
    
    public static function copyTeamMetrics($from_team_id, $to_team_id) {
        $sql = sprintf("INSERT IGNORE INTO team_metrics (team_id, metric_id, account_metric_id)
                        SELECT %d, metric_id, account_metric_id FROM team_metrics WHERE team_id = %d", $to_team_id, $from_team_id);

        return DB::connect()->update($sql);
    }
}

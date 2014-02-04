<?php

class Measurement extends Model {
    
    protected $_version_ = 1.0;
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "measurements";
        $this->row_identifier = "id";
    }
    
    
    public static function getMostRecentTeamMetricMeasurements($team_id, $metrics) {
        
        $metrics_sql = self::buildMetricSQL($metrics, 't');
        
        $sql = sprintf("SELECT t.* FROM team_recent_measurements t
                        WHERE t.team_id = %d AND %s", $team_id, $metrics_sql);
        
        return DB::connect()->getAll($sql);
        
    }
    
    public static function getMostRecentTeamMeasurements($team_id) {
        
        $sql = sprintf("SELECT t.* FROM team_recent_measurements t
                        WHERE t.team_id = %d", $team_id);
        
        return DB::connect()->getAll($sql);
    }
    
    public static function getMostRecentAthleteMetricMeasurements($athlete_id, $metrics) {
        
        $metrics_sql = self::buildMetricSQL($metrics, "m");
        
        $sql = sprintf("SELECT m.*, t.max_data_value, t.min_data_value, t.avg_data_value, t.sum_data_value FROM recent_measurements m
                        JOIN team_athletes ta ON ta.athlete_id = m.athlete_id
                        JOIN team_recent_measurements t ON t.team_id = ta.team_id AND t.metric_id = m.metric_id AND t.account_metric_id = m.account_metric_id AND t.unit_id = m.unit_id
                        WHERE m.athlete_id = %d AND %s", $athlete_id, $metrics_sql);
                        
        return DB::connect()->getAll($sql);
        
    }
    
    public static function getMostRecentAthleteMeasurements($athlete_id) {
        
        $sql = sprintf("SELECT m.*, t.max_data_value, t.min_data_value, t.avg_data_value, t.sum_data_value FROM recent_measurements m
                        JOIN team_athletes ta ON ta.athlete_id = m.athlete_id
                        JOIN team_recent_measurements t ON t.team_id = ta.team_id AND t.metric_id = m.metric_id AND t.account_metric_id = m.account_metric_id AND t.unit_id = m.unit_id
                        WHERE m.athlete_id = %d", $athlete_id);
        
        return DB::connect()->getAll($sql);
    }
    
    private static function buildMetricSQL($metrics, $table = "") {
        
        $mets = array();
        
        if(empty($metrics)) return "1";
        
        if(!empty($table)) $table = $table.".";
        
        foreach($metrics as $m) {
            $mets[] = '('. $m['account_metric_id'].','.$m['metric_id'] .')';
        }
        
        return "({$table}account_metric_id, {$table}metric_id) IN (". implode(",", $mets) .")";
        
    }
    
    
    public static function getAtheleteMeasurementsForDays($athlete_id, $days = 30, $trials = 0) {
                
        if(is_array($trials)) $trials = "AND trial in (". implode(",", $trials) .")";
        else $trials = "AND trial = ". $trials;
        
        $sql = "SELECT * FROM measurements WHERE athlete_id = %d %s ORDER BY data_date DESC LIMIT %d";
        
        return DB::connect()->getAll(sprintf($sql, $athlete_id, $trials, $days));
    }
    
    public static function getAtheleteMetricMeasurementsForDays($athlete_id, $metrics, $days = 30) {
            
        $metrics_sql = self::buildMetricSQL($metrics);
        
        $metrics_sql = "AND ". $metrics_sql;
        
        $sql = "SELECT * FROM measurements where athlete_id = %d and trial = 0 %s ORDER BY data_date DESC LIMIT %d";
        $sql = sprintf($sql, $athlete_id, $metrics_sql, $days);
        
        return DB::connect()->getAll($sql);
    }
    
    public static function updateByOriginalDate($athlete_id, $account_metric_id, $metric_id, $unit_id, $value, $original_date) {
        
        
        $sql = "UPDATE measurements SET data_value = %f WHERE athlete_id = %d AND account_metric_id = %d AND metric_id = %d AND unit_id = %d AND data_date = '%s'";
        
        $sql = sprintf($sql, $value, $athlete_id, $account_metric_id, $metric_id, $unit_id, $original_date);
        
        return DB::connect()->update($sql);
        
    }
    
    public static function track($athlete_id, $account_metric_id, $metric_id, $unit_id, $value, $original_date) {
        
        $sql = "INSERT INTO measurements SET data_value = %f, athlete_id = %d, account_metric_id = %d, metric_id = %d, unit_id = %d, data_date = '%s'";
        
        $sql = sprintf($sql, $value, $athlete_id, $account_metric_id, $metric_id, $unit_id, $original_date);
        
        return DB::connect()->insert($sql);
    }
    
    public static function deleteMeasurement($athlete_id, $account_metric_id, $metric_id, $unit_id, $original_date) {
        
        $sql = "DELETE FROM measurements WHERE athlete_id = %d AND account_metric_id = %d AND metric_id = %d AND unit_id = %d AND data_date = '%s'";
        
        $sql = sprintf($sql, $athlete_id, $account_metric_id, $metric_id, $unit_id, $original_date);
        
        return DB::connect()->update($sql);
    }
}

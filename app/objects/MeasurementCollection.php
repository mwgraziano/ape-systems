<?php

class MeasurementCollection {
    
    public $measurements;
    public $units;
    private $index;
    
    public function __construct($measurements = false) {
        
        if(!empty($measurements))
            $this->indexMeasurements($measurements);
        
    }
    
    private function indexMeasurements($ms) {
        
        $this->measurements = array_values($ms);
        
        if(empty($ms)) return;
        
        $ret = array();
        
        $this->units = array();
        
        foreach($this->measurements as $id=>$m) {
            
            $this->measurements[$id]['pretty_data_value'] = round($m['data_value'],1);
            $this->measurements[$id]['pretty_data_date'] = App::init()->userTime(date_create_from_format('Y-m-d H:i:s',$m['data_date']))->format('Y-m-d H:i:s');
            
            if(!isset($this->units[$m['unit_id']])) {
                $this->units[$m['unit_id']] = Unit::load($m['unit_id']);
            }
            
            if(!$this->units[$m['unit_id']]) {
                error_log("MeasurementCollection::indexMeasurements - error loading unit ". $m['unit_id']);
                continue;
            }
            
            $this->measurements[$id]['label'] = $this->units[$m['unit_id']]->getLabel();
            $this->measurements[$id]['long_label'] = $this->units[$m['unit_id']]->getName();
            
            $ret['athletes'][$m['athlete_id']][] = $id;
            $ret['account_metric_ids'][$m['account_metric_id']][] = $id;
            $ret['metric_ids'][$m['metric_id']][] = $id;
            $ret['metrics'][$m['account_metric_id'] .','. $m['metric_id']][] = $id;
            
        }
        
        $this->index = $ret;
    }
    
    public function getMeasurementsByMetricIds($account_metric_id = 0, $metric_id = 0) {
        
        $ids = array_merge((array)$this->index['metrics'][$account_metric_id .','. $metric_id], (array)$this->index['metrics']['0,'. $metric_id]);
        
        return array_values(array_intersect_key($this->measurements, array_fill_keys($ids,0)));
        
    }
    
    public function getMeasurementsByMetricId($metric_id = 0) {
        
        return array_values(array_intersect_key($this->measurements, array_fill_keys($this->index['metric_ids'][$metric_id],0)));
        
    }
    
    public function getMeasurementsByAccountMetricId($account_metric_id = 0) {
        
        return array_values(array_intersect_key($this->measurements, array_fill_keys($this->index['account_metric_ids'][$account_metric_id],0)));
        
    }

    public function getMeasurementsByAthleteId($athlete_id = 0) {
        
        return array_values(array_intersect_key($this->measurements, array_fill_keys($this->index['athletes'][$athlete_id],0)));
        
    }
    
}

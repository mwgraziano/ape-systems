<?php

class Athlete extends EncryptedModel {
    
    protected $_version_ = 1.0;
    
    protected $my_params = array("id","account_id","pin");
    
    public function __construct()
    {
        parent::__construct();
        $this->table = "athletes";
        $this->row_identifier = "id";
    }
    
    public static function load($id, $acct_id = false) {
        $ret = parent::load($id);

        if(!empty($ret)) {
            if(is_array($ret)) {
                foreach($ret as &$c) {
                    $c->loadProfile($acct_id);
                }
            }
            else if(is_a($ret, "Athlete")) {
                $ret->loadProfile($acct_id);
            }
        }

        return $ret;
    }

    public function loadProfile($acct_id = false) {
        $this->setProfile(AthleteProfile::load($this->getId(), $acct_id));
        
        $photo = $this->getPhoto();
        if(empty($photo)) $this->profile->setPhoto($this->getDefaultPhoto());
        
    }
    
    public function delete() {
        $p = $this->getProfile();
        if(is_a($p, "CoachProfile")) {
            $p->setCoachId($this->getId());
            $p->delete();
        }
        return parent::delete();
    }
    
    public function search($q = false) {
        if(empty($q)) return false;
        else {
            
            //Do the search
            return AthleteProfile::search(App::init()->account->id, $q);
            
        }
    }
    
    private function getDefaultPhoto() {
        return '/img/no-img-profile.png';
    }
    
    public function getTeam() {
        $teams = TeamAthlete::findTeamId(array("athlete_id"=>$this->getId()));
        $team_id = array_shift($teams);
        
        if(!empty($team_id)) {
            return Team::load($team_id['team_id']);
        }
        
        return false;
    }
    
    public function getMostRecentMetricMeasurements($metrics) {
        
        return new MeasurementCollection(Measurement::getMostRecentAthleteMetricMeasurements($this->getId(), $metrics));
        
    }
    
    public function getMostRecentMeasurements() {
        
        return new MeasurementCollection(Measurement::getMostRecentAthleteMeasurements($this->getId()));
        
    }
    
    public function getMeasurements($past = 30) {
        
        return new MeasurementCollection(Measurement::getAtheleteMeasurementsForDays($this->getId(), $past));
        
    }
    
    public function getMetricMeasurements($metrics, $past = 30) {
        return new MeasurementCollection(Measurement::getAtheleteMetricMeasurementsForDays($this->getId(), $metrics, $past));
    }
    
    public function updateMeasurementByOriginalDate($account_metric_id, $metric_id, $unit_id, $value, $original_date) {
        
        return Measurement::updateByOriginalDate($this->getId(), $account_metric_id, $metric_id, $unit_id, $value, $original_date);
        
    }
    
    public function addMeasurement($account_metric_id, $metric_id, $unit_id, $value, $original_date) {
        
        return Measurement::track($this->getId(), $account_metric_id, $metric_id, $unit_id, $value, $original_date);
        
    }
    
    public function deleteMeasurement($account_metric_id, $metric_id, $unit_id, $original_date) {
        return Measurement::deleteMeasurement($this->getId(), $account_metric_id, $metric_id, $unit_id, $original_date);
    }
}

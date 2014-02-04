<?php

class AthleteController extends Controller {
    
    const AUTH_LEVEL_REQUIRED = AUTH_LEVEL_LOGGEDIN;
    
    public function get_search() {
        
        $app = App::init();
        
        $account = $app->account;
        
        $data = $app->request->data;
        
        $athletes = Athlete::search($data['q']);
        
        foreach($athletes as $id=>$ath) {
            if(!empty($ath['photo'])) {
                $athletes[$id]['thumb'] = str_replace('.jpg','_sm.jpg',$ath['photo']);
            }
        }
        
        $this->respondJSON($athletes);
        
    }
    
    public function get_new() {
        $this->respondView("athlete_form");
    }
    
    public function get_edit($athlete_id = false) {
        $this->respondView("athlete_form", func_get_args());
    }
    
    public function get_index($athlete_id = false) {
        
        if(!is_numeric($athlete_id) || $athlete_id <= 0) $this->respondJSON(array('error'=>"Invalid Athlete"));
        
        $app = App::init();
        
        $a = Athlete::load($athlete_id, $app->account->id);
        if($a && $a->account_id == $app->account->id) {
            $data = array("id"=>$a->getId());
            $data = array_merge($data, $a->profile->getData());
            $data['pretty_height'] = $this->prettyHeight($data['height']);
            
            $team = $a->getTeam();
            $key_metrics = $team->getKeyMetrics();
            $key_metric_values = $a->getMostRecentMetricMeasurements($key_metrics);
            
            foreach($key_metrics as $metric) {
                $km = array();
                $m = $key_metric_values->getMeasurementsByMetricIds($metric['account_metric_id'], $metric['metric_id']);
                if(!empty($m)) {
                    $km = $m[0];
                }
                else $km['pretty_data_value'] = 'N/A';
                
                $km['metric_name'] = $metric['name'];
                
                $data['key_metrics'][] = $km;
                
                unset($km);
            }
            
            $this->respondJSON($data);
        } 
        
        $this->respondJSON(array('error'=>'You may not view this athlete'));
        
    }
    
    public function get_profile($athlete_id = false) {
        $app = App::init();
        
        if(!is_numeric($athlete_id) || $athlete_id <= 0) $app->hredirect("#roster");
        
        $a = Athlete::load($athlete_id, $app->account->id);
        if($a && $a->account_id == $app->account->id) {
            $data = array("id"=>$a->getId());
            $data = array_merge($data, $a->profile->getData());
            $data['pretty_height'] = $this->prettyHeight($data['height']);
            
            $this->respondView("athlete_profile",$data);
        }
        else {
            $app->hredirect("#roster");
        }
    }
    
    public function post_index($athlete_id = false) {
        
        $app = App::init();

        $data = $app->request->data;
        
        //Handle Image
        $file_params = $_FILES["ath-photo"];
            
        if ($file_params['size']/1024 > (5*1024))
        {
            $this->respondJSON(array("error"=>"Your image is too large. Try to keep it under 5MB."));
        }

        if(is_numeric($athlete_id) && $athlete_id > 0) {
            if(empty($data['req-key']) || !Session::checkNonce($data['req-key'], "Athlete",$acct_id)) $this->respondJSON(array("error"=>"You may not edit this athlete","sigs"=>$_SESSION['nonce']));
            
            $mod_athlete = Athlete::load($athlete_id, $app->account->id);
            if(empty($mod_athlete)) $this->respondJSON(array("error"=>"Cannot modify this user right now."));
        }
        
        $team_id = Session::getTeamId();
        if(empty($team_id)) $this->respondJSON(array("error"=>"It seems that there is no team seleted. Please select a team first."));
        
        $db = DB::connect()->transact();
        
        if(!$mod_athlete) {
            $a = new Athlete();
            $a->setAccountId($app->account->id);
            $athlete_id = $a->save();
        }
        
        if(empty($athlete_id)) {
            $db->rollback();
            $this->respondJSON(array("error"=>"There was an error creating this athlete"));
        }
        
        $a = new AthleteProfile();
        $a->setAthleteId($athlete_id);
        $a->setName($data['ath-name']);
        $a->setHometown($data['ath-hometown']);
        $a->setEmail($data['ath-email']);
        $a->setPosition($data['ath-position']);
        $a->setStart($data['ath-start']);
        $a->setHeight($data['ath-height']);
        $success = $a->save($app->account->id);
        
        if(!$success) {
            $db->rollback();
            $this->respondJSON(array("error"=>"There was an error creating this athlete's profile"));
        }
        
        $ta = new TeamAthlete();
        $ta->setTeamId($team_id);
        $ta->setAthleteId($athlete_id);
        if(!$ta->save()) {
            $db->rollback();
            $this->respondJSON(array("error"=>"There was an error adding the user to the team"));
        }
        
        //Handle Image
        if(empty($file_params)) $this->respondJSON(array("error"=>"Your image didn't upload. Please try again."));
            
        if ($file_params['size']/1024 > (5*1024))
        {
            $db->rollback();
            $this->respondJSON(array("error"=>"Your image is too large. Try to keep it under 5MB."));
        }
        else if($file_params['size'] > 0) {
            try {
                $f = new ImageResize($file_params['tmp_name'], strrchr($file_params['name'], "."));
                
                if(!$f) {
                    $db->rollback();
                    $this->respondJSON(array("error"=>"This is not a supported file type. Please upload a JPG, PNG, or GIF."));
                }
                
                $img_save_name = md5($team_id ."|". $athlete_id) .".jpg";
                $img_save_name_sm = md5($team_id ."|". $athlete_id) ."_sm.jpg";
                
                $f->resizeImage(120,140, "crop");//large image
                $f->saveImage(USER_IMG_PATH . $img_save_name);
                
                $f->resizeImage(30,35, "crop");//large image
                $f->saveImage(USER_IMG_PATH . $img_save_name_sm);
                
                $a->setAthleteId($athlete_id);
                $a->setPhoto(USER_IMG_URL . $img_save_name);
                $success = $a->save($app->account->id);
                
                if(!$success) {
                    $db->rollback();
                    $this->respondJSON(array("error"=>"Failed to save the athlete's image"));
                }
            } catch (Exception $e) {
                $db->rollback();
                $this->respondJSON(array("error"=>"Failed to save the athlete's image (1)"));
            }
        }
        
        $db->commit();
        
        
        if(!empty($data['ath-add-another'])) Session::setDataEntryMode(1);
        else Session::setDataEntryMode(0);
        
        $this->respondJSON($athlete_id);
    }
    
    
    public function prettyHeight($height) {
        if(empty($height)) return "";
        $ft = floor($height/12);
        $in = round(($height % 12),1);
        
        return $ft."'".$in.'"';
    }
    
    
    public function get_measurements($athlete_id = false) {
        
        if(empty($athlete_id) || !is_numeric($athlete_id)) $this->respondJSON(array("error"=>"Invalid athlete id"));
        
        $app = App::init();
        
        $a = Athlete::load($athlete_id, $app->account->id);
        
        if(!$a || $a->getAccountId() != $app->account->id) $this->respondJSON(array("error"=>"Invalid athlete information"));
        
        $met = $app->request->data['metric'];
        
        $metric_category = "";
        if(!empty($met)) {
            list($amid, $mid) = explode(",", $met);
            $metric = array(array("account_metric_id"=>$amid, "metric_id"=>$mid));
            $metrics = $a->getMetricMeasurements($metric);
            
            $met_cate = $app->account->getMetricById($amid, $mid);
            if($met_cate) $metric_category = $met_cate['name'];
        }
        else {
            $metrics = $a->getMeasurements();
        }
        
        $this->respondJSON(array("metrics"=>$metrics->measurements, "category"=>$metric_category));
    }
    
    public function post_measurement($action = false) {
        
        $app = App::init();
        
        $athlete_id = $app->request->data['ath-id'];
        
        if(empty($athlete_id) || !is_numeric($athlete_id)) $this->respondJSON(array("error"=>"Invalid athlete id"));
        
        $a = Athlete::load($athlete_id, $app->account->id);
        
        if(!$a || $a->getAccountId() != $app->account->id) $this->respondJSON(array("error"=>"Invalid athlete information"));
        
        
        if($action == 'delete') {
            return $this->deleteMeasurement($a);
        }
        
        $data = $app->request->data;
        
        $date = $data['ath-new-date'];
        $time = $data['ath-new-time'];
        
        $val = $data['ath-new-value'];
        
        if(trim($val) == "" || !is_numeric($val)) $this->respondJSON(array("error"=>"Only numbers are allowed"));
        
        $orig_date = $data['ath-orig-date'];
        
        list($amid, $mid) = explode(",",$data['ath-metric']);
        
        if(empty($amid) && empty($mid)) $this->respondJSON(array("error"=>"Invalid metric"));
        
        $dta = $app->account->getMetricById($amid, $mid);
        
        if(!empty($dta)) {
            $unit_id = $dta['unit_id'];
        }
        else $this->respondJSON(array("error"=>"Invalid metric category"));
        
        if(!empty($orig_date) && $_new_date = date_create_from_format('Y-m-d H:i:s', $orig_date)) {
            //this is an update
            $_new_date = $app->userTime($_new_date);
            if(!$a->updateMeasurementByOriginalDate($amid, $mid, $unit_id, $val, $orig_date)) {
                $this->respondJSON(array("error"=>"Failed to update metric"));
            }
        }
        else {
            
            if(empty($date) || !date_create_from_format('Y-m-d', $date)) {
                $dt = new DateTime();
                $date = $app->userTime($dt)->format('Y-m-d');
            }
            
            if(empty($time) || !date_create_from_format('H:i:s', $time)) {
                $dt = new DateTime();
                $time = $app->userTime($dt)->format('H:i:s');
            }
            
            $_new_date = date_create_from_format("Y-m-d H:i:s", $date ." ". $time);
            
            if(!$_new_date) $_new_date = new DateTime('now');
            $new_date = $app->serverTime($_new_date)->format('Y-m-d H:i:s');
            
            if(!$a->addMeasurement($amid, $mid, $unit_id, $val, $new_date)) {
                $this->respondJSON(array("error"=>"Failed to save metric"));
            }
        }
        
        $this->respondJSON(array(
            "pretty_data_value"=>round($val,1),
            "data_value"=>$val, 
            "pretty_data_date"=>$_new_date->format("Y-m-d H:i:s"),
            "data_date"=>$new_date
        ));
        
    }
    
    
    public function deleteMeasurement(Athlete $a) {
        
        $app = App::init();
        
        $data = $app->request->data;
        
        $orig_date = $data['ath-orig-date'];
        
        list($amid, $mid) = explode(",",$data['ath-metric']);
        
        if(empty($amid) && empty($mid)) $this->respondJSON(array("error"=>"Invalid metric"));
        
        $dta = $app->account->getMetricById($amid, $mid);
        
        if(!empty($dta)) {
            $unit_id = $dta['unit_id'];
        }
        else $this->respondJSON(array("error"=>"Invalid metric category"));
        
        $orig_date = $data['ath-orig-date'];
        
        $a->deleteMeasurement($amid, $mid, $unit_id, $orig_date);
        
        $this->respondJSON(true);
        
    }
}






<?

class TeamController extends Controller {
    const AUTH_LEVEL_REQUIRED = AUTH_LEVEL_LOGGEDIN;
    
    public function get_index($id = null) {
        $this->respondView("create_team",array("id"=>$id));
    }

    public function post_index($id = null) {

        $app = App::init();
        $data = $app->request->data;

        //Verify access to id


        //Verify posted data


        //Mod Team
        $t = new Team();
        $t->setAccountId($app->account->id);
        $t->setSportId($data['team-sport']);
        $t->setGender($data['team-gender']);
        if(!empty($id)) {
            $t->setId($id);
        }

        $success = $t->save($id);

        $this->respondJSON($success);

    }

    public function get_switch($id = null) {
        if(is_numeric($id)) {
            Session::setTeamId($id);
            App::init()->hredirect('#roster');
        }

        $this->respondView("switch_team");
    }
    
    public function get_availableMetrics($id = false) {
        
        $app = App::init();
        
        $data = $app->request->data;
        
        if(!$t = Team::load($id)) $this->respondJSON(array("error"=>"Unable to locate team"));
        
        if($t->getAccountId() != $app->account->id) $this->respondJSON(array("error"=>"You may not edit this team"));
        
        list($amcid, $mcid) = explode(",", $data['id']);
        
        if(empty($amcid) && empty($mcid)) $this->respondJSON(array("error"=>"Invalid category id"));
        
        $metrics = $t->getAvailableMetrics($amcid, $mcid);
        
        $this->respondJSON($metrics);
    }
    
    public function get_availableMetricsCategories($id = false) {
        
        $app = App::init();
        
        $cats = $app->account->getMetricCategories();
        
        $this->respondJSON($cats);
    }
    
    public function post_addMetric($id = false) {
        if(!is_numeric($id)) $this->respondJSON(array("error"=>"Cannot add a metric to an invalid team"));
        
        $app = App::init();
        
        if((!$t = Team::load($id)) || $t->getAccountId() != $app->account->id) $this->respondJSON(array("error"=>"You are not authorized to modify this team."));
        
        $data = $app->request->data;
        
        list($amid, $mid) = explode(",",$data['metric-id']);
        
        if(empty($amid) && empty($mid)) $this->respondJSON(array("error"=>"Cannot add an invalid metric"));
        
        $m = new TeamMetric();
        $m->setTeamId($id);
        $m->setMetricId($mid);
        $m->setAccountMetricId($amid);
        $success = $m->save();
        
        if($success) {
            
            $amid = $m->getAccountMetricId();
            
            if(empty($amid)) $_m = Metric::load($m->getMetricId());
            else $_m = AccountMetric::load($m->getAccountMetricId());
            
            $this->respondJSON(array_merge($m->getData(), $_m->getData()));
        }
        else {
            $this->respondJSON(array("error"=>"Error saving metric"));
        }
    }
    
    public function post_removeMetric($id = false) {
        if(!is_numeric($id)) $this->respondJSON(array("error"=>"Cannot remove a metric from an invalid team"));
        
        $app = App::init();
        
        if((!$t = Team::load($id)) || $t->getAccountId() != $app->account->id) $this->respondJSON(array("error"=>"You are not authorized to modify this team."));
        
        $data = $app->request->data;
        
        list($amid, $mid) = explode(",",$data['metric-id']);
        
        if(empty($amid) && empty($mid)) $this->respondJSON(array("error"=>"Cannot remove an invalid metric"));
        
        $m = TeamMetric::load(array($id, $amid, $mid));
        
        if(!$m) $this->respondJSON(array("error"=>"Cannot load metric"));
        
        $success = $m->delete();
        
        if($success) {
            $this->respondJSON(true);
        }
        else {
            $this->respondJSON(array("error"=>"Error removing metric"));
        }
    }
    
    public function post_copyMetrics($id = false) {
        if(!is_numeric($id)) $this->respondJSON(array("error"=>"Cannot copy metrics to an invalid team"));
        
        $app = App::init();
        
        if((!$t = Team::load($id)) || $t->getAccountId() != $app->account->id) $this->respondJSON(array("error"=>"You are not authorized to modify this team."));
        
        $data = $app->request->data;
        
        if(!$ct = Team::load($data['copy-team-id'])) {
            $this->respondJSON(array('error'=>'Cannot copy an invalid team\'s metrics'));
        }
        
        $success = TeamMetric::copyTeamMetrics($data['copy-team-id'], $id);
        
        if($success) {
            $this->respondJSON(true);
        }
        else {
            $this->respondJSON(array("error"=>"Error copying metrics"));
        }
    }
    
    public function get_keyMetrics($id = false) {
        if(!is_numeric($id)) $this->respondJSON(array("error"=>"Cannot find metrics for an invalid team"));
        
        $app = App::init();
        
        if((!$t = Team::load($id)) || $t->getAccountId() != $app->account->id) $this->respondJSON(array("error"=>"You are not authorized to modify this team."));
        
        $ret['metrics'] = $t->getMetrics();
        $vals = $t->getKeyMetrics();
        
        for($i=0;$i<4;$i++) {
            foreach($vals as $val) {
                if($val['key_metric'] == $i) {
                    $ret['values'][$i] = $val['account_metric_id'] .','. $val['metric_id'];
                    continue 2;
                }
                else
                    $ret['values'][$i] = "";
            }
        }
        
        $this->respondJSON($ret);
    }
    
    public function post_keyMetrics($id = false) {
        if(!is_numeric($id)) $this->respondJSON(array("error"=>"Cannot find metrics for an invalid team"));
        
        $app = App::init();
        
        if((!$t = Team::load($id)) || $t->getAccountId() != $app->account->id) $this->respondJSON(array("error"=>"You are not authorized to modify this team."));
        
        $data = $app->request->data;
        
        $metric1 = empty($data['key-metric-1']) ? false : explode(",", $data['key-metric-1']);
        $metric2 = empty($data['key-metric-2']) ? false : explode(",", $data['key-metric-2']);
        $metric3 = empty($data['key-metric-3']) ? false : explode(",", $data['key-metric-3']);
        $metric4 = empty($data['key-metric-4']) ? false : explode(",", $data['key-metric-4']);
        
        if($metric1 && count($metric1) == 2) $metrics[0] = $metric1;
        else $metrics[0] = false;
        
        if($metric2 && count($metric2) == 2) $metrics[1] = $metric2;
        else $metrics[1] = false;
        
        if($metric3 && count($metric3) == 2) $metrics[2] = $metric3;
        else $metrics[2] = false;
        
        if($metric4 && count($metric4) == 2) $metrics[3] = $metric4;
        else $metrics[3] = false;
        
        $success = $t->updateKeyMetrics($metrics[0], $metrics[1], $metrics[2], $metrics[3]);
        
        if($success) $this->respondJSON(true);
        else $this->respondJSON(array("error"=>"Error updating key metrics"));
    }
}

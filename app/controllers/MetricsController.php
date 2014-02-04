<?php

class MetricsController extends Controller {
    const AUTH_LEVEL_REQUIRED = AUTH_LEVEL_LOGGEDIN;
    
    public function get_index() {
        $this->respondView("metrics_tracking");
    }
    
    public function get_test() {
        $this->respondView("test");
    }
    
    public function get_manage() {
        $this->respondView("metrics_manage");
    }
    
    public function get_category() {
        
        $app = App::init();
        
        $data = $app->request->data;
        
        if(empty($data['id'])) $this->respondJSON(array("error"=>"Invalid category id (1)"));
        
        $id = explode(",", $data['id']);
        
        if(count($id) != 2) $this->respondJSON(array("error"=>"Invalid category id (2)"));
        
        $account_metric_category_id = $id[0];
        $metric_category_id = $id[1];
        
        $category = $app->account->getMetricCategoryById($account_metric_category_id, $metric_category_id);
        
        if(!empty($category)) {
            $ret['category-id'] = $category[0]["account_metric_category_id"] .",". $category[0]['metric_category_id'];
            $ret['req-key'] = Session::generateNonce("AccountMetricCategory",$ret['category-id']);
            $ret['category-name'] = $category[0]['name'];
            $this->respondJSON($ret);
        }
        
        $this->respondJSON(array("error"=>"Category lookup failed"));
    }

    public function post_category($func = false) {

        if(!empty($func) && $func == 'delete') {
            $this->post_category_delete();
            return;
        }
        
        $app = App::init();
        
        $data = $app->request->data;
        
        $success = false;
        
        if(!empty($data['category-id']) && !Session::checkNonce($data['req-key'], 'AccountMetricCategory', $data['category-id'])) 
            $this->respondJSON(array("error"=>"You may not edit this record."));
        
        if(!empty($data['category-id'])) {
            $id = explode(",", $data['category-id']);
        
            if(count($id) != 2) $this->respondJSON(array("error"=>"Invalid category id (1)"));
        
            $account_metric_category_id = $id[0];
            $metric_category_id = $id[1];
        
            $category = $app->account->getMetricCategoryById($account_metric_category_id, $metric_category_id);
            
            if(empty($category)) $this->respondJSON(array("error"=>"Invalid category id (2)"));
        
            $a = new AccountMetricCategory();
            $a->setAccountId($app->account->id);
            $a->setMetricCategoryId(($metric_category_id ?: '0'));
            $a->setName($data['category-name']);
            
            if(!empty($account_metric_category_id)) {
                $a->setId($account_metric_category_id);
                $success = $a->save($account_metric_category_id);
            }
            else {
                $success = $a->save();
            }
            
        }
        else {
            //This is a new category
            $a = new AccountMetricCategory();
            $a->setAccountId($app->account->id);
            $a->setName($data['category-name']);
            $success = $a->save();
        }
        
        if(!$success) $this->respondJSON(array("error"=>"Failed to save the category"));
        else $this->respondJSON(true);
    }

    public function post_category_delete() {
        $app = App::init();
        
        $data = $app->request->data;
        
        $success = false;
        
        if(!empty($data['category-id']) && !Session::checkNonce($data['req-key'], 'AccountMetricCategory', $data['category-id'])) 
            $this->respondJSON(array("error"=>"You may not edit this category."));
        
        list($amc, $mc) = explode(",", $data['category-id']);
        
        if(empty($amc)) $this->respondJSON(array("error"=>"You may not edit this category."));
        
        $metrics = $app->account->getMetricCategoryMetrics($amc,$mc);
        
        if(!empty($metrics)) $this->respondJSON(array("error"=>"This category has metrics. Remove all metrics before removing."));
        
        $a = AccountMetricCategory::load($amc);
        
        if(!$a) $this->respondJSON(array("error"=>"There was an error finding this category."));
        
        $this->respondJSON($a->delete());
    }
    
    public function get_edit() {
        $app = App::init();
        
        $data = $app->request->data;
        
        $success = false;
        
        if(!empty($data['id'])) {
            
            $id = explode(",", $data['id']);
        
            if(count($id) != 2) $this->respondJSON(array("error"=>"Invalid metric id"));
        
            $account_metric_id = $id[0];
            $metric_id = $id[1];
        
            $metric = $app->account->getMetricById($account_metric_id, $metric_id);
            
            $ret['metric'] = array("metric-name"=>$metric['name'],
                                   "metric-id"=>$metric['account_metric_id'] .','. $metric['metric_id'],
                                   "metric-unit"=>$metric['unit_id'],
                                   "metric-category"=>$metric['account_metric_category_id'] .','. $metric['metric_category_id'],
                                   "req-key"=>Session::generateNonce("AccountMetric", $metric['account_metric_id'] .','. $metric['metric_id']));

            if(!empty($metric['metric_id'])) {
                $unit_ids = MetricUnit::findUnitId(array("metric_id"=>$metric['metric_id']));
                foreach($unit_ids as $unit_id) {
                    if(!$unit = Unit::load($unit_id['unit_id'])) continue;
                    $ret['units'][] = $unit->getData();
                }
            }
            else {
                $unit_ids = Unit::findId(array("active"=>1));
                
                foreach($unit_ids as $unit_id) {
                    if(!$unit = Unit::load($unit_id['id'])) continue;
                    $ret['units'][] = $unit->getData();
                }
            }
            
            $this->respondJSON($ret);

        }
        
        $this->respondJSON(array("error"=>"Error finding the metric."));
    }

    public function get_units() {
        $unit_ids = Unit::findId(array("active"=>1));
        
        foreach($unit_ids as $unit_id) {
            if(!$unit = Unit::load($unit_id['id'])) continue;
            $ret[] = $unit->getData();
        }
        
        $this->respondJSON($ret);
    }
    
    public function post_edit($func = false) {
        if(!empty($func) && $func == 'delete') {
            $this->post_category_delete();
            return;
        }
        
        $app = App::init();
        
        $data = $app->request->data;
        
        $success = false;
        
        if(!empty($data['metric-id']) && !Session::checkNonce($data['req-key'], 'AccountMetric', $data['metric-id'])) 
            $this->respondJSON(array("error"=>"You may not edit this metric."));
        
        list($amci, $mci) = explode(",",$data['metric-category']);
        
        if(empty($amci) && empty($mci)) $this->respondJSON(array("error"=>"Invalid category chosen"));
        
        if(!empty($data['metric-id'])) {
            
            $id = explode(",", $data['metric-id']);
        
            if(count($id) != 2) $this->respondJSON(array("error"=>"Invalid metric id (1)"));
        
            $account_metric_id = $id[0];
            $metric_id = $id[1];
        
            $metric = $app->account->getMetricById($account_metric_id, $metric_id);
            
            if(empty($metric)) $this->respondJSON(array("error"=>"Invalid metric id (2)"));
        
            $db = DB::connect()->transact();
            try {
                $a = new AccountMetric();
                $a->setAccountId($app->account->id);
                $a->setMetricId(($metric_id));
                $a->setName($data['metric-name']);
                
                if(!empty($account_metric_id)) {
                    $a->setId($account_metric_id);
                    $success = $a->save($account_metric_id);
                }
                else {
                    $success = $a->save();
                    $account_metric_id = $success;
                }
                
                if($success) {
                    if($am = AccountMetricCategoryMetric::loadByAccountMetric($app->account->id, $account_metric_id, $metric_id)) {
                     
                        $am->setAccountMetricCategoryId(($amci));
                        $am->setMetricCategoryId(($mci));
                        $am->setUnitId($data['metric-unit']);
                        
                        if(!$am->updateByMetricId()) {
                            $db->rollback();
                            $this->respondJSON(array("error"=>"Failed to update metric category"));
                        }
                    }
                    else {
                        $am = new AccountMetricCategoryMetric();
                        $am->setAccountId($app->account->id);
                        $am->setMetricId(($metric_id));
                        $am->setAccountMetricId(($account_metric_id));
                        $am->setAccountMetricCategoryId(($amci));
                        $am->setMetricCategoryId(($mci));
                        $am->setUnitId($data['metric-unit']);
                        if(!$am->save()) {
                            $db->rollback();
                            $this->respondJSON(array("error"=>"Failed to save metric unit"));
                        }
                        
                        //Update any team metrics associated with this
                        TeamMetric::updateAccountMetricId($app->account->id, $metric_id, $account_metric_id);
                    }
                }
                else {
                    $db->rollback();
                    $this->respondJSON(array("error"=>"Failed to save metric"));
                }
                
                $db->commit();
                
            } catch (Exception $e) {
                $db->rollback();
                error_log("MetricsController::post_edit: ". $e->getMessage());
                $this->respondJSON(array("error"=>"Failed to save metric"));
            }
            
        }
        else {
            
            $db = DB::connect()->transact();
            
            //This is a new category
            $a = new AccountMetric();
            $a->setAccountId($app->account->id);
            $a->setName($data['metric-name']);
            $a->setMetricId("0");
            $success = $a->save();
            
            if($success) {
                $am = new AccountMetricCategoryMetric();
                $am->setAccountId($app->account->id);
                $am->setMetricId("0");
                $am->setAccountMetricId($success);
                $am->setAccountMetricCategoryId(($amci));
                $am->setMetricCategoryId(($mci));
                $am->setUnitId($data['metric-unit']);
                if(!$am->save()) {
                    $db->rollback();
                    $this->respondJSON(array("error"=>"Failed to save metric unit"));
                }
            }
            else {
                $db->rollback();
                $this->respondJSON(array("error"=>"Failed to save metric"));
            }
            
            $db->commit();
        }
        
        if(!$success) $this->respondJSON(array("error"=>"Failed to save the metric"));
        else $this->respondJSON(true);
    }
}

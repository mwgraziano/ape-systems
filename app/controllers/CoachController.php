<?php

class CoachController extends Controller {
	
	const AUTH_LEVEL_REQUIRED = AUTH_LEVEL_LOGGEDIN;

    public function post_index($coach_id = false) {

    	$app = App::init();

    	$data = $app->request->data;

    	if(empty($coach_id)) $coach_id = false;

    	if(is_numeric($coach_id)) {
    		if(empty($data['coach-edit-key']) || !Session::checkNonce($data['coach-edit-key'], "Coach",$coach_id)) $this->respondJSON(array("error"=>"You may not edit this coach"));
    		else {
    		    $c = Coach::load($coach_id);
    			if(empty($c)) $this->respondJSON(array("error"=>"Invalid Coach"));
    		}
    	}
    	
    	if(!empty($data['coach-pass']) && trim($data['coach-pass']) != trim($data['coach-pass-confirm'])) $this->respondJSON(array("error"=>"Passwords do not match"));

        if(empty($data['coach-email'])) $this->respondJSON(array("error"=>"Email cannot be empty"));

    	//Email Check
    	$bypass_email_check = false;

    	if($c){
    		if($c->getEmail() == $data['coach-email']) $bypass_email_check = true;
    	}

    	if(!$bypass_email_check) {
    	    if(User::loadByEmail($data['coach-email'])) $this->respondJSON(array("error"=>"Email is already in use"));
            else if($c) {
                $mod_user = User::loadByEmail($c->getEmail());
            }
    	}

        if(empty($data['coach-name'])) $this->respondJSON(array('error'=>"Name cannot be empty"));

    	if(!is_numeric($coach_id)) {
    		$c = new Coach();
    		$c->setAccountId($app->account->id);
    		$coach_id = $c->save();
    		$coach_created = true;
    	}
        
    	if(empty($coach_id)) $this->respondJSON(array("error"=>"Error creating coach"));
    	
        $db = DB::init()->transact();

    	$cp = new CoachProfile();
    	$cp->setCoachId($coach_id);
    	$cp->setName($data['coach-name']);
    	$cp->setTitle($data['coach-title']);
    	$cp->setEmail($data['coach-email']);
    	if(!$cp->save($app->account->id)) {
    		if($coach_created) {
    			$c->setActive(0);
    			$c->save($coach_id);
                $db->rollback();
    			$this->respondJSON(array('error'=>"Error creating coach profile"));
    		}
            $db->rollback();
    		$this->respondJSON(array("error"=>"Error saving coach profile"));
    	}
        
    	//We got here, so we're good.
    	if($mod_user) {
			if(!$bypass_email_check) {
				if(!$mod_user->changeEmail($data['coach-email'])) {
				    $db->rollback();
                    $this->respondJSON(array('error'=>'Failed to change coach\'s email'));
				}
			}

			if(!empty($data['coach-pass'])) $mod_user->changePassword(trim($data['coach-pass']));

			$success = true;
            
		}
        else if($coach_created) {
            
			//create new user
			$success = User::createUser($data['coach-email'], $data['coach-pass'], $app->account->id);
            
            if($success) {
                //Create Coach User
                $cu = new CoachUser();
                $cu->setCoachId($coach_id);
                $cu->setUserId($success);
                $cu->save();
            }
            else {
                $db->rollback();
            }
            
		}
        else {
            if(!empty($data['coach-pass'])) {
                $pass = trim($data['coach-pass']);
                
                $user_id = array_shift(CoachUser::findUserId(array("coach_id"=>$coach_id)));
                
                if(!empty($user_id)) {
                    if($u = User::load($user_id['user_id'])) {
                        $u->changePassword($pass);
                    }
                    else dump($user_id);
                }
            }
            $success = true;
        }

		if($success) {
		    $db->commit();
		    $this->respondJSON(true);
        }
		else
		{
		    $db->rollback();
		    $this->respondJSON(array('error'=>"Couldn't create user"));
        }
   	}

    public function post_delete($coach_id = false) {
        $app = App::init();

        $data = $app->request->data;

        if(empty($coach_id)) $coach_id = false;

        if(is_numeric($coach_id)) {
            if(empty($data['coach-edit-key']) || !Session::checkNonce($data['coach-edit-key'], "Coach",$coach_id)) $this->respondJSON(array("error"=>"You may not edit this coach"));
            else {
                $c = Coach::load($coach_id);
                if(empty($c)) $this->respondJSON(array("error"=>"Invalid Coach"));
            }
        }
        
        $db = DB::connect()->transact();
        
        try {
        
            $u = User::loadByEmail($c->getEmail());
            
            if($u) {
                $user_id = $u->getId();
                
                if(!$u->delete()) throw new Exception("Error deleting the coach's user.");
                
                if($cu = CoachUser::load(array($coach_id, $user_id))) {
                    if(!$cu->delete()) throw new Exception("Error deleteing the coach user association");
                }
            }
            
            if(!$c->delete()) throw new Exception("Error deleting the coach");
            
            $db->commit();
            
            $this->respondJSON(true);
            
        } catch(Exception $e) {
            $db->rollback();
            $this->respondJSON(array("error"=>$e->getMessage()));
        }
        
    }

}
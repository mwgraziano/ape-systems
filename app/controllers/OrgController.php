<?php

class OrgController extends Controller {
        
    const AUTH_LEVEL_REQUIRED = AUTH_LEVEL_LOGGEDIN;
    
    public function get_switch($acct_id = false) {
        
    	if($acct_id && is_numeric($acct_id)) {
    	    
            $app = App::init();
            
            $uacct_id = $app->user->getAccountId();
            $auth_level = $app->isAuth();
            
            if(($acct_id != $uacct_id) && $auth_level < AUTH_LEVEL_SUPERADMIN) {
                $app->hredirect("#org");
                exit;
            }
            
    	    Session::setAccountId($acct_id);
            Session::setTeamId(0);
        }
        
        $this->respondJSON($acct_id);
    }
    
    public function get_index($acct_id) {
        if($acct_id && is_numeric($acct_id)) {
            
            $app = App::init();
            
            $uacct_id = $app->user->getAccountId();
            $auth_level = $app->isAuth();
            
            if(($acct_id != $uacct_id) && $auth_level < AUTH_LEVEL_SUPERADMIN) {
                $app->redirect("/app#org");
                exit;
            }
        }
        
        $this->respondView("org_admin",func_get_args());
    }

    public function get_edit($acct_id = false) {
        
        $app = App::init();
        
        $uacct_id = $app->user->getAccountId();
        $auth_level = $app->isAuth();
        
    	if(!$acct_id) $acct_id = Session::getAccountId() ?: App::init()->account->id;

        if(($acct_id != $uacct_id) && $auth_level < AUTH_LEVEL_SUPERADMIN) {
            $app->redirect("/app#org");
            exit;
        }

    	Session::setAccountId($acct_id);

    	$this->respondView("org_admin", array($acct_id));
    }

    public function post_index($acct_id = false) {

    	$app = App::init();

    	$data = $app->request->data;

        //Handle Image
        $file_params = $_FILES["org-logo"];
            
        if ($file_params['size']/1024 > (5*1024))
        {
            $this->respondJSON(array("error"=>"Your image is too large. Try to keep it under 5MB."));
        }

    	if(is_numeric($acct_id)) {
    		if(empty($data['req-key']) || !Session::checkNonce($data['req-key'], "Account",$acct_id)) $this->respondJSON(array("error"=>"You cannot edit this account."));
    	}

        $db = DB::connect()->transact();

    	//We've successfully validated the reqeust key
    	//Now make sure we can modify the object
    	$a = new Account();
    	$a->setName($data['org-name']);
    	$a->setMascot($data['org-mascot']);
    	$a->setAddress($data['org-address']);
    	$a->setCity($data['org-city']);
    	$a->setState($data['org-state']);
    	$a->setZip($data['org-zip']);
    	$a->setContact($data['org-contact']);
    	$a->setPhone($data['org-phone']);

    	if($acct_id) $a->setId($acct_id);
    	$success = $a->save($acct_id);

    	if(!$acct_id) $acct_id = $success;

    	if($acct_id) {
    		$as = new AccountStyle();
    		$as->setStyle(json_encode(array(
    			'{back-color-1}'=>$data['back-color-1'],
    			'{back-color-2}'=>$data['back-color-2'],
    			'{back-color-3}'=>$data['back-color-3'],
    			'{back-color-4}'=>$data['back-color-4'],
    			'{back-color-error}'=>$data['back-color-error'],
    			'{font-color-1}'=>$data['font-color-1'],
    			'{font-color-2}'=>$data['font-color-2']
    		)));

    		$as->setAccountId($acct_id);
    		$as->save();
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
                
                $img_save_name = md5($acct_id ."|image") .".jpg";
                
                $f->resizeImage(256,67, "crop");//large image
                $f->saveImage(USER_IMG_PATH . $img_save_name);
                
                $a->setId($acct_id);
                $a->setImage(USER_IMG_URL . $img_save_name);
                $success = $a->save($acct_id);
                
                if(!$success) {
                    $db->rollback();
                    $this->respondJSON(array("error"=>"Failed to save the account's image"));
                }
            } catch (Exception $e) {
                $db->rollback();
                $this->respondJSON(array("error"=>"Failed to save the account's image (1)"));
            }
        }
        else if($data['remove-image'] == 'on') {
            
            $img_save_name = md5($acct_id ."|image") .".jpg";
            
            unlink(USER_IMG_PATH . $img_save_name);
            
            $a->setId($acct_id);
            $a->setImage("");
            $success = $a->save($acct_id);
        }

        $db->commit();

    	$this->respondJSON($acct_id);

    }
}

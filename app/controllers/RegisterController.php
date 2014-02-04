<?php

class RegisterController extends Controller {
    
    
    public function get_index() {
        
        App::init()->redirect("/#register-modal");
        
        // $request_key = App::init()->generateRequestKey();
//         
        // Session::setRequestKey($request_key);
//         
        // $this->respondView("register");
        
    }
    
    public function post_index() {
        
        $app = App::init();
        
        /* 
         * Field expectation definition
         */
        $fields = array("name"=>true,
                        "email"=>true,
                        "password"=>true,
                        "hip-code"=>true,
                        "user-agreement"=>true,
                        "tz"=>false);
                        
        $validations = array("email"=>"email","password"=>"password","hip-code"=>"hipcode","user-agreement"=>"checked"/*,"reg-key"=>"session_key"*/);

        $this->checkFields($fields, $validations);
        
        $data = $app->request->data;
        
        $u = User::loadByEmail($data['email']);
        
        $hip = Device::loadByHIP($data['hip-code']);
        
        if($hip) {
            $this->respondJSON(array("error"=>"hip_invalid"));
        }
        
        if(!HipCode::checkExistsAndAvailable(strtoupper($data['hip-code']))) {
            $this->respondJSON(array("error"=>"hip_invalid"));
        }
        
        if(!$u) {
            //Create the user
            try {
                $user_id = User::createUser($data['email'], $data['password']);
                
                if(empty($user_id)) {
                    $this->respondJSON(array("error"=>"There was an error creating your user. Please try again."));
                }
                else {
                    $u = User::load($user_id);
                    
                    if(!$u) $this->respondJSON(array("error"=>"There was an error creating your user. Please try again."));
                    
                    $up = new UserProfile();
                    $up->setUserId($user_id);
                    $up->setSignedTerms(time());
                    $up->setName($data['name']);
                    if(!$up->save($user_id)) {
                        //Remove this user entry
                        $u->delete();
                        $this->respondJSON(array("error"=>"There was an error creating your user. Please try again."));
                    }
                    else {
                        //Profile is created and user is created. just add the device now
                        $d = new Device();
                        $d->setUserId($user_id);
                        $d->setName("Primary Device");
                        $d->setHip($data['hip-code']);
                        $d->setStatus("active");
                        $d->save();
                        
                        HipCode::setUsed($data['hip-code']);
                        $u->setName($data['name']);
                        $u->setEmail($data['email']);
                        $email = EmailMessage::buildPlatformReg($u);
                        
                        if(is_numeric($data['tz'])) $u->saveTimezone($data['tz']);
                        
                        mail($u->getEmail(), "Welcome to HealthID!", $email, "From: Health ID <no-reply@healthid.com>");
                        
                        //We're good to go, start the session
                        Session::beginSession($u);
                        $this->respondJSON(array("redirect"=>"/my_health"));
                    }
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                $this->respondJSON(array("error"=>"There was an error creating your user. Please try again."));
            }
        }
        else {
            $this->respondJSON(array("error"=>"email_in_use"));
        }
        
    }
    
}

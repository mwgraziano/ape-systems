<?php


class LoginController extends Controller {
    
    
    /*
     * |url post /login
     * |req user str
     * |req pass str
     * |res JSON Object {"success":true, "redirect":"/me"}
     * |res Error Object
     * |res FormError Object If a field is missing or not a valid email
     */
    public function post_index() {
        
        $c = Cache::init();
            
        $app = App::init();
        
        /* 
         * Field expectation definition
         */
        $fields = array("username"=>true,
                        "password"=>true,
                        "tz_offset"=>false);
                        
        $validations = array("user"=>"email");

        $this->checkFields($fields, $validations);
        
        $user = $app->request->data["username"];
        $pass = $app->request->data["password"];
        
        if(empty($user) || empty($pass)) $this->failedLogin();
        
        $key = $c->generateKey("user_auth_attempt[". $user ."]");
            
        if(!$attempts = $c->get($key)) {
                
            $c->set($key, 1, 0, 1800);
            
        } else {
            
            $attempts = $c->increment($key);
            
            if($attempts >= MAX_FAILURE_USER && ENV != 'dev') {
                
                $this->respondJSON(new Error("You've exceeded the maximum number of login attempts. Please try again in 30 minutes."));
                
            }
        }
        
        $user_id = User::authenticate($user, $pass);
        
        if(!empty($user_id)) {
            
            $u = User::load($user_id);
            
            $tz_data = $app->request->data['tz_offset'];
            
            if ($tz_data !== null && !empty($tz_data)) {
                
                list($tz_offset,$tz_name) = explode(',',$tz_data);
                
                $u->saveTimezone($tz_offset, $tz_name);
                
            }
            
            if($u) {
                    
                Session::beginSession($u);
                
                $u->setLastUpdate();
                
                AuthLog::log($u->getId());
                
                $this->respondJSON(SUCCESS);
                
            }
            
        } else {
            
            
            $key = $c->generateKey("auth_attempt[". Session::getSessionId() ."]");
            
            if(!$i = $c->get($key)) {
                    
                $c->set($key, 1, 0, 3600);
                
            } else {
                
                $i = $c->increment($key);
                
                if($i >= MAX_FAILURE_SESSION) {
                    
                    $block_key = $c->generateKey("session_blocked[". Session::getSessionId() ."]");
                    
                    $c->set($block_key, 1, 0, 86400);
                    
                    $this->respondJSON(new Error("You have exceeded the maximum number of login attempts. You may try again later."));
                }
                
            }
            
            $this->failedLogin();
            
        }
        
        
    }

    private function failedLogin() {
        $this->respondJSON(new Error("Sorry, we couldn't find your email and password. Please try again."));
    }
    
    private function generateForgotLink(User $u) {
        
        $link = ForgotPasswordLink::create($u);
        
        if($link) return $link->getHashedUrl();
        
        return false;
        
    }
    
    public function get_viewSession() {
        if(ENV == 'dev') dump($_SESSION);
        
        else App::init()->redirect("/not_found");
    }
    
    /*
     * |url post /login/forgot
     * |req email str
     * |res OK str
     * |res Error Object On failure to create hash or load user
     * |res FormError Object On failure to validate email address
     */
    public function post_forgot() {
        
        $fields = array("email"=>true);
                        
        $validations = array("email"=>"email");
        
        $this->checkFields($fields, $validations);
        
        $app = App::init();
        
        if($u = User::loadByEmail($app->request->data['email'])) {
            
            //The user exists
            if(!$url = $this->generateForgotLink($u)) {
                //Wha?
                $this->respondJSON(new Error("An error occurred while creating the password reset link"));
            }
            
            $email = EmailMessage::buildForgotAuth($u, $url);
            
            if(mail($u->getEmail(), "Ape System Login Information", $email, "From: Ape System <no-reply@ape-system.com>")) {
                
                $this->respondJSON(SUCCESS);
                
            }
            
            $this->respondJSON(new Error("Failed to send email"));
            
        }
        else $this->respondJSON(new Error("An error occurred while locating your email address"));
        
    }
    
    
    /*
     * Reset link landing page
     * |url get /login/reset/[hash str]
     * |res reset page
     */
    public function get_reset($hash = false) {
        error_log("reset: ".$hash);
        
        if(empty($hash) || !is_md5($hash)) $hash = false;
        
        $app = App::init();
        
        try {
            $u = ForgotPasswordLink::loadUserByHash($hash);
            
            $_SESSION['reset_hash'] = $hash;
            
            $this->respondView("reset");
            
            exit;
            
        }
        catch (Exception $e) {
            if($e->getMessage() == "expired") {
                $this->respondView("reset_expired");
            }
        }
        
        $app->redirect("/");
        
    }
    
    
    /*
     * |url post /login/reset
     * |req pass str New Password
     * |req hash str
     * |res OK str
     * |res Error Object On failure
     * |res FormError Object On invalid field
     */
    public function post_reset($hash = false) {
        
        $fields = array("pass"=>true);
                        
        $this->checkFields($fields, array("pass"=>"password"));
        
        try {
            
            $hash = $_SESSION['reset_hash'];
            
            error_log("hash: ".$hash);
            
            $u = ForgotPasswordLink::loadUserByHash($hash);
            
            ForgotPasswordLink::acceptHash($hash);
        
            $app = App::init();
            
            if($u->changePassword($app->request->data['pass'])) {
                
                Session::beginSession($u);
                
                $u->setLastUpdate();
                
                AuthLog::log($u->getId());
                
                $this->respondJSON(SUCCESS);
                
            }   
        }
        catch (Exception $e) {
            $this->respondJSON("Failed hash lookup");
        }
        
        $this->respondJSON(new Error("Failed to reset password"));
        
    }
    
    
    
    
}

<?php

class UsersController extends Controller {
    
    const AUTH_LEVEL_REQUIRED = AUTH_LEVEL_LOGGEDIN;
    
    /*
     * |url get /users/profile/
     * |res UserProfile Object
     */
    public function get_profile() {
        
        $app = App::init();
        $user = $app->user;
        
        $this->respondJSON($user->getProfile());
        
        
    }
    
    
}

?>
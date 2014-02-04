<?php

class LogoutController extends Controller {
    
    
    public function get_index() {
        
        Session::endSession();
        
        App::init()->redirect("/");
        
    }
}

?>
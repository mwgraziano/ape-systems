<?php

class AdminController extends Controller {
    
    const AUTH_LEVEL_REQUIRED = AUTH_LEVEL_ADMIN;
    
    public function get_index() {
        $this->respondView("admin");
    }
    
    
    
}

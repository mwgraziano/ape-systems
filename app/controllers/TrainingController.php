<?php

class TrainingController extends TeamAdminController {
        
    const AUTH_LEVEL_REQUIRED = AUTH_LEVEL_LOGGEDIN;
    
    public function get_index() {
        $this->respondView("training");
    }
    
}

<?php


class RosterController extends TeamAdminController {
        
    public function get_index() {
        $this->respondView("roster");
    }
    
}

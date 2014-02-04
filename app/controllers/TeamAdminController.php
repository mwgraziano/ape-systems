<?php

abstract class TeamAdminController extends Controller {
        
    const AUTH_LEVEL_REQUIRED = AUTH_LEVEL_LOGGEDIN;
    
    public function authorize() {
        //these controllers require a team to be selected
        $team = Team::load(Session::getTeamId());
        
        if(empty($team)) {
            $this->respondView("switch_team");
            exit;
        }
        else return true;
        
    }
}

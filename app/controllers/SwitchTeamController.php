<?php

class SwitchTeamController extends Controller {
    const AUTH_LEVEL_REQUIRED = AUTH_LEVEL_LOGGEDIN;
    
    public function get_index() {
        $this->respondView("switch_team");
    }
}

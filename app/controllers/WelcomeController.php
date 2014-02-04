<?php

class WelcomeController extends Controller {
    public function get_index() {
        $this->respondView("welcome");
    }
    
    public function get_welcome() {
        $this->respondView("welcome2");
    }
}

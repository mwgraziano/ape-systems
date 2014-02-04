<?php

class PubController extends Controller {
    
    public function get_index() {
        
        $this->respondView("welcome");
        
    }
    
}

?>
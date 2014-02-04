<?php

class NotFoundController extends Controller {
        
    public function get_index() {
            
        $this->respondView("not_found");
        
    }
}

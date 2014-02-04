<?php

class Error {
    
    
    public function __construct($msg, $id = 0, $trace = false) {
        
        $this->message = $msg;
        
        if($id) $this->error_number = $id;
        
        if($trace) $this->trace = debug_backtrace();
    }
    
}

?>

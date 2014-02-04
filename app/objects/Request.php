<?php

class Request {
        
    private static $instance;
    public $controller;
    public $function;
    public $args;
    public $data;
    
    private function __construct(){
        
        $_REQUEST = $this->scrubRequest($_REQUEST);
        
        $this->parseURI($_REQUEST['_cp']);
        
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);
        
        unset($_REQUEST['_cp']);
        
        $this->parseData();
        
        $_REQUEST = array();
        
    }
    
    public static function init() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    
    private function parseURI($str) {
        $parts = explode("/", trim($str,"/"));
        
        $parts[0] = ucfirst(camel($parts[0]));
        $parts[1] = camel($parts[1]);
        
        //Special cases for legacy KeyAccess
        if(strtoupper($parts[0]) == 'KEYACCESS') {
            $parts[0] = 'KeyAccess';
        }
        
        
        $this->controller = Controller::exists($parts[0]."Controller") ? $parts[0]."Controller" : DEFAULT_CONTROLLER;
        $this->function = !empty($parts[1]) ? $parts[1] : DEFAULT_FUNCTION;
        
        $this->args = array_slice($parts,2);
        
        //account for numeric index function args
        if(is_numeric($this->function)) {
            array_unshift($this->args, $this->function);
            $this->function = DEFAULT_FUNCTION;
        }
    }
    
    private function parseData() {
        foreach($_REQUEST as $key => $val) {
            $this->data[$key] = $val;
        }
    }
    
    
    private function scrubRequest($req) {
        
        foreach($req as $key => $val) {
            if(is_array($val)) $req[$key] = self::scrubRequest($val);
            else $req[$key] = self::scrub($val);
        }
        
        return $req;
    }
    
    private function scrub($val)
    {
        if(!is_string($val)) throw new Exception("Cannot scrub anything but a string");
        
        $db = DB::connect();
        
        $val1 = $db->real_escape_string($val);
        
        if(empty($val1)) $val = addslashes($val);
        else $val = $val1;
        
        $bad = array(" UNION ", "LOAD_FILE","OUTFILE",";--",";/*","<",">");
        
        $val = str_ireplace($bad,"",$val);
        
        return $val;
    }
}

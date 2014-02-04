<?php

class HashModel extends Model {
    
    public static function load($id) {
        return parent::load($id, 0);
    }
    
    public function save($id) {
        return parent::save($id, true);
    }
    
    
    protected function __find($cols, $args = array()) {
        $args[2] = 1;
        return parent::__find($cols, $args);
    }
    
}

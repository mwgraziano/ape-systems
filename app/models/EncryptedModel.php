<?php

abstract class EncryptedModel extends Model {
	
    protected $my_params = array();
    
    public function __get($param) {
            
        if($param == 'profile') return parent::__get($param);
        
        if(!in_array($param, (array)$this->my_params, false) && is_object($this->data['profile'])) return $this->data['profile']->__get($param);
    	else return parent::__get($param);
    }

}
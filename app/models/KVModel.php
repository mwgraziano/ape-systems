<?php

class KVModel extends Model {
    
    
    public static function load($id = false, $owner_id = false, $is_active = 1) {
        if(empty($id)) return false;
        
        $class = get_called_class();
        
        $c = new $class();
        
        $cache_key = $c->buildCacheKey($id);
        
        $data = $c->getFromCache($cache_key);
        
        if(empty($data) || (count($data) == 1 && isset($data['_VERSION_'])) || (isset($data['_VERSION_']) && $data['_VERSION_'] != $c->getVersion()))
        {
            if(is_array($c->row_identifier))
            {
                if(!is_array($id) || count($id) != count($c->row_identifier))
                {
                    error_log($class ."::load() - Cannot load multicolumn identifiers where the id column count doesn't match");
                    return array();
                }
                
                $iden = "(". implode(',',$c->row_identifier) .")";
                $id = "(". implode(",",$id) .")";
            }
            else {
                $iden = $c->row_identifier;
                $id = is_array($id) ? $id[0] : $id;
            }
            
            if($is_active == 1) $is_active_sql = "AND active = 1";
            else $is_active_sql = "";
            
            $sql = "SELECT prop_name, CAST(AES_DECRYPT(prop_value, '". App::init()->buildSecret($owner_id) ."') AS CHAR) as `prop_value` FROM ". $c->table ." WHERE ". $iden ." = ". $id ." ". $is_active_sql;
            
            $data = DB::connect()->getKeyedAssoc($sql,'prop_name');
            
            if(empty($data)) return array();
            
            $c->addToCache($cache_key, $data);
        }
        
        if(count($data)==1) return array(); //_VERSION_ is always set
        
        $c->addData($data);
        
        return $c;
    }
    
    public function save( $owner_id = false ) {
        
        $id = $this->getRowIdentifier();
        
        foreach((array)$this->row_identifier as $i) unset($this->data[$i]);
        
        $parts = array();
        
        $db = DB::connect();
        
        foreach($this->data as $prop_name => $prop_value) {
            
            $parts[] = "('". implode("','", $id) ."', '". $prop_name ."',". " AES_ENCRYPT('". $prop_value ."', '". App::init()->buildSecret($owner_id) ."'), NOW(), 1)";
        }
        
        $sql = "INSERT INTO ". $this->table ." VALUES ". implode(",",$parts) ." ON DUPLICATE KEY UPDATE prop_value = VALUES(prop_value), created = CURRENT_TIMESTAMP(), active = 1";
        
        $success = $db->update($sql);
        
        $this->uncache($id);
        
        return $success;
    }
    
    private function createNew() {
        return false;
        
    }
    
    private function saveChanges() {
        return false;
    } 
    
}

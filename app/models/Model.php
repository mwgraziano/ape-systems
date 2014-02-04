<?php

abstract class Model
{
	
	protected $data;
	protected $_version_ = 1;
	protected $table;
	protected $row_identifier;
	protected $export_cols;
	
	public function __construct()
	{
		$this->data = array();
	}
	
	public function __call($method, $args)
	{
		if(substr($method,0,3) == "get")
		{
			return $this->__get(uncamel(substr($method,3)));
		}
		else if(substr($method,0,3) == "set")
		{
			return $this->__set(uncamel(substr($method,3)), $args);
		}
		else if(substr($method,0,4) == 'find')
		{
			return $this->__find(uncamel(substr($method,4)), $args);
		}
        else if(substr($method,0,4) == "_get")
        {
            return nl2br(htmlspecialchars($this->__get(uncamel(substr($method,4)))));
        }
		
		return false;
	}

	public function __callStatic($method, $args) {
		if(substr($method,0,4) == 'find')
		{
			$class = get_called_class();
			$c = new $class();
			return $c->__find(uncamel(substr($method,4)), $args);
		}
		
		return false;
	}
	
	public function __set($var, $val)
	{
		if(is_array($val)) $val = $val[0];
		$this->data[$var] = $val;
	}
	
	public function __get($var)
	{
		return isset($this->data[$var]) ? $this->data[$var] : null;
	}
	
	
	/*
	 * __find takes an array of searches i.e. [array("email"=>"myemail@mydomain.com"), return_count, ignore_active];
	 * it also takes a limiter
	 */
	protected function __find($cols,$args = array())
	{
		if(!is_array($cols)) $cols = array($cols);

		$cache_key = Cache::generateKey("find|". md5(serialize(func_get_args())));

		$c = Cache::init();
		$data = $c->get($cache_key);

		if(!empty($data)) return $data;
		
		$limit = false;
		$orderby = false;
		$orderdir = false;
		
		$sql = "SELECT ". implode(',',$cols) ." FROM ". $this->table ." WHERE 1";
        
        if(empty($args[2])) $sql .= " AND active = 1";
        
		if(!empty($args[0]))
		{
			foreach($args[0] as $col => $val)
			{
				if($col == 'LIMIT') {
					$limit = $val;
					continue;
				}
				if($col == 'ORDER') {
					list($orderby, $orderdir) = (array)$val;
					if(empty($orderdir)) $orderdir = 'ASC';
					continue;
				}
				if(is_array($val))
				{
					switch(strtoupper($val[0]))
					{
						case "LIKE":
							$where_parts[] = $col ." LIKE '%". $val[1] ."%'";
							break;
						case "%LIKE":
							$where_parts[] = $col ." LIKE '%". $val[1] ."'";
							break;
						case "LIKE%":
							$where_parts[] = $col ." LIKE '". $val[1] ."%'";
							break;
						case "=":
						default:
							$where_parts[] = $col ." = '". $val[1] ."'";
							break;
					}
				}
				else $where_parts[] = $col ."='". $val ."'";
			}
			
			if(!empty($where_parts)) $sql .= " AND ". implode(" AND ", $where_parts);
		}

		if(!empty($orderby)) $sql .=" ORDER BY ". $orderby ." ". $orderdir;
		
		if(!empty($limit)) $sql .= " LIMIT ". $limit;
		
		$db = DB::connect();
		
		$response = $db->getAll($sql);
		
		if(isset($args[1]) && $args[1] > 0)
		{
			if($args[1] > 1)
			{
				$response_ct = count($response);
				
				for($i = 0; $i < $args[1], $i < $response_ct; $i++)
				{
					$ret[] = $response[$i];
				}
			}
			else if($args[1] == 1)
			{
				$ret = $response[0];
			}
		}
		else $ret = $response;

		$c->set($cache_key, $ret, 10);
		
		return $ret;
	}
	
	public static function load($id, $is_active = 1)
	{
		if(empty($id)) return false;

		$class = get_called_class();
		
		//Handle a loadAll scenario
		if(is_array($id) && is_array($id[0])) {

			$ret = array();

			foreach($id as $i) {
				$ret[] = $class::load($i);
			}

			return array_filter($ret);
		}

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
					return false;
				}
				
				$iden = "(". implode(',',$c->row_identifier) .")";
				$id = "(". implode(",",$id) .")";
			}
			else 
			{
				$iden = $c->row_identifier;
				$id = is_array($id) ? $id[0] : $id;
			}
			
			if($is_active == 1) $is_active_sql = "AND active = 1";
			else $is_active_sql = "";
			
			$sql = "SELECT * FROM ". $c->table ." WHERE ". $iden ." = ". $id ." ". $is_active_sql;
			
			$data = DB::connect()->getFirst($sql);
			
			if(empty($data)) return false;
			
			$c->addToCache($cache_key, $data);
		}
		
		if(count($data)==1) return false; //_VERSION_ is always set
		
		$c->addData($data);
		
		return $c;
	}
	
	public function getFromCache($key)
	{
		$c = Cache::init();
		$val = $c->get($key);
		
		if(!empty($val)) return $val;
		
		return false;
	}
	
	public function buildCacheKey($id)
	{
		if(!is_array($id)) $id = array($id);
		$class = get_class($this);
		$key = SITE_CACHE_PREFIX ."|". DBNAME ."|". $class ."[". implode(',',$id) ."]";
		return $key;
	}

	public function buildCacheSubKey($subkey)
	{
		$id = $this->getRowIdentifier();
		$key = $this->buildCacheKey($id);
		$key .= "[$subkey]";
		return $key;
	}
	
	public function getRowIdentifier()
	{
		$c = array();
		
		$a = $this->row_identifier;
		if(!is_array($a))
		{
			$a = array($a);
		}
		
		foreach($a as $b)
		{
			$c[] = $this->__get($b);
		}
		
		return $c;
	}
	
    public function cache()
    {
        $key = $this->buildCacheKey($this->getRowIdentifier());
        return $this->addToCache($key,$this->getData());
    }
    
	public function addToCache($key, $data)
	{
		$data['_VERSION_'] = $this->getVersion();
		$c = Cache::init();
        
		$c->set($key, $data, MEMCACHE_COMPRESSED, 0);
	}
	
	public function subCache($key, $val, $exp = 0)
	{
		$key = $this->buildCacheSubKey($key);
		$c = Cache::init();
		$c->set($key, $val, MEMCACHE_COMPRESSED, $exp);
	}
    
    public function subUncache($key)
    {
        $key = $this->buildCacheSubKey($key);
        $c = Cache::init();
        return $c->delete($key);
    }
	
	public function subGet($key)
	{
	    if(ENV == 'dev') return false;
        $key = $this->buildCacheSubKey($key);
		$c = Cache::init();
		return $c->get($key);
	}
    
    public function uncache($id = false) {
        $c = Cache::init();
        $key = $this->buildCacheKey($id ?: $this->getRowIdentifier());
        
        $cdata = $c->delete($key);
    }
	
	public function save($id = null, $ignore_created = false)
	{
	    if(empty($id))
		{
			$id = $this->createNew($ignore_created);
			$this->setIdentifier($id);
			$success = $id;
		}
		else
		{
			$success = $this->saveChanges($ignore_created);
		}
		
        
        if($id)
            $this->updateCache($id);
		
		return $success;
	}
	
	private function createNew($ignore_created = false)
	{
		$this->verifyRowIdentifiers(true);
		
		if(!$ignore_created && !isset($this->data['created']))
		{
			$this->setCreated(date('Y-m-d H:i:s'));
		}
		
		if(!$ignore_created && !isset($this->data['active'])) $this->data['active'] = 1;
		
		$sql = "INSERT INTO ". $this->table ." SET ";
		foreach($this->data as $key=>$val)
		{
			if(substr($key,0,1) == '_') continue;
            if($val === false || $val == 'NULL')
                $save_parts[] = "`$key` = NULL";
            else
			    $save_parts[] = "`$key` = '$val'";
			
			$dupe_parts[] = "`$key` = VALUES(`$key`)";
		}
		
		$sql .= implode(',',$save_parts);
		
		$sql .= " ON DUPLICATE KEY UPDATE ". implode(",", $dupe_parts);
		
		$db = DB::connect();
		$db->insert($sql);
		
		if(!$db->errno)
		{
			if(is_array($this->row_identifier))
			{
				$id = array();
				
				foreach($this->row_identifier as $iden)
				{
					$id[] = $this->__get($iden);
				}
				
				return $id;
			}
			else return $db->insert_id;
		}
		else
		{
			throw new Exception(get_class($this)."::createNew() - Cannot create new record: ". $db->error ." - ". $sql);
			return false;
		}
	}
	
	private function saveChanges($ignore_created = false)
	{
		$this->verifyRowIdentifiers();
		
		$sql = "UPDATE ". $this->table ." SET ";
		
		if(!$ignore_created && !isset($this->data['active'])) $this->data['active'] = 1;
		
		foreach($this->data as $key=>$val)
		{
			if(substr($key,0,1) == '_') continue;
			if($val === false || $val == 'NULL')
                $save_parts[] = "`$key` = NULL";
            else
                $save_parts[] = "`$key` = '$val'";
		}
		
		$sql .= implode(',',$save_parts);
		
		$id_parts = array();
		
		if(is_array($this->row_identifier))
		{
			foreach($this->row_identifier as $iden)
			{
				$id_parts[] = $iden ." = '". $this->__get[$iden] ."'";
			}
		}
		else
		{
			$id_parts[] = $this->row_identifier ." = '". $this->__get($this->row_identifier) ."'";
		}
		
		$sql .= " WHERE ". implode(" AND ", $id_parts);
		
		$db = DB::connect();
		$db->insert($sql);
		
		if(!$db->errno)
		{
			return true;
		}
		else
		{
			throw new Exception(get_class($this)."::saveChanges() - Cannot save record: ". $db->error);
			return false;
		}
	}
	
	private function verifyRowIdentifiers($chkArrayOnly = false)
	{
		//Verify the identifiers first
		$iden_mismatch = false;
		
		if(is_array($this->row_identifier))
		{
			foreach($this->row_identifier as $iden)
			{
				if(!isset($this->data[$iden])) $iden_mismatch = true;
			}
		}
		else if(!isset($this->data[$this->row_identifier]) && $chkArrayOnly == false) $iden_mismatch = true;
		
		if($iden_mismatch === true)
		{
			throw new Exception(get_class($this)."::verifyRowIdentifiers() - Identifier mismatch.". var_export($this,true));
		}
	}
	
	public function getVersion()
	{
		return $this->_version_;
	}
	
	public function addData($data)
	{
		if(isset($data['_VERSION_']))
		{
			unset($data['_VERSION_']);
		}
		
		foreach($data as $key=>$val)
		{
			$this->__set($key, $val);
		}
	}
    
    public static function loadFromData($data) {
        $class = get_called_class();
        $c = new $class();
        $c->addData($data);
        return $c;
    }
	
	public function exportSafe()
	{
		$d = array();
		foreach($this->export_cols as $col)
		{
			$d[$col] = $this->$col;
		}
		return $d;
	}
    
    private function updateCache($id)
    {
        $c = Cache::init();
        $key = $this->buildCacheKey($id);
        
        $cdata = $c->delete($key);
        
    }
	
	public function getData()
    {
        return $this->data;
    }
	
	public function setIdentifier($id)
	{
		if(is_array($id) && is_array($this->row_identifier))
		{
			for($i=0;$i<count($this->row_identifier);$i++)
			{
				$this->__set($this->row_identifier[$i], $id[$i]);
			}
		}
		else if(!is_array($id) && !is_array($this->row_identifier))
		{
			$this->__set($this->row_identifier, $id);
		}
	}
    
    public function deactivate() {
        
        $id = $this->verifyRowIdentifiers();
        
        $sql = sprintf("UPDATE %s SET active = 0", $this->table);
        
        if(is_array($this->row_identifier))
        {
            foreach($this->row_identifier as $iden)
            {
                $id_parts[] = $iden ." = '". $this->__get[$iden] ."'";
            }
        }
        else
        {
            $id_parts[] = $this->row_identifier ." = '". $this->__get($this->row_identifier) ."'";
        }
        
        $sql .= " WHERE ". implode(" AND ", $id_parts);
        
        $db = DB::connect();
        
        return $db->update($sql);
        
    }
    
    public function delete($debug = false) {
        
        $id = $this->verifyRowIdentifiers();
        
        $sql = sprintf("DELETE FROM %s ", $this->table);
        
        if(is_array($this->row_identifier))
        {
            foreach($this->row_identifier as $iden)
            {
                $id_parts[] = $iden ." = '". $this->__get($iden) ."'";
            }
        }
        else
        {
            $id_parts[] = $this->row_identifier ." = '". $this->__get($this->row_identifier) ."'";
        }
        
        if(empty($id_parts)) throw new Exception("Cannot delete records with improper ids");
        
        $sql .= " WHERE ". implode(" AND ", $id_parts);
        
        if($debug) error_log($sql);
        
        $db = DB::connect();
        
        return $db->update($sql);
        
    }
    
    public function generateCacheKey($sub_key = "") {
        
        $key = implode("|", (array)$this->row_identifier);
        foreach((array)$this->row_identifier as $ri) {
            $key .= "|". $this->__get($ri);
        }
        
        if(!empty($sub_key)) $key .= "[". $sub_key ."]";
        
        return Cache::init()->generateKey($key);
    }
    
}

?>
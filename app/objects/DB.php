<?php

class DB
{
    private static $instance;
    private $connection;
    
    private function __construct(){
    	$this->_connect();
    }
    
    private function _connect()
    {
        if(!is_null($this->connection)) {
            if(mysqli_ping($this->connection)) return;
        }
        
        $c = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
        if($c->connect_error)
        {
            throw new Exception("Failed to connect to DB: ".$c->connect_error);
        }
		
		$c->set_charset('utf8_general_ci');
        
        $this->connection = $c;
    }
    
    public static function init()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new DB();
        }
        
        return self::$instance;
    }
    
    public static function connect()
    {
        $db = DB::init();
        $db->_connect();
        
        return $db;
    }

    public function __call($method, $args)
    {
        if($this->connection)
        {
            return @call_user_func_array(array($this->connection, $method), $args);
        }
    }
	
	public function __get($var)
	{
		if($this->connection)
			return $this->connection->$var;
	}
	
	public function getAll($sql)
	{
		$res = $this->query($sql);
		if($this->errno)
		{
			throw new Exception("DB::query() - ". $this->error ." - ". $sql);
		}
		
		if(!$res->num_rows) return false;
		
		while($row = $res->fetch_array(MYSQLI_ASSOC))
		{
			$ret[] = $row;
		}
		
		$res->free();
		
		return $ret;
	}
    
    public function getKeyedAssoc($sql,$key)
    {
        $res = $this->query($sql);
        if($this->errno)
        {
            throw new Exception("DB::query() - ". $this->error ." - ". $sql);
        }
        
        if(!$res->num_rows) return false;
        
        while($row = $res->fetch_array(MYSQLI_ASSOC))
        {
            if(count($row) == 2)
            {
                $val = array_values(array_diff($row, array($key => $row[$key])));
                $val = $val[0];
                $ret[$row[$key]] = $val;
            }
            else
            {
                $val = $row[$key];
                unset($row[$key]);
                $ret[$val] = $row;
            }
        }
        
        $res->free();
        
        return $ret;
    }
    
    public function getLoaded($sql, $class)
    {
        $res = $this->query($sql);
        if($this->errno)
        {
            throw new Exception("DB::query() - ". $this->error ." - ". $sql);
        }
        
        if(!$res->num_rows) return array();
        
        $ret = array();
        while($row = $res->fetch_array(MYSQLI_ASSOC))
        {
        	$ret[] = $class::loadFromData($row);
        }
        
        $res->free();
        
        return $ret;
    }
	
	public function getOne($sql)
	{
		$res = $this->query($sql);
		
		if(!$res->num_rows) return false;
		
		$row = $res->fetch_array(MYSQLI_NUM);
		$ret = $row[0];
		
		$res->free();
		
		return $ret;
	}
	
	public function getFirst($sql)
	{
		$res = $this->query($sql);
		if(!$res || !$res->num_rows) return false;
		
		$ret = $res->fetch_array(MYSQLI_ASSOC);
		
		$res->free();
		
		return $ret;
	}
	
	public function getLast($sql)
	{
		$res = $this->query($sql);
		if(!$res->num_rows) return false;
		
		$res->data_seek(($res->num_rows-1));
		
		$ret = $res->fetch_array(MYSQLI_ASSOC);
		
		$res->free();
		
		return $ret;
	}
	
	public function clean($val)
	{
		return $this->real_escape_string($val);
	}
	
	public function insert($sql)
	{
		$res = $this->query($sql);
		if(!$this->errno)
		{
			return $this->insert_id;
		}
		else
		{
		    error_log("DB::insert - ". $this->error);
			return false;
		}
	}
	
	public function update($sql)
	{
		$res = $this->query($sql);
		if(!$this->errno)
		{
			return ($this->affected_rows > -1);
		}
		else
		{
		    error_log("DB::update - ". $this->error);
		    return false;
        }
	}
    
    public function transact() {
        $this->query("start transaction");
        return $this;
    }
    
    public function rollback() {
        $this->query("rollback");
        return $this;
    }
    
    public function commit() {
        $this->query("commit");
        return $this;
    }
}

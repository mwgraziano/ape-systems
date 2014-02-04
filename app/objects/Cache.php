<?php

class Cache extends Memcache
{
	private static $instance;
	
	public static function init()
	{
		if(is_null(self::$instance))
		{
			self::$instance = new self();
            
            foreach(explode(",",MEMCACHE_SERVERS) as $server){
                self::$instance->addServer($server);
            }
		}
		
		return self::$instance;
	}
    
    public static function generateKey($key) {
        return SITE_CACHE_PREFIX . "|". $key;
    }

    public function set($key, $val, $expire = 60) {
    	return parent::set($key, $val, MEMCACHE_COMPRESSED, $expire);
    }
}

?>
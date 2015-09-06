<?PHP
/*
Create By PiaoFen in 2008-6-9
模拟 Session 方法，在需要时候调用。减少不必要的系统开销，比如一开始就session_start;
*/
Class Session
{
	private static $Session;//会话是否存在
	public function __construct()
	{
		##初始化
//		session_set_cookie_params(-1, '/', cookie_domain);
		
//		if (extension_loaded('Memcache') && $conf = CacheMemcached::load_config('session'))
//		{
//			ini_set("session.save_handler", "memcache");
//  			ini_set("session.save_path", implode(',', $conf));
//		}
	}

	#如果没建立连接就建立
	public function init()
	{
		if(!isset($_SESSION)){ 
			session_start(); 
		}
		/*if(empty(self::$Session)){
            if (!headers_sent() && !isset($_SESSION)) {
                session_regenerate_id(true);
                self::$Session = session_start();
                
            }
        }*/
	}

	public function set($key,$value)
	{
		$this->init();
		$_SESSION[strtolower($key)] = $value;
	}

	public function get($key)
	{
		$this->init();
		return !EMPTY($_SESSION[strtolower($key)])?$_SESSION[strtolower($key)]:'';
	}

	public function clear($key)
	{
		$this->init();
		if(!empty($_SESSION[strtolower($key)])){
			session_unregister(strtolower($key));
			unset($_SESSION[strtolower($key)]);
		}
	}

	public function clearall()
	{
		$this->init();
        session_unset();
        session_destroy();
		unset($_SESSION);
	}
}
?>
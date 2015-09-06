<?PHP
/*
Create By PiaoFen in 2008-6-9
模拟 Session 方法，在需要时候调用。减少不必要的系统开销，比如一开始就session_start;
*/
Class Cookie
{
	private static $Cookie;//会话是否存在
	private static $domain = '';

	public function __construct($domain='')
	{
		##初始化
		self::$domain = $domain;
	}

	public function set($key,$value,$timeout=0)
	{

		 setcookie($key,$value,time()+$timeout); 
		#setcookie($key,$value);
		//$_COOKIE[strtolower($key)] = $value;
	}

	public function get($key)
	{
		return $_COOKIE[$key];
	}

	public function clear($key)
	{
		if(!empty($_COOKIE[$key])){
			unset($_COOKIE[$key]);
			setcookie($key,'',time()-1000);
		}
	}

	public function clearall()
	{
		foreach((array)$_COOKIE as $key=>$val){
			setcookie($key,'',time()-1000);
		}
	}
}
?>
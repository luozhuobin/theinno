<?PHP
Class Mysql
{
	public $threads = '';
    public $qflag = '';
    public static $links = array();//连接线程
	public $debug = true;//调试模式
	public $query = '';//上次请求
	public $opencache = true; //开启缓存？
	public $cachetimeout = 0;//开启缓存时间
	public $server = '';//服务器名
	public $user = '';//数据库登陆名
	public $pass = '';//数据库密码
	public $dbname = '';//当前数据库
	public $charset = '';//当前的字符模式
	public $sqlmode = '';//当前的sql请求模式
	public $cachearray;
	public $cachenum = 0;
	public static $sqlnum = 0;
	public Static $sql = array();//记录所有查询过的Sql语句
	public Static $querynum = 0;
	public Static $maxquerynum = 0;

	function __construct($server='',$user='',$pass='',$dbname='',$charset='',$sqlmode='')
	{
		//是否开启调试模式
		if(defined('debug') && debug==1 && $this->debug===true){
			$this->debug = 1;
		}elseif($this->debug===false){
			$this->debug = 0;
		}
		//开启数据库连接
		if($server){
			$this->server = $server;
		}
		if($user){
			$this->user = $user;
		}
		if($pass){
			$this->pass = $pass;
		}
		if($dbname){
			$this->dbname = $dbname;
		}
		if($charset){
			$this->charset = $charset;
		}
		if($sqlmode){
			$this->sqlmode = $sqlmode;
		}
	}

	//后台写, 连接建立
	public function sel_init()
	{
		try{
			$this->links['sel'] = mysqli_connect(sel_mysql_server,sel_mysql_user,sel_mysql_pass,$this->dbname);
            $this->qflag = 'sel';
			if(!$this->links['sel'])
				throw new Exception("Connect Is Error!Error Info:<font color=red>".mysqli_connect_error()."</font>");
                //$this->links['sel'] = $this->ins_init();
		}catch(Exception $e){
			exit($e->getMessage());
		}
		if($this->charset){
			mysqli_query($this->links['sel'],"SET NAMES {$this->charset}");
		}
		mysqli_query($this->links['sel'],"SET @sqlmode = '".$this->sqlmode."'");
		return $this->links['sel'];
	}
	
	//官网写,  连接建立
	public function sel2_init()
	{
		try{
            //$this->links['sel2'] = mysqli_connect(sel_mysql_server,sel_mysql_user,sel_mysql_pass,$this->dbname);
			$this->links['sel2'] = mysqli_connect(sel2_mysql_server,sel2_mysql_user,sel2_mysql_pass,$this->dbname);
            $this->qflag = 'sel2';
			if(!$this->links['sel2'])
				throw new Exception("Connect Is Error!Error Info:<font color=red>".mysqli_connect_error()."</font>");
                //$this->links['sel'] = $this->ins_init();
		}catch(Exception $e){
			exit($e->getMessage());
		}
		if($this->charset){
			mysqli_query($this->links['sel2'],"SET NAMES {$this->charset}");
		}
		mysqli_query($this->links['sel2'],"SET @sqlmode = '".$this->sqlmode."'");
		return $this->links['sel2'];
	}

	//主数据库(平台读，所有写)
    public function ins_init(){
        try{
            $this->links['ins'] = mysqli_connect($this->server,$this->user,$this->pass,$this->dbname);
            $this->qflag = 'ins';
            if(!$this->links['ins'])
                throw new Exception("Connect Is Error!Error Info:<font color=red>".mysqli_connect_error()."</font>");
        }catch(Exception $e){
            exit($e->getMessage());
        }
        if($this->charset){
            mysqli_query($this->links['ins'],"SET NAMES {$this->charset}");
        }
        mysqli_query($this->links['ins'],"SET @sqlmode = '".$this->sqlmode."'");
        return $this->links['ins'];
    }
    
	public function getsql()
	{
		return self::$sql;
	}
	
    public function insert($tablename,array $bind, $dbname = '')
    {
        // extract and quote col names from the array keys
        if(!empty($dbname)) $pre_dbname = "`$dbname`.";
        $cols = array();
        $vals = array();
        foreach ($bind as $col => $val) {
            $cols[] = $col;
            $vals[] = $val;
            unset($bind[$col]);
        }
        
        $sql = "INSERT INTO $pre_dbname`"
             . $tablename ."`"
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES (\'' . implode('\', \'', $vals) . '\')';
             
        $query = $this->query($sql);
        return $this->insert_id();
    }
    
    #在某些特别情况下会出现问题，比如stat='stat*-1'实际上他是减去1而不是乘于1
    public function update($tablename,array $bind, $where = '',$dbname = '')
    {
        if(!empty($dbname)) $pre_dbname = "`$dbname`.";
        $set = array();
        foreach ($bind as $col => $val) 
        {
            $set[] = $col . ' = \'' . $val .'\'';
        }
        
        $sql = "UPDATE $pre_dbname`"
             . $tablename ."`"
             . ' SET ' . implode(', ', $set)
             . (($where) ? " WHERE $where" : '');
             
        $query = $this->query($sql);
        return $this->affected_rows();
    }

    /**
     @var $sqlsize sql限定长度,单位为 KB, 默认为10
     @var $strict  是否为严格模式, 默认为 true
     */
	public function query($sql, $sqlsize = 5, $strict = true, $servtype='')
	{
		if($strict && (strstr($sql,'1=2') || strstr($sql,'union select concat') || strstr($sql,'char('))) return;
        $mtime = explode(' ', microtime());
        $starttime = $mtime[1] + $mtime[0];
        $sqllen = strlen($sql);
        if($sqllen >= 1024 * $sqlsize){
            $sqlfile = root.'data/sqllog/longsql/longsql_'.date('Y-m').'.log';
            isdir(dirname($sqlfile));
            $size = getsize($sqlsize);
            $sql = str_replace(array("\r\n", "\n"), array('',''),$sql);
            $str = "REQUEST TIME: ".date('Y-m-d H:i:s')."\n";
            $str = "SQL SIZE: $size\n";
            $str .="SQL: $sql \n";
            
            if($fp = @fopen($sqlfile,'a+')){
                if(flock($fp, LOCK_EX | LOCK_NB)){
                    fwrite($fp, $str);
                    flock($fp,LOCK_UN);
                }   
            }
            return ;
        }
		
		$sql = trim($sql);
        $servtype = in_array($servtype, array('sel','ins','sel2'))?$servtype:'';
        
        if($servtype){
            $qflag = $servtype;
        }else if(in_array(substr(strtolower($sql), 0, 6), array('select', 'show')) && $GLOBALS['entry'] == 'backstage'){
            $qflag = 'sel';
        }else if(in_array(substr(strtolower($sql), 0, 6), array('select', 'show')) && $GLOBALS['entry'] == 'subsite'){
            $qflag = 'sel2';
        }else{
            $qflag = 'ins';
        }
         
		self::$sql[] = $sql;
		self::$sqlnum++;
		try{
            $this->qflag = $qflag;
			if(!$this->links[$qflag]){
				//未建立连接，开启连接，并初始化
				eval("\$this->{$qflag}_init();");
                //var_dump($this->links[$qflag]);
                //echo "\$this->{$qflag}_init();$sql<br/>";
			}
            
            
            $this->threads = $this->links[$qflag];
            //var_dump($this->links);
			##请求
            #echo strtolower(substr(trim($sql),0,7))!='explain'?$sql."<BR />":"";
			$tmpquery = mysqli_query($this->threads,$sql);
			if(!$tmpquery && !EMPTY($this->debug))
			{
				errorlog("sql",date("Y-m-d H:i:s")."||".str_replace("\t"," ",str_replace("\r\n","",$sql))."||".mysqli_error($this->threads));
			}
				//throw new Exception('请求失败，请检查你的SQL语句：'.$sql."<BR />失败原因：".mysqli_error($this->threads));
		}catch(Exception $e){
            if(strtolower(substr(trim($sql),0,7))!='explain'){
			    exit($e->getMessage());
            }
		}
		

        $mtime = explode(' ', microtime());
        $endtime = $mtime[1] + $mtime[0];
        $exectime = $endtime - $starttime;
        if ($exectime > 15)
        {
	        isdir("data/slow_sql_{$qflag}");
	        error_log("[". date("Y-m-d H:i:s"). "] -- $exectime -- ". $_SERVER['QUERY_STRING']. "\n$sql:{$this->qflag}\n\n", 3, "data/slow_sql_{$qflag}/slow_{$qflag}_". date("Ymd"). '.log');
        } 
        
        if(stristr($sql, "SELECT * FROM member WHERE 1  AND email")){
            $specialfile = ROOT."data/logs/sql/member_email/member_email_".date("Y-m").".log";
            $log[] = date("Y-m-d H:i:s")." SQL:$sql : $qflag";
            $log[] = date("Y-m-d H:i:s")." Referer:{$_SERVER['HTTP_REFERER']}";
            $log[] = date("Y-m-d H:i:s")." URI:{$_SERVER['REQUEST_URI']}&".http_build_query($_POST);
            $log[] = date("Y-m-d H:i:s")." exectime:$exectime";
            $log[] = date("Y-m-d H:i:s")." remoteip:".getip();
            $logstr = implode("\n", $log)."\n\n";
            isdir(dirname($specialfile));
            @file_put_contents($specialfile, $logstr, FILE_APPEND);
        } 
        
        if($_REQUEST['debug']=='yellow')
        {
	        echo "$sql : {$this->qflag}<br>";
	        echo ($endtime - $starttime)."<br>\n";
        }
		
		return $tmpquery;
	}

	##循环数组
	public function fetch_assoc($query)
	{
		return mysqli_fetch_assoc($query);
	}

	public function fetch_array($query)
	{
		return mysqli_fetch_array($query);
	}
	
	public function fetch_all($sql)
	{
		$query = $this->query($sql);
		$return_array = array();
		while($row = $this->fetch_array($query))
		{
			$return_array[] = $row;
		}
		return $return_array;
	}
	
	public function fetch_all_assoc($sql)
	{
		$query  = $this->query($sql);
		$return_array = array();
		while ($row = $this->fetch_assoc($query))
		{
			$return_array[current($row)] = $row;
		}
		return $return_array;
	}
	
	
	
	public function fetch_cache($sql, $expire=60, $path="", $reflesh=false)
	{
		$expire *= 60;
		if ($path == '.' || $path == '..') return false;
		
		$expire = intval($expire);
		$filepath = 'data/sql_cache/'. $path;
		$filename = $filepath. '/'. md5($sql).'.sql';
		isdir($filepath);
		
		if (!file_exists($filename) || (time()-filemtime($filename)) > $expire || $reflesh === true)
		{
			$query  = $this->query($sql);
			$return_array =  mysqli_fetch_assoc($query);
			
			$fp = @fopen($filename, 'w+');
			if ($fp && flock($fp, LOCK_EX | LOCK_NB))
			{
				fwrite($fp, json_encode($return_array));
				flock($fp, LOCK_UN);
			}
			@fclose($fp);
			
			return $return_array;
		}
		else 
		{
			$return_array = file_get_contents($filename);
			return json_decode($return_array, true);
		}
		
	}
	
	public function fetch_all_cache($sql, $expire = 60, $path='', $reflesh=false,$bFixed=false,$fixedname='')
	{
		$expire *= 60;
		if ($path == '.' || $path == '..') return false;
		
		$expire = intval($expire);
		$filepath = root.'data/sql_cache/'. $path;
		
		$filename = $filepath. '/'.md5($bFixed?$fixedname:$sql).'.sql';
		
		isdir($filepath);
		
        if($_REQUEST['debug2'] == 'leonyu'){
            echo "SQL : $sql <br/>\n";
            echo "$filename exists is ".file_exists($filename)."<br/>\n";
            $isexpire = filemtime($filename);
            echo "$filename expire is $isexpire<br/>\n";
        }
        
		if (!file_exists($filename) || (time()-filemtime($filename)) > $expire || $reflesh === true)
		{
			$query  = $this->query($sql);
			$return_array = array();
			while ($row = $this->fetch_assoc($query))
			{
				$return_array[current($row)] = $row;
			}
			
			$fp = @fopen($filename, 'w+');
			if ($fp && flock($fp, LOCK_EX | LOCK_NB))
			{
				fwrite($fp, json_encode($return_array));
				flock($fp, LOCK_UN);
			}
			@fclose($fp);
			
			return $return_array;
		}
		else 
		{
			$return_array = file_get_contents($filename);
			return json_decode($return_array, true);
		}
	}
	
	
	//@todo
	public function fetch_memory($sql, $expire=60, $path="", $reflesh=false)
	{
		include_once 'Class/CacheMemcached.php';
		$cache = new CacheMemcached(); //
		$result_array = $cache->setDefaultPath(array('sql_cache', $path))->get(md5($sql));
		
		if($_REQUEST['debug2'] == 'leonyu')
		{
            echo "SQL : $sql <br/>\n";
            echo "SQL HASH: ".md5($sql)."\n";
        }
		
		if (!$result_array || $reflesh === true || $_GET['reflesh_data']  === 'true')
		{
			$query  = $this->query($sql);
			$result_array =  mysqli_fetch_assoc($query);
			$cache->setDefaultPath(array('sql_cache', $path))->set(md5($sql), $result_array, $expire * 60);
		}
		return $result_array;
	}
	
	//@todo  
	public function fetch_all_memory($sql, $expire = 60, $path='', $reflesh=false,$bFixed=false,$fixedname='')
	{
		include_once 'Class/CacheMemcached.php';
		$cache = new CacheMemcached(); //
		$result_array = $cache->setDefaultPath(array('sql_cache', $path))->get(md5($sql));
        
		
		if($_REQUEST['debug2'] == 'leonyu')
		{
            echo "SQL : $sql <br/>\n";
            echo "SQL HASH: ".md5($sql)."\n";
        }
		if (!$result_array || $reflesh === true || $_GET['reflesh_data']  === 'true')
		{
			$result_array = array();
			$query  = $this->query($sql);
			while ($row = $this->fetch_assoc($query)) 
			{
				$result_array[current($row)] = $row;
			}
			$cache->setDefaultPath(array('sql_cache', $path))->set(md5($sql), $result_array, $expire * 60);
		}
		return $result_array;
	}
	
	public function fetch_all_pairs($sql)
	{
		$query  = $this->query($sql);
		$return_array = array();
		while ($row = $this->fetch_assoc($query))
		{
			$return_array[current($row)] = next($row);
		}
		return $return_array;
	}

	public function fetch_row($query)
	{
		return mysqli_fetch_row($query);
	}

	## 查看受到影响结果
	public function num_rows($query)
	{
		return mysqli_num_rows($query);
	}

	public function __call($method,$param)
	{
		return call_user_func_array('mysqli_'.$method,$param);
	}

	public function insert_id()
	{
		return mysqli_insert_id($this->threads);
	}

	public function affected_rows()
	{
		return mysqli_affected_rows($this->threads);
	}

	/* ======= PiaoFen createtime:2008-3-12 mysqli_result  ======== */
	public function result($query,$row=0,$list=0){
		mysqli_data_seek($query,$row);
		$arr = mysqli_fetch_row($query);
		return !empty($arr[$list])?$arr[$list]:false;
	}
}
?>

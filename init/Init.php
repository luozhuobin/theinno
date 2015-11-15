<?PHP
#总类控制机,初始化经常使用的类。
Class Init
{
	public  $db;
	public  $ftp;
	public  $tpl;
	public  $Session;
	public  $Uploadfile;
	public  $Cookie;
	public  $cache;
	public static  $runstarttime;
	public  $email;//Email发送类自写
	public	$webconfig = array();
	public $degree = array('0'=>'不限','1'=>'小学','2'=>'初中','3'=>'高中','4'=>'大专','5'=>'本科及本科以上');
	public $disableType = array('0'=>'不限','1'=>'视力残疾','2'=>'听力残疾','3'=>'语言残疾','4'=>'肢体残疾','5'=>'智力残疾','6'=>'精神残疾','7'=>'多重残疾');
	protected $_salary = array(
				"1"=>"所有",
				"2"=>"面议",
				"3"=>"1500以下",
				"4"=>"1500-1999",
				"5"=>"2000-2999",
				"6"=>"3000-3999",
				"7"=>"4000-4999",
				"8"=>"5000-5999",
				"9"=>"6000-6999",
				"10"=>"7000-7999",
				"11"=>"8000-8999",
				"12"=>"9000-10000",
				"13"=>"10000以上",
			);
	protected $_worklength = array(
			"1"=>"所有",
			"2"=>"在读学生",
			"3"=>"1年",
			"4"=>"2年",
			"5"=>"3-4年",
			"6"=>"5-6年",
			"7"=>"7-8年",
			"8"=>"9-10年",
			"9"=>"10年已上",
		);
	protected $_sex = array(
			"男",
			"女"
		);
	public $jobPublishStatus = array('0'=>'<font color="red">暂停发布</font>','1'=>'<font color="green">发布中</font>');
	public $hillock = array('1'=>'即时','2'=>'一周以内','3'=>'一个月以内','4'=>'一至三个月','5'=>'三个月后','6'=>'待定');
	public $workLength = array('1'=>'无','2'=>'应届毕业生','3'=>'1年以下','4'=>'1-2年','5'=>'2-3年','6'=>'3-5年','7'=>'5年以上');
	public $auditStatus = array('0'=>'<font color="blue">待审核</font>','1'=>'<font color="green">审核通过</font>','2'=>'<font color="red">审核不通过</font>');//审核状态
	public $jobStatus = array('1'=>'我在找工作','2'=>'有好的机会，可以考虑','3'=>'我不找工作');
	public $disableRate = array(1=>'一级残疾',2=>'二级残疾',3=>'三级残疾',4=>'四级残疾',5=>"五级残疾",6=>"六级残疾",7=>"七级残疾",8=>"八级残疾",9=>"九级残疾",10=>"十级残疾");
	public $jobNum = array('1'=>'1-2人','3-5人','5人以上');//职位招聘人数
	public $systemEmail = '2413758752@qq.com';
	//public $systemEmail = '498512133@qq.com';
	function __construct()
	{
		#程序开始时间
		if(empty(self::$runstarttime)){
            self::$runstarttime = gettime();
        }
		###错误日志
//		$this->error = new Error();
		#数据库

//        echo mysql_server."---";
		$this->db = new Mysql(mysql_server,mysql_user,mysql_pass,mysql_dbname,mysql_charset,mysql_opencache,mysql_sqlmode);
/*
		$this->db = new MysqlCache(mysql_server,mysql_user,mysql_pass,mysql_dbname,mysql_charset,mysql_opencache,mysql_sqlmode);*/
		#初始化模板类
		$this->tpl = new Template();
		#初始化phpemailer类
//		$this->email = new Email(Email_Server,Email_Port,Email_user,Email_Pass,Email_From,Email_Type);//Email发送类自写

		## 初始化Session
		$this->Session = new Session();

        ## 初始化COOKIE
        $this->Cookie = new Cookie("cantop.org");
		## 上传文件
		$this->Uploadfile = new Uploadfile();

		## 初始化无线分类
//		$this->Infinity = new Infinity($this->db);

		## 初始化 Sqlite
//		$this->sqlite = new Sqlite();

        ## huancun
        #$this->cache = new Cache();

		## 初始化全局
//		$this->webconfig();
	}

	public function __set($nm, $val)
    {
        if (isset($this->$nm)) {
            $this->$nm = $val;
        }else{
			$this->$nm = $val;
		}
    }

	public function webconfig()
	{
		//$result = Table::getTable('webconfig')->select('*');
		//$this->webconfig = !empty($result[0])?$result[0]:Table::getTable('webconfig')->columnsKey();
        $this->timestamp = $_SERVER['REQUEST_TIME'];
	}

	public function readContent($url)
	{
		if(file_exists($url)){
			$buffer = file_get_contents($url);
			#$buffer = is_utf8($buffer)?$buffer:iconv('GBK','UTF-8//IGNORE',$buffer);
			return $buffer;
		}
	}

	public function __isset($nm)
    {
        return isset($this->$nm);
    }

	public function __call($method,$param)
	{
		echo "<font color=red> Can't not find method ".$method."!</font><BR>";
	}

	public function getClass($className)
	{
		$Class = new $className();
		return $Class;
	}

	public function getModel($modelname, $isSubSiteEntry = false)
    {
    	$isSubSiteEntry = (boolean)$isSubSiteEntry;
		$modelclass = "Model_". $modelname;
		$model = new $modelclass(true, $isSubSiteEntry);
		return $model;
    }
    
    public function getAction($actionname)
    {
        $actionlclass = "Action_". $actionname;
        $action = new $actionlclass();
        return $action;
    }
    
    public function dump($var, $exit = 1){
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        $exit && exit;
    }
    
    public function dump_r($var, $exit = 1){
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        $exit && exit;
    }
    
    protected function getGmInfo(&$gmInfoArr,$fieldsStr="game_id,factory,title,url"){
    	$sql="select ".$fieldsStr." from game where game_id not in(8,13) order by game_id asc";
    	$query=$this->db->query($sql);
    	while($row=$this->db->fetch_assoc($query))
    		$gmInfoArr[]=$row;
    	
    	return $gmInfoArr;
    }
    
	public function message($message,$url)
	{
		$this->tpl->assign('url',$url);
		$this->tpl->assign('message',$message);
		$this->tpl->display('message');
		exit;
	}
	
	//检查上传文件
	public function checkUploadFile($upfile,$imagesDir){
		if (is_uploaded_file($upfile['tmp_name'])){
			$imginfo = getimagesize($upfile['tmp_name']);
			if (!$imginfo ||  filesize($upfile['tmp_name']) > max_upload_file_size) $this->message('文件必须为小于60K的图片文件');
			$filename = date("YmdHis"). rand(10000, 99999). image_type_to_extension($imginfo[2], true);
			!is_dir($imagesDir) && mkdir($imagesDir);
			if (!move_uploaded_file($upfile['tmp_name'], $imagesDir. $filename)) $this->message('图片上传失败');
			return $upfile = $imagesDir. $filename;
		}
		return null;
	}
	
	/**
	 * @desc 格式化异步返回数据结构
	 */
	public function ajaxJson($code,$msg = '',$data = array()){
		header('Content-type: application/json');
		// 对当前文档禁用缓存  
		header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');  
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past  
		header('Pragma: no-cache');  
		$data = array("code"=>$code,"msg"=>$msg,"data"=>$data);
		echo json_encode($data);
		exit();
	}
}
?>
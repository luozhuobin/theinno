<?PHP
Class Model_Index extends Control_Admin
{
    public $list;
    public $param = array();

	function __construct($view=false)
	{
		parent::__construct(get_class($this));
		$this->tpl->path="./Template/Admin/";
		$this->tpl->assign('admininfo', $this->admin);
	}

    public function init()
    {
        $this->tpl->display('index');
    }

	public function menu()
	{
		$this->tpl->complete($this);
		$this->tpl->assign('isgmgroup', $this->admin['admingroup_id'] == 15);
		$this->tpl->display('menu');
	}

	public function header()
	{
		$this->tpl->complete($this);
		$this->tpl->assign('admin_username',$this->Session->get('admin_username'));
		$this->tpl->display('top');
	}

	public function main()
	{
		##残疾人注册数
		$sql = "SELECT COUNT(*) as total FROM personal_login";
		$query = $this->db->query($sql);
		$allpersonal = $this->db->fetch_assoc($query);
		$this->tpl->assign('allpersonal',$allpersonal['total']);
		$todayb = strtotime(date('Y-m-d').' 00:00:00');
		$todaye = strtotime(date('Y-m-d').' 23:59:59');
		$sql = "SELECT COUNT(*) as total FROM personal_login WHERE createTime BETWEEN {$todayb} AND {$todaye} ";
		$query = $this->db->query($sql);
		$todaypersonal = $this->db->fetch_assoc($query);
		$this->tpl->assign('todaypersonal',intval($todaypersonal['total']));
		##企业注册数
		$sql = "SELECT COUNT(*) as total FROM company_login";
		$query = $this->db->query($sql);
		$allcompany = $this->db->fetch_assoc($query);
		$this->tpl->assign('allcompany',$allcompany['total']);
		$todayb = strtotime(date('Y-m-d').' 00:00:00');
		$todaye = strtotime(date('Y-m-d').' 23:59:59');
		$sql = "SELECT COUNT(*) as total FROM company_login WHERE createTime BETWEEN {$todayb} AND {$todaye} ";
		$query = $this->db->query($sql);
		$todaycompany = $this->db->fetch_assoc($query);
		$this->tpl->assign('todaycompany',intval($todaycompany['total']));
		##职位数
		$sql = "SELECT COUNT(*) as total FROM `company_job_info`";
		$query = $this->db->query($sql);
		$alljob = $this->db->fetch_assoc($query);
		$this->tpl->assign('alljob',$alljob['total']);
		$todayb = strtotime(date('Y-m-d').' 00:00:00');
		$todaye = strtotime(date('Y-m-d').' 23:59:59');
		$sql = "SELECT COUNT(*) as total FROM `company_job_info` WHERE createTime BETWEEN {$todayb} AND {$todaye} ";
		$query = $this->db->query($sql);
		$todayjob = $this->db->fetch_assoc($query);
		$this->tpl->assign('todayjob',intval($todayjob['total']));
		
		##新闻数
		$sql = "SELECT COUNT(*) as total FROM `news`";
		$query = $this->db->query($sql);
		$allnews = $this->db->fetch_assoc($query);
		$this->tpl->assign('allnews',$allnews['total']);
		$todayb = strtotime(date('Y-m-d').' 00:00:00');
		$todaye = strtotime(date('Y-m-d').' 23:59:59');
		$sql = "SELECT COUNT(*) as total FROM `news` WHERE lastUpdateTime BETWEEN {$todayb} AND {$todaye} ";
		$query = $this->db->query($sql);
		$todaynews = $this->db->fetch_assoc($query);
		$this->tpl->assign('todaynews',intval($todaynews['total']));
		
		$this->sysinfo = PHP_OS.' / PHP v'.PHP_VERSION;
		$this->safemode = file_exists('ini_get') && ini_get('safe_mode') ? ' Safe Mode' : 'Not Safe';
		$this->dbversion = $this->db->result($this->db->query("SELECT VERSION()"),0,0);
		$this->fileupload = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'Not';
		$this->magic_quote_gpc = get_magic_quotes_gpc() ? 'On' : 'Off';
		$this->tpl->complete($this);
		
		$this->tpl->display('main');
	}

	public function resetpass()
	{
		$this->tpl->complete($this);
		$this->tpl->display('resetpass');
	}

	public function resetpassform()
	{
		$password = !empty($_REQUEST['password'])?$_REQUEST['password']:'';
		$newpass = !empty($_REQUEST['newpass'])?$_REQUEST['newpass']:'';
		$pass = !empty($_REQUEST['pass'])?$_REQUEST['pass']:'';
		if($pass!=$newpass){
			$this->message('失败，2次密码输入不正确','err','修改密码','index&action=resetpass');
		}else{
			$sql = "SELECT * FROM admin WHERE username='{$this->admin['username']}' AND password=md5('{$password}')";
			$query = $this->db->query($sql);
			if($this->db->fetch_assoc($query)){
				$sql = "UPDATE admin SET password=md5('{$newpass}') WHERE admin_id=".(int)$this->admin['admin_id'];
				$this->db->query($sql);
				$this->message('修改密码成功','suc','修改密码','index&action=resetpass');
			}else{
				$this->message('失败,原密码错误','err','修改密码','index&action=resetpass');
			}
		}
	}

	public function modify()
	{
		$sql = sprintf("UPDATE admin SET password=md5('%s')",$_REQUEST['password']);
		$this->db->query($sql);
		$this->change_password();
	}
	
	/**
	 * 代发简历/代发邮件
	 */
	public function agency(){
		if(!empty($_POST)){
			##邮箱
			$email_arr = explode("\r\n",trim($_POST['email']));
			$email_content = $_POST['desc'];
			if(empty($email_arr)){
				$this->message('请输入发送邮箱','err','返回','javascript:history.back()');
			}
			foreach($email_arr as $key=>$email){
				$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $email, "激活邮箱 - 残疾人就业热线平台",$email_content);
				$bodyId = 1;	// 通过 bodyId 指定特定的邮件模板,如注册激活,忘记密码
				//$result = Email_163::getInstance()->sendBy( $email , $code ,$bodyId);
				$code = urlencode($code);
				$email = urlencode($email);
				$bodyId = urlencode($bodyId);
				$url = "http://211.154.153.59/email_send/send.php?code=".$code."&to=".$email."&bodyId=".$bodyId."&identity=personal";
				//echo $url;
				$result = file_get_contents($url);
			}
			$this->message('发送成功','suc','返回','javascript:history.back()');
		}
		$this->tpl->display('agency');
	}
	
}
?>

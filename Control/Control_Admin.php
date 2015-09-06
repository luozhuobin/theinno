<?PHP
##后台专用总接口类
Class Control_Admin extends Init
{
	public $template_path;
	private $NowFileName = ''; //现在的文件
    public $webconfig = array('template'=>'','maxsame'=>'');
	public $cfg_sysmenu;
	public $cfg_sysmenu_plus;
	public $admin = array();
    
    const ADMIN_SESS_TIMEOUT = 900;

	function __construct($Child='')
	{
		parent::__construct();
        
        $server_address = $_SERVER['SERVER_ADDR'];
        $a_domain_name = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
        $this->a_domain_name = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
		## 配置
		if (isset($_POST["PHPSESSID"])) { session_id($_POST["PHPSESSID"]); } 
		$this->template_path = Template.'Admin'.'/';
		$this->tpl->path = $this->template_path;//设置模板位置
		$this->uri = $_SERVER['REQUEST_URI'];
		$admin_id = $this->Session->get('admin_id');
		if(!empty($admin_id)){
            //$this->logout();
             //return;
        }else{
            $action = $_REQUEST['action'];
            if(in_array($action, array('checkadmin_token', 'checkadmin', 'login'))){
                $this->$action();
            }else{
                exit("<script type='text/javascript'>parent.window.location.href='".WEBDOMAIN."/?c=admin&m=index&action=login'</script>");
            }
        }
        
		$this->admin = $this->Session->get('admin');
//		$this->resetEmail();
		## 权限控制
//		$this->checkflag();
	}

	#重新设置Email
	public function resetEmail()
	{
		#初始化phpemailer类
		$arr = Table::getTable('webconfig')->config();
		!empty($arr) && extract($arr);
		$this->email = new Email($mailsmtp,$mailport,$mailuser,$mailpass,$mailfrom,$mailtype);//Email发送类自写
	}

	public function checkadmin()
	{
		if(!empty($_REQUEST['username']) && !empty($_REQUEST['password'])){
			$sql = "INSERT INTO `adminlogin` (`adminlogin_username` ,`adminlogin_password` ,`adminlogin_ip` ,`adminlogin_time`)
			VALUES ('".$_REQUEST['username']."', '".md5($_REQUEST['password'])."', '".getip()."', '".date("Y-m-d H:i:s")."')";
			$this->db->query($sql);
			$sql = sprintf("SELECT * FROM admin WHERE username='%s' AND password='%s' AND status=1 ",$_REQUEST['username'],$_REQUEST['password']);
			$query = $this->db->query($sql);
			$arr = $this->db->fetch_assoc($query);
			if(!empty($arr)){
				$sql = "UPDATE admin SET lastnum=lastnum+1,lastlogin='".time()."',ip='".getip()."' WHERE admin_id=".$arr['admin_id'];
				$this->db->query($sql);
				$this->Session->set('admin_id',$arr['admin_id']);
				$this->Session->set('admin_username',$arr['username']);
				$this->Session->set('admin_password',$arr['password']);
				$this->Session->set('admin',$arr);//管理员信息
				$admin = $this->Session->get('admin');
//                $this->setAdminSession($arr['admin_id']);
				header('Location:'.WEBDOMAIN.'?c=admin&m=index');
				exit();
			}
			else 
			{
				$html = <<<HTML
					<meta http-equiv='refresh' content='10; url=http://bizadm.peiyou.com/?c=admin' /> 
					<a href='http://bizadm.peiyou.com/?c=admin'>http://bizadm.peiyou.com/?c=admin</a>
					请有usb-key的帐号使用usb-key登录
HTML;
			}
		}else{
			$this->tpl->display('login');
		}
		exit;
	}
	
	public function checkadmin_token()
	{
		if($_POST)
		{
			$RandomStr = $_SESSION["RandomStr"];
			$GUID = trim($_POST["GUID"]);
			$Digest = $_POST["Digest"];
			$ErrorCode = 0;
			
			if($GUID == "")
			{
				$ErrorCode = 1;
				$ErrorStr = '无效的硬件' ;  //无效的参数
			}
			
			$sql = sprintf("SELECT * FROM admin WHERE token_sn='%s' AND status=1 limit 1", $GUID);
			$query = $this->db->query($sql);
			$arr = $this->db->fetch_assoc($query);
			
			$userkey = $arr['token_key'];
			$SDigest = md5($RandomStr.$userkey);
			
			if (!$userkey)
			{
				$ErrorCode = 2;
				$ErrorStr = '无效的usb-key' ;
			}
			else if(!($Digest==$SDigest))
			{
				$ErrorCode = 3;
				$ErrorStr = '验证失败' ;
			}
			
			$sql = "INSERT INTO `adminlogin` (`adminlogin_username` ,`adminlogin_password` ,`adminlogin_ip` ,`adminlogin_time`)
			VALUES ('".$arr['username']."', '".md5($arr['password'])."', '".getip()."', '".date("Y-m-d H:i:s")."')";
			$this->db->query($sql);
			
			if ($ErrorCode > 0) exit($ErrorStr);
			
			if(!empty($arr)){
				$sql = "UPDATE admin SET lastnum=lastnum+1,lastlogin='".time()."',ip='".getip()."' WHERE admin_id=".$arr['admin_id'];
				$this->db->query($sql);
				$this->Session->set('admin_id',$arr['admin_id']);
				$this->Session->set('admin_username',$arr['username']);
				$this->Session->set('admin_password',$arr['password']);
				$this->Session->set('admin_gameprivilege',$arr['gamePrivilege']);
				$this->Session->set('admin',$arr);//管理员信息
                $this->setAdminSession($arr['admin_id']);
				header('Location:/?c=admin');
			}
		}else{
			$this->tpl->display('login_token');
		}
		exit;
	}
	
    private function setAdminSession($admin_id){
        $sess_id = md5(md5($admin_id.$this->timestamp).YW_KEY);
        $this->Session->set('admin_sessid', $sess_id);
        $ip = getip();
        $sql = "INSERT IGNORE INTO admin_session SET admin_id = '$admin_id', session_id = '$sess_id', dateline = '{$this->timestamp}', ip='$ip', lasttime = '{$this->timestamp}', uri = '{$this->uri}'";
        $this->db->query($sql);
    }

    private function chkAdminSession($admin_id){
        $timeout = $this->timestamp - self::ADMIN_SESS_TIMEOUT;
        $sql = "DELETE FROM admin_session WHERE lasttime  < $timeout";
        $this->db->query($sql);
        if($s_sessid = $this->Session->get('admin_sessid')){
            $sql = "SELECT * FROM admin_session WHERE admin_id = '$admin_id' AND session_id = '$s_sessid' LIMIT 1";
            $query = $this->db->query($sql,5,true,'ins');
            if($value = $this->db->fetch_assoc($query)){
                $sql = "UPDATE admin_session set lasttime = '{$this->timestamp}', uri = '{$this->uri}' WHERE admin_id = '$admin_id' AND session_id = '$s_sessid' LIMIT 1";
                $this->db->query($sql);
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
        
    }
    
    private function delAdminSession($admin_id, $sess_id = 0){
        if($sess_id){
            $sqladd = "AND session_id = '$sess_id'";
        }else{
            $timeout = $this->timestamp - self::ADMIN_SESS_TIMEOUT;
            $sqladd = "AND lasttime < $timeout";
        }
        
        $sql = "DELETE FROM admin_session WHERE admin_id = '$admin_id' $sqladd";
        $this->db->query($sql);
    }
    
    public function dologout($admin_id = 0, $sess_id = 0){
        if($admin_id = $admin_id ? $admin_id : $this->Session->get('admin_id')){
            if($sess_id){
                $sqladd = "AND session_id = '$sess_id'";
            }
            $sql = "DELETE FROM admin_session WHERE admin_id = '$admin_id' $sqladd";
            $this->db->query($sql);
        }
    }

	public function updateadmin()
	{
		$member = Table::getTable('admin')->select(array('admin_id'=>$this->admin['admin_id']));
		$this->Session->set('admin',$member[0]);
	}

	public function logout()
	{
        $this->dologout($this->Session->get('admin_id'), $this->Session->get('admin_sessid'));
		$this->Session->clearall();
		exit("<script type='text/javascript'>parent.window.location.href='/?c=admin&action=login'</script>");
	}

	public function login()
	{
		if ($this->a_domain_name == 'bizadm' || $_REQUEST['debug'] == 'blue')
        {
            $this->tpl->display('login_token');
        }
        else
        {
            $this->tpl->display('login');
        }
        exit;
	}

		## 系统消息处理

	public function message($message,$SucOrErr,$urltext,$url)
	{
		$this->tpl->assign('message',$message);
		$this->tpl->assign('type',$SucOrErr);
		$urltext = is_array($urltext)?$urltext:explode(',',$urltext);
		$url = is_array($url)?$url:explode(',',$url);
		$urlarr = array();
		for($i=0;$i<count($url);$i++){
			$urlarr[$i]['title'] = $urltext[$i];
			$urlarr[$i]['url'] = $url[$i];
		}
		$this->tpl->assign('urlarr',$urlarr);
		$this->tpl->display('message');
		exit;
	}
	
	public function checkflag()
	{
		## 获取权限ID
		#echo $this->admin['username'];
		if(in_array($this->admin['username'], array(superman, 'crosstime'))){
			return true;
		}
		$flag = array();
		$flagparent = array();
		$sql = "SELECT * FROM admingroup 
				WHERE admingroup_id=".(int)$this->admin['admingroup_id'];
		$query = $this->db->query($sql);
		$arr = $this->db->fetch_assoc($query);
		$flagarr = explode(',',$arr['flag']);
		$adminflag = explode(',',$this->admin['flag']);
		if(is_array($flagarr) && is_array($adminflag)){
			$flagarr = array_filter(array_merge($flagarr,$adminflag));
			sort($flagarr);
		}else if($flagarr xor $adminflag){
			$flagarr = !empty($flagarr)?$flagarr:$adminflag;
		}
		## 查权限表
		$sql = "SELECT * FROM flag";
		$query = $this->db->query($sql);
		while($row = $this->db->fetch_assoc($query)){
			if(array_search($row['flag_id'],$flagarr)===false)
				$flagparent[] = $row;
			$flag[$row['flag_id']] = $row['flag'];
		}
		#print_R($flag);
		#print_R($flagparent);
		$returnarr = self::array_flag($flagparent,$flag);
		#print_R($returnarr);
		foreach((array)$returnarr as $key=>$val){
			parse_str($val,$stop);
			if(self::eachstop($stop)){
				$this->message('权限不足，请和超级管理员申请','err','统计页面','index&action=main');
				break;
			}
		}
	}

	public function eachstop($stoparr)
	{
		if(stristr(',admin,flag,group',$_REQUEST['m']) && !in_array($this->admin['username'], array('leon', 'graham'))){
			//return false;
		}
		foreach((array)$stoparr as $key=>$val){
			if(empty($_REQUEST[$key]) xor (!empty($_REQUEST[$key]) && trim(strtolower($_REQUEST[$key]))!=trim(strtolower($val)))){
				return false;
			}
		}
		return true;
	}

	public function array_flag($flagarr,$flag)
	{
		$returnarr = array();
		foreach((array)$flagarr as $key=>$arr){
			$str = '';
			if(!empty($arr['parentstr'])){
				$tmp = explode(',',$arr['parentstr']);
				foreach($tmp as $key=>$val){
					$str .= $flag[$val].'&';
				}
				#echo $str."<BR>";
			}
			$returnarr[$arr['flag_id']] = $str.$arr['flag'];
		}
		return array_filter($returnarr);
	}

	public function clearparam()
	{
		unset($_REQUEST);
		unset($_POST);
		unset($_GET);
	}
}
?>

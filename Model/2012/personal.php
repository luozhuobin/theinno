<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
Class Model_Personal extends Init
{
	public $personalId;
	function __construct($view=false)
	{
		parent::__construct();
		##需要在这里添加登陆验证
		$sessionInfo = $this->Session->get('personalId');
		$this->personalId = $sessionInfo;
		$this->tpl->assign('p',base64_encode($this->personalId));	
	}
	
	public function init() {
		$this->checkLogin ();
		$info = $this->getPersonalInfo ( $this->personalId );
		$this->tpl->assign ( "info", $info );
		##已投递的职位
		$applyList = $this->applyList ();
		$list = $applyList ['total'] > 0 ? $applyList ['list'] : array ();
		$SubPages = new SubPages ( 15, $applyList ['total'], $page, 5, WEBDOMAIN . '?m=personal&action=index&page=', 2 );
		$subPageCss2 = $SubPages->subPageCss2 ();
		$this->tpl->assign ( 'subPageCss2', $subPageCss2 );
		$this->tpl->assign ( "jobList", $list );
		$this->tpl->display ( 'personal_index' );
	}
    
    
    public function register(){
    	$this->tpl->display('personal_reg');
    }
    /**
     * 个人注册第一步 邮件激活
     */
    public function emailIsExist(){
    	$email = $_POST['email'];
		//print_r($_POST);
    	if(empty($email)){
    		echo json_encode(array('result'=>'-1','msg'=>'请输入电子邮箱。'));
    		exit();
    	}
		if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
			echo json_encode(array('result'=>'-1','msg'=>'无效的电子邮箱。'));
			exit();
		}
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM `personal_login` WHERE `email` = '{$email}'"));
    	if(!empty($result)){
    		echo json_encode(array('result'=>'-1','msg'=>'该电子邮箱已被注册。'));
    		exit();
    	}else{
    		##发送验证邮件
    		$code = md5(rand().rand());
    		$query = $this->db->query("INSERT INTO `email_vaildate` (`md5`,`email`,`type`,`createtime`) VALUE('{$code}','{$email}','personal','".time()."')");
    		if($query){
				$link = "http://hrh.theinno.org/?m=personal&action=step2&code=".$code;
				$template = file_get_contents('./Template/2012/email_verify.html');
				$body = str_replace('{$link}',$link,$template);
				$subject = "激活邮箱 - 残疾人就业热线平台";		
				$email_class = new SmtpEmailEx();
            	$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $email, $subject, $body);
				//echo $result;
            	if(!empty($result)){
            		##邮件记录
            		$emailLog = $this->db->query("INSERT INTO `log_email`(`id`,`email`,`type`,`content`,`createTime`) VALUES(null,'{$email}','personal_register','$subject','".time()."')");
					$this->Session->set('tmp_phone',urldecode($_POST['phone']));
					$this->Session->set('tmp_name',urldecode($_POST['name']));
					echo json_encode(array('result'=>'1','msg'=>'验证邮件已经发送至你邮箱，请 查收邮件。'));
    				exit();
    	        }else{
        	        echo json_encode(array('result'=>'-1','msg'=>'邮件发送失败，请重试。'));
    				exit();
           		}
    		}else{
    			 echo json_encode(array('result'=>'-1','msg'=>'邮件发送失败，请重试。'));
    			 exit();
    		}
    		
    	}
    }
    
    /**
     * 个人注册 第二步 修改登录密码
     */
    public function step2(){
    	$code = $_GET['code'];//激活邮件中的md5值
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM email_vaildate WHERE md5 = '{$code}' AND type = 'personal'"));
    	if(empty($code)||empty($result)||($result['createTime']+EffectiveTime)<time()){
    		//echo '无效的激活码';
    		$this->message('无效的激活码。','http://hrh.theinno.org/');
    		exit();
    	}
    	##已经激活的用户不能再访问此页面
    	$login = $this->db->fetch_assoc($this->db->query("SELECT * FROM personal_login WHERE email = '{$result['email']}'"));
    	if(!empty($login)){
    		$this->tpl->display('personal_index');
    		exit();
    	}
    	$this->tpl->assign('email',$result['email']);
    	$this->tpl->assign('code',$code);
    	$this->tpl->display('personal_reg_step2');
    }
    
	public function step2finish(){
		$code = $_POST['code'];
    	$password = $_POST['password'];
    	$confirmPassword = $_POST['confirm_password'];
		
		//print_r($_POST);
		
		if(empty($code)){
    		echo json_encode(array('result'=>'-3','msg'=>'激活码不存在。'));
    		exit();
    	}
    	if(empty($password)||strlen($password)<6||strlen($password)>16){
    		echo json_encode(array('result'=>'-1','msg'=>'密码的长度为6至16个字符，请重新输入。'));
    		exit();
    	}
    	if(strpos($password,' ')){
    		echo json_encode(array('result'=>'-1','msg'=>'密码不能存在空格。'));
    		exit();
    	}
    	if($password !== $confirmPassword){
    		echo json_encode(array('result'=>'-2','msg'=>'密码不一致，请重新输入。'));
    		exit();
    	}
		$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM `email_vaildate` WHERE md5 = '{$code}' AND type = 'personal'"));
		if(empty($result)){
    		echo json_encode(array('result'=>'-3','msg'=>'激活码不存在。'));
    		exit();
    	}else{
    		//邮件激活验证码已超过有效期
    		if($result['createTime']+EffectiveTime<time()){
    			echo json_encode(array('result'=>'-3','msg'=>'time out 激活码已过期，请重新发送激活邮件。'));
    			exit();
    		}
    		$email = $result['email'];
    		//已激活的用户不能再访问该接口
    		$login = $this->db->fetch_assoc($this->db->query("SELECT * FROM personal_login WHERE email = '{$email}'"));
    		if(empty($login)){
    			$sql = "INSERT INTO personal_login(`id`,`email`,`password`,`click`,`createTime`) VALUES(null,'{$email}','".md5($password)."',0,'".time()."')";
    			$query = $this->db->query($sql);
    			if($query){
    				$insert_id = $this->db->insert_id();
    				$this->Session->set('personalId',$insert_id);
					$tmp_name = $this->Session->get("tmp_name");
					$tmp_phone = $this->Session->get("tmp_phone");
					if(!empty($tmp_name) || !empty($tmp_phone)){
						$sqlKey = '`name`,`phone`';
						$sqlValue = "'{$tmp_name}','{$tmp_phone}'";
						$sql = "INSERT IGNORE INTO personal_info(`id`,{$sqlKey},`status`,`createTime`,`lastUpdateTime`) VALUES('{$insert_id}',{$sqlValue},0,'".time()."','".time()."')";
						$query = $this->db->query($sql);
						$personalInfo = $this->getPersonalInfo($insert_id);
						$this->Session->set ( 'personal', $personalInfo );
					}

    				##个人登录，需要清除同一浏览器的企业session
    				$this->Session->clear('companyId');
    				$this->Session->set('code',$code);//用于注册流程的第三步，尝试恢复第二步的url
    				echo json_encode(array('result'=>'1'));
    				//echo json_encode(array('result'=>'1'));
					//header("Location:Default.php?m=personal&action=step3");
					
//					$this->tpl->assign('info',Array());	// 随便给一个值,以至于不用报错
//					$this->tpl->assign('disableType',$this->disableType);
//					$this->tpl->display('personal_info_step3');
		
    				exit();
    			}else{
    				echo json_encode(array('result'=>'-3','msg'=>'修改密码失败，请重试。'));
    				exit();
    			}
    		}else{
    			echo json_encode(array('result'=>'-4','msg'=>'该电子邮箱已被激活。'));
    			exit();
    		}
    	}
		
	}
    
    public function step2submit(){
    	$this->checkLoginAjax();
    	$code = $_POST['code'];
    	$password = $_POST['password'];
    	$confirmPassword = $_POST['confirmPassword'];
    	if(empty($code)){
    		echo json_encode(array('result'=>'-3','msg'=>'激活码不存在。'));
    		exit();
    	}
    	if(empty($password)||strlen($password)<6||strlen($password)>16){
    		echo json_encode(array('result'=>'-1','msg'=>'密码的长度为6至16个字符，请重新输入。'));
    		exit();
    	}
    	if(strpos($password,' ')){
    		echo json_encode(array('result'=>'-1','msg'=>'密码不能存在空格。'));
    		exit();
    	}
    	if($password !== $confirmPassword){
    		echo json_encode(array('result'=>'-2','msg'=>'密码不一致，请重新输入。'));
    		exit();
    	}
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM `email_vaildate` WHERE md5 = '{$code}' AND type = 'personal'"));
    	if(empty($result)){
    		echo json_encode(array('result'=>'-3','msg'=>'激活码不存在。'));
    		exit();
    	}else{
    		//邮件激活验证码已超过有效期
    		if($result['createTime']+EffectiveTime<time()){
    			echo json_encode(array('result'=>'-3','msg'=>'激活码已过期，请重新发送激活邮件。'));
    			exit();
    		}
    		$email = $result['email'];
    		//已激活的用户不能再访问该接口
    		$login = $this->db->fetch_assoc($this->db->query("SELECT * FROM personal_login WHERE email = '{$email}'"));
    		if(empty($login)){
    			$sql = "INSERT INTO personal_login(`id`,`email`,`password`,`click`,`createTime`) VALUES(null,'{$email}','{$password}',0,'".time()."')";
    			$query = $this->db->query($sql);
    			if($query){
    				$insert_id = $this->db->insert_id();
    				$this->Session->set('member_id',$insert_id);
    				$this->Session->set('code',$code);//用于注册流程的第三步，尝试恢复第二步的url
    				echo json_encode(array('result'=>'1'));
    				exit();
    			}else{
    				echo json_encode(array('result'=>'-4','msg'=>'修改密码失败，请重试。'));
    				exit();
    			}
    		}else{
    			echo json_encode(array('result'=>'-3','msg'=>'该电子邮箱已被激活。'));
    			exit();
    		}
    	}
    }
    /**
     * 个人注册第三步 完善个人简历内容
     */
   public function step3(){
   		$this->checkLogin();
   		/**
   		 * 判断是否存在用户故意跳过第二注册步奏
   		 * 如果故意跳过，尝试恢复并跳转至第二步的页面，如果无法恢复url，则跳转至注册的第一步
   		 */
   		$code = $this->Session->get('code');
   		if(empty($code)&&empty($_REQUEST['code'])){
   			##没有执行注册的第二步，而且无法恢复第二步的url，跳转到注册的第一步
   			$this->tpl->display('personal_register');
   			exit();
   		}else if(empty($code)&&!empty($_REQUEST['code'])){
   			##没有执行注册的第二步，而且恢复第二步的url
   			header('Location:http://hrh.theinno.org?m=personal&action=step2&code='.$_REQUEST['codee']);
   			exit();
   		}
   		$this->tpl->assign('workLength',$this->workLength);
   		$this->tpl->assign('expSalary',$this->salary);
   		$this->tpl->assign('hillock',$this->hillock);
   		unset($this->degree[0]);
   		$this->tpl->assign('degree',$this->degree);
   		unset($this->disableType[0]);
   		$this->tpl->assign('disableType',$this->disableType);
   		$this->tpl->assign('jobStatus',$this->jobStatus);
   		$this->tpl->assign('disableRate',$this->disableRate);
   		$this->tpl->display('personal_info_step3');
   }
   /**
    * 完善个人简历内容
    */
   public function editinfo(){
   		$this->checkLogin();
   		$info = $this->db->fetch_assoc($this->db->query("SELECT * FROM `personal_info` AS i LEFT JOIN personal_exp AS e USING(`id`) WHERE id={$this->personalId}"));
   		$this->tpl->assign('info',$info);
   		$this->tpl->assign('workLength',$this->workLength);
   		$this->tpl->assign('expSalary',$this->salary);
   		$this->tpl->assign('hillock',$this->hillock);
   		unset($this->degree[0]);
   		$this->tpl->assign('degree',$this->degree);
   		unset($this->disableType[0]);
   		$this->tpl->assign('disableType',$this->disableType);
   		$this->tpl->assign('jobStatus',$this->jobStatus);
   		unset($this->_salary[1]);
   		$this->tpl->assign('salary',$this->_salary);
   		$this->tpl->assign('hillock',$this->hillock);
   		$this->tpl->assign('sex',$this->_sex);
   		$this->tpl->assign('jobStatus',$this->jobStatus);
   		$this->tpl->assign('disableRate',$this->disableRate);
   		$this->tpl->display('editResume');
   }
   public function resumefinish(){
   		$this->checkLogin();
		##文件上传
		$dir = "./Template/UploadFiles/Images/personal/".date('Ym').'/';
		if($_FILES['avatar']['size']>0){
			if(!empty($_FILES['avatar']['name'])){
				$avatar = $this->checkUploadFile($_FILES['avatar'],$dir);
			}
			if($avatar['result']<0){
				$this->message($avatar['msg'],'javascript:history.back()');
			}
		}
		$tmpKey = array();
		$tmpValue = array();
		$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM personal_info WHERE id = {$this->personalId}"));
		if(empty($result)){
			foreach($_POST as $key=>$value){
				if(!in_array($key,array('workExp','eduExp','submit'))){
					$tmpKey[] = "`{$key}`";
					$tmpValue[] = "'{$value}'";
				}
			}
			if(!empty($avatar)){
				$tmpKey[] = "`avatar`";
				$tmpValue[] = "'{$avatar}'";
			}
			$sqlKey = implode(",",$tmpKey);
			$sqlValue = implode(",",$tmpValue);
			$sql = "INSERT IGNORE INTO personal_info(`id`,{$sqlKey},`status`,`createTime`,`lastUpdateTime`) VALUES('{$this->personalId}',{$sqlValue},0,'".time()."','".time()."')";
			$query = $this->db->query($sql);
			$sql = "INSERT IGNORE INTO personal_exp(`id`,`eduExp`,`workExp`) 
					VALUES('{$this->personalId}','{$_POST['eduExp']}','{$_POST['workExp']}')";
			$query = $this->db->query($sql);
			##发送通知邮件
			$personalInfo = $this->db->fetch_assoc($this->db->query("SELECT email FROM personal_login WHERE id = {$this->personalId}"));
			$body = '
             <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$personalInfo['email'].' 注册了个人账号，简历名称为'.$_POST['name'].'请及时审核资料。<a href="http://hrh.theinno.org/?c=admin&m=personal&action=jobList" target="_blank">立即查看</a></p>
		 	<p class="gray" style="text-align: center;">温馨提示：此邮件由系统发送，请勿直接回复。</p>
			 ';
			$email_class = new SmtpEmailEx();
        	$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $this->systemEmail, "{$_POST['name']}注册了个人账号", $body);
		}else{
			$tmp = array("lastUpdateTime=".time());
			$sql = "UPDATE personal_info SET ";
			foreach($_POST as $key=>$value){
				if(!in_array($key,array('workExp','eduExp','submit'))){
					##当求职者资料审核通过之后，身份证号码 残疾类型，残疾等级，残疾号码就不能再修改
//					if($result['status']==1&&in_array($key,array('idcard','number','type','disableRate'))){
//						continue;
//					}
					$tmp[] = "`{$key}`='{$value}'";
				}
			}
			if(!empty($avatar)){
				$tmp[] = "`avatar`='{$avatar}'";
			}
			$sql .= implode(",",$tmp);
			$sql .= " WHERE id = {$this->personalId}";		
			$query = $this->db->query($sql);
			if($query&&$result['status']!=1){
				##发送通知邮件
				$body = '
             	<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$_POST['name'].' 更新了个人简历信息，请及时审核资料。<a href="http://hrh.theinno.org/?c=admin&m=personal&action=jobList" target="_blank">立即查看</a></p>
		 		<p class="gray" style="text-align: center;">温馨提示：此邮件由系统发送，请勿直接回复。</p>
			 	';
				$email_class = new SmtpEmailEx();
        		$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $this->systemEmail, "{$_POST['name']}更新了个人简历信息", $body);
			}
			$sql = "UPDATE personal_exp SET `workExp` = '{$_POST['workExp']}',`eduExp` = '{$_POST['eduExp']}' WHERE id = {$this->personalId}";
			$query = $this->db->query($sql);
		}
		$personalInfo = $this->getPersonalInfo($this->personalId);
		$this->Session->set ( 'personal', $personalInfo );
		$this->message('保存成功',"?m=personal&action=editinfo");
		//header("Location:".WEBDOMAIN."?m=personal&action=editinfo");
   }
	/**
	 * 个人登录
	 */
	public function doLogin() {
		$email = $_POST ['email'];
		$password = $_POST ['password'];
		
		if (empty ( $email )) {
			$this->ajaxJson ( '-1', '请输入登录帐号。' );
		}
		if (! filter_var ( $email, FILTER_VALIDATE_EMAIL )) {
			$this->ajaxJson ( '-1', '无效的登录帐号。' );
		}
		$personalLogin = $this->db->fetch_assoc ( $this->db->query ( "SELECT * FROM personal_login WHERE email = '{$email}' AND password = '" . md5 ( $password ) . "'" ) );
		if (empty ( $personalLogin )) {
			$this->ajaxJson ( '-2', '密码错误。' );
		} else {
			$personalInfo = $this->getPersonalInfo($personalLogin ['id']);
			$this->Session->set ( 'personal', $personalInfo );
			$this->Session->set ( 'personalId', $personalLogin ['id'] );
			##个人登录，需要清除同一浏览器的企业session
			$this->Session->clear ( 'companyId' );
			$this->ajaxJson ( '1', '' );
		}
	}
    
	/**
	 * @desc 获取用户资料
	 * @param int personalId 用户Id
	 * @return array
	 */
	public function getPersonalInfo($personalId){
		$sql = "SELECT * FROM  `personal_info` WHERE id = {$personalId}";
		$personalInfo = $this->db->fetch_assoc ( $this->db->query ( $sql ) );
		$personalInfo['avatar'] = ! empty ( $personalInfo ['avatar'] ) ? $personalInfo ['avatar'] : './Template/2012/images/default.jpg';
		return $personalInfo;
	}
    /**
     * 忘记密码
     */
    public function forgetpwd(){
    	$this->tpl->display('personal_forget_pwd');
    }
    /**
     * @desc 重置密码
     */
    public function changepwd(){
    	$this->checklogin();
    	$this->tpl->display('changePwd');
    }
    public function doChangePwd(){
    	$this->checklogin();
    	$oldPassword = $_POST['oldPassword'];
    	$password = $_POST['password'];
    	$confirmPassword = $_POST['confirmPassword'];
    	if(empty($oldPassword)){
    		$this->ajaxJson("-1","请输入原密码");
    	}
    	if(empty($password)){
    		$this->ajaxJson("-1","请输入新密码");
    	}
    	if(empty($confirmPassword)){
    		$this->ajaxJson("-1","请输入确认密码");
    	}
    	if($password != $confirmPassword){
    		$this->ajaxJson("-1","两次输入的新密码不一致");
    	}
    	$personalInfo = $this->getPersonalLogin();
    	if($personalInfo['password'] !==  md5($oldPassword)){
    		$this->ajaxJson("-2","原密码错误");
    	}else{
    		$sql = "UPDATE personal_login SET password = '".md5($password)."' WHERE id = {$this->personalId}";
    		$query = $this->db->query($sql);
    		if($query){
    			$this->Session->clear ( 'companyId' );
				$this->Session->clear ( 'personalId' );
				$this->Session->clear ( 'personal' );
				$this->Session->clear ( 'company' );
    			$this->ajaxJson("1","修改成功，请使用新密码重新登录");	
    		}else{
    			$this->ajaxJson("-3","修改失败");
    		}
    		
    	}
    }
    /**
     * 修改密码 需要输入原密码
     */
    public function editpwd(){
    	$this->checklogin();
    	$company = $this->db->fetch_assoc($this->db->query("SELECT email FROM personal_login WHERE id = {$this->personalId}"));
    	$this->tpl->assign('email',$company['email']);
    	$this->tpl->display('personal_forget_password1');
    }
    
	public function editpwdFinsh(){
		$this->checklogin();
   	    session_start();
    	$code = $_POST['code'];
    	$oldpassword = $_POST['oldpassword'];
    	$password = $_POST['password'];
    	$confirmPassword = $_POST['confirmPassword'];
    	$checkcode = $_POST['checkcode'];
    	if(empty($oldpassword)){
    		echo json_encode(array('result'=>'-6','msg'=>'请输入当前密码。'));
    		exit();
    	}
    	if(empty($password)){
    		echo json_encode(array('result'=>'-1','msg'=>'请输入密码。'));
    		exit();
    	}
    	if(empty($confirmPassword)){
    		echo json_encode(array('result'=>'-2','msg'=>'请输入确认密码。'));
    		exit();
    	}
    	if(md5($password)!=md5($confirmPassword)){
    		echo json_encode(array('result'=>'-2','msg'=>'密码不一致，请重新输入。'));
    		exit();
    	}
    	if($checkcode != $_SESSION['checkcode']){
    		echo json_encode(array('result'=>'-3','msg'=>'验证码有误。'));
    		exit();
    	}
    	$company = $this->db->fetch_assoc($this->db->query("SELECT password FROM personal_login WHERE id = {$this->personalId}"));
    	if($company['password'] !== md5($oldpassword)){
    		echo json_encode(array('result'=>'-6','msg'=>'旧密码有误。'));
    		exit();
    	}
    	$query = $this->db->query("UPDATE personal_login SET password = '".md5($password)."' WHERE id = '{$this->personalId}'");
    	if($query){
    		echo json_encode(array('result'=>'1','msg'=>''));
    		exit();
    	}else{
    		echo json_encode(array('result'=>'-4','msg'=>'修改密码失败，请重试。'));
    		exit();
    	}
    }
    
	/**
     * 修改密码 不需要输入原密码
     */
    public function editpwd2(){
    	$code = $_GET['code'];
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM email_vaildate WHERE md5 = '{$code}' AND type = 'personal'"));
    	if(empty($code)||empty($result)||($result['createTime']+EffectiveTime)<time()){
    		$this->message('无效的激活码','http://hrh.theinno.org/');
    		exit();
    	}
    	$this->tpl->assign('email',$result['email']);
    	$this->tpl->assign('code',$code);
    	$this->tpl->display('personal_forget_pwd2');
    }
	public function editpwd2Finsh(){
    	session_start();
    	$code = $_POST['code'];
    	$password = $_POST['password'];
    	$confirmPassword = $_POST['confirmPassword'];
    	$checkcode = $_POST['checkcode'];
    	$code = $_POST['code'];
    	if(empty($password)){
    		$this->ajaxJson("-1","请输入密码");
    	}
    	if(empty($confirmPassword)){
    		$this->ajaxJson("-2","请输入确认密码");
    	}
    	if(md5($password)!=md5($confirmPassword)){
    		$this->ajaxJson("-2","密码不一致，请重新输入");
    	}
    	if($checkcode != $_SESSION['checkcode']){
    		$this->ajaxJson("-3","验证码有误");
    	}
		$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM email_vaildate WHERE md5 = '{$code}' AND type = 'personal'"));
    	if(empty($code)||empty($result)||($result['createTime']+EffectiveTime)<time()){
    		$this->ajaxJson("-4","激活码无效");
    	}
    	$query = $this->db->query("UPDATE personal_login SET password = '".md5($password)."' WHERE email = '{$result['email']}'");
    	if($query){
    		$this->ajaxJson("1","修改成功");
    	}else{
    		$this->ajaxJson("1","修改密码失败，请重试");
    	}
    }
    /**
     * 发送个人忘记密码邮件
     */
    public function sendForgetPwdEmail(){
    	$email = $_POST['email'];
    	$result = $this->db->fetch_assoc($this->db->query("SELECT l.email,l.password,i.name FROM personal_login AS l LEFT JOIN personal_info AS i USING(`id`) WHERE l.email = '{$email}'"));
    	if(empty($result)){
    		$this->ajaxJson("-1",'不存在此注册邮箱，请重新输入。');
    	}
    	$code = md5(rand().rand());
    	$query = $this->db->query("INSERT INTO `email_vaildate` (`md5`,`email`,`type`,`createtime`) VALUE('{$code}','{$email}','personal','".time()."')");
    	if($query){
    		$personal = $this->db->fetch_assoc($this->db->query("SELECT name FROM personal_info AS i RIGHT JOIN personal_login AS l USING(`id`) WHERE l.email = '{$email}'"));
			$username = empty($personal['name'])?$email:$personal['name'];
    		$link = "http://hrh.theinno.org/?m=personal&action=editpwd2&code=".$code;
			$template = file_get_contents('./Template/2012/forgetpwd_email_template.html');
			$search = array('{$username}','{$link}');
			$replace = array($username,$link);
			$body = str_replace($search,$replace,$template);
			$subject = "找回您的账号密码 - 残疾人就业热线平台";		
			$email_class = new SmtpEmailEx();
			$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $email, $subject, $body);
            if($result){
            	##邮件记录
            	$emailLog = $this->db->query("INSERT INTO `log_email`(`id`,`email`,`type`,`content`,`createTime`) VALUES(null,'{$email}','company_forget_password','$body','".time()."')");
				$this->ajaxJson("1",'找回密码邮件已经发送至你邮箱，请 查收邮件');
            }else{
            	$this->ajaxJson("-1",'邮件发送失败，请重试');
           	}
    	}else{
    		$this->ajaxJson("-1",'邮件发送失败，请重试');
    	}
    }
   ##职位申请
   public function jobapply(){
   		$this->checkLoginAjax();
   		$jobId = $_POST['jobId'];
   		if(empty($jobId)){
   			$this->ajaxJson("-1","参数传递错误，请刷新页面重试");
   		}
   		##一个星期之内不能重复申请职位
   		$personalId = $this->Session->get('personalId');
   		if(empty($personalId)){
   			$this->ajaxJson("-2","请先登录");
   		}
   		$info = $this->db->fetch_assoc($this->db->query("SELECT * FROM `personal_info` WHERE id = {$personalId}"));
   		if(empty($info)){
   			$this->ajaxJson("-1","请先把简历内容填写完整，再申请职位");
   		}else if($info['status'] == '0'){
   			$this->ajaxJson("-1","您的简历内容还处于待审核，请审核通过之后再申请职位");
   		}else if($info['status'] == '2'){
   			$this->ajaxJson("-1","您的简历内容审核不通过，暂时不能申请 职位");
   		}
   		$sql = "SELECT * FROM `job_apply` WHERE personalId = '{$personalId}' AND jobId = {$jobId} AND createTime >= ".strtotime('-7 days',strtotime(date('Y-m-d')));
   		$result = $this->db->fetch_assoc($this->db->query($sql));
   		if(!empty($result)){
   			$this->ajaxJson("-1","7天之内不能重复申请同一职位");
   		}else{ 
   			$sql = "SELECT companyId FROM company_job_info WHERE id = {$jobId}";
   			$company = $this->db->fetch_assoc($this->db->query($sql));
   			$companyId = intval($company['companyId']);
   			$sql = "INSERT INTO `job_apply` (`id`,`companyId`,`jobId`,`personalId`,`createTime`) VALUES(null,{$companyId},'{$jobId}','{$personalId}','".time()."')";
   			$query = $this->db->query($sql);
   			if($query){
   				$company = $this->db->fetch_assoc($this->db->query("SELECT companyId FROM company_job_info WHERE id = '{$jobId}'"));
   				$toId = $company['companyId'];
   				$tableId = $this->db->insert_id();
   				$query = $this->db->query("INSERT INTO `message`(`id`,`toId`,`toType`,`tableName`,`tableId`,`isRead`,`messageType`,`createTime`) 
   											VALUES(null,'{$toId}','company','job_apply','{$tableId}','N','job_apply','".time()."')");
   				##发送应聘邮件
   				$template = file_get_contents('./Template/2012/job_apply_email_template.html');
   				$search = array('{$companyName}','{$personalName}','{$personalPhoto}','{$jobId}','{$jobName}','{$info}','{$expJob}','{$workExp}','{$eduExp}','{$intro}','{$phone}','{$email}','{$qq}','{$username}','{$password}');
   				$personal = $this->db->fetch_assoc($this->db->query("SELECT * FROM personal_login AS l LEFT JOIN personal_info AS i USING(`id`) LEFT JOIN personal_exp AS e USING(`id`) WHERE i.id = {$personalId}"));
   				$job = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_job_info WHERE id = {$jobId}"));
   				$company = $this->db->fetch_assoc($this->db->query("SELECT l.email as username,l.password,i.* FROM company_login AS l LEFT JOIN company_info AS i USING(`id`) WHERE l.id = {$toId}"));
   				$info = '';
   				if(!empty($personal['sex'])){
   					$info .= $personal['sex'];
   				}
   				if(!empty($personal['degree'])){
   					$info .= '&nbsp;&nbsp;&nbsp;&nbsp;学历：'.$this->degree[$personal['degree']];
   				}
   				if(!empty($personal['city'])){
   					$info .= '&nbsp;&nbsp;&nbsp;&nbsp;所在地：'.$personal['city'];
   				}
   				$replace = array($company['name'],$personal['name'],$personal['avatar'],$job['id'],$job['name'],$info,$personal['job'],nl2br($personal['workExp']),nl2br($personal['eduExp']),nl2br($personal['intro']),$personal['phone'],$personal['email'],$personal['qq'],$company['username'],$company['password']);
   				$body = str_replace($search,$replace,$template);
   				$subject = "{$personal['name']} 向贵公司 {$job['name']} 职位投递了简历，请及时 查收 - 残疾人就业热线平台";		
				$email_class = new SmtpEmailEx();
				$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $personal['email'], $subject, $body);
				$this->ajaxJson("1","申请成功");
   			}else{
   				$this->ajaxJson("-1","申请失败，请重试");
   			}
   		}
   }
   ##职位申请记录列表
   public function applyList(){
   	$this->checkLogin();
   	$page = empty($_GET['page'])||$_GET['page']<0?1:$_GET['page'];
   	$pagesize = 15;
   	$result = $this->db->fetch_assoc($this->db->query("SELECT count(*) as total FROM job_apply WHERE personalId = '{$this->personalId}'"));
   	$page = $page>ceil(intval($result['total'])/$pagesize)?ceil(intval($result['total'])/$pagesize):$page;
   	$offset = ($page-1)*$pagesize;
   	$sql = "SELECT j.jobId,j.createTime as applyTime,c.* 
   			FROM job_apply as j 
   			LEFT JOIN `company_job_info` as c ON j.jobId = c.id 
   			WHERE j.personalId = '{$this->personalId}'
   			 LIMIT {$offset},{$pagesize}";
   	$query = $this->db->query("SELECT j.jobId,j.createTime as applyTime,c.* FROM job_apply as j LEFT JOIN `company_job_info` as c ON j.jobId = c.id WHERE j.personalId = '{$this->personalId}' LIMIT {$offset},{$pagesize}");
   	$list = array();
   	while($row = $this->db->fetch_assoc($query)){
		 $tmp = array();
		 $tmp['jname'] = $row['name'];
		 $company = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_info WHERE id = '{$row['companyId']}'"));
		 $tmp['cname'] = $company['name'];
		 $tmp['city'] = $row['city'];
		 $tmp['displayType'] = $this->disableType[$row['type']];
		 $tmp['degree'] = $this->degree[$row['degree']];
		 $tmp['salary'] = $this->salary[$row['salary']];
		 $tmp['applyTime'] = date('Y-m-d',$row['applyTime']);
		 $tmp['jobId'] = $row['jobId'];
		 $tmp['comId'] = $row['companyId'];
		 $list[] = $tmp;
   	}
   	$data = array("total"=>$result['total'],"list"=>$list);
   	return $data;
   }
   ##消息列表
   public function interviewList(){
   	$this->checkLogin();
   	$page = empty($_GET['page'])||$_GET['page']<0?1:$_GET['page'];
   	$pagesize = 15;
   	$result = $this->db->fetch_assoc($this->db->query("SELECT count(*) as total FROM `interview` WHERE personalId = '{$this->personalId}'"));
   	$page = $page>ceil(intval($result['total'])/$pagesize)?ceil(intval($result['total'])/$pagesize):$page;
   	$offset = ($page-1)*$pagesize;
   	$query = $this->db->query("SELECT * FROM `interview` WHERE personalId = '{$this->personalId}' ORDER BY createTime DESC LIMIT {$offset},{$pagesize}");
   	$list = array();
   	while($row = $this->db->fetch_assoc($query)){
   		$jobId = $row['jobId'];
   		$info = $this->db->fetch_assoc($this->db->query("SELECT j.name AS jobName,i.name AS companyName,j.companyId FROM company_job_info AS j LEFT JOIN company_info AS i ON j.companyId = i.id WHERE j.id = {$jobId}"));
   		$row['type'] = '面试通知';
   		$row['companyId'] = $info['companyId'];
   		$row['companyName'] = $info['companyName'];
   		$row['jobName'] = $info['jobName'];
   		$row['time'] = date('Y-m-d H点',$row['time']); 
		$row['createTime'] = date('Y-m-d',$row['createTime']); 
   		$list[] = $row;
   	}
   	$SubPages = new SubPages(15,$result['total'],$page,5,WEBDOMAIN.'?m=personal&action=interviewList&page=',2);
   	$subPageCss2 = $SubPages->subPageCss2();
   	$this->tpl->assign('subPageCss2',$subPageCss2);  
   	$this->tpl->assign('list',$list);
   	$this->tpl->display('personal_interview');
   }
   /**
    * 用户退出
    */
   public function logout(){
   		$this->Session->clear('personalId');
   		$this->Session->clear('personal');
   		header("Location:".WEBDOMAIN);
   		exit();
   }
   /**
    * 个人中心 后边菜单列表
    */
   public function accountMenu(){
   	$this->checkLogin();
   	//最新发布职位
   	$query = $this->db->query("SELECT id,name FROM company_job_info WHERE status = 1 AND publishStatus = 1 ORDER BY lastUpdateTime DESC LIMIT 10");
   	while($row = $this->db->fetch_assoc($query)){
   		$tmp = array();
   		$tmp['jobId'] = $row['id'];
   		$tmp['name'] = $row['name'];
   		$latestJob[] = $tmp;
   	}
   	$this->tpl->assign('latestJob',$latestJob);
   	$this->tpl->assign('personalId',$this->personalId);
   	$this->tpl->display('personal_account_menu');
   }
   /**
    * 检查登录状态 跳转页面方式
    */
   public function checkLogin(){
   		if(empty($this->personalId)){
   			$this->message('请先登录求职者账号。','http://'.$_SERVER['HTTP_HOST']);
   		}
   }
   /**
    * 检查登录状态 ajax数据提交方式
    */
   public function checkLoginAjax(){
  	    if(empty($this->personalId)){
  	    	$this->ajaxJson("-2","请先登录求职者账号");
   		}
   }
   public function leftMenu(){
   		$this->tpl->display('personal_left_menu');
   }
   
   /**
    * @desc 
    */
   public function getPersonalLogin(){
   		$this->checkLogin();
   		$sql = "SELECT * FROM personal_login WHERE id = {$this->personalId}";
   		$login = $this->db->fetch_assoc($this->db->query($sql));
   		return $login;
   }
}
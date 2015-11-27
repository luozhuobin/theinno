<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
Class Model_Company extends Init
{
	public $companyId;
	function __construct($view=false)
	{
		parent::__construct();
		$this->companyId = $this->Session->get('companyId');
	}
	
	public function init() {
		$this->checklogin ();
		$companyId = $this->Session->get ( 'companyId' );
		$info = $this->db->fetch_assoc ( $this->db->query ( "SELECT i.`name`,l.`email`,i.`status` FROM `company_info` AS i RIGHT JOIN `company_login` AS l USING(`id`) WHERE id = {$companyId}" ) );
		$this->tpl->assign ( 'info', $info );
		##已发送邀请的简历
		$page = intval($_GET['page']) > 0 ? intval($_GET['page']) : 1;
		$pagesize = intval($_GET['pagesize']) > 0 ? intval($_GET['pagesize']) : 30;
		$resumeList = $this->getResumeWithInterview ( $page, $pagesize );
		$list = $resumeList ['total'] > 0 ? $resumeList ['list'] : array ();
		$SubPages = new SubPages ( 15, $resumeList ['total'], $page, 5, WEBDOMAIN . '?m=company&page=', 2 );
		$subPageCss2 = $SubPages->subPageCss2 ();
		$this->tpl->assign ( 'subPageCss2', $subPageCss2 );
		$this->tpl->assign ( 'resumeList', $list );
		$this->tpl->display ( 'company_index' );
	}
    
    public function register(){
    	$this->tpl->display('company_reg');
    }
    
	/**
     * 电子邮箱存在验证
     */
    public function emailIsExist(){
    	$email = $_POST['email'];
    	if(empty($email)){
    		echo json_encode(array('result'=>'-1','msg'=>'请输入电子邮箱。'));
    		exit();
    	}
		if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
			echo json_encode(array('result'=>'-1','msg'=>'无效的电子邮箱。'));
			exit();
		}
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM `company_login` WHERE `email` = '{$email}'"));
    	if(!empty($result)){
    		echo json_encode(array('result'=>'-1','msg'=>'该电子邮箱已被注册。'));
    		exit();
    	}else{
    	##发送验证邮件
    		$code = md5(rand().rand());
    		$query = $this->db->query("INSERT INTO `email_vaildate` (`md5`,`email`,`type`,`createtime`) VALUE('{$code}','{$email}','company','".time()."')");
    		if($query){
				$link = "http://hrh.theinno.org/?m=company&action=step2&code=".$code;
				$template = file_get_contents('./Template/2012/email_verify.html');
				$body = str_replace('{$link}',$link,$template);	
				$subject = "激活邮箱 - 残疾人就业热线平台";		
				$email_class = new SmtpEmailEx();
				$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $email, $subject, $body);
            	if($result){
            		##邮件记录
            		$emailLog = $this->db->query("INSERT INTO `log_email`(`id`,`email`,`type`,`content`,`createTime`) VALUES('','{$email}','company_register','$mail','".time()."')");
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
     * 企业注册 第二步 修改登录密码
     */
    public function step2(){
    	$code = $_GET['code'];//激活邮件中的md5值
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM email_vaildate WHERE md5 = '{$code}' AND type = 'company'"));
    	if(empty($code)||empty($result)||($result['createTime']+EffectiveTime)<time()){
    		//echo '无效的激活码';
    		$this->message('无效的激活码。','http://hrh.theinno.org');
    		exit();
    	}
    	##已经激活的用户不能再访问此页面
    	$login = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_login WHERE email = '{$result['email']}'"));
    	if(!empty($login)){
    		$this->tpl->display('company_index');
    		exit();
    	}
    	$this->tpl->assign('email',$result['email']);
    	$this->tpl->assign('code',$code);
    	$this->tpl->display('company_reg_step2');
    }
    
	public function step2finish(){
    	$code = $_POST['code'];
    	$password = $_POST['password'];
    	$confirmPassword = $_POST['confirm_password'];
    	$code = $_POST['code'];
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
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM `email_vaildate` WHERE md5 = '{$code}' AND type = 'company'"));
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
    		$login = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_login WHERE email = '{$email}'"));
    		if(empty($login)){
    			$sql = "INSERT INTO company_login(`id`,`email`,`password`,`createtime`) VALUES(null,'{$email}','".md5($password)."','".time()."')";
    			$query = $this->db->query($sql);
    			if($query){
    				$insert_id = $this->db->insert_id();
    				$this->Session->set('companyId',$insert_id);
    				$this->companyId = $insert_id;
    				##企业登录，需要清除同一浏览器的个人session
    				$this->Session->clear('personalId');
    				$this->Session->set('code',$code);//用于注册流程的第三步，尝试恢复第二步的url
    				echo json_encode(array('result'=>'1'));
    				exit();
    			}else{
    				echo json_encode(array('result'=>'-3','msg'=>'修改密码失败，请重试。'));
    				exit();
    			}
    		}else{
    			echo json_encode(array('result'=>'-3','msg'=>'该电子邮箱已被激活。'));
    			exit();
    		}
    	}
    }
    
	/**
     * 企业注册第三步 完善企业资料内容
     */
   public function step3(){
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
   			header('Location:http://hrh.theinno.org?m=company&action=step2&code='.$_REQUEST['code']);
   			exit();
   		}
   		$info = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_info WHERE id = {$this->companyId}"));
   		$this->tpl->assign('info',$info);
   		$this->tpl->assign('degree',$this->degree);
   		$this->tpl->assign('disableType',$this->disableType);
   		$this->tpl->display('company_info_step3');
   }
    /**
     * 添加或者修改企业资料
     * 两个地方需要调用此接口，其一：企业注册流程，添加企业资料。其二，企业后台修改企业资料。
     * 这两个接口大致内容相同，有一点需要注意的是，企业注册流程添加企业资料需要验证是否已经存在企业名称或者营业执照，而企业后台修改资料不需要验证，
     * 所以，在企业后台修改资料调用此接口时，定义一个参数加以标识，绕过企业名称和营业执照的验证。
     */
	public function infofinish(){
		##文件上传
		$dir = "./Template/UploadFiles/Images/company/".date('Ym').'/';
		if($_FILES['avatar']['size']>0){
			$avatar = $this->checkUploadFile($_FILES['avatar'],$dir);
			/*if($upload['result']<0&&$upload['result']=='-1'){
				parent::message($upload['msg']);
			}else{
				$avatar = $upload['url'];
			}*/
		}
		if($_FILES['license']['size']>0){
			$license = $this->checkUploadFile($_FILES['license'],$dir);
			/*if($upload['result']<0&&$upload['result']=='-1'){
				parent::message($upload['msg']);
			}else{
				$license = $upload['url'];
			}*/
		}
		$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_info WHERE id = {$this->companyId}"));
		if(empty($result)){
			foreach($_POST as $key=>$value){
				if(!in_array($key,array('submit','editinfosign','m','action'))){
					$sqlKey .= ",`{$key}`";
					$sqlValue .= ",'{$value}'";
				}
			}
			if(!empty($avatar)){
				$sqlKey .= " ,`avatar`";
				$sqlValue .= " ,'{$avatar}'";
			}
			if(!empty($license)){
				$sqlKey .= " ,`license`";
				$sqlValue .=" ,'{$license}'";
			}
			##企业后台修改资料，绕过“已被注册”验证
			$editinfosign = $this->Session->get('editinfosign');
			if($_POST['editinfosign']!=$editinfosign){
				$isNameExist = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_info WHERE name = '{$_POST['name']}' AND status = 1 "));
				if(!empty($isNameExist)){
					//公司名称已存在	
					parent::message('该公司名称已被注册，请重新输入。');
				}
				$isCodeExist = $this->db->fetct_assoc($this->db->query("SELECT * FROM company_info WHERE code ='{$_POST['code']}' AND status = 1 "));
				if(!empty($isCodeExist)){
				//营业执照已存在
					parent::message('该公司营业执照已被注册，请重新输入。');
				}
			}
			$sql = "INSERT IGNORE INTO company_info(`id`{$sqlKey},`status`,`createTime`,`lastUpdateTime`) VALUES('{$this->companyId}'{$sqlValue},0,'".time()."','".time()."')";
			$query = $this->db->query($sql);
			if($query){
				##发送通知邮件
				$companyLogin = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_login WHERE id = {$this->companyId}"));
				$body = '
            	 <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 邮箱：'.$companyLogin['email'].' 注册了企业账号，企业名称为：'.$_POST['name'].'，请及时审核资料。<a href="http://hrh.theinno.org/?c=admin&m=company">立即查看</a></p>
				 <p class="gray" style="text-align: center;">温馨提示：此邮件由系统发送，请勿直接回复。</p>
				 ';
				$email_class = new SmtpEmailEx();
        		$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $this->systemEmail, "新企业用户注册通知", $body);
			}
		}else{
			$this->checklogin();
			$sql = "UPDATE company_info SET ";
			$post = $_POST;
			unset($post['submit']);
			unset($post['editinfosign']);
			unset($post['m']);
			unset($post['action']);
			$count = count($post);
			$tmp = array();
			foreach($post as $key=>$value){
				##当企业资料审核通过之后，企业名称和营业执照号码不能再修改
				/*if($result['status']==1&&in_array($key,array('name','code'))){
					continue;
				}*/
				$tmp[] = "`{$key}`='{$value}'";
			}
			if(!empty($avatar)){
				$tmp[] = " `avatar` = '{$avatar}'";
			}
			if(!empty($license)){
				$tmp[] = " `license` = '{$license}'";
			}
			$tmp[] = " lastUpdateTime = '".time()."'";
			$sql .= implode(",",$tmp);
			$sql .= " WHERE id = {$this->companyId}";
			$query = $this->db->query($sql);
			if($query&&$result['status']!=1){
				##发送通知邮件
				$companyLogin = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_login WHERE id = {$this->companyId}"));
				$body = '
            	 <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$_POST['name'].' 更新了企业资料，请及时审核资料。<a href="http://hrh.theinno.org/?c=admin&m=company">立即查看</a></p>
				 <p class="gray" style="text-align: center;">温馨提示：此邮件由系统发送，请勿直接回复。</p>
				 ';
				$email_class = new SmtpEmailEx();
        		$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $this->systemEmail, "企业更新资料通知", $body);
			}
		}
		$companyInfo = $this->getCompanyInfo($this->companyId);
		$this->Session->set ( 'company', $companyInfo );
		$this->message('保存成功','?m=company&action=editinfo');
		/*if(!empty($_POST['editinfosign'])){
			header('location:'.WEBDOMAIN.'?m=company&action=infoView');
		}else{
			header('location:'.WEBDOMAIN.'?m=company');
		}*/
		
		exit();
   }
   /**
    * 查看企业资料页面
    */
   public function infoView(){
   	$this->checklogin();
   	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_info WHERE id = '{$this->companyId}'"));
   	$this->tpl->assign('info',$result);
   	$this->tpl->display('company_info_view');
   }
   /**
    * 企业资料修改页面
    */
   public function editinfo(){
   	$this->checklogin();
   	 ##在session记录一变量，企业资料修改传递的参数必须跟此参数一致时，才能够绕过对应的验证，防止注入。
   	 $editinfosign = $this->companyId.rand(1000,9000);
   	 $set = $this->Session->set('editinfosign',$editinfosign);
   	 $result = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_info WHERE id = '{$this->companyId}'"));
   	 $this->tpl->assign('editinfosign',$editinfosign);
   	 $this->tpl->assign('info',$result);
   	 $this->tpl->display('company_editinfo_new');
   }
   /**
    * 发布职位页面和修改职位页面
    * 本接口和下面的publishJobSubmit接口一样，均为共用接口，一个是新增职位，一个是修改职位
    * 新增职位和修改职位的验证规则不一样，新增职位需要验证用户POST过来的职位名称是否存在，而修改职位不需要此验证
    * 故，使用职位Id来作为区分新增职位和修改职位两个不同接口的标志。
    */
   public function publishJob(){
   	$this->checklogin();
   	$id = $_GET['id'];
   	if(!empty($id)){
   		$info = $this->db->fetch_assoc($this->db->query("SELECT * FROM `company_job_info` WHERE id = '{$id}'"));
   		$info['cutoffTime'] = date('Y-m-d',$info['cutoffTime']);
   		$this->tpl->assign('info',$info);
   	}
   	$this->tpl->assign('disableType',$this->disableType);
   	unset($this->_salary[1]);
   	$this->tpl->assign('salary',$this->_salary);//薪酬
   	$this->tpl->assign('workLength',$this->workLength);
   	$this->tpl->assign('degree',$this->degree);//学历
   	$this->tpl->assign('jobNum',$this->jobNum);
   	$this->tpl->display('company_publish_job_new');
   }
   /**
    * 
    */
   public function publishJobFinish(){
   		$this->checkLoginAjax();
   		$jobId = intval($_POST['jobId']);
   		$jobName = trim($_POST['name']);
   		$jobCity = trim($_POST['city']);
   		$disableType = trim($_POST['type']);
   		$degree = trim($_POST['degree']);
   		$salary = trim($_POST['salary']);
   		$desc = trim($_POST['desc']);
   		$num = $_POST['num'];
   		$workLength = $_POST['workLength'];
   		$cutoffTime = strtotime($_POST['cutoffTime']);
   		$com = $this->db->fetch_assoc($this->db->query("SELECT status FROM company_info WHERE id={$this->companyId}"));
   		if(empty($com)){
//   			echo json_encode(array('result'=>'-7','msg'=>'请先完善企业资料，再发布职位。'));
//   			exit();
   			$this->message('请先完善企业资料，再发布职位','?m=company&action=publishJob');
   		}else if($com['status']=='0'){
   			$this->message("贵公司资料还处于待审核状态，暂时不能发布职位。",'javascript:history.back()');
//   			echo json_encode(array('result'=>'-7','msg'=>'贵公司资料还处于待审核状态，暂时不能发布职位。'));
   			exit();
   		}else if($com['status']=='2'){
   			$this->message("贵公司资料审核不通过，暂时不能发布职位",'javascript:history.back()');
//   			echo json_encode(array('result'=>'-7','msg'=>'贵公司资料审核不通过，暂时不能发布职位。'));
//   			exit();
   		}
   		if(empty($jobName)){
   			$this->message("请输入你的职位名称",'javascript:history.back()');
//   			echo json_encode(array('result'=>'-1','msg'=>'请输入你的职位名称。'));
//   			exit();
   		}
   		if(empty($jobCity)){
   			$this->message("请输入该职位的工作城市",'javascript:history.back()');
//   			echo json_encode(array('result'=>'-2','msg'=>'请输入该职位的工作城市。'));
//   			exit();
   		}
   		if(empty($disableType)&&$disableType != 0){
   			$this->message("请选择你职位的残疾类型",'javascript:history.back()');
//   			echo json_encode(array('result'=>'-3','msg'=>'请选择你职位的残疾类型。'));
//   			exit();
   		}
   		if(empty($degree)&&$degree != 0){
   			$this->message("请选择学历要求",'javascript:history.back()');
//   			echo json_encode(array('result'=>'-4','msg'=>'请选择学历要求。'));
//   			exit();
   		}
   		if(empty($cutoffTime)){
   			$this->message("请填写截止招聘时间",'javascript:history.back()');
//   			echo json_encode(array('result'=>'-7','msg'=>'请填写截止招聘时间。'));
//   			exit();
   		}
   		if($cutoffTime<time()){
   			$this->message("职位发布时间不能和截止招聘时间相同",'javascript:history.back()');
//   			echo json_encode(array('result'=>'-8','msg'=>'职位发布时间不能和截止招聘时间相同'));
//   			exit();
   		}
   		if(empty($salary)&&$salary != 0){
   			$this->message("请选择薪酬范围",'javascript:history.back()');
//   			echo json_encode(array('result'=>'-5','msg'=>'请选择薪酬范围。'));
//   			exit();
   		}
   		if(empty($desc)){
   			$this->message("请输入该职位的工作内容",'javascript:history.back()');
//   			echo json_encode(array('result'=>'-6','msg'=>'请输入该职位的工作内容。'));
//   			exit();
   		}
   		
   		##为保证数据的准确性，无论是新增职位还是修改职位，在insert或者是update之前都必须要验证是否存在对应的数据
   		$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_job_info WHERE companyId = {$this->companyId} AND name = '{$jobName}'"));
   		if(!empty($result)){
   			##判断是否为修改职位
   			if(!empty($jobId)){
   				##修改职位
   				$sql = "UPDATE `company_job_info` SET `name` = '{$jobName}',`city`='{$jobCity}',`num`='{$num}',`workLength`='{$workLength}',`cutoffTime`='{$cutoffTime}',`type`='{$disableType}',`degree`='{$degree}',`salary`='{$salary}',`desc`='{$desc}',`lastUpdateTime`='".time()."' WHERE id = {$jobId}";
   				$query = $this->db->query($sql);
   				if($query){
   					##发送通知邮件
					$companyInfo = $this->db->fetch_assoc($this->db->query("SELECT name FROM company_info WHERE id = {$this->companyId}"));
					$body = '
            	 	<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$companyInfo['name'].' 更新了职位信息，职位名称为：'.$jobName.'请及时审核资料。<a href="http://hrh.theinno.org/?c=admin&m=company&action=jobList" target="_blank">立即查看</a></p>
				 	<p class="gray" style="text-align: center;">温馨提示：此邮件由系统发送，请勿直接回复。</p>
					 ';
					$email_class = new SmtpEmailEx();
        			$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $this->systemEmail, "企业修改职位通知", $body);
        			$this->message("修改成功",'javascript:history.back()');
//   					echo json_encode(array('result'=>'1','msg'=>'修改成功。'));
//   					exit();
   				}else{
   					$this->message("修改失败，请重试",'javascript:history.back()');
//   					echo json_encode(array('result'=>'-7','msg'=>'修改失败，请重试。'));
//   					exit();
   				}
   			}else{
   				$this->message("你已发布相同的职位名称，请重新输入",'javascript:history.back()');
//   				echo json_encode(array('result'=>'-1','msg'=>'你已发布相同的职位名称，请重新输入。'));
//   				exit();
   			}
   			
   		}else{
   			$sql = "INSERT INTO `company_job_info`(`id`,`companyId`,`name`,`city`,`type`,`degree`,`workLength`,`num`,`salary`,`desc`,`cutoffTime`,`status`,`publishStatus`,`createTime`,`lastUpdateTime`) 
   					VALUES(null,'{$this->companyId}','{$jobName}','{$jobCity}','{$disableType}','{$degree}','{$workLength}','{$num}','{$salary}','{$desc}','{$cutoffTime}','0','1','".time()."','".time()."')
   			";
   			$query = $this->db->query($sql);
   			if($query){
   				##发送通知邮件
				$companyInfo = $this->db->fetch_assoc($this->db->query("SELECT name FROM company_info WHERE id = {$this->companyId}"));
				$body = '
            	 <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$companyInfo['name'].' 发布了一个职位，职位名称为：'.$jobName.' 请及时审核资料。<a href="http://hrh.theinno.org/?c=admin&m=company&action=jobList" target="_blank">立即查看</a></p>
				 <p class="gray" style="text-align: center;">温馨提示：此邮件由系统发送，请勿直接回复。</p>
				 ';
				$email_class = new SmtpEmailEx();
        		$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $this->systemEmail, "企业发布职位通知", $body);
        		$this->message("发布成功",'javascript:history.back()');
//   				echo json_encode(array('result'=>'1','msg'=>'发布成功'));
//   				exit();
   			}else{
   				$this->message("发布失败，请刷新页面重试",'javascript:history.back()');
//   				echo json_encode(array('result'=>'-7','msg'=>'发布失败，请刷新页面重试。'));
//   				exit();
   			}
   		}
   }
   /**
    * 职位列表
    */
   public function joblist(){
   	$this->checklogin();
   	$page = empty($_GET['page'])||$_GET['page']<0?1:$_GET['page'];
   	$data = $this->getJobList($this->companyId,$page,30);
   	$SubPages = new SubPages(15,$data['total'],$page,5,WEBDOMAIN.'?m=company&action=jobList&page=',2);
   	$subPageCss2 = $SubPages->subPageCss2();
   	$this->tpl->assign('subPageCss2',$subPageCss2);
   	$this->tpl->assign('list',$data["list"]);
   	$this->tpl->display('joblist');
   }
	
	/**
	 * @desc 获取职位列表
	 * @param int $companyId 企业id
	 */
	private function getJobList($companyId, $page = 1, $pagesize = 30) {
		if (empty ( $companyId )) {
			return array ();
		}
		$page = empty ( $page ) || $page < 0 ? 1 : $page;
		$result = $this->db->fetch_assoc ( $this->db->query ( "SELECT COUNT(*) AS total FROM company_job_info WHERE companyId = '{$this->companyId}'" ) );
		$page = $page < ceil ( $result ['total'] / $pagesize ) ? $page : ceil ( $result ['total'] / $pagesize );
		$offset = ($page - 1) * $pagesize;
		$query = $this->db->query ( "SELECT * FROM company_job_info WHERE companyId = '{$this->companyId}' ORDER BY lastUpdateTime DESC LIMIT {$offset},{$pagesize}" );
		$list = array ();
		while ( $row = $this->db->fetch_assoc ( $query ) ) {
			$row ['displayType'] = $this->disableType [$row ['type']];
			$row ['degree'] = $this->degree [$row ['degree']];
			$row ['publish'] = $this->jobPublishStatus [$row ['publishStatus']];
			$row ['salary'] = $this->salary [$row ['salary']];
			$row ['status'] = $this->auditStatus [$row ['status']];
			$row ['createTime'] = date ( 'Y-m-d', $row ['createTime'] );
			$row ['lastUpdateTime'] = date ( 'Y-m-d', $row ['lastUpdateTime'] );
			$list [] = $row;
		}
		$data = array(
				"total"=>$result ['total'],
				"list"=>$list
			);
		return $data;
	}
	/**
	 * 企业登录
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
		$login = $this->db->fetch_assoc ( $this->db->query ( "SELECT * FROM company_login WHERE email = '{$email}' AND password = '" . md5 ( $password ) . "'" ) );
		if (empty ( $login )) {
			$this->ajaxJson ( '-2', '密码错误。' );
		} else {
			$companyInfo = $this->getCompanyInfo($login ['id']);
			$set = $this->Session->set ( 'company', $companyInfo );
			$set = $this->Session->set ( 'companyId', $login ['id'] );
			##企业登录，需要清除同一浏览器的个人session
			$this->Session->clear ( 'personalId' );
			$this->ajaxJson ( '1', '' );
		}
	}
	
	
	/**
	 * @desc 获取企业资料
	 * @param int $companyId
	 * @return array
	 */
	public function getCompanyInfo($companyId){
		$sql = "SELECT * FROM  `company_info` WHERE id = {$companyId}";
		$companyInfo = $this->db->fetch_assoc ( $this->db->query ( $sql ) );
		return $companyInfo;
	}
    public function getFooter(){
    	$this->tpl->display('footer');
    }
    public function getLoginBox(){
    	$this->tpl->display("login-box");
    }
    
    /**
     * 判断公司名称是否存在，公司名称必须唯一
     */
    public function isNameExist(){
    	$name = trim($_POST['name']);
    	if(empty($name)){
    		echo json_encode(array('result'=>'-1','msg'=>'请输入你的公司名称。'));
    		exit();
    	}
    	//status 0为默认待审核状态，1为已通过审核，2为审核不通过。
    	$sql = "";
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_info WHERE name = '{$name}' AND status = 1"));
    	if(!empty($result)){
    		echo json_encode(array('result'=>'-1','msg'=>'公司名称已被注册，请重新输入。'));
    		exit();
    	}else{
    		echo json_encode(array('result'=>'1','msg'=>''));
    		exit();
    	}
    }
    /**
     * 判断公司的营业执照是否存在，公司营业执照必须唯一
     */
    public function isCodeExist(){
    	$code = trim($_POST['code']);
    	if(empty($code)){
    		echo json_encode(array('result'=>'-1','msg'=>'请输入你的公司营业执照号码。'));
    		exit();
    	}
    	//status 0为默认待审核状态，1为已通过审核，2为审核不通过。
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_info WHERE code = '{$code}' AND status = 1"));
    	if(!empty($result)){
    		echo json_encode(array('result'=>'-1','msg'=>'营业执照已被注册，请重新输入。'));
    		exit();
    	}else{
    		echo json_encode(array('result'=>'1','msg'=>''));
    		exit();
    	}
    }
    /**
     * 判断职位名称是否存在，职位名称必须唯一
     */
    public function isJobNameExist(){
    	$jobName = trim($_POST['jobName']);
    	$jobId = trim($_POST['jobId']);
    	/**
    	 * 由于发布职位页面和修改职位页面 都共用了company_publish_job.html 
    	 * 发布职位 需要验证职位名称是否存在 而修改职位不需要验证职位名称是否存在
    	 * 所以传递jobId来区分是发布职位还是修改职位。
    	 */
    	if(empty($jobName)){
    		echo json_encode(array('result'=>'-1','msg'=>'请输入职位名称。'));
    		exit();
    	}
    	##当为修改职位时，不需要验证职位名称是否存在，故直接返回true
    	if(!empty($jobId)){
    		echo json_encode(array('result'=>'1','msg'=>''));
    		exit();
    	}
    	//status 0为默认待审核状态，1为已通过审核，2为审核不通过
    	$result =$this->db->fetch_assoc($this->db->query("SELECT * FROM company_job_info WHERE companyId = {$this->companyId} AND name = '{$jobName}'"));
    	if(!empty($result)){
    		echo json_encode(array('result'=>'-1','msg'=>'你已发布相同的职位名称，请重新输入。'));
    		exit();
    	}else{
    		echo json_encode(array('result'=>'1','msg'=>''));
    		exit();
    	}
    }
    /**
     * 重新发布或者是刷新职位
     */
    public function releasedPublish(){
    	$this->checkLoginAjax();
    	$jobId = $_POST['jobId'];
    	$todo = $_POST['todo']=='refresh'?'刷新':'重新发布';
    	if(empty($jobId)){
    		echo json_encode(array('result'=>'-1','msg'=>"{$todo}失败，请刷新页面重试。"));
    		exit();
    	}
    	$publishStatus = 1;
    	$lastUpdateTime = time();
    	$sql = "UPDATE `company_job_info` SET `publishStatus` = '{$publishStatus}',`lastUpdateTime` = '{$lastUpdateTime}' WHERE id = '{$jobId}'";
    	$query = $this->db->query($sql);
    	if($query){
    		echo json_encode(array('result'=>'1','msg'=>"{$todo}成功。",'publishStatus'=>$this->jobPublishStatus[$publishStatus],'lastUpdateTime'=>date('Y-m-d',$lastUpdateTime)));
    		exit();
    	}else{
    		echo json_encode(array('result'=>'-1','msg'=>"{$todo}失败，请刷新页面重试。"));
    		exit();
    	}
    }
    
    /**
     * 删除职位
     */
    public function deleleJob(){
    	$this->checkLoginAjax();
    	$jobId = $_POST['jobId'];
    	if(empty($jobId)){
    		echo json_encode(array('result'=>'-1','参数传递错误，请刷新页面重试。'));
    		exit();
    	}
    	$sql = "DELETE FROM `company_job_info` WHERE id = '{$jobId}'";
    	$query = $this->db->query($sql);
    	if($query){
    		echo json_encode(array('result'=>'1','msg'=>'成功删除。'));
    		exit();
    	}else{
    		echo json_encode(array('result'=>'-1','msg'=>'删除失败，请重试。'));
    		exit();
    	}
    }
    
    /**
     * 应聘列表
     */
    public function messageList(){
    	$this->checklogin();
    	$page = empty($_GET['page'])||$_GET['page']<0?1:$_GET['page'];
   		$pagesize = 15;
   		$result = $this->db->fetch_assoc($this->db->query("SELECT count(*) as total FROM message WHERE toId = '{$this->companyId}'"));
   		$page = $page>ceil(intval($result['total'])/$pagesize)?ceil(intval($result['total'])/$pagesize):$page;
   		$offset = ($page-1)*$pagesize;
   		$query = $this->db->query("SELECT * FROM message WHERE toId = '{$this->companyId}' AND tableName = 'job_apply' ORDER BY createTime DESC LIMIT {$offset},{$pagesize}");
   		$list = array();
   		while($row = $this->db->fetch_assoc($query)){
   			$tmp = array();
   			$sql = "SELECT j.createTime as applyTime,j.jobId,j.personalId as personalId,p.name as resumeName,c.name as jobName,c.city,c.createTime as publishTime FROM `{$row['tableName']}` AS j LEFT JOIN company_job_info AS c ON j.jobId = c.id LEFT JOIN personal_info AS p on j.personalId = p.id WHERE j.id = {$row['tableId']};";
   			$join = $this->db->fetch_assoc($this->db->query($sql));
   			$tmp['applyTime'] = date('Y-m-d',$join['applyTime']);
   			if(empty($join['resumeName'])){
   				$personal = $this->db->fetch_assoc($this->db->query("SELECT * FROM personal_login WHERE id={$join['personalId']}"));
   				$tmp['resume'] = $personal['email'];
   			}else{
   				$tmp['resume'] = $join['resumeName'];
   			}
   			$tmp['pId'] = base64_encode($join['personalId']);
   			$tmp['jobName'] = $join['jobName'];
   			$tmp['city'] = $join['city'];
   			$tmp['publishTime'] = date('Y-m-d',$join['publishTime']);
   			$tmp['trClick'] = $row['isRead'] == 'N'?'onclick="setRead('.$row['id'].',this);"':'';
   			$tmp['isRead'] = $row['isRead'] == 'N'?'style="font-weight:bold;"':'';
   			$tmp['jobId'] = $join['jobId'];
   			$list[] = $tmp;
   		}
   		$SubPages = new SubPages(15,$result['total'],$page,5,WEBDOMAIN.'?m=company&action=messageList&page=',2);
   		$subPageCss2 = $SubPages->subPageCss2();
   		$this->tpl->assign('subPageCss2',$subPageCss2);  
   		$this->tpl->assign('list',$list);
   		$this->tpl->display('company_message_list');
    }
    /*
     * 消息设为已读
     */
   public function setRead(){
   		$this->checkLoginAjax();
   		$msgId = $_POST['msgId'];
   		$query = $this->db->query("UPDATE `message` SET isRead = 'Y' WHERE id = '{$msgId}'");
   		echo json_encode(array('result'=>'1'));
   }
   /**
    * 发送面试通知
    */
   public function sendInterview(){
   		$this->checkLoginAjax();
   		$jobId = intval($_POST['jobId']);
   		$time = strtotime($_POST['time']);
   		$address = $_POST['address'];
   		$desc = $_POST['desc'];
   		$pId = intval($_POST['pId']);
   		if(empty($jobId)){
   			$this->ajaxJson("-1","请选择面试职位");
   		}
   		if(empty($time)){
   			$this->ajaxJson("-2","请选择面试时间");
   		}
   		if(empty($address)){
   			$this->ajaxJson("-3",'请选择面试地点');
   		}
   		##相同职位 相同简历 一个星期只能发送一次面试通知
   		$result = $this->db->fetch_assoc($this->db->query("SELECT count(*) as total FROM interview WHERE jobId = {$jobId} AND personalId = {$pId} AND createTime >=".strtotime("-7 days",strtotime(date('Y-m-d')))));
   		if($result['total']>0){
   			$this->ajaxJson("-4","7天内不能重复发送面试通知");
   		}
   		$sql = "INSERT IGNORE INTO `interview`(`id`,`companyId`,`jobId`,`personalId`,`time`,`address`,`desc`,`createTime`) 
   					VALUES(null,{$this->companyId},{$jobId},{$pId},{$time},'{$address}','{$desc}',".time().")";
   		$query = $this->db->query($sql);
   		if($query){
   			##发送消息和邮件
   			$insert_id = $this->db->insert_id();
   			$query = $this->db->query("INSERT INTO `message`(`id`,`toId`,`toType`,`tableName`,`tableId`,`isRead`,`messageType`,`createTime`) VALUES(null,{$pId},'personal','interview',{$insert_id},'N','interview','".time()."')");
   			$personal_login = $this->db->fetch_assoc($this->db->query("SELECT `email`,`password`,`name` FROM `personal_login` AS l LEFT JOIN `personal_info` AS i USING(`id`) WHERE id={$pId}"));
   			$username = empty($personal_login['name'])?'用户':$personal_login['name'];
   			$info = $this->db->fetch_assoc($this->db->query("SELECT i.createTime,i.time,i.address,i.desc,j.name FROM interview AS i LEFT JOIN `company_job_info` AS j ON i.jobId = j.id  WHERE i.id = {$insert_id}"));
   			$company = $this->db->fetch_assoc($this->db->query("SELECT `name` FROM `company_info` WHERE id = {$this->companyId}"));
           	$template = file_get_contents('./Template/2012/interview_email_template.html');
           	$search = array('{$personalName}','{$companyName}','{$time}','{$jobName}','{$address}','{$interviewTime}','{$desc}','{$email}','{$password}');
           	$replace = array($username,$company['name'],date('Y-m-d H:i:s',$info['createTime']),$info['name'],$info['address'],date('Y-m-d H点',$info['time']),nl2br($desc),$personal_login['email'],$personal_login['password']);
           	$body = str_replace($search,$replace,$template);
            $email_class = new SmtpEmailEx();
            $result = Email_163::getInstance()->send(Email_163::MAIL_USER, $personal_login['email'], "面试通知 - 残疾人就业热线平台", $body);
            ##邮件记录
            $emailLog = $this->db->query("INSERT INTO `log_email`(`id`,`email`,`type`,`content`,`createTime`) VALUES(null,'{$personal_login['email']}','interview','$content','".time()."')");
   			$this->ajaxJson("1","发送成功");
   		}else{
   			$this->ajaxJson("-4","面试通知发送失败，请重新发送");
   		}
   }
	/**
     * 忘记密码
     */
    public function forgetpwd(){
    	$this->tpl->display('company_forget_pwd');
    }
    /**
     * 修改密码 需要输入原密码
     */
    public function editpwd(){
    	$this->checklogin();
    	$company = $this->db->fetch_assoc($this->db->query("SELECT email FROM company_login WHERE id = {$this->companyId}"));
    	$this->tpl->assign('email',$company['email']);
    	$this->tpl->display('company_forget_password1');
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
    	$company = $this->db->fetch_assoc($this->db->query("SELECT password FROM company_login WHERE id = {$this->companyId}"));
    	if($company['password'] !== md5($oldpassword)){
    		echo json_encode(array('result'=>'-6','msg'=>'旧密码有误。'));
    		exit();
    	}
    	$query = $this->db->query("UPDATE company_login SET password = '".md5($password)."' WHERE id = '{$this->companyId}'");
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
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM email_vaildate WHERE md5 = '{$code}' AND type = 'company'"));
    	if(empty($code)||empty($result)||($result['createTime']+EffectiveTime)<time()){
    		$this->message('无效的激活码','http://hrh.theinno.org/');
    		exit();
    	}
    	$this->tpl->assign('email',$result['email']);
    	$this->tpl->assign('code',$code);
    	$this->tpl->display('company_forget_pwd2');
    }
    public function editpwd2Finsh(){
    	session_start();
    	$code = $_POST['code'];
    	$password = $_POST['password'];
    	$confirmPassword = $_POST['confirmPassword'];
    	$checkcode = $_POST['checkcode'];
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
    	$company = $this->db->fetch_assoc($this->db->query("SELECT * FROM email_vaildate WHERE md5 = '{$code}' AND type = 'company'"));
    	if(empty($code)||empty($company)||($company['createTime']+EffectiveTime)<time()){
    		$this->ajaxJson("-4","激活码无效");
    	}
    	$query = $this->db->query("UPDATE company_login SET password = '".md5($password)."' WHERE email = '{$company['email']}'");
    	if($query){
    		$this->ajaxJson("1","");
    	}else{
    		$this->ajaxJson("-4","修改密码失败，请重试");
    	}
    }
 	/**
     * 发送企业忘记密码邮件
     */
    public function sendForgetPwdEmail(){
    	$email = $_POST['email'];
    	$result = $this->db->fetch_assoc($this->db->query("SELECT l.email,l.password,i.name FROM company_login AS l LEFT JOIN company_info AS i USING(`id`) WHERE l.email = '{$email}'"));
    	if(empty($result)){
    		$this->ajaxJson("-1","不存在此注册邮箱，请重新输入");
    	}
    	$code = md5(rand().rand());
    	$query = $this->db->query("INSERT INTO `email_vaildate` (`md5`,`email`,`type`,`createtime`) VALUE('{$code}','{$email}','company','".time()."')");
    	if($query){
    		$company = $this->db->fetch_assoc($this->db->query("SELECT name FROM company_info AS i RIGHT JOIN company_login AS l USING(`id`) WHERE l.email = '{$email}'"));
			$username = empty($company['name'])?$email:$company['name'];
    		$link = "http://hrh.theinno.org/?m=company&action=editpwd2&code=".$code;
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
				$this->ajaxJson("1","找回密码邮件已经发送至你邮箱，请 查收邮件");
            }else{
            	$this->ajaxJson("-1","邮件发送失败，请重试");
           	}
    	}else{
    		$this->ajaxJson("-1","邮件发送失败，请重试");
    	}
    }
    
   /**
    * 用户退出
    */
   public function logout(){
   		$this->Session->clear('companyId');
   		$this->Session->clear('company');
   		header("Location:".WEBDOMAIN);
   		exit();
   }
   /**
    * 检查登录状态 跳转页面方式
    */
   public function checklogin(){
   		if(empty($this->companyId)){
   			$this->message('请先登录企业后台。','http://'.$_SERVER['HTTP_HOST']);
   		}
   }
   /**
    * 检查登录状态 ajax数据提交方式
    */
   public function checkLoginAjax(){
  	    if(empty($this->companyId)){
  	    	$this->ajaxJson("-1","请先登录企业后台。");
   		}
   }
   
   /**
    * @desc 发送面试邀请界面
    */
   public function getResumeCompany(){
   		##权限判断，只有企业用户登录之后可看
		$Model_Company = new Model_Company();
		$Model_Company->checkLoginAjax();
   		$resumeId = intval($_POST['resumeId']);
   		$Model_Resume = new Model_Resume();
   		$resumeDetail = $Model_Resume->getResumeDetailById($resumeId);
   		$joblist = $this->getJobList($this->companyId,1,1000);
   		$resumeDetail['jobList'] = !empty($joblist["list"]) ? $joblist["list"] : array();
   		$this->ajaxJson("1","",$resumeDetail);
   }
   
   public function leftMenu(){
   		$this->tpl->display('company_left_menu');
   }
   
   public function leftMenu2(){
   		if($_GET['m'] == "job"){
   			$jobId = intval($_GET['jobId']);
	   		$sql = "SELECT * FROM company_job_info WHERE id = {$jobId}";
	   		$result = $this->db->fetch_assoc($this->db->query($sql));
   		}else{
   			$comapnyId = intval($_GET['comId']);
	   		$sql = "SELECT * FROM company_info WHERE id = {$comapnyId}";
	   		$company = $this->db->fetch_assoc($this->db->query($sql));
	   		$result = array("companyId"=>$company['id']);
   		}
   		$this->tpl->assign("company",$result);
   		$this->tpl->display('company_left_menu2');
   }
	/**
	 * @desc 已发送邀请的简历
	 */
	public function getResumeWithInterview($page = 1, $pagesize = 30) {
		$sql = "SELECT COUNT(id) AS total FROM interview WHERE companyId = {$this->companyId}" ;
		$result = $this->db->fetch_assoc($this->db->query($sql));
		$offset = intval($page) > 1 ? ($page-1)*$pagesize : 0;
		$sql = "SELECT v.*,j.name AS jname,i.name AS pname FROM interview AS v 
   				LEFT JOIN `company_job_info` AS j ON v.jobId = j.id
   				LEFT JOIN `personal_info` AS i ON v.personalId = i.id
   				WHERE v.companyId = {$this->companyId}
   				LIMIT {$offset},{$pagesize}
   				";
		$tmp = array ();
		$query = $this->db->query ( $sql );
		while ( $row = $this->db->fetch_assoc ( $query ) ) {
			$row ['createtime'] = formateDay ( $row ['createtime'], 'Y-m-d' );
			$row ['time'] = formateDay ( $row ['time'], 'Y-m-d' );
			$tmp [] = $row;
		}
		$data = array(
			"total"=>$result['total'],
			"list"=>$tmp
		);
		return $data;
	}
	
	/**
	 * @desc 求职者主动投递的简历
	 */
	public function jobApplyList(){
		$page = intval ( $_GET ['page'] ) > 0 ? intval ( $_GET ['page'] ) : 1;
		$pagesize = intval($_GET['pagesize']) > 0 ? intval($_GET['pagesize']) : 30;
		$applyList = $this->getJobApplyList($page,$pagesize);
		$SubPages = new SubPages(15,$applyList['total'],$page,5,WEBDOMAIN.'?m=company&action=jobApplyList&page=',2);
   		$subPageCss2 = $SubPages->subPageCss2();
   		$this->tpl->assign('subPageCss2',$subPageCss2);  
   		$this->tpl->assign('list',$applyList["list"]);
   		$this->tpl->display('jobApplyList');
	}
	/**
	 * @desc 获取应聘简历
	 */
	public function getJobApplyList($page = 1,$pagesize = 30){
		$sql = "SELECT COUNT(id) AS total FROM job_apply WHERE companyId = {$this->companyId}" ;
		$result = $this->db->fetch_assoc($this->db->query($sql));
		$offset = intval($page) > 1 ? ($page-1)*$pagesize : 0;
		$sql = "SELECT a.*,j.name AS jname,i.name AS pname FROM job_apply AS a
   				LEFT JOIN `company_job_info` AS j ON a.jobId = j.id
   				LEFT JOIN `personal_info` AS i ON a.personalId = i.id
   				WHERE a.companyId = {$this->companyId}
   				LIMIT {$offset},{$pagesize}
   				";
		$tmp = array ();
		$query = $this->db->query ( $sql );
		while ( $row = $this->db->fetch_assoc ( $query ) ) {
			$row['p'] = base64_encode($row['personalId']);
			$row ['createTime'] = formateDay ( $row ['createTime'], 'Y-m-d' );
			$row ['time'] = formateDay ( $row ['time'], 'Y-m-d' );
			$tmp [] = $row;
		}
		$data = array(
			"total"=>$result['total'],
			"list"=>$tmp
		);
		return $data;
	}
	
	/**
	 * @desc 重置密码
	 */
	public function changepwd() {
		$this->checklogin ();
		$this->tpl->display ( 'companyChangePwd' );
	}
	public function doChangePwd() {
		$this->checklogin ();
		$oldPassword = $_POST ['oldPassword'];
		$password = $_POST ['password'];
		$confirmPassword = $_POST ['confirmPassword'];
		if (empty ( $oldPassword )) {
			$this->ajaxJson ( "-1", "请输入原密码" );
		}
		if (empty ( $password )) {
			$this->ajaxJson ( "-1", "请输入新密码" );
		}
		if (empty ( $confirmPassword )) {
			$this->ajaxJson ( "-1", "请输入确认密码" );
		}
		if ($password != $confirmPassword) {
			$this->ajaxJson ( "-1", "两次输入的新密码不一致" );
		}
		$companyInfo = $this->getCompanyLogin ();
		if ($companyInfo ['password'] !== md5 ( $oldPassword )) {
			$this->ajaxJson ( "-2", "原密码错误" );
		} else {
			$sql = "UPDATE company_login SET password = '" . md5 ( $password ) . "' WHERE id = {$this->companyId}";
			$query = $this->db->query ( $sql );
			if ($query) {
				$this->Session->clear ( 'companyId' );
				$this->Session->clear ( 'personalId' );
				$this->Session->clear ( 'personal' );
				$this->Session->clear ( 'company' );
				$this->ajaxJson ( "1", "修改成功，请使用新密码重新登录" );
			} else {
				$this->ajaxJson ( "-3", "修改失败" );
			}
		}
	}
	
   /**
    * @desc 
    */
   public function getCompanyLogin(){
   		$this->checkLogin();
   		$sql = "SELECT * FROM company_login WHERE id = {$this->companyId}";
   		$login = $this->db->fetch_assoc($this->db->query($sql));
   		return $login;
   }
}
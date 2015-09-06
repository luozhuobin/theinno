<?php
Class Model_Company extends Control_Admin
{
	
	function __construct($view=false)
	{
		parent::__construct(get_class($this));
	}
	
    public function init()
    {
    	##搜索条件
    	$where = " WHERE 1 ";
    	if($_REQUEST['status'] != ''){
    		$where .= " AND i.status = '{$_REQUEST['status']}'";
    	}
    	if(!empty($_REQUEST['name'])){
    		$where .= " AND i.name = '{$_REQUEST['name']}'";
    	}
        $page = empty($_GET['page'])||$_GET['page']<0?1:$_GET['page'];
    	$result = $this->db->fetch_assoc($this->db->query("SELECT count(*) AS total FROM company_login AS l LEFT JOIN company_info AS i USING(`id`) {$where}"));
    	$pagesize = 15;
    	$page = $page>ceil($result['total']/$pagesize)?ceil($result['total']/$pagesize):$page;
    	$offset = ($page-1)*$pagesize;
    	$sql = "SELECT l.id AS companyId,l.email AS username,l.createTime as time,i.* FROM company_login AS l LEFT JOIN company_info AS i USING(`id`) {$where} ORDER BY lastUpdateTime DESC LIMIT {$offset},{$pagesize}";
    	$query = $this->db->query($sql);
    	$list = array();
    	while($row = $this->db->fetch_assoc($query)){
    		$row['int_status'] = $row['status'];
    		$row['status'] = empty($this->auditStatus[$row['status']])?$this->auditStatus['0']:$this->auditStatus[$row['status']];
    		$row['createTime'] = date('Y-m-d H:i:s',$row['time']);
    		$list[] = $row;
    	}
    	$SubPages = new SubPages(15,$result['total'],$page,5,WEBDOMAIN.'?c=admin&m=company&status='.$_REQUEST['status'].'&name='.$_REQUEST['name'].'&page=',2);
   		$subPageCss2 = $SubPages->subPageCss2();
   		$this->tpl->assign('subPageCss2',$subPageCss2);  
    	$this->tpl->assign("list",$list);
    	$this->tpl->assign('status',$_REQUEST['status']);
    	$this->tpl->assign('auditStatus',$this->auditStatus);
    	$this->tpl->assign('total',$result['total']);
    	$this->tpl->assign('page_total',ceil($result['total']/$pagesize));
        $this->tpl->display('Company_list');
    }
    
    /**
     * 企业资料审核
     */
    public function changeStatus(){
    	$companyId = $_POST['companyId'];
    	$status = $_POST['status'];
    	$content = $_POST['content'];
    	$sql = "UPDATE `company_info` SET status = {$status} WHERE id={$companyId}";
    	$query = $this->db->query($sql);
    	if($query){
    		##发送通知邮件
    		$companyInfo = $this->db->fetch_assoc($this->db->query("SELECT l.email,i.name FROM company_login AS l LEFT JOIN company_info AS i USING(`id`) WHERE id = {$companyId}"));
			$template = file_get_contents('./Template/admin/admin_notice_email_template.html');
			if($status == 1){
				$content = '您在<a href="http://hrh.theinno.org" target="_blank">残疾人就业热线平台</a>注册的企业账号，已被审核通过。';
			}else{
				$content = '您好，您在<a href="http://hrh.theinno.org" target="_blank">残疾人就业热线平台</a>提交的企业资料审核不通过，原因如下：<br />'.$content;
			}
			$search = array('{$name}','{$content}');
			$replace = array($companyInfo['name'],$content);
			$body = str_replace($search,$replace,$template);
			$subject = "企业资料审核结果通知 - 残疾人就业热线平台";		
			$email_class = new SmtpEmailEx();
			$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $companyInfo['email'], $subject, $body);
			$emailLog = $this->db->query("INSERT INTO `log_email`(`id`,`email`,`type`,`content`,`createTime`) VALUES(null,'{$companyInfo['email']}','$subject','$body','".time()."')");
    		echo json_encode(array('result'=>'1','msg'=>$this->auditStatus[$status]));
    	}else{
    		echo json_encode(array('result'=>'-1','msg'=>'修改失败，请刷新页面'));
    	}
    	exit();
    }
    /**
     * 职位列表
     */
    public function jobList(){
    	$where = ' WHERE 1 ';
    	if($_REQUEST['status'] != ''){
    		$where .= " AND j.status = '{$_REQUEST['status']}'";
    	}
    	if(!empty($_REQUEST['cName'])){
    		$where .=" AND i.name = '{$_REQUEST['cName']}'";
    	}
    	if(!empty($_REQUEST['jName'])){
    		$where .= " AND j.name = '{$_REQUEST['jName']}'";
    	}
    	$page = empty($_GET['page'])||$_GET['page']<0?1:$_GET['page'];
    	$pagesize = 15;
    	$result = $this->db->fetch_assoc($this->db->query("SELECT count(*) AS total FROM company_job_info AS j LEFT JOIN company_info AS i ON j.companyId = i.id {$where}"));
    	$page = $page>ceil($result['total']/$pagesize)?ceil($result['total']/$pagesize):$page;
    	$offset = ($page-1)*$pagesize;
    	$query = $this->db->query("SELECT j.*,i.name AS companyName,i.id AS companyId,i.status as companyStatus FROM company_job_info AS j LEFT JOIN company_info AS i ON j.companyId = i.id {$where} ORDER BY lastUpdateTime DESC LIMIT {$offset},{$pagesize}");
    	$list = array();
    	while($row = $this->db->fetch_assoc($query)){
    		$row['createTime'] = date('Y-m-d',$row['createTime']);
    		$row['lastUpdateTime']= date('Y-m-d',$row['lastUpdateTime']);
    		$row['type'] = $this->disableType[$row['type']];
    		$row['degree'] = $this->degree[$row['degree']];
    		$row['salary'] = $this->_salary[$row['salary']];
    		$row['int_status'] = $row['status'];
    		$row['status'] = $this->auditStatus[$row['status']];
    		$row['companyStatus'] = $this->auditStatus[$row['companyStatus']];
    		$row['publishStatus'] = $this->jobPublishStatus[$row['publishStatus']];
    		$list[] = $row;
    	}
    	$this->tpl->assign('list',$list);
    	$SubPages = new SubPages($pagesize,$result['total'],$page ,5,"?c=admin&m=company&action=jobList&status={$_REQUEST['status']}&cName={$_REQUEST['cName']}&jName={$_REQUEST['jName']}&page=",2);
		$subPageCss2 = $SubPages->subPageCss2();
		$this->tpl->assign('status',$_REQUEST['status']);
		$this->tpl->assign('auditStatus',$this->auditStatus);
		$this->tpl->assign('subPageCss2',$subPageCss2);
		$this->tpl->assign('total',$result['total']);
    	$this->tpl->assign('page_total',ceil($result['total']/$pagesize));
    	$this->tpl->display('jobList');
    }
    /**
     * 修改职位的审核状态
     */
    public function changeJobStatus(){
    	$id = $_POST['id'];
    	$status = $_POST['status'];
    	$content = $_POST['content'];
    	$query = $this->db->query("UPDATE company_job_info SET status = {$status} WHERE id = {$id}");
    	if($query){
    		##发送通知邮件
    		$companyInfo = $this->db->fetch_assoc($this->db->query("SELECT l.email,i.name AS cname,j.name AS jname FROM company_login AS l LEFT JOIN company_info AS i USING(`id`) LEFT JOIN `company_job_info` as j ON l.id = j.companyId WHERE j.id = {$id}"));
			$template = file_get_contents('./Template/admin/admin_notice_email_template.html');
			if($status == 1){
				$content = '您在<a href="http://hrh.theinno.org" target="_blank">残疾人就业热线平台</a>发布的“'.$companyInfo['jname'].'职位”，已被审核通过。';
			}else{
				$content = '您好，您在<a href="http://hrh.theinno.org" target="_blank">残疾人就业热线平台</a>发布的“'.$companyInfo['jname'].'”职位暂不能审核通过，原因如下：<br />'.$content;
			}
			$search = array('{$name}','{$content}');
			$replace = array($companyInfo['name'],$content);
			$body = str_replace($search,$replace,$template);
			$subject = "职位资料审核结果通知 - 残疾人就业热线平台";		
			$email_class = new SmtpEmailEx();
			$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $companyInfo['email'], $subject, $body);
			$emailLog = $this->db->query("INSERT INTO `log_email`(`id`,`email`,`type`,`content`,`createTime`) VALUES(null,'{$companyInfo['email']}','$subject','$body','".time()."')");
    		echo json_encode(array('result'=>'1','msg'=>$this->auditStatus[$status]));
    	}else{
    		echo json_encode(array('result'=>'-1','msg'=>'修改失败。'));
    	}
    	exit();
    }
/**
     * 修改职位的审核状态
     */
    public function changeIsTop(){
    	$id = intval($_POST['id']);
    	$isTop = intval($_POST['isTop']);
    	if($isTop == 1){ 
			$topTime = time();  		
    	}else{
			$topTime = 0;    		
    	}
    	$query = $this->db->query("UPDATE company_job_info SET isTop = {$isTop} ,topTime = {$topTime} WHERE id = {$id}");
    	if($query){
    		echo json_encode(array('result'=>'1','msg'=>"操作成功"));
    	}else{
    		echo json_encode(array('result'=>'-1','msg'=>'操作失败。'));
    	}
    	exit();
    }
    /**
     * 显示企业所有信息
     */
    public function getCompanyInfo(){
    	$companyId = $_REQUEST['companyId'];
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM company_info WHERE id = {$companyId}"));
    	$result['status'] = $this->auditStatus[$result['status']];
    	$result['createTime'] = date('Y-m-d',$result['createTime']);
    	$result['desc'] = nl2br($result['desc']);
    	$result['lastUpdateTime'] = date('Y-m-d',$result['lastUpdateTime']);  
    	$this->tpl->assign('list',$result);
    	$this->tpl->display('company_info');
    }
    /**
     * 显示职位详细信息
     */
    public function getJobInfo(){
    	$jobId = $_REQUEST['jobId'];
    	$result = $this->db->fetch_assoc($this->db->query("SELECT i.name as companyName,i.status as companyStatus,j.* FROM company_job_info AS j LEFT JOIN company_info AS i ON j.companyId = i.id  WHERE j.id = {$jobId}"));
    	$result['status'] = $this->auditStatus[$result['status']];
    	$result['companyStatus'] = $this->auditStatus[$result['companyStatus']];
    	$result['disableType'] = $this->disableType[$result['type']];
    	$result['degree'] = $this->degree[$result['degree']];
    	$result['salary'] = $this->salary[$result['salary']];
    	$result['createTime'] = date('Y-m-d',$result['createTime']);
    	$result['cutofftime'] = date('Y-m-d',$result['cutoffTime']);
    	$result['publishStatus'] = $this->jobPublishStatus[$result['publishStatus']];
    	$result['desc'] = nl2br($result['desc']);
    	$result['lastUpdateTime'] = date('Y-m-d',$result['lastUpdateTime']);  
    	$this->tpl->assign('list',$result);
    	$this->tpl->display('job_info');
    }
}
?>

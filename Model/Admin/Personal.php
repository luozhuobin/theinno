<?php
Class Model_Personal extends Control_Admin
{
	
	function __construct($view=false)
	{
		parent::__construct(get_class($this));
	}
	
    public function init()
    {
    	##搜索条件
    	$where = ' WHERE 1 ';
    	if($_REQUEST['status'] != ''){
    		if($_REQUEST['status'] == 0){
    			$where .= " AND (i.status = '0' OR i.status IS NULL)";
    		}else{
    			$where .= " AND i.status = '{$_REQUEST['status']}'";
    		}
    		
    	}
    	if(!empty($_REQUEST['email'])){
    		$where .=" AND l.email = '{$_REQUEST['email']}'";
    	}
    	if(!empty($_REQUEST['name'])){
    		$where .= " AND i.name = '{$_REQUEST['name']}'";
    	}
    	$page = empty($_GET['page'])||$_GET['page']<0?1:$_GET['page'];
    	$result = $this->db->fetch_assoc($this->db->query("SELECT count(*) AS total FROM personal_login AS l LEFT JOIN personal_info AS i USING(`id`) {$where}"));
    	$pagesize = 15;
    	$page = $page>ceil($result['total']/$pagesize)?ceil($result['total']/$pagesize):$page;
    	$offset = ($page-1)*$pagesize;
    	$sql = "SELECT l.id AS personalId,l.email,l.createTime as time,l.click,i.* FROM personal_login AS l LEFT JOIN personal_info AS i USING(`id`) {$where} ORDER BY lastUpdateTime DESC LIMIT {$offset},{$pagesize}";
    	$query = $this->db->query($sql);
    	$list = array();
    	while($row = $this->db->fetch_assoc($query)){
    		$row['type'] = $this->disableType[$row['type']];
    		$row['degree'] = $this->degree[$row['degree']];
    		$row['createTime'] = date('Y-m-d H:i:s',$row['time']);
    		$row['int_status'] = $row['status'];
    		$row['status'] = empty($this->auditStatus[$row['status']])?$this->auditStatus['0']:$this->auditStatus[$row['status']];
    		$list[] = $row;
    	}
    	$SubPages = new SubPages(15,$result['total'],$page,5,WEBDOMAIN.'?c=admin&m=Personal&status='.$_REQUEST['status'].'&email='.$_REQUEST['email'].'&name='.$_REQUEST['name'].'&page=',2);
   		$subPageCss2 = $SubPages->subPageCss2();
   		$this->tpl->assign('subPageCss2',$subPageCss2);  
    	$this->tpl->assign("list",$list);
    	$this->tpl->assign('total',$result['total']);
    	$this->tpl->assign('page_total',ceil($result['total']/$pagesize));
    	$this->tpl->assign('status',$_POST['status']);
    	$this->tpl->assign('auditStatus',$this->auditStatus);
        $this->tpl->display('Personal_list');
    }
	/**
     * 修改简历置顶状态
     */
    public function changeIsTop(){
    	$id = intval($_POST['id']);
    	$isTop = intval($_POST['isTop']);
    	if($isTop == 1){ 
			$topTime = time();  		
    	}else{
			$topTime = 0;    		
    	}
    	$query = $this->db->query("UPDATE personal_info SET isTop = {$isTop} ,topTime = {$topTime} WHERE id = {$id}");
    	if($query){
    		echo json_encode(array('result'=>'1','msg'=>"操作成功"));
    	}else{
    		echo json_encode(array('result'=>'-1','msg'=>'操作失败。'));
    	}
    	exit();
    }
    /**
     * 求职者资料审核
     */
    public function changeStatus(){
    	$personalId = intval($_POST['personalId']);
    	$status = $_POST['status'];
    	$content = $_POST['content'];
    	$sql = "UPDATE `personal_info` SET status = {$status} WHERE id={$personalId}";
    	$query = $this->db->query($sql);
    	if($query){
    		##发送通知邮件
    		$personalInfo = $this->db->fetch_assoc($this->db->query("SELECT l.email,i.name  FROM personal_login AS l LEFT JOIN personal_info AS i USING(`id`) WHERE id = {$personalId}"));
			$template = file_get_contents('./Template/admin/admin_notice_email_template.html');
			if($status == 1){
				$content = '您在<a href="http://hrh.theinno.org" target="_blank">残疾人就业热线平台</a>注册的简历，已被审核通过。';
			}else{
				$content = '您好，您在<a href="http://hrh.theinno.org" target="_blank">残疾人就业热线平台</a>注册简历暂不能审核通过，原因如下：<br />'.$content;
			}
			$search = array('{$name}','{$content}');
			$replace = array($personalInfo['name'],$content);
			$body = str_replace($search,$replace,$template);
			$subject = "简历资料审核结果通知 - 残疾人就业热线平台";		
			$email_class = new SmtpEmailEx();
			$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $personalInfo['email'], $subject, $body);
			$emailLog = $this->db->query("INSERT INTO `log_email`(`id`,`email`,`type`,`content`,`createTime`) VALUES(null,'{$personalInfo['email']}','$subject','$body','".time()."')");
    		echo json_encode(array('result'=>'1','msg'=>$this->auditStatus[$status]));
    	}else{
    		echo json_encode(array('result'=>'-1','msg'=>'修改失败，请刷新页面'));
    	}
    	exit();
    }
}
?>

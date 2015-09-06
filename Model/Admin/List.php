<?php
Class Model_List extends Control_Admin
{
	public $_type = array(1=>"活动培训",2=>"残就新闻",3=>"名企招聘",4=>"关于映诺");
	function __construct($view=false)
	{
		parent::__construct(get_class($this));
		
	}
	
    public function init()
    {
    	##搜索条件
    	$where = ' WHERE status in(1,2) ';
    	if(!empty($_REQUEST['type'])){
    		$where .= " AND type = '{$_REQUEST['type']}'";
    	}
    	if(!empty($_POST['title'])){
    		$where .= " AND title = '{$_REQUEST['title']}'";
    	}
    	$page = empty($_REQUEST['page'])||$_REQUEST['page']<0?1:$_REQUEST['page'];
    	$pagesize = 15;
    	$result = $this->db->fetch_assoc($this->db->query("SELECT count(*) as total FROM news {$where}"));
    	$page = $page>ceil($result['total']/$pagesize)?ceil($result['total']/$pagesize):$page;
    	$offset = ($page-1)*$pagesize;
    	$sql = "SELECT * FROM news {$where} ORDER BY publish_time DESC LIMIT {$offset},{$pagesize}";
    	$query = $this->db->query($sql);
    	$status = array('1'=>"<font color='green'>发布中</font>",'2'=>'<font color="red">已屏蔽</font>','3'=>'<font color="red">已删除</font>');
    	$slide_show = array('1'=>'<font color="red">已设为幻灯片</font>');
    	$list = array();
    	while($row = $this->db->fetch_assoc($query)){
    		$row['status_show'] = $status[$row['status']];
    		$row['slide_show'] = $slide_show[$row['slide_show']];
    		$row['type'] = $this->_type[$row['type']];
    		$row['url'] = "?m=news&action=info&nId={$row['id']}";
    		$list[] = $row;
    	}
    	$SubPages = new SubPages($pagesize,$result['total'],$page ,5,"?c=admin&m=list&type={$_REQUEST['type']}&title={$_REQUEST['title']}&page=",2);
		$subPageCss2 = $SubPages->subPageCss2();
		$this->tpl->assign('subPageCss2',$subPageCss2);
    	$this->tpl->assign('list',$list);
    	$this->tpl->assign('total',$result['total']);
    	$this->tpl->assign('page_total',ceil($result['total']/$pagesize));
        $this->tpl->display('List');
    }
    
    ##代发简历
    public function resumeAgency(){
    	if(!empty($_POST['submit'])){
    		ini_set("max_execution_time",0);
    		extract($_POST);
    		$dir = "./Template/UploadFiles/Images/personal/".date('Ym').'/';
    		if($_FILES['logo']['size']>0){
				if(!empty($_FILES['logo']['name'])){
					$avatar = $this->checkUploadFile($_FILES['logo'],$dir);
				}
			}
    		##发送应聘邮件
			if(!empty($_POST['job'])){
				$job = explode("\r\n",$_POST['job']);
				foreach($job as $key=>$value){
					if(empty($value)){
						continue;
					}
					$avatar = empty($avatar)?'/Template/2012/images/default.jpg':$avatar;
					$arr = explode('=',$value);
					$subject = "{$_POST['name']} 向贵公司 {$arr[1]} 职位投递了简历，请及时 查收 - 残疾人就业热线平台";	
					$template = file_get_contents('./Template/Admin/resume_agency_email_template.html');
					$search = array('{$companyName}','{$personalName}','{$personalPhoto}','{$jobName}','{$sex}','{$birthday}','{$degree}','{$disableType}','{$disableRate}','{$expJob}','{$workExp}','{$eduExp}','{$intro}','{$phone}','{$email}','{$qq}');
					$info .= $sex."&nbsp;&nbsp;<br /><strong>出生日期：</strong>".$birthday."&nbsp;&nbsp;<strong>学历：</strong>".$this->degree[$degree]."&nbsp;&nbsp;<br /><strong>残疾类型：</strong>".$this->disableType[$disableType]."&nbsp;&nbsp;<strong>残疾等级：</strong>".$this->disableRate[$disableRate];
					$replace = array($arr[0],$_POST['name'],$avatar,$arr[1],$sex,$birthday,$this->degree[$degree],$this->disableType[$diableType],$this->disableRate[$disableRate],$expJob,nl2br($workExp),nl2br($eduExp),nl2br($intro),$phone,$email,$qq);
					$body = str_replace($search,$replace,$template);
					$email_class = new SmtpEmailEx();
					$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $arr[2], $subject, $body);
					if($result>0){
            			##邮件记录
            			$emailLog = $this->db->query("INSERT INTO `log_email`(`id`,`email`,`type`,`content`,`createTime`) VALUES(null,'{$arr[2]}','job_agency','$body','".time()."')",1024);
            			##代发简历记录
            			unset($_POST['action']);
            			unset($_POST['submit']);
            			unset($_POST['job']);
            			foreach($_POST as $key=>$value){
            				$keys[] = '`'.$key.'`';
            				$values[] = "'".$value."'";
            			}
            			$sql = "INSERT INTO log_resume_agency (`id`,".implode(',',$keys).",`avatar`,`companyName`,`jobName`,`c_email`,`createTime`) VALUES(null,".implode(',',$values).",'{$avatar}','{$arr[0]}','{$arr[1]}','{$arr[2]}',".time().")";
            			$q = $this->db->query($sql);
    	        	}
				}
				$affected_rows = $this->db->affected_rows();
				$this->message('成功发送'.$affected_rows.'封邮件','suc','返回','?c=admin&m=list&action=resumeAgency');
			}
   			
    	}
    	$id = $_GET['id'];
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM log_resume_agency WHERE id = {$id}"));
    	$this->tpl->assign('result',$result);
    	unset($this->disableType[0]);
    	$this->tpl->assign('disableType',$this->disableType);
    	$this->tpl->assign('disableRate',$this->disableRate);
    	unset($this->degree[0]);
    	$this->tpl->assign('degree',$this->degree);
    	$this->tpl->display('resumeAgency');
    }
    ##代发职位
    public function jobAgency(){
    	if(!empty($_POST['submit'])){
    		##邀请邮件
			if(!empty($_POST['personal'])){
				$job = explode("\r\n",$_POST['personal']);
				foreach($job as $key=>$value){
					if(empty($value)){
						continue;
					}
					$arr = explode('=',$value);
					$subject = "{$_POST['companyName']} 招聘{$_POST['jobName']}职位，你感兴趣吗？ - 残疾人就业热线平台";	
					$template = file_get_contents('./Template/Admin/job_agency_email_template.html');
					$search = array('{$personalName}','{$companyName}','{$jobName}','{$info}','{$jobInfo}','{$companyInfo}','{$contactInfo}');
					$info = '<strong>工作地点：</strong>&nbsp;&nbsp;'.$_POST['city'].'&nbsp;&nbsp;&nbsp;&nbsp;<strong>招聘人数：</strong>&nbsp;&nbsp;'.$this->jobNum[$_POST['num']].'<br /><strong>工作年限:</strong>&nbsp;&nbsp;'.$this->workLength[$_POST['workLength']].'&nbsp;&nbsp;&nbsp;&nbsp;<strong>学历：</strong>'.$this->degree[$_POST['degree']];
					$replace = array($arr[0],$_POST['companyName'],$_POST['jobName'],$info,nl2br($_POST['jobInfo']),nl2br($_POST['companyInfo']),nl2br($_POST['contact']));
					$body = str_replace($search,$replace,$template);
					$email_class = new SmtpEmailEx();
					$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $arr[1], $subject, $body);
					if($result>0){
            			##邮件记录
            			$emailLog = $this->db->query("INSERT INTO `log_email`(`id`,`email`,`type`,`content`,`createTime`) VALUES(null,'{$arr[1]}','job_agency','$body','".time()."')",1024);
            			##代发职位记录
            			unset($_POST['action']);
            			unset($_POST['submit']);
            			unset($_POST['personal']);
            			foreach($_POST as $key=>$value){
            				$keys[] = '`'.$key.'`';
            				$values[] = "'".$value."'";
            			}
            			$sql = "INSERT INTO log_job_agency (`id`,".implode(',',$keys).",`name`,`email`,`createTime`) VALUES(null,".implode(',',$values).",'{$arr[0]}','{$arr[1]}',".time().")";
            			$q = $this->db->query($sql);
    	        	}
				}
				$affected_rows = $this->db->affected_rows();
				$this->message('成功发送'.$affected_rows.'封邮件','suc','返回','?c=admin&m=list&action=jobAgency');
			}
    	}
    	$id = $_GET['id'];
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM log_job_agency WHERE id = {$id}"));
    	$this->tpl->assign('result',$result);
    	$this->tpl->assign('workLength',$this->workLength);
    	$this->tpl->assign('degree',$this->degree);
    	$this->tpl->assign('jobNum',$this->jobNum);
    	$this->tpl->display('jobAgency');
    }
    
    public function jobAgencyList(){
    	$page = empty($_GET['page'])||$_GET['page']<0?1:$_GET['page'];
    	$pagesize = 15;
    	$condition = array();
    	$where = '';
    	if(!empty($_REQUEST['cName'])){
    		$condition[] = "`companyName`='{$_REQUEST['cName']}'";
    	}
    	if(!empty($_REQUEST['jName'])){
    		$condition[] = "`jobName`='{$_REQUEST['jName']}'";
    	}
    	if(!empty($_REQUEST['name'])){
    		$condition[] = "`name`='{$_REQUEST['name']}'";
    	}
    	if(!empty($condition)){
    		$where = implode(' AND ',$condition);
    		$where = 'where '.$where;
    	}
    	$result = $this->db->fetch_assoc($this->db->query("SELECT count(*) AS total FROM log_job_agency {$where}"));
    	$ceil = ceil($result['total']/$pagesize);
    	$page = $page>$ceil?$ceil:$page;
    	$offset = ($page-1)*$pagesize;
    	$query = $this->db->query("SELECT * FROM log_job_agency {$where} ORDER BY createTime DESC LIMIT {$offset},{$pagesize}");
    	$list = array();
    	while($row = $this->db->fetch_assoc($query)){
    		$row['createTime'] = date('Y-m-d H:i:s',$row['createTime']);
    		$row['workLength'] = $this->workLength[$row['workLength']];
    		$row['degree'] = $this->degree[$row['degree']];
    		$row['disableType'] = $this->disableType[$row['disableType']];
    		$row['disableRate'] = $this->disableRate[$row['disableRate']];
    		$list[] = $row;
    	}
    	$SubPages = new SubPages(15,$result['total'],$page,5,'?c=admin&m=list&action=jobAgencyList&cName='.$_REQUEST['cName'].'&jName='.$_REQUEST['jName'].'&name='.$_REQUEST['name'].'&page=',2);
   		$subPageCss2 = $SubPages->subPageCss2();
   		$this->tpl->assign('subPageCss2',$subPageCss2);  
    	$this->tpl->assign('list',$list);
    	$this->tpl->display('job_agency_log_list');
    }
    public function jobAgencyInfo(){
    	$agencyId = $_GET['id'];
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM `log_job_agency` WHERE id = {$agencyId}"));
    	$result['degree'] = $this->degree[$result['degree']];
    	$result['jobInfo'] = nl2br($result['jobInfo']);
    	$result['companyInfo'] = nl2br($result['companyInfo']);
    	$result['contact'] = nl2br($result['contact']);
    	$result['createTime'] = date('Y-m-d H:i:s',$result['createTime']);
    	$result['num'] = $this->jobNum[$result['num']]; 
    	$this->tpl->assign('list',$result);
    	$this->tpl->display('job_agency_info');
    }
    
    public function resumeAgencyList(){
    	$page = empty($_GET['page'])||$_GET['page']<0?1:$_GET['page'];
    	$pagesize = 15;
    	$condition = array();
    	$where = '';
    	if(!empty($_REQUEST['cName'])){
    		$condition[] = " `companyName`='{$_REQUEST['cName']}'";
    	}
    	if(!empty($_REQUEST['jName'])){
    		$condition[] = "`jobName`='{$_REQUEST['jName']}'";
    	}
    	if(!empty($_REQUEST['name'])){
    		$condition[] = "`name`='{$_REQUEST['name']}'";
    	}
    	if(!empty($condition)){
    		$where = implode(' AND ',$condition);
    		$where = 'where '.$where;
    	}
    	$result = $this->db->fetch_assoc($this->db->query("SELECT count(*) AS total FROM log_resume_agency {$where}"));
    	$ceil = ceil($result['total']/$pagesize);
    	$page = $page>$ceil?$ceil:$page;
    	$offset = ($page-1)*$pagesize;
    	$query = $this->db->query("SELECT * FROM log_resume_agency {$where} ORDER BY createTime DESC LIMIT {$offset},{$pagesize}");
    	$list = array();
    	while($row = $this->db->fetch_assoc($query)){
    		$row['createTime'] = date('Y-m-d H:i:s',$row['createTime']);
    		$row['workLength'] = $this->workLength[$row['workLength']];
    		$row['degree'] = $this->degree[$row['degree']];
    		$row['disableType'] = $this->disableType[$row['disableType']];
    		$row['disableRate'] = $this->disableRate[$row['disableRate']];
    		$list[] = $row;
    	}
    	$SubPages = new SubPages(15,$result['total'],$page,5,'?c=admin&m=list&action=resumeAgencyList&cName='.$_REQUEST['cName'].'&jName='.$_REQUEST['jName'].'&name='.$_REQUEST['name'].'&page=',2);
   		$subPageCss2 = $SubPages->subPageCss2();
   		$this->tpl->assign('subPageCss2',$subPageCss2);  
    	$this->tpl->assign('list',$list);
    	$this->tpl->display('resume_agency_log_list');
    }
    public function resumeAgencyInfo(){
    	$agencyId = $_GET['id'];
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM `log_resume_agency` WHERE id = {$agencyId}"));
    	$result['degree'] = $this->degree[$result['degree']];
    	$result['disableType'] = $this->disableType[$result['disableType']];
    	$result['disableRate'] = $this->disableRate[$result['disableRate']];
    	$result['workExp'] = nl2br($result['workExp']);
    	$result['eduExp'] = nl2br($result['eduExp']);
    	$result['intro'] = nl2br($result['intro']);
    	$result['createTime'] = date('Y-m-d H:i:s',$result['createTime']);
    	$this->tpl->assign('list',$result);
    	$this->tpl->display('resume_agency_info');
    }
    //-----------------------------------------------@todo 内容修改 -----------------------------------------
	public function add()
    {
    	$id = $_GET['id'];
    	$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM news WHERE id = {$id}"));
    	$this->tpl->assign("info",$result);
        $this->tpl->display('ListModify');
    }

	

	public function modify()
	{
		$sqlplus = '';
		if($_FILES['index_img']['size']){
			$cover = $this->Uploadfile->upload('index_img',UploadFile.'Cover/',array('*'));
			$coverImg = explode('hrh.theinno.org',$cover['index_img']);
			$sqlplus .= ",index_img = '{$coverImg[1]}'";
		}
		if($_FILES['project_img']['size']){
			$notice = $this->Uploadfile->upload('project_img',UploadFile.'Cover/',array('*'));
			$noticeImg = explode('hrh.theinno.org',$notice['project_img']);
			$sqlplus .= ",project_img  = '{$noticeImg[1]}'";
		}
		$newid = !empty($_REQUEST['id'])?$_REQUEST['id']:'';
		if(empty($newid)){
			##新增内容
			$sql = "INSERT INTO news(`id`,`title`,`intro`,`desc`,`type`,`author`,`index_img`,`project_img`,`status`,`publish_time`,`lastUpdateTime`) VALUES(null,'{$_POST['title']}','{$_POST['intro']}','{$_POST['desc']}','{$_POST['type']}','{$_POST['author']}','{$coverImg[1]}','{$noticeImg[1]}',1,'".time()."','".time()."')";
		}else{
			##修改内容
			$post = $_POST;
			unset($post['B1']);
			unset($post['id']);
			unset($post['action']);
			$tmp = array();
			foreach($post as $key=>$value){
				$tmp[] = "`{$key}` = '".trim($value)."'";
			}
			$sql = "UPDATE news SET ".implode(',',$tmp).$sqlplus.",lastUpdateTime = ".time()." WHERE id=".$_POST['id'];
		}
		$query = $this->db->query($sql,1000);
		$this->message("修改成功",'suc','内容列表','list');
	}

	/**
	 * 设置为首页幻灯片显示
	 */
	public function setSlideShow(){
		$id = $_POST['id'];
		$status = $_POST['status'];
		if(empty($id)){
			echo json_encode(array('result'=>'-1','msg'=>'<font color="red">参数传递错误。</font>'));
			exit();	
		}
		$sql = "UPDATE news SET slide_show = {$status},lastUpdateTime = ".time()." WHERE id = {$id}";
		$query = $this->db->query($sql);
		if($query){
			if($status == 1){
				echo json_encode(array('result'=>"1",'msg'=>'<font color="red">已设为幻灯片</font>'));
				exit();
			}else{
				echo json_encode(array('result'=>"1",'msg'=>''));
				exit();
			}
		}else{
			echo json_encode(array('result'=>"-1",'msg'=>'<font color="red">修改失败，请重试。</font>'));
			exit();
		}
	}
	/**
	 * 友情链接
	 */
	public function friendLink(){
		if(!empty($_POST)){
			$query = $this->db->query("UPDATE friend_link SET content = '{$_POST['content']}'");
			if($query){
				$this->message("修改成功",'suc','友情 链接','?c=admin&m=list&action=friendLink');
			}else{
				$this->message("修改失败",'err','友情 链接','?c=admin&m=list&action=friendLink');
			}
		}
		$result = $this->db->fetch_assoc($this->db->query("SELECT * FROM friend_link"));
		$this->tpl->assign('friendLink',$result['content']);
		$this->tpl->display('friendLink');
	}
	
	//-----------------------------------------------	@todo 函数们		 -----------------------------------------

	public function search()
	{
		$this->keyword = !empty($_REQUEST['keyword'])?$_REQUEST['keyword']:'';
		$this->content = !empty($_REQUEST['content'])?true:false;
		if(!empty($_REQUEST['class_id']))
			$this->sqlplus .= sprintf(" AND content.class_id=%d",$_REQUEST['class_id']);
		if(!empty($this->keyword)){
			$this->sqlplus .= " AND (content.title LIKE '%{$this->keyword}%'  OR content.title='{$this->keyword}')";
		}

	}

	## 排序
	public function orderBy()
	{
		$order = !empty($_REQUEST['order'])?$_REQUEST['order']:'content.content_id';
		$sort =	!empty($_REQUEST['sort'])?$_REQUEST['sort']:'desc';
		$this->orderBy = " ORDER BY {$order} {$sort} ";
		$sort = $sort=='asc'?'desc':'asc';
		$this->tpl->assign('sort',$sort);
	}


	# 删除
	public function del()
	{
		$id = !empty($_REQUEST['id'])?$_REQUEST['id']:'';
		## 删除一些文件
		$arr = Table::getTable('content')->select(array("content_id"=>$id));
		$arr = $arr[0];
		# 封面
		!empty($arr['cover']) && file_exists($arr['cover']) && unlink($arr['cover']);
		## 删除内容
		$sql = "DELETE FROM content WHERE content_id=".(int)$id;
		$this->db->query($sql);
		$this->message('删除成功!','suc','新闻列表','list');
		$this->init();
	}

	public function changeStatus()
	{
		$id = !empty($_REQUEST['id'])?$_REQUEST['id']:'';
		$status = $_REQUEST['status'];
		$sql = "UPDATE news SET status={$status} WHERE id={$id}";
		$query = $this->db->query($sql);
		$arr = array('1'=>'<font color="green">发布中</font>','2'=>'<font color="red">已屏蔽</font>');
		echo json_encode(array('result'=>'1','msg'=>$arr[$status]));
		exit();
	}
	public function upload4ListModify()
	{
		$filepath='Template/UploadFiles/UpImg/'.date('Ym').'/';
		isdir($filepath);
		$editor = new CKeditor(md5('%T^YY&UU'));
		$editor->uploadImage($filepath);
		exit;
	}
}
?>
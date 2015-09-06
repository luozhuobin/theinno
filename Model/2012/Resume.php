<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
class Model_Resume extends Init {
	
	private $table_name = "personal_info";
	private $perPage = 10;
	function __construct($view = false) {
		parent::__construct ();
	}
	
	public function init() {
		$job = $_GET ['job'];
		$area = $_GET ['area'];
		$workLength = intval($_GET['worklength']);
		$salary = intval($_GET['salary']);
		$jobStatus = '1,2';
		$page = intval($_GET['page']) >  1  ? intval($_GET['page']) : 1;
		$pagesize = intval($_GET['pagesize'])>1? intval($_GET['pagesize']) : 30;
		$offset = ($page - 1) * $pagesize;
		$result = Array ();
		$resumes = $this->getNewResume($job,$area,$workLength,$salary,'1,2',$page,$pagesize,1);
		$this->tpl->assign ( 'resumes', $resumes['list'] );
		$SubPages = new SubPages ( $pagesize, $resumes ['total'], $page, 5, "?m=resume&page=", 2 );
		$subPageCss1 = $SubPages->subPageCss1 ();
		$this->tpl->assign ( 'subPageCss1', $subPageCss1 );
		unset ( $this->disableType [0] );
		##推荐简历
		$topResume = $this->getNewResume ( '', '','','',1, 1, 100 ,1);
		$this->tpl->assign ( 'topResume', $topResume['list'] );
		$this->tpl->assign ( 'j', $jkw );
		$this->tpl->assign ( 'dtype', $dtype );
		$this->tpl->assign ( 'area', $area );
		$this->tpl->assign ( 'other_area', $other_area );
		$this->tpl->assign ( "displayType", $this->disableType );
		$this->tpl->display ( 'resume_search' );
	}
	
	/**
	 * 个人简历预览
	 */
	public function view() {
		$pId = base64_decode ( $_GET ['p'] );
		if (empty ( $pId )) {
			##简历参数传递错误
			$this->message ( '连接参数错误！', 'http://' . $_SERVER ['SERVER_NAME'] );
			exit ();
		}
		##验证查看简历者身份，只有企业或者简历所属人才能查看简历
		$personalId = $this->Session->get ( 'personalId' );
		$companyId = $this->Session->get ( 'companyId' );
		$admin_id = $this->Session->get ( 'admin_id' );
		if (empty ( $personalId ) && empty ( $companyId ) && empty ( $admin_id )) {
			$this->message ( '必须登录之后才能查看简历', 'http://' . $_SERVER ['SERVER_NAME'] );
			exit ();
		}
		if (! empty ( $personalId ) && $personalId != $pId) {
			$this->message ( '抱歉，你不能查看其它求职者的简历。', 'javascript:history.back()' );
			exit ();
		}
		##企业发布职位列表
		if (! empty ( $companyId )) {
			$sql = "SELECT `id`,`name` FROM company_job_info WHERE companyId = {$companyId} AND status = 1 AND publishStatus = 1";
			$jobList = $this->db->fetch_all_assoc ( $sql );
			$this->tpl->assign ( 'jobList', $jobList );
			##统计简历浏览数
			$sql = "UPDATE personal_login SET click = click + 1 WHERE id = {$pId}";
			$q = $this->db->query ( $sql );
		}
		$sql = "SELECT * FROM `personal_login` AS l LEFT JOIN `personal_info` AS i USING(`id`) LEFT JOIN `personal_exp` USING(`id`) WHERE id = {$pId}";
		$info = $this->db->fetch_assoc ( $this->db->query ( $sql ) );
		$info ['degree'] = $this->degree [$info ['degree']];
		$info ['type'] = $this->disableType [$info ['type']];
		$info ['workLength'] = $this->workLength [$info ['workLength']];
		$info ['salary'] = $this->salary [$info ['salary']];
		$info ['sex'] = $this->_sex [$info ['sex']];
		$info ['hillock'] = $this->hillock [$info ['hillock']];
		$info ['disableRate'] = $this->disableRate [$info ['disableRate']];
		$this->tpl->assign ( 'info', $info );
		$this->tpl->display ( 'resume_view' );
	}
	/**
	 * @desc 获取最新简历
	 * @param string $job 期望职位
	 * @param striing $area 期望工作地方
	 * @page int 页数
	 * @pagesize 每页显示条数
	 */
	public function getNewResume($job = '', $area = '',$workLength = '',$salary='',$jobStatus = '1', $page = 1, $pagesize = 30,$isTop = 0) {
		$condition = array ("status = 1", "jobStatus in ({$jobStatus})" );
		if (! empty ( $job )) {
			$condition [] = ' job = "' . $job . '"';
		}
		if (! empty ( $area )) {
			$condition [] = ' expAddress = "' . $area . '"';
		}
		if (! empty ( $workLength )) {
			$condition [] = ' workLength = ' . $workLength ;
		}
		if (! empty ( $salary )) {
			$condition [] = ' salary = ' . $salary;
		}
		$where = ! empty ( $condition ) ? ' where ' . implode ( " AND ", $condition ) : '';
		$sql = "SELECT COUNT(id) AS total FROM `personal_info` {$where}";
		$result = $this->db->fetch_assoc($this->db->query($sql));
		$offset = $page > 1 ? ($page - 1) * $pagesize : 0;
		$sql = "SELECT * FROM `personal_info` {$where}";
		if($isTop == 1){
			$sql .= " ORDER BY isTop DESC,topTime DESC,lastUpdateTime DESC";
		}else{
			$sql .= " ORDER BY lastUpdateTime DESC";
		}
		$sql .= " LIMIT {$offset},{$pagesize}";
		$query = $this->db->query ( $sql );
		while ( $row = $this->db->fetch_assoc ( $query ) ) {
			$row['pId'] = base64_encode($row['id']);
			$row['type'] = $this->disableType[$row['type']];
			$row['workLength'] = $this->workLength[$row['workLength']];
			$row['avatar'] = empty($row['avatar']) ? '/Template/2012/images/default.jpg' : $row['avatar'];
			$row ['lastUpdateTime'] = formateDay ( $row ['lastUpdateTime'] ,'Y-m-d');
			$tmp [] = $row;
		}
		$data = array("list"=>$tmp,"total"=>$result["total"]);
		return $data;
	}
	/**
	 * @desc 获取简历详情
	 * @param int $resumeId 简历id
	 * @return json
	 */
	public function getResumeDetail(){
		$resumeId = intval($_POST['resumeId']);
		if(empty($resumeId)){
			$this->ajaxJson("-1","简历id不能为空");			
		}		
		##权限判断，只有企业用户登录之后可看
		$Model_Company = new Model_Company();
		$Model_Company->checkLoginAjax();
		##获取详细内容
		$resume = $this->getResumeDetailById($resumeId);
		$this->ajaxJson("1","",$resume);
	}
	
	/**
	 * @desc 获取简历详情
	 * @param int $resumeId 简历id
	 * @return array
	 */
	public function getResumeDetailById($resumeId){
		if(empty($resumeId)){
			return array();
		}
		$sql = " SELECT * FROM `personal_info` AS i 
					LEFT JOIN `personal_login` AS l USING(id) 
					LEFT JOIN `personal_exp` AS E USING(id)
					WHERE i.id = {$resumeId}";
		$resume = $this->db->fetch_assoc($this->db->query($sql));
		$resume['workLength'] = $this->workLength[$resume['workLength']];
		$resume['avatar'] = empty($resume['avatar']) ? '/Template/2012/images/default.jpg' : $resume['avatar'];
		return $resume;
	}
	
	/**
	 * @desc 7天内是否已经发送面试邀请
	 * @resumeId int 用户Id
	 */
	public function isSendInterview($resumeId){
		##权限判断，只有企业用户登录之后可看
		$Model_Company = new Model_Company();
		$Model_Company->checkLoginAjax();
		$result = $this->db->fetch_assoc($this->db->query("SELECT count(*) as total FROM interview WHERE jobId = {$jobId} AND personalId = {$pId} AND createTime >=".strtotime("-7 days",strtotime(date('Y-m-d')))));
   		if($result['total']>0){
   			$this->ajaxJson("-4","7天内不能重复发送面试通知");
   		}		
	}
}
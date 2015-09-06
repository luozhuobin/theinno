<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
Class Model_Job extends Init
{
	
	private $job_table_name = "company_job_info";
	private $company_table_name = "company_info";
	private $perPage = 10;

	function __construct($view=false)
	{
		parent::__construct();
	}
		##职位搜索
	public function init() {
		$companyId = intval ( $_GET ['cid'] );
		$job = $_GET ['job'];
		$area = $_GET ['area'];
		$workLength = intval ( $_GET ['worklength'] );
		$salary = intval ( $_GET ['salary'] );
		$page = intval ( $_GET ['page'] ) > 1 ? intval ( $_GET ['page'] ) : 1;
		$pagesize = intval ( $_GET ['pagesize'] ) > 1 ? intval ( $_GET ['pagesize'] ) : 30;
		$offset = ($page - 1) * $pagesize;
		$result = Array ();
		$jobs = $this->getNewJob ( $job, $area, $workLength, $salary, $companyId, $page, $pagesize ,1);
		$this->tpl->assign ( 'jobs', $jobs['list'] );
		$SubPages = new SubPages ( $pagesize, $jobs ['total'], $page, 5, "?m=resume&page=", 2 );
		$subPageCss1 = $SubPages->subPageCss1 ();
		$this->tpl->assign ( 'subPageCss1', $subPageCss1 );
		##推荐简历
		$topJob = $this->getNewJob ( '', '', '', '', 0, 1, 100 ,1);
		$this->tpl->assign ( 'topJob', $topJob['list'] );
		$this->tpl->display ( 'job_search' );
	}
	
   ##职位详细页面
   public function info(){
   		$jobId = $_REQUEST['jobId'];
		$table_name = $this->table_name;
		$perPage = $this->perPage;
		$nowPage = ($nowPage - 1)*$perPage;
		$sql = "SELECT * FROM ".$table_name." WHERE id = ".$jobId;
		$job_table_name = $this->job_table_name;
		$company_table_name = $this->company_table_name;
   		$sql = "SELECT  j.* , c.id as cid, c.name as cname FROM ".$job_table_name." j LEFT JOIN ".$company_table_name." c ON j.companyId = c.id  WHERE j.id = ".$jobId;
   		$result = Array();
		$query = $this->db->query($sql);
		while($row = $this->db->fetch_assoc($query)){
			if(in_array($row['jsalary'],array(1,2,3))){
				$row['jsalary'] = $this->_salary[$row['salary']].'元/月';
			}else{
				$row['jsalary'] = $this->_salary[$row['salary']];
			}
			$row['degree'] = $this->degree[$row['degree']];
			$row['workLength'] = $this->workLength[$row['workLength']];
			$row['cutoffTime'] = date('Y.m.d',$row['cutoffTime']);
			$row["pubDate"] = formateDay($row["createTime"]);
			$result = $row;
		}
		$jobInfo = $result;
		##查看用户是否有登录
		$sessionInfo = $this->Session->get('personalId');
		$isLogin = empty($sessionInfo)?'false':'true';
		$this->tpl->assign('isLogin',$isLogin);
		$this->tpl->assign('jobInfo',$jobInfo);
		$this->tpl->display('job_info');
   }
   
	/**
	 * @desc 获取最新职位
	 * @param string $job 期望职位
	 * @param striing $area 期望工作地方
	 * @page int 页数
	 * @pagesize 每页显示条数
	 */
	public function getNewJob($job = '', $area = '',$workLength = '',$salary='',$companyId = 0 ,$page = 1, $pagesize = 30,$isTop = 0) {
		$condition = array ("j.status = 1", "publishStatus = 1" ,"cutoffTime>=". time());
		if (! empty ( $job )) {
			$condition [] = ' job = "' . $job . '"';
		}
		if (! empty ( $area )) {
			$condition [] = ' city = "' . $area . '"';
		}
		if (! empty ( $workLength )) {
			$condition [] = ' workLength = ' . $workLength ;
		}
		if (! empty ( $salary )) {
			$condition [] = ' salary = ' . $salary;
		}
		if (! empty ( $companyId )) {
			$condition [] = ' j.companyId = ' . $companyId;
		}
		$where = ! empty ( $condition ) ? ' where ' . implode ( " AND ", $condition ) : '';
		$sql = "SELECT count(j.id) AS total FROM `company_job_info` AS j 
				LEFT JOIN `company_info` AS i ON j.companyId = i.id 
				{$where} ";
		$result = $this->db->fetch_assoc($this->db->query($sql));
		$offset = $page > 1 ? ($page - 1) * $pagesize : 0;
		$sql = "SELECT j.*,i.avatar,i.name as companyName,i.id as companyId FROM `company_job_info` AS j 
				LEFT JOIN `company_info` AS i ON j.companyId = i.id 
				{$where}";
		if($isTop == 1){
			$sql .= " ORDER BY isTop DESC ,TopTime DESC,lastUpdateTime DESC";
		}else{
			$sql .= " ORDER BY lastUpdateTime DESC ";
		}
		$sql .= " LIMIT {$offset},{$pagesize}";
		$query = $this->db->query ( $sql );
		while ( $row = $this->db->fetch_assoc ( $query ) ) {
			$row['type'] = $this->disableType[$row['type']];
			$row['salary'] = $this->_salary[$row['salary']];
			$row['avatar'] = empty($row['avatar']) ? '/Template/2012/images/default.jpg' : $row['avatar'];
			$row ['lastUpdateTime'] = formateDay ( $row ['lastUpdateTime'] ,'m-d');
			$tmp [] = $row;
		}
		$data = array("list"=>$tmp,"total"=>$result["total"]);
		return $data;
	}
}
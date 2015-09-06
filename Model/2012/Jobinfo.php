<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
Class Model_JobInfo extends Init
{

	private $job_table_name = "company_job_info";
	private $company_table_name = "company_info";
	private $perPage = 10;
	
	function __construct($view=false)
	{
		parent::__construct();
	}

    public function init()
    {
		$jobId = $_REQUEST['jobId'];
		$table_name = $this->table_name;
		$perPage = $this->perPage;
		$nowPage = ($nowPage - 1)*$perPage;
		$sql = "SELECT * FROM ".$table_name." WHERE id = ".$jobId;
		$job_table_name = $this->job_table_name;
		$company_table_name = $this->company_table_name;
		$result = Array();
   		$sql = "SELECT  j.id as jid , c.id as cid , j.name as jname , j.desc as jdesc , j.city as jcity , salary as jsalary , c.name as cname , j.createTime as ctime  FROM ".$job_table_name." j LEFT JOIN ".$company_table_name." c ON j.companyId = c.id  WHERE j.id = ".$jobId;
		$result = Array();
		$query = $this->db->query($sql);
		while($row = $this->db->fetch_assoc($query)){
			$pubTime = $row["ctime"];
			$pubTime = formateDay($pubTime);
			if(in_array($row['jsalary'],array(1,2,3))){
				$jsalary = $this->salary[$row['jsalary']]."元/月";
			}else{
				$jsalary = $this->salary[$row['jsalary']];
			}
			$row["pubDate"] = $pubTime ;
			$row["jsalary"] = $jsalary;
			$result[] = $row;
		}
		$sessioninfo = $this->Session->get('personalId');
		if(!empty($sessioninfo)){
			$isLogin = 'true';
		}else{
			$isLogin = 'false';
		}
		$jobInfo = $result[0];
		print_r($jobInfo);
		$this->tpl->assign('jobInfo',$jobInfo);
		$this->tpl->display('Job_Info');
    }
}
?>

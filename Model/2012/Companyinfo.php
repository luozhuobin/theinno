<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
Class Model_CompanyInfo extends Init
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
		$cId = intval($_REQUEST['comId']);
		$table_name = $this->table_name;
		$perPage = $this->perPage;
		$job_table_name = $this->job_table_name;
		$company_table_name = $this->company_table_name;
		$result = Array();
   		$sql = "SELECT  j.id as jid , c.id as cid , j.name as jname ,j.workLength,j.num,j.degree, j.desc as jdesc , c.desc as cinfo , j.city as jcity , salary as jsalary , c.name as cname , j.createTime as ctime,j.cutoffTime  FROM ".$job_table_name." j RIGHT JOIN ".$company_table_name." c ON j.companyId = c.id  WHERE c.id = ".$cId;
   		$result = Array();
		$cname = "";
		$cinfo = "";
		$query = $this->db->query($sql);
		while($row = $this->db->fetch_assoc($query)){
			if(in_array($row['jsalary'],array(1,2,3))){
				$row['jsalary'] = $this->salary[$row['jsalary']].'元/月';
			}else{
				$row['jsalary'] = $this->salary[$row['jsalary']];
			}
			$row['degree'] = $this->degree[$row['degree']];
			$row["cutoffTime"] = date('Y.m.d',$row['cutoffTime']) ;
			$cname = $row["cname"];
			$cinfo = nl2br($row["cinfo"]);
			$row['num'] = $this->jobNum[$row['num']];
			$row['workLength'] = $this->workLength[$row['workLength']];
			$result[] = $row;
		}
		$this->tpl->assign('jobList',$result);
		$this->tpl->assign('cname',$cname);
		$this->tpl->assign('cinfo',$cinfo);
		$this->tpl->display('company_info_new');
    }
}
?>

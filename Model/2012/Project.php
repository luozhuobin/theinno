<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
Class Model_Project extends Init
{

	private $table_name = "news";
	private $perPage = 10;
	
	
	function __construct($view=false)
	{
		parent::__construct();
	}

    public function init()
    {
    	
    	$pagesize = 7;
    	$page = empty($_GET['page'])||$_GET['page']<0?1:$_GET['page'];
    	$count = $this->db->fetch_assoc($this->db->query("SELECT count(*) AS total FROM {$this->table_name} WHERE type= '项目' AND status = 1 "));
    	$page = $page>(ceil($count['total']/$pagesize))?ceil($count['total']/$pagesize):$page;
    	$offset = ($page-1)*$pagesize;
		$sql = "SELECT * FROM ".$this->table_name." WHERE type = '项目' AND status = 1  ORDER BY lastUpdateTime DESC LIMIT ".$offset." , ".$pagesize;
		$result = Array();
		$query = $this->db->query($sql);
		while($row = $this->db->fetch_assoc($query)){
			$pre_info = $row["desc"];
			$publish_time = $row["publish_time"];
			$pdate = formateDay($publish_time);
			$pre_info = clearFormate($pre_info);
			$pre_info = subStrEx($pre_info , 150);
			$row["desc"] = $pre_info;
			$row['image'] = $row['project_img'];
			$row["pdate"] = $pdate;
			$result[] = $row;
		}
		$SubPages = new SubPages($pagesize,$count['total'],$page ,5,"?m=project&page=",2);
		$subPageCss1 = $SubPages->subPageCss1();
		$this->tpl->assign('subPageCss1',$subPageCss1);
		$this->tpl->assign('projects',$result);
		$this->tpl->display('project');
    }
	
	public function info()
	{
	
		$proId = $_REQUEST['pId'];
		$table_name = $this->table_name;
		$sql = "SELECT * FROM ".$table_name." WHERE id = ".$proId;
		$result = Array();
		$query = $this->db->query($sql);
		while($row = $this->db->fetch_assoc($query)){
			$publish_time = $row["publish_time"];
			$pdate = formateDay($publish_time);
			$row['desc'] = $row['desc'];
			$row["pdate"] = $pdate;
			$result[] = $row;
		}
		$this->tpl->assign('projects',$result[0]);
		$this->tpl->display('project_info');
	}
    
}
?>

<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
Class Model_News extends Init
{

	private $table_name = "news";
	private $perPage = 10;
	
	function __construct($view=false)
	{
		parent::__construct();
	}

    public function init()
    {
		$nowPage = $_REQUEST['nowPage'];
		
		if(empty($nowPage)){
			$nowPage = 1;
		}
		$table_name = $this->table_name;
		$perPage = $this->perPage;
		$nowPage = ($nowPage - 1)*$perPage;
		$sql = "SELECT * FROM ".$table_name." WHERE type = '新闻' LIMIT ".$nowPage." , ".$perPage;

		$result = Array();
//		
		$query = $this->db->query($sql);
		while($row = $this->db->fetch_assoc($query)){
			$pre_info = $row["info"];

			$pre_info = clearFormate($pre_info);
			$pre_info = subStrEx($pre_info , 50);
//			$pre_info = $pre_info."...";
			$row["info"] = $pre_info;
//			print_r($row);
			$result[] = $row;
		}
		
		$this->tpl->assign('news',$result);

		$this->tpl->display('News');
    }
	
	public function info(){
		$nId = $_REQUEST['nId'];
		
		$table_name = $this->table_name;
		
		$sql = "SELECT * FROM ".$table_name." WHERE type = '新闻' WHERE id = ".$nId;

		$result = Array();
//		
		$query = $this->db->query($sql);
		while($row = $this->db->fetch_assoc($query)){
			$pre_info = $row["info"];

			$result[] = $row;
		}
		
		$this->tpl->assign('news',$result);

		$this->tpl->display('NewsInfo');
		
	}
}
?>

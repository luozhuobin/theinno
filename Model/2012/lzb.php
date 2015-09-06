<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
Class Model_Lzb extends Init
{
	

	function __construct($view=false)
	{
		parent::__construct();
	}

    public function init()
    {
//    	$sql = "SELECT * FROM personal_login";
//    	$query = $this->db->query($sql);
//    	while($row = $this->db->fetch_assoc($query)){
//    		$personalId = $row['id'];
//    		$q = $this->db->query("update personal_login set password = '".md5($row['password'])."' WHERE id = {$personalId}");
//    		var_dump($q);
//    	}
//    	
//    	$sql = "SELECT * FROM company_login";
//    	$query = $this->db->query($sql);
//    	while($row = $this->db->fetch_assoc($query)){
//    		$companyId = $row['id'];
//    		$q = $this->db->query("update company_login set password = '".md5($row['password'])."' WHERE id = {$companyId}");
//    		var_dump($q);
//    	}
    }
	public function qq(){
		$sql = "SELECT * FROM company_login WHERE email like '2690386404@qq.com'";
		$result = $this->db->fetch_assoc($this->db->query($sql));
		var_dump($result);
	}
}
?>

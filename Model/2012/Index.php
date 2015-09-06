<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
class Model_Index extends Init {
	
	function __construct($view = false) {
		parent::__construct ();
	}
	
	public function init() {
		// 对简历
		$sql = "SELECT id,name,city,intro,avatar,job,workLength FROM personal_info WHERE status = 1 AND jobStatus in(1,2) ORDER BY lastUpdateTime DESC LIMIT 0,7";
		$ResumeResult = Array ();
		$query = $this->db->query ( $sql );
		while ( $row = $this->db->fetch_assoc ( $query ) ) {
			$pre_info = $row ["intro"];
			$row['workLength'] = $this->workLength[$row['workLength']];
			$pre_info = clearFormate ( $pre_info );
			$pre_info = subStrEx ( $pre_info, 50 );
			$pre_info = $pre_info . "...";
			$row ["intro"] = $pre_info;
			$row ['pId'] = base64_encode ( $row ['id'] );
			if (empty ( $row ['avatar'] )) {
				$row ['avatar'] = './Template/2012/images/default.jpg';
			}
			$ResumeResult [] = $row;
		}
		// 对职位
		$JobResult = $this->getNewJob();
		$this->tpl->assign ( "newResume", $ResumeResult );
		// 对企业
		$CompanyResult = $this->getNewCompany();
		$this->tpl->assign ( "CompanyResult", $CompanyResult );
		##幻灯片
		$query = $this->db->query ( "SELECT id,title,index_img FROM `news` WHERE `slide_show` = 1 ORDER BY lastUpdateTime DESC LIMIT 4" );
		while ( $row = $this->db->fetch_assoc ( $query ) ) {
			$tabList [] = $row;
		}
		##友情链接
		$friendLink = $this->db->fetch_assoc ( $this->db->query ( "SELECT * FROM friend_link" ) );
		$linkArr = explode ( "\r\n", $friendLink ['content'] );
		$link = array ();
		foreach ( $linkArr as $key => $value ) {
			$arr = explode ( "=", $value );
			$link [] = array ('name' => $arr [0], 'link' => $arr [1] );
		}
		$alphabet = $this->getAlphabet();
		$this->tpl->assign ( 'link', $link );
		$this->tpl->assign ( 'alphabet', $alphabet );
		$this->tpl->assign ( 'tabList', $tabList );
		$this->tpl->assign ( "newJob", $JobResult );
		$this->tpl->display ( 'index' );
	}
	
	/**
	 * @desc 获取字母表
	 */
	public function getAlphabet(){
		$tmp = range('A','Z');
		$tmp[] = "#";
		return $tmp;
	}
	/**
	 * @desc 获取最新的企业数据
	 */
	public function getNewCompany($page = 1,$pagesize = 100){
		$offset = $page > 1 ? ($page - 1) * $pagesize : 0;
		$sql = "SELECT id,avatar,name FROM `company_info` WHERE status = 1 ORDER BY createTime DESC LIMIT {$offset},{$pagesize}";
		$CompanyResult = Array ();
		$query = $this->db->query ( $sql );
		while ( $row = $this->db->fetch_assoc ( $query ) ) {
			$CompanyResult [] = $row;
		}
		return $CompanyResult;
	}
	/**
	 * @desc 获取最新的职位数据
	 */
	public function getNewJob($page = 1,$pagesize = 100){
		$offset = $page > 1 ? ($page - 1) * $pagesize : 0;
		$sql = "SELECT id,name FROM `company_job_info` WHERE status = 1 AND publishStatus = 1 AND cutoffTime >= ".time()."
				ORDER BY createTime DESC LIMIT {$offset},{$pagesize}";
		$JobResult = Array ();
		$query = $this->db->query ( $sql );
		while ( $row = $this->db->fetch_assoc ( $query ) ) {
			$JobResult [] = $row;
		}
		return $JobResult;
	}
	/**
	 * @desc 异步获取企业或者职位数据
	 */
	public function getCompanyJob(){
		$type = $_POST['type'];
		$alphabet = $_POST['alphabet'];
		if (empty ( $type ) || empty ( $alphabet )) {
			$this->ajaxJson ( "10000", "参数不能为空" );
		}
		$data = array();
		switch($type){
			case '1':
					##企业
					$data = $this->getNewCompany(1,21);
				break;
			case '2':
					##职位
					$data = $this->getNewJob(1,30);
				break;
			default:
				break;
		}
		if(!empty($data)){
			$Pinyin = new Pinyin();
			foreach($data as $key=>$value){
			     $yin = $Pinyin->conv(mb_substr($value['name'],0,1,'UTF-8'));
			     $k = strtoupper(mb_substr($yin,0,1,'UTF-8'));
			     $k = preg_match('/[A-Z]/',$k)?$k:"#";
			     if($k != $alphabet){
			     	unset($data[$key]);
			     }
			}
		}
		$this->ajaxJson(10001,"",$data);
	}
}
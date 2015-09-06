<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
class Model_Header extends Init {
	
	function __construct($view = false) {
		parent::__construct ();
	}
	
	public function init() {
		$this->tpl->display ( 'header' );
	}
	public function headerMenu() {
		$personal = $this->Session->get ( "personal" );
		$company = $this->Session->get ( "company" );
		$name = "访客";
		$m = "index";
		if (! empty ( $personal )) {
			$name = $personal ['name'];
			$m = "personal";
		}
		
		if (! empty ( $company )) {
			$name = $company ['name'];
			$m = "company";
		}
		$this->tpl->assign ( "name", $name );
		$this->tpl->assign ( "m", $m );
		$this->tpl->assign ( 'personal', $personal );
		$this->tpl->assign ( 'company', $company );
		$this->tpl->display ( 'header_menu' );
	}
	public function getBanner() {
		##获取广告
		$Model_platads = new Model_platads();
		$adsList = $Model_platads->getAdsList("right_couplet");
		$personal = $this->Session->get("personal");
		$company = $this->Session->get("company");
		$this->tpl->assign ( 'adsList', $adsList );
		$this->tpl->assign ( 'personal', $personal );
		$this->tpl->assign ( 'company', $company );
		$this->tpl->display ( 'banner' );
	}
	public function getLeftMenu() {
		$this->tpl->display ( 'left_menu' );
	}
	public function searchForm() {
		$this->tpl->display ( 'search_form' );
	}
	public function searchCondition() {
		$this->tpl->assign ( '_salary', $this->_salary );
		$this->tpl->assign ( '_worklength', $this->_worklength );
		$this->tpl->display ( 'search_condition' );
	}
	public function getFooter() {
		$this->tpl->display ( 'footer' );
	}
	public function forgetModuleLeftMenu(){
		$this->tpl->display ( 'forget_module_left_menu' );
	}
	##页面右边登录表单
	public function getLoginForm() {
		$personalId = $this->Session->get ( 'personalId' );
		$companyId = $this->Session->get ( 'companyId' );
		$identity = "";
		$info = Array ();
		$isLogin = false;
		if (! empty ( $personalId )) {
			$isLogin = true;
			$identity = 'personal';
			$info = $this->db->fetch_assoc ( $this->db->query ( "SELECT i.id,i.`name`,l.`email`,i.avatar FROM `personal_info` AS i RIGHT JOIN `personal_login` AS l USING(`id`) WHERE id = {$personalId}" ) );
			##职位申请
			$jobApply = $this->db->fetch_assoc ( $this->db->query ( "SELECT count(*) as total FROM `job_apply` WHERE personalId = {$personalId}" ) );
			$info ['applyCount'] = $jobApply ['total'];
			##面试通知
			$interview = $this->db->fetch_assoc ( $this->db->query ( "SELECT count(*) as total FROM `interview` WHERE `personalId` = {$personalId}" ) );
			$info ['interviewCount'] = $interview ['total'];
		} else if (! empty ( $companyId )) {
			$identity = 'company';
			$isLogin = true;
			$info = $this->db->fetch_assoc ( $this->db->query ( "SELECT i.id,i.`name`,l.`email`,i.avatar,i.phone,i.status FROM `company_info` AS i RIGHT JOIN `company_login` AS l USING(`id`) WHERE id = {$companyId}" ) );
			$info ['status'] = $this->auditStatus [$info ['status']];
		}
		$this->tpl->assign ( 'isLogin', $isLogin );
		$this->tpl->assign ( 'header_info', $info );
		$this->tpl->assign ( 'header_identity', $identity );
		$this->tpl->display ( "login-form" );
	}
	
	##弹出登录框
	public function getLoginBox() {
		$personalId = $this->Cookie->get ( 'personalId' );
		$companyId = $this->Cookie->get ( 'companyId' );
		
		$this->tpl->display ( "login-box" );
	}
	
	/**
	 * 企业后台后边内容
	 */
	public function companyRight() {
		$query = $this->db->query ( "SELECT id,name FROM personal_info WHERE status = 1 AND jobStatus = 1 ORDER BY lastUpdateTime DESC limit 10" );
		while ( $row = $this->db->fetch_assoc ( $query ) ) {
			$tmp = array ();
			$tmp ['p'] = base64_encode ( $row ['id'] );
			$tmp ['name'] = $row ['name'];
			$latestResume [] = $tmp;
		}
		$this->tpl->assign ( 'latestResume', $latestResume );
		$this->tpl->display ( 'company_right' );
	}
	
	public function getLoginInfo() {
		//    	$personalId = $this->Cookie->get('personalId');
		//    	$companyId = $this->Cookie->get('companyId');
		$personalId = $this->Session->get ( 'personalId' );
		$companyId = $this->Session->get ( 'companyId' );
		$identity = "";
		$info = Array ();
		if (! empty ( $personalId )) {
			$identity = 'personal';
			$info = $this->db->fetch_assoc ( $this->db->query ( "SELECT i.`name`,l.`email` FROM `personal_info` AS i RIGHT JOIN `personal_login` AS l USING(`id`) WHERE id = {$personalId}" ) );
		} else if (! empty ( $companyId )) {
			$identity = 'company';
			$companyId = $this->Session->get ( 'companyId' );
			$info = $this->db->fetch_assoc ( $this->db->query ( "SELECT i.`name`,l.`email` FROM `company_info` AS i RIGHT JOIN `company_login` AS l USING(`id`) WHERE id = {$companyId}" ) );
		}
		
		$this->tpl->assign ( 'header_info', $info );
		$this->tpl->assign ( 'header_identity', $identity );
		$this->tpl->display ( 'header_label' );
	}
	
	/**
	 * 判断验证码是否有误
	 */
	public function codeCheck() {
		session_start ();
		$code = $_REQUEST ['code'];
		if ($code == $_SESSION ['checkcode']) {
			echo json_encode ( array ('result' => '1' ) );
			exit ();
		} else {
			echo json_encode ( array ('result' => '2' ) );
			exit ();
		}
	}
}
?>

<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
class Model_News extends Init {
	
	private $table_name = "news";
	private $perPage = 10;
	
	function __construct($view = false) {
		parent::__construct ();
	}
	
	public function init() {
		$t = intval($_GET['t']) == 0 ? 1 : intval($_GET['t']);
		$page = empty ( $_GET ['page'] ) || $_GET ['page'] < 0 ? 1 : $_GET ['page'];
		$count = $this->db->fetch_assoc ( $this->db->query ( "SELECT count(*) AS total FROM {$this->table_name} WHERE type = '{$t}' AND status = 1" ) );
		$pagesize = 7;
		$page = $page > ceil ( $count ['total'] / $pagesize ) ? ceil ( $count ['total'] / $pagesize ) : $page;
		$offset = $page > 1 ? ($page - 1) * $pagesize : 0;
		$sql = "SELECT * FROM " . $this->table_name . " WHERE type = '{$t}' AND status = 1 ORDER BY lastUpdateTime DESC LIMIT " . $offset . " , " . $pagesize;
		$result = Array ();
		$query = $this->db->query ( $sql );
		while ( $row = $this->db->fetch_assoc ( $query ) ) {
			$pre_info = $row ["desc"];
			$pre_info = clearFormate ( $pre_info );
			$pre_info = subStrEx ( $pre_info, 150 );
			$row['date'] = date("Y-m-d",$row['publish_time']);
			$row ["desc"] = $pre_info;
			$result [] = $row;
		}
		$SubPages = new SubPages ( $pagesize, $count ['total'], $page, 5, "?m=news&t=".$_GET["t"]."&page=", 2 );
		$subPageCss1 = $SubPages->subPageCss1 ();
		$this->tpl->assign ( 'subPageCss1', $subPageCss1 );
		$this->tpl->assign ( 'news', $result );
		$this->tpl->display ( 'News' );
	}
	/**
	 * 项目\新闻详细页
	 */
	public function info() {
		$nId = intval($_REQUEST ['nId']);
		$table_name = $this->table_name;
		$sql = "SELECT * FROM " . $table_name . " WHERE id = " . $nId;
		$result = Array ();
		$query = $this->db->query ( $sql );
		while ( $row = $this->db->fetch_assoc ( $query ) ) {
			$row ["publish_time"] = formateDay ( $row ["publish_time"] );
			$result  = $row;
		}
		##上一篇
		$preId = $nId > 1 ? $nId - 1 : 1;
		$preNews = $this->getNewsDetail($preId);
		##下一篇
		$nextId = $nId + 1;
		$nextNews = $this->getNewsDetail($nextId);
		$this->tpl->assign ( 'news', $result);
		$this->tpl->assign ( 'preNews', $preNews);
		$this->tpl->assign ( 'nextNews', $nextNews);
		$this->tpl->display ( 'news_info' );
	
	}
	/**
	 * @desc 获取文章内容
	 * @param int @id 文章内容
	 * @return array
	 */
	public function getNewsDetail($id){
		$sql = "SELECT * FROM news WHERE id = " . $id;
		$result = Array ();
		$query = $this->db->query ( $sql );
		$row = $this->db->fetch_assoc ( $query );
		return $row;
	}
}
?>

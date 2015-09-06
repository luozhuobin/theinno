<?PHP
Class Model_Class extends Control_Admin
{
    public $list = array();
    public $param = array();

	function __construct($view=false)
	{
		parent::__construct(get_class($this));
		if (in_array($_POST['action'], array('modify', 'del', 'confirm','add'))) $this->buildClassTree();
	}

    public function init()
    {
		$sql = "SELECT * FROM class ORDER BY class.rootid ASC,class.orders ASC";
		$query = $this->db->query($sql);
		while($row = $this->db->fetch_assoc($query)){
			$this->list[$row['class_id']] = $row;
		}
		
		$this->tpl->assign('list',$this->list);
		$this->tpl->assign('param',getparam('',''));
        $this->tpl->display('class');
    }
	
	## 添加内容
	public function add()
	{
		$this->class_option = $this->Infinity->get_class_option(!empty($_REQUEST['id'])?$_REQUEST['id']:'');
		$this->tpl->complete($this);
		$this->tpl->display('classModify');
	}

	## 编辑内容
	public function edit()
	{
		$this->class_option = $this->Infinity->get_class_option(!empty($_REQUEST['id'])?$_REQUEST['id']:'');
		$this->tpl->setvar($this->db->fetch_assoc($this->db->query("SELECT * FROM class WHERE class_id='{$_REQUEST['id']}'")));
		$this->tpl->complete($this);
		$this->tpl->display('classModify');
	}

	## 添加内容的提交
	public function confirm()
	{
		#条件判断
		if(empty($_POST['title'])){
			$this->message('类别名称没有填写','err','添加新类别','class&action=add');
		}
		else if(empty($_REQUEST['domain_name'])){
		    $this->message('域名不能为空','err','添加新类别','class&action=add');
		}
		## 新增加
		#print_R($_REQUEST);exit;
		$id = !empty($_REQUEST['id'])?$_REQUEST['id']:'';
		$arr = Table::getTable('class')->select(array('class_id'=>$id));
		$data = array();
		$data['showfront'] = !empty($_REQUEST['showfront'])?$_REQUEST['showfront']:0;
		$data['title'] = $_REQUEST['title'];
		$data['headertitle'] = $_REQUEST['headertitle'];
		$data['keyword'] = $_REQUEST['keyword'];
		$data['domain_name'] = trim($_REQUEST['domain_name']);	
		$data['showcover'] = !empty($_REQUEST['showcover'])?$_REQUEST['showcover']:0;
		$data['description'] = $_REQUEST['description'];
		$class_id = $this->Infinity->insert_class($_POST['class_id'],$data);
		##成功返回列表
		$this->message('插入成功','suc','添加 '.(!empty($arr[0]['title'])?$arr[0]['title']:'').' 下级目录'.',添加 '.$_POST['title'].' 下级目录,添加新目录,目录详细','class&action=add&id='.$id.',class&action=add&id='.$class_id.',class&action=add,class');
	}

	## 修改内容的提交
	public function modify()
	{
		if(empty($_REQUEST['domain_name'])){
		    $this->message('域名不能为空','err','返回修改','class&action=edit&id='.$_POST['id']);
		}
		$data = array();
		$data['title'] = $_REQUEST['title'];
		$data['headertitle'] = $_REQUEST['headertitle'];
		$data['keyword'] = $_REQUEST['keyword'];
		$data['showcover'] = !empty($_REQUEST['showcover'])?$_REQUEST['showcover']:0;
		$data['description'] = $_REQUEST['description'];
		$data['domain_name'] = trim($_REQUEST['domain_name']);
		$suc = $this->Infinity->class_modify($_POST['id'],$data);
		if($suc){
			$this->message('修改成功','suc','内容列表,再次修改次类别','class,class&action=edit&id='.$_POST['id']);
		}else{
			$this->message('修改失败','err','返回修改','class&action=edit&id='.$_POST['id']);
		}
	}

	## 内容删除
	public function del()
	{
		$this->Infinity->delete($_REQUEST['id']);
		$this->message('删除成功','suc','类别列表','class');
	}

	## 上移
	public function prev()
	{
		$this->Infinity->move_class($_GET['id'],'prev');
		$this->init();
	}

	## 下移
	public function next()
	{
		$this->Infinity->move_class($_GET['id'],'next');
		$this->init();
	}
	
	
	# 重建树型表 （表:service_posts_type）
	protected function buildClassTree($pid = 0, $left = 0, $depth = 0)
	{	
		static $run = 0;
		$left = $run;
		if ($depth > 10) return false;
		
		$a = $this->db->fetch_all_pairs("SELECT class_id, title FROM class WHERE parentid = {$pid} ORDER BY orders DESC");
		foreach ($a as $key => $value) {
			$rs = $this->buildClassTree($key, ++$run, ($depth+1));
			$right = $rs - 1;
			$sql = sprintf("UPDATE class SET lft=%d, rgt=%d, depth=%d WHERE class_id=%d", $left, $right, $depth, $key); 
			$this->db->query($sql); //echo str_repeat('_____', $depth). ($left)."--{$value}({$key}) -- ".($right)."<br />";
			$left = $rs;
		}
		return ++$run;
	}
	
	#获取类型树
	protected function getTypeTree4Options($class_id = 0, $parent_id = 0) 
	{
		if ($parent_id > 0)
		{
			
		}
		
		$rows = $this->db->fetch_all("SELECT * FROM c WHERE class_id = {$class_id} ORDER BY lft ASC");
		foreach ($rows as $k => $v) 
		{
			if ($v['parentid'] <= 0) $right = array();
			if (count($right) > 0)
			{
				while ($right[count($right) - 1] < $v['rgt'])
				{
					array_pop($right);
					$i++;
					if ($i > 20) { $i = 0; echo 'type error'; break;}
				}
			}
			
			$rows[$k]['depth']	 =  count($right);
			$right[] = $v['rgt'];
		}
		
		return $rows;
	}
	
	public function test()
	{
		$class = $this->db->fetch_assoc($this->db->query("SELECT lft, rgt FROM class WHERE class_id = 171"));
		$list = $this->db->fetch_all_assoc("SELECT * FROM class	WHERE lft>={$class['lft']} AND rgt<={$class['rgt']} ORDER BY lft, orders desc");
		
		foreach ($list as $val) {
			echo str_repeat('_____', $val['depth']). ($val['lft'])." -- {$val['title']}({$val['class_id']}) -- ".($val['rgt'])."<br />";;
		}
	}
}
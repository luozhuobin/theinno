<?PHP
##PHP模板
/*
Create By Piaofen in 2008
Modify last By Piaofen in 2008-8-28
*/
Class Template
{
	public $path;//模板文件路径
	private $param; //内容参数
	public $phptohtml = false;//缓存成静态？
	public $timeout = 3600;//过期时间？时间单位：秒
	public $phptohtml_path;//静态文件夹
    public $filetype = array('.php','.html');

	function __construct($path='')
	{
		if($path){
			$this->path = $path;
		}else{
			$this->path = Template.'2012'.'/';
		}
		$this->phptohtml_path = CacheFile.'Html/';//缓存文件夹
	}

	public function db($db)
	{
		$this->db = $db;
	}

	public function assign($key,$value)
	{
		$this->param[$key] = $value;
	}

	public function setvar($arr,$prefix='')
	{
		foreach((array)$arr as $key=>$value){
			if(is_array($value)){
				$this->setvar($value,$prefix);
			}else{
				$this->param[$prefix.$key] = $value;
			}
		}
	}

	public function complete($obj)
	{
		foreach((array)$obj as $key=>$value){
			$this->param[$key] = $value;
		}
	}

	#变成静态页面
	#思路
	#程序执行后给出过期时间
	#未超出过期时间的就用模板缓存

	public function display($filename)
	{
		if($this->phptohtml){
			//开启静态
			$PHPTOHTML_URL = $this->GetFileUrl($filename);
			$filetime = file_exists($PHPTOHTML_URL)?filemtime($PHPTOHTML_URL):0;
			if(!$filetime || $filetime<=time()-$this->timeout){
				ob_start();
			    if(!empty($this->param)){
                    extract($this->param);
                }
                foreach($this->filetype as $type){
                    if(file_exists($this->path.$filename.$type)){
                        require ($this->path.$filename.$type);
                    }
                }
				$buffer = ob_get_contents();
				file_put_contents($PHPTOHTML_URL,$buffer);
			}else{
				echo file_get_contents($PHPTOHTML_URL);
			}
		}else{
			//未使用静态
			if(!empty($this->param)){
                 extract($this->param);
            }
			foreach($this->filetype as $type){
                if(file_exists($this->path.$filename.$type)){
                    require ($this->path.$filename.$type);
                }else if(file_exists($this->path.ucfirst($filename).$type)){
                    require ($this->path.ucfirst($filename).$type);
                }else if(file_exists($this->path.strtolower($filename).$type)){
                    require ($this->path.strtolower($filename).$type);
                }
            }
		}
	}                                                                                                   

    public function get($filename) {
            if(!empty($this->param)){
                 extract($this->param);
            }
			foreach($this->filetype as $type){
                if(file_exists($this->path.$filename.$type)){
                    ob_start();
                    require ($this->path.$filename.$type);
                    $buffer = trim(ob_get_contents());
                    ob_end_clean();
                    return $buffer;
                }
            }
    }

	public function checkPHPTOHTML($filename='')
	{
		if($this->phptohtml){
			//开启静态
			$PHPTOHTML_URL = $this->GetFileUrl($filename);
			$filetime = @filemtime($PHPTOHTML_URL);
			if($filetime && $filetime>time()-$this->timeout){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function GetFileUrl($filename='')
	{
		if(!$filename){
			$filename = $_SERVER['PHP_SELF'];
		}
		$file = preg_replace('/.*?([^\/\\\]*)\..*?$/i','\\1',$filename);
		$htmlFileName = $file.'_'.md5(serialize($this->param));
		$path = $this->phptohtml_path.'/'.$file.'/';
		$htmlFileName .= '.html';
		try{
			if(!isdir($path)){
				throw new Exception('TempLate Create Folder Is Lost:<font color=red>'.$path."<font>");
			}
			/*
			if(!touch($path.$htmlFileName)){
				throw new Exception('TempLate File Not Exists:<font color=red>'.($path.$htmlFileName)."<font>");
			}*/
		}catch(Exception $e){
			echo $e->getMessage();
		}
		return $path.$htmlFileName;
	}

	public function ch2url($value)
	{
		return is_array($value)?self::ch2url($value):urlencode($value);
	}

    ## 加载模块
	/*
	调用模块里面的子成员
	$this->getModel('header')->gogo('adsjfkas');
	*/
    public function getModel($modelname)
    {
		$modelclass = "Model_".$modelname;
		$model = new $modelclass(true);
		return $model;
    }

	## 转化成地址
	public function tourl($action,$param)
	{
		$arr = '';
		$param = explode(',',$param);
		foreach((array)explode(',',$action) as $key=>$val){
			$arr[$val] = $param[$key];
		}
		#print_R($arr);
        if(empty($arr['m']))
            $arr['m'] = 'index';
        return '?'.http_build_query($arr);
	}
	## 带参数转地址
	public function toparamurl($action='',$param='',$remove='')
	{
		return getparam($action,$param,$remove);
	}

    public function paging($recordcount,$pagesize,$page=1)
    {
        $style_parma = array("pagenext" => ">"
                            ,"pagepre" =>"<"
                            ,"pagefirst" => "|<"
                            ,"pageend" => ">|");
        if(!$recordcount)return false;
        if(!$pagesize)$pagesize = 30;//默认30�?
        $pagecount = ceil($recordcount/$pagesize);
        $echo =  "<ul class=page>";
        $echo .= "<li class=total>{$recordcount}</li><li class=total>{$page}/{$pagecount}</li>";
        $echo .= "<li><a href=".self::tourl('page',1).">{$style_parma['pagefirst']}</a></li>";
        $echo .= $page>1 ? "<li><a href=".self::tourl('page',$page-1).">{$style_parma['pagepre']}</a></li>" : "<li><a>{$style_parma['pagepre']}</a></li>";
        for($i = ($page>20 ? $page-10 : 1);($i<=$page+20 && $i<=$pagecount);$i++)
        {
            $echo .= $i==$page ? "<li>{$i}</li>" : "<li><a href=".self::tourl('page',$i).">$i</a></li>";
        }
        $echo .= $page<$pagecount ? "<li><a href=".self::tourl('page',$page+1).">{$style_parma['pagenext']}</a></li>" : "<li><a>{$style_parma['pagenext']}</a></li>";
        $echo .= "<li><a href=".self::tourl('page',$pagecount).">{$style_parma['pageend']}</a></li>";
        $echo .= "</ul>";
        return $echo;
    }
}
?>
<?PHP
#控制器
if(EMPTY($_REQUEST['c'])){
	$_REQUEST['c'] = $_GET['c'] = '2012';//默认是首页
}

if(EMPTY($_REQUEST['m'])){
	$_REQUEST['m'] = $_GET['m'] = 'Index';//默认 model 是index.php
}

$_SERVER['REMOTE_ADDR'] = isset($_SERVER['HTTP_X_REAL_IP'])?$_SERVER['HTTP_X_REAL_IP']:$_SERVER['REMOTE_ADDR'];

##自动导入类
function __Autoload($classname)
{
	try{
		#业务逻辑层
		if(substr(strtolower($classname),0,6)=='model_'){
			$includepath = Model.ucfirst($_REQUEST['c']).'/';
		}elseif(substr(strtolower($classname),0,6)=='table_'){
			$includepath = Table;
		}elseif(strtolower(substr($classname,0,7))=='action_'){
			$includepath = Action;
        }elseif(strtolower(substr($classname,0,7))=='config_'){
            $includepath = Config;
		}elseif(strtolower(substr($classname,0,7))=='control'){
			$includepath = Control;
		}else{
			#公共类文件
			$includepath = Classpath;
		}
        $_classnameshort = __Choose_Auto_File_Name($classname);
		$filepath_one = $includepath.ucfirst($_classnameshort).".php";
        
		if(!file_exists($filepath_one)){
			//echo $filepath_one." cannot found!";
			$filepath_two = $includepath.strtolower($_classnameshort).".php";
			if(!file_exists($filepath_two)){
				echo $filepath_one." cannot found!";
                exit;
			}else{
				require_once $filepath_two;
			}
		}else{
			require_once $filepath_one;
		}
	}catch(Exception $e){
		exit($e->getMessage());
	}
}
//选择类导入的名称
function __Choose_Auto_File_Name($className)
{
	if(strtolower(substr($className,0,6))=='model_'){
		return substr($className,6);
	}elseif(strtolower(substr($className,0,7))=='action_'){
		return substr($className,7);
    }elseif(strtolower(substr($className,0,6))=='table_'){
        return substr($className,6);
	}elseif(strtolower(substr($className,0,7))=='config_'){
		return substr($className,7);
	}else{
		return $className;
	}
}
?>

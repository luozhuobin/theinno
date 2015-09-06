<?PHP
ignore_user_abort(true);
set_time_limit(0);
//ini_set('display_errors',1);
//error_reporting( E_ALL);
//ini_set('display_errors','false');
error_reporting(0);
header('Content-Type: text/html; charset=utf-8');

!defined('cache_table_sign') && define('cache_table_sign','@@_@@',true);
define('debug',1);//调试模式
define('Auto_Create',1);//自动创建文件比如 语言包 和 Js 文件

$baseRoot =  "./";
/* ========================== MVC 模型正式进行... =================== */
/*
	*配置表
	*公共函数
	*类库
	*过滤
	*语言包
	*控制模型
	*控制页面（模板）
	*输出
*/
#配置表


include_once "./config.php";
#类库(自动导入)
include_once "./init/Auto_Include.php";
#公共函数
include_once "./Func/Common.php";
#include_once Func."EmailEx.php";
#过滤
include_once './init/Filter.php';
# 逻辑 和 业务 初始化
include_once './init/Init.php';
include_once './init/Action.php';
# 逻辑控制器
include_once './init/Control.php';



?>

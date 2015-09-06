<?PHP
#执行业务逻辑
try{
    $___model = $_REQUEST['m'];
    $_action = 'init';
	$modelname = 'model_'.$___model;
	
	//echo $modelname;
	$model = new $modelname;
    $___action = empty($_REQUEST['action'])? $_action : $_REQUEST['action']; 
	empty($___action)?$model->init():$model->$___action();

}catch(Exception $e){
	echo ("执行模型发生错误:".$e->getMessage());
}
?>
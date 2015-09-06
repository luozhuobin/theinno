<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>残疾人就业热线平台</title>
<link href="./Template/2012/css/second.css" rel="stylesheet" type="text/css">
<script language="javascript" src="./js/jquery-1.8.1.min.js"></script>
</head>
<body>
<br><br>
	<div class="pop_mo">
	<div class="pop_1">系统提示</div>
	<div class="pop_3">
	<div class="p_3">
	<h2><?PHP echo $message;?></h2>
	<p>系统将在 <b><span style="color:red" id="spanSeconds">3</span></b> 秒后自动跳转到上一页</p>
	<p><span>如果您的浏览器没有自动跳转，请点击<a href="javascript:history.back()">返回</a></span></p>
	</div>
	<div class="p_4">
	<ul>
	</ul>
	<div class="cl"></div>
	</div>
	</div>
	</div>
<script>
var url = '<?php echo empty($url)?'javascript:history.back()':$url;?>';
setTimeout('go()',1000);
var time=3;
function go()
{
	var obj = document.getElementById('spanSeconds');

	if(parseInt(obj.innerHTML)<=0 || time<=0){
		window.location=url;
	}else{
		time -= 1;
		obj.innerHTML= parseInt(obj.innerHTML)-1;
		setTimeout('go()',1000);
	}
}
</script>
</body>
</html>
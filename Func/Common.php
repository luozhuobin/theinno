<?PHP

#include_once("soap/nusoap.php");

function errorlog($filename,$content)
{
	$log_filename = "data/errorlog/".$filename.date("Y-m-d").".log";

	if ( @filesize($log_filename) > 1024*1024*2){
		$handle = @fopen($log_filename, 'w');
	}else{
		$handle = @fopen($log_filename, 'a');
	}
	@fwrite($handle, $content."\n");
	@fclose($handle);
}

//加密
$mcrypt_key = md5('yaowancomhd %^*&((@&@0dsf2j;/."[]');

function encode($value) {
	global $mcrypt_key;
	$key = $mcrypt_key;
	$text = urlencode($value);
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv);
	return base64_encode($crypttext);
}


function hmac_md5($data, $key='')
{
	if (extension_loaded('mhash'))
	{
      if ($key== '') {
        $mhash=mhash(MHASH_MD5,$data);
      } else {
        $mhash=mhash(MHASH_MD5,$data,$key);
      }
      return bin2hex($mhash);
    }
    if (!$key) {
         return pack('H*',md5($data));
    }
    $key = str_pad($key,64,chr(0x00));
    if (strlen($key) > 64) {
        $key = pack("H*",md5($key));
    }
    $k_ipad =  $key ^ str_repeat(chr(0x36), 64) ;
    $k_opad =  $key ^ str_repeat(chr(0x5c), 64) ;
    /* Heh, let's get recursive. */
    $hmac=hmac_md5($k_opad . pack("H*",md5($k_ipad . $data)) );
    return bin2hex($hmac);
}
/**
 * 大富豪解码
 *
 * @param string $value 输入已经加密的值
 * @return 返回解密的值(id,用户名密码)
 */
function decode($value) {
	global $mcrypt_key;
	$key = $mcrypt_key;
	$crypttext = base64_decode($value);
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
	return trim(urldecode($decrypttext));
}


##公共函数库
###########################返回运行时间
/* Create By Author by Piaofen */
function gettime()
{
	list($a,$b) = explode(" ",microtime());
	return (float)$a+(float)$b;
}
#######################判断文件夹是否存在不存在就创建。[可判断多个文件夹]
/* Create By Author by Piaofen */
function isdir($dir,$mod = 0755)
{
	return is_dir($dir) ? true : (isdir(dirname($dir)) ?  mkdir($dir,$mod): false);
}
## 获取IP
/* Create By Author by Piaofen */
function getip()
{
	static $realip;
    if (isset($_SERVER)){
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];}
         else if (isset($_SERVER["HTTP_CLIENT_IP"])) {$realip = $_SERVER["HTTP_CLIENT_IP"];}
         else {$realip = $_SERVER["REMOTE_ADDR"];}
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")){$realip = getenv("HTTP_X_FORWARDED_FOR");} 
        else if (getenv("HTTP_CLIENT_IP")) {$realip = getenv("HTTP_CLIENT_IP");} 
        else {$realip = getenv("REMOTE_ADDR");}
    }
    return $realip;
}
function utf8Cut($str,$length=50){
		if(mb_strlen($str,"utf8") > $length){
			return mb_substr($str,0,$length,"utf8")."...";
		}else {
			return $str;
		}
}
//飘枫：获得get,post的集合参数post优先 2007-7-7
//Modify by LeonYu 2010-03-30
/*
$key 是参数名称
$value 是需要给参数负值
$remove 是移除get 或者post参数中的内容
功能：一般用于分页带过参数
*/
function getparam($key='',$value='',$remove='')
{
	$str = '';
	$key = $key?explode(",",$key):array();
	$value = explode(",",$value);
	$remove = $remove?explode(",",$remove):array();
	//$arr = $_POST + $_GET;
	$arr = array_merge($_GET,$_POST);
	#print_R($_POST);
	for($i=0;$i< count($key);$i++)
	{
		//unset($arr[$key[$i]]);
		$arr[$key[$i]] = $value[$i];
	}
    
	for($i=0;$i<count($remove);$i++)
	{
		if(isset($arr[$remove[$i]])) unset($arr[$remove[$i]]);
	}
	#print_R($arr);
	//$arr = array_flip(array_unique(array_flip($arr)));
	/*
	foreach($arr as $key=>$value)
	{
		$str .= (empty($str)?'':'&').$key."=".urlencode($value);
	}
	*/
	return "?".http_build_query($arr);
}

######### 获得文件名称 比如 c:\ajsdk\asdf\asdf\asdf\a.txt 获取 a.txt
/* Create By Author by Piaofen */
function getfilename($url)
{
	$arr = parse_url($url);
	if(!empty($arr['path'])){
		return preg_replace('/.*?([^\/\\\]*?)$/i','\\1',$arr['path']);
	}else{
		return false;
	}
}
######## 获取文件夹名称 比如 c:\ajsdk\asdf\asdf\asdf\a.txt 获取 c:\ajsdk\asdf\asdf\asdf\
/* Create By Author by Piaofen */
function getpathname($url)
{
	$path = preg_replace('/(.*?)[^\/\\\]*?$/i','\\1',$url);
	if(substr($path,0,7)!='http://'){
		##$path = substr($path,0,2)!='./'||substr($path,0,1)!='/'||substr($path,0,3)!='../'?'./'.$path:'';
		$path = str_replace(array('//','\\'),'/',$path);
	}
	return $path;
}
/* ============== 获得网址 ===================== */
/* Create By Author by Piaofen */
function geturl($url)
{
	preg_match('/(.*\/|\\\)[^\/]*\./i',$url,$result);
	return $result[1];
}
/* ========== 返回一个解析过的 Insert sql 语句 ========== */
/* Create By Author by Piaofen */
function arrtoinsertsql($arr=array(),$table='')
{
	$sql = "INSERT IGNORE INTO `{$table}`(`".implode('`,`',array_keys($arr))."`) VALUES('".implode("','",$arr)."')";
	return $sql;
}
/* ========== 返回一个解析过的 Update sql 语句 ========== */
/* Create By Author by Piaofen */
function arrtoupdatesql($arr=array(),$table='',$where='')
{
	$sqlplus = '';
	foreach($arr as $key=>$val){
		$sqlplus .= ($sqlplus?',':'')."{$key}='{$val}'";
	}
	$wheresql = '';
	foreach((array)$where as $key=>$val){
		$wheresql = " AND {$key}='$val'";
	}
	$sql = "UPDATE IGNORE {$table} SET {$sqlplus} WHERE 1 {$wheresql}";
	return $sql;
}
/* ========= 返回一个替换掉的Text =========== */
/* Create By Author by Piaofen */
function p2nl($str) {
	$str = preg_replace(array('/<br>/i','/<br \/>/i'),"\r\n",$str);
    return preg_replace(array("/<p[^>]*>/iU","/<\/p[^>]*>/iU"),
                        array("","\n"),
                        $str);
}
/* ======== \r\n=><br> \n=><p> === */
/* Create By Author by Piaofen */
function nl2p($str)
{
	return str_replace(array("\r\n","\n"),array("<br />","</p>"),$str);
}
/* ======== 返回文件的类型 ========= */
function getfiletype($file)
{
	preg_match('/\.([a-zA-Z0-9]*)$/i',$file,$match);
	return strtolower($match[1]);
}
/* ======== 分页 ========= */
/* Create By Author by Piaofen */
$style_parma = array("pagenext" => ">"
	,"pagepre" =>"<"
	,"pagefirst" => "|<"
	,"pageend" => ">|");
function paging($recordcount,$pagesize='',$page=1)
{
	Global $style_parma;
	if(!$recordcount)return false;
	if(!$pagesize)$pagesize = 30;//默认30�?
	$pagecount = ceil($recordcount/$pagesize);
	$echo =  "<ul class=page>";
	$echo .= "<li class=total>{$recordcount}</li><li class=total>{$page}/{$pagecount}</li>";
	$echo .= "<li><a href=".getparam('page',1).">{$style_parma['pagefirst']}</a></li>";
	$echo .= $page>1 ? "<li><a href=".getparam('page',$page-1).">{$style_parma['pagepre']}</a></li>" : "<li><a>{$style_parma['pagepre']}</a></li>";
	for($i = ($page>20 ? $page-10 : 1);($i<=$page+20 && $i<=$pagecount);$i++)
	{
		$echo .= $i==$page ? "<li>{$i}</li>" : "<li><a href=".getparam('page',$i).">$i</a></li>";
	}
	$echo .= $page<$pagecount ? "<li><a href=".getparam('page',$page+1).">{$style_parma['pagenext']}</a></li>" : "<li><a>{$style_parma['pagenext']}</a></li>";
	$echo .= "<li><a href=".getparam('page',$pagecount).">{$style_parma['pageend']}</a></li>";
	$echo .= "</ul>";
	$list = getlist($pagecount,$page);
	return $echo.$list;
}

function MyPagingFun($rc,$size,$p){
            			if($rc==0)return;
            			
            			$domain="./?";
            			$pc=ceil($rc/$size);
            			
            			$ParamArr=$_GET;
            			if(array_key_exists("c",$ParamArr))unset($ParamArr["c"]);
            			if(array_key_exists("page",$ParamArr))unset($ParamArr["page"]);
            			
            			$HtmlStr ="<select style='float:right;' onchange=\"location.href='./?".http_build_query($ParamArr)."&page='+this.options[this.selectedIndex].value;\">";
            			for($pi=1;$pi<=$pc;$pi++){
            				if($pi==$p){
            					$HtmlStr.="<option value=\"".$pi."\" selected>".$pi."</option>";
            					continue;
            				}
            				$HtmlStr.="<option value=\"".$pi."\">".$pi."</option>";
            			}
            			
            			$HtmlStr .="</select>";
            			$HtmlStr .="<ul style='float:right;'>";
            			
            			$HtmlStr.="<li>".$rc."</li>";
            			$HtmlStr.="<li>".$p."/".$pc."</li>";
            			
            			if($p!=1){
            				$HtmlStr.="<li><a href=\"".$domain.http_build_query($ParamArr)."\">|<</a></li>";
            				$HtmlStr.="<li><a href=\"".$domain.http_build_query($ParamArr)."&page=".($p-1)."\"><</a></li>";
            			}else{
            				$HtmlStr.="<li>|<</li>";
            				$HtmlStr.="<li><</li>";
            			}
            			
            			$showleft=floor(($p-1)/10)*10+1;
            			//$showpc=$p%10+$showleft;
            			
            			for($pi=$showleft;$pi<=$showleft+9;$pi++){
            				if($pi>$pc)break;
            				if($pi!=$p)
            					$HtmlStr.="<li><a href=\"".$domain.http_build_query($ParamArr)."&page=".$pi."\">".$pi."</a></li>";
            				else
            					$HtmlStr.="<li class='curr'>".$pi."</li>";
            			}
            			
            			if($p!=$pc){
            				$HtmlStr.="<li><a href=\"".$domain.http_build_query($ParamArr)."&page=".($p+1)."\">></a></li>";
            				$HtmlStr.="<li><a href=\"".$domain.http_build_query($ParamArr)."&page=".($pc)."\">>|</a></li>";
            			}else{
            				$HtmlStr.="<li>></li>";
            				$HtmlStr.="<li>>|</li>";
            			}
            			
            			$HtmlStr.="</ul>";
            			$HtmlStr .= '<div style="clear:both; font-size:0; line-height:0;"></div>';

            			
            			return $HtmlStr;
            		}
#2011新平台分页功能   @create by qiuleo
function PagingFun_2011($rc,$size,$p){
            			if($rc==0)return;
            			
            			$domain="./?";
            			$pc=ceil($rc/$size);
            			
            			$ParamArr=$_GET;
            			if(array_key_exists("c",$ParamArr))unset($ParamArr["c"]);
            			if(array_key_exists("page",$ParamArr))unset($ParamArr["page"]);
            			
            			if($p!=1){
            				$HtmlStr.="<a href=\"".$domain.http_build_query($ParamArr)."\">第一页</a>";
            				$HtmlStr.="<a href=\"".$domain.http_build_query($ParamArr)."&page=".($p-1)."\">上一页</a>";
            			}
            			$showleft=floor(($p-1)/5)*5+1;
            			for($pi=$showleft;$pi<=$showleft+4;$pi++){
            				if($pi>$pc)break;
            				if($pi!=$p){
            					$HtmlStr.="<a href=\"".$domain.http_build_query($ParamArr)."&page=".$pi."\">".$pi."</a>";}
            				else
            					$HtmlStr.="<a class='hover'>".$pi."</a>";
            			}
            			
            			if($p!=$pc){
            				$HtmlStr.="<a href=\"".$domain.http_build_query($ParamArr)."&page=".($p+1)."\">下一页</a>";
            				$HtmlStr.="<a href=\"".$domain.http_build_query($ParamArr)."&page=".($pc)."\">最后一页</a>";
            			}
            			return $HtmlStr;
            		}

#无刷新分页功能   @create by qiuleo
function PagingFun_ajax($rc,$size,$p,$ajaxId){
            			if($rc==0)return;
            			
            			$HtmlStr.='<i class="M-info">共'.$rc.'条 </i>';
            			$domain="./?";
            			$pc=ceil($rc/$size);
            			
            			$ParamArr=$_GET;
            			if(array_key_exists("c",$ParamArr))unset($ParamArr["c"]);
            			if(array_key_exists("page",$ParamArr))unset($ParamArr["page"]);
            			
            			if($p!=1){
            				$HtmlStr.='<a href="javascript:getPage(\''.$domain.http_build_query($ParamArr).'\',\''.$ajaxId.'\')">首页</a>';
            				$HtmlStr.='<a href="javascript:getPage(\''.$domain.http_build_query($ParamArr)."&page=".($p-1).'\',\''.$ajaxId.'\')">上一页</a>';
            			}
            			$showleft=floor(($p-1)/5)*5+1;
            			for($pi=$showleft;$pi<=$showleft+4;$pi++){
            				if($pi>$pc)break;
            				if($pi!=$p){
            					$HtmlStr.='<a href="javascript:getPage(\''.$domain.http_build_query($ParamArr)."&page=".$pi.'\',\''.$ajaxId.'\')">'.$pi.'</a>';}
            				else
            					$HtmlStr.="<a class='M-current'>".$pi."</a>";
            			}
            			
            			if($p!=$pc){
            				$HtmlStr.='<a href="javascript:getPage(\''.$domain.http_build_query($ParamArr)."&page=".($p+1).'\',\''.$ajaxId.'\')">下一页</a>';
            				$HtmlStr.='<a href="javascript:getPage(\''.$domain.http_build_query($ParamArr)."&page=".($pc).'\',\''.$ajaxId.'\')">末页</a>';
            			}
            			return $HtmlStr;
            		}
            		
/* Create By Author by Piaofen */
function getlist($pagecount,$page)
{
	$echo = "<select size=1 class='page_select' name=page onchange=javascript:location.href='".getparam("page","")."'+this.options[this.selectedIndex].value>";
	for($i=1;$i<=$pagecount;$i++)
	{
		$result = $pagecount/15;
		if($i>=$page-20 && $i<=$page+15){// ||
			$echo .= "<option value=$i".($page==$i ? ' selected' : '').">".$i."</option>";
		}elseif($i%$result ==0 || $i==1 || $i==$pagecount){
			$echo .= "<option value=$i>".$i."</option>";
		}
	}
	$echo.= "</select>";
	return $echo;
}
/* ======== 加水印 ====== */
/* watermask("1266050_F1500.jpg","web.gif",'rb',80);     */
function watermask($destination,$waterpngfilename,$pos = 'rb',$transparent = 20)
{
	// 参数分别是 $destination => 图片地址，
	// $waterpngfilename => 水印图片地址
	// $pos => 水印位置 lt 左上 lb 左下 rt 右上 rb 右下(默认)
	// $transparent => 透明度 默认 20
	$imagetype = array("1"=>"gif","2"=>"jpeg","3"=>"png","4"=>"wbmp");
	$imagetypeother = array('gif'=>'gif','jpg'=>'jpeg','jpeg'=>'jpeg','png'=>'png','bmp'=>'bmp');
	$image_size = getimagesize($destination);
	$iinfo=getimagesize($destination,$iinfo);
	$type = !empty($imagetype[$iinfo[2]])?$imagetype[$iinfo[2]]:$imagetypeother[getfiletype($destination)];
	$f ="imagecreatefrom".$type;
	if($f=='imagecreatefrom'){
		return '';
	}
	$simage = $f($destination);
	$imagesize_mask = getimagesize($waterpngfilename);
	$f ="imagecreatefrom".$imagetype[$imagesize_mask[2]];
	$simage1 = $f($waterpngfilename); // 水印文件
	// 合并2个文件
	$random = array('lt','lb','rt','rb');
	$pos = strstr(',lt,lb,rt,rb,',$pos)?$pos:$random[mt_rand(0,(count($random)-1))];
	switch($pos)
	{
		case 'lt':
			imagecopymerge($simage,$simage1,0,0,0,0,$imagesize_mask[0],
			$imagesize_mask[1],$transparent); // 左上
			break;
		case 'lb':
			imagecopymerge($simage,$simage1,0,$image_size[1]-$imagesize_mask[1],0,0,$imagesize_mask[0],$imagesize_mask[1],$transparent); // 左下
			break;
		case 'rt':
			imagecopymerge($simage,$simage1,$image_size[0]-$imagesize_mask[0],0,0,0,$imagesize_mask[0],$imagesize_mask[1],$transparent); // 右上
			break;
		case 'rb':
			imagecopymerge($simage,$simage1,$image_size[0]-$imagesize_mask[0],$image_size[1]-$imagesize_mask[1],0,0,$imagesize_mask[0],$imagesize_mask[1],$transparent); // 右下
			break;
	}
	// 输出
	$f ="image".$type;
	$type=='jpeg'?$f($simage,$destination,100):$f($simage,$destination);
	imagedestroy($simage);
	imagedestroy($simage1);
}
/* Create By Author by Piaofen */
## 配置Flash 来画图，这个函数是输出参数
function createStat($data=array(),$param=array())
{
	$param = $param + array(
						'title'=>array(
							'text'=>'',
							'size'=>0,
							'color'=>'',
							),
						'background'=>'#FFFFFF',
						'ticks'=>array(
								'range'=>2,
								'length'=>10,
								'step'=>10,
								)
						);
	$data = $data + array('min'=>0);
	if(!empty($data['labels']) && !empty($data['values'])){
		$count = count($data['values']);
		$max = array_max($data['values']);
		$unit = strlen($max)<2?strlen($max):2;
		$unitnum = strlen($max)<2?'1'.str_repeat(0,strlen($max)):100;
		$data['max'] = !empty($data['max'])?$data['max']:($max + $unitnum-substr($max,-$unit));
		$data['point'] = (!empty($data['point'])?$data['point']:array()) + array_fill(0,$count,4);
		$data['size'] = (!empty($data['size'])?$data['size']:array()) + array_fill(0,$count,12);
		$data['weight'] = (!empty($data['weight'])?$data['weight']:array()) + array_fill(0,$count,2);
		$data['text'] = (!empty($data['text'])?$data['text']:array()) + array_fill(0,$count,'');
		$color = array();
		for($i=0;$i<$count;$i++){
			$color[] = getrandomcolor();
		}
		$data['color'] = (!empty($data['color'])?$data['color']:array()) + $color;
		$values = '';
		for($i=0;$i<$count;$i++){
				$values .= '&values'.(!empty($i)?('_'.($i+1)):'').'='.implode(",",(array)($i==0?current($data['values']):next($data['values']))).'&'."\r\n";
		}
		$lines = '';
		for($i=0;$i<$count;$i++){
				$lines .= '&line_dot'.(!empty($i)?('_'.($i+1)):'').'='.$data['weight'][$i].','.$data['color'][$i].','.$data['text'][$i].','.$data['size'][$i].','.$data['point'][$i].'&'."\r\n";
		}
		echo	"&title=".$param['title']['text'].",".$param['title']['size'].",".$param['title']['color']."&"."\r\n".
				"&y_ticks=".$param['ticks']['range'].",".$param['ticks']['length'].",".$param['ticks']['step']."&"."\r\n".
				"&y_min=".$data['min']."&"."\r\n".
				"&y_max=".$data['max']."&"."\r\n".
				"&bg_colour=".$param['background']."&"."\r\n".
				"&x_labels=".implode(",",$data['labels'])."&"."\r\n".
				$values."\r\n".
				$lines."\r\n";
		exit;

	}

}
/* 返回数组最大的数 */
function array_max($array,$max=0)
{
	foreach((array)$array as $value){
		if(is_array($value)){
			$max = array_max($value,$max);
		}elseif((int)$value>$max){
			$max = $value;
		}
	}
	return $max;
}
/* ===== Piaofen 获得随机颜色 ======*/
function getrandomcolor()
{
	$arr = Array();
	$tmp = '';
	$arr = range(0,9) + array_combine(range(10,15),range('A','F'));
	for($i=1;$i<=6;$i++){
		$tmp .= $arr[mt_rand(0,15)];
	}
	return '#'.$tmp;
}
function imageresize($img,$target="",$width=0,$height=0,$percent=0)
{

   // create an image of the given filetype
   if (strpos($img,".jpg") !== false or strpos($img,".jpeg") !== false){
	  $image = ImageCreateFromJpeg($img);
	   $extension = ".jpg";
  } elseif (strpos($img,".png") !== false) {
	   $image = ImageCreateFromPng($img);
	   $extension = ".png";
   } elseif (strpos($img,".gif") !== false) {
	   $image = ImageCreateFromGif($img);
	   $extension = ".gif";
   }elseif(getfiletype($img)=='bmp'){
		$image = ImageCreateFromwbmp($img);
		$extension = '.bmp';
   }

   $size = getimagesize ($img);

   // calculate missing values
   if ($width and !$height) {
	   $height = ($size[1] / $size[0]) * $width;
   } elseif (!$width and $height) {
	   $width = ($size[0] / $size[1]) * $height;
   } elseif ($percent) {
	   $width = $size[0] / 100 * $percent;
	   $height = $size[1] / 100 * $percent;
   } elseif (!$width and !$height and !$percent) {
	   $width = 100; // here you can enter a standard value for actions where no arguments are given
	   $height = ($size[1] / $size[0]) * $width;
   }

   $thumb = imagecreatetruecolor ($width, $height);

   if (function_exists("imageCopyResampled"))
   {
	   if (!@ImageCopyResampled($thumb, $image, 0, 0, 0, 0, $width, $height, $size[0], $size[1])) {
		   ImageCopyResized($thumb, $image, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
	   }
	} else {
	   ImageCopyResized($thumb, $image, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
	}

   //ImageCopyResampleBicubic ($thumb, $image, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);

   if (!$target) {
	   $target = "temp".$extension;
   }

   $return = true;

   switch ($extension) {
	   case ".jpeg":
	   case ".jpg": {
		   imagejpeg($thumb, $target, 100);
	   break;
	   }
	   case ".gif": {
		   imagegif($thumb, $target);
	   break;
	   }
	   case ".png": {
		   imagepng($thumb, $target);
	   break;
	   }
	   case ".bmp": {
		   imagewbmp($thumb,$target);
	   }
	   default: {
		   $return = false;
	   }
   }

   // report the success (or fail) of the action
   return $return;
}
function thum($file,$width,$height=0)
{
	$filename = md5($file.$width.$height).'.'.getfiletype($file);
	$filepath = UploadFile.'Thumbnails/'.$width.'/'.$height.'/'.substr($filename,0,2).'/'.substr($filename,-8,-6).'/';
	$filefullname = $filepath.$filename;
	if(file_exists($filefullname)){
		return $filefullname;
	}elseif(file_exists($file)){
		if(isdir($filepath) && touch($filefullname)){
			list($w,$h,$false,$false) = getimagesize($file);
			if(($w>=$h && $width>0) || $height<=0){//宽度>高度
				$rate = number_format($w /$width,2);
				$height_now = ceil($h/$rate);
				if(!imageresize($file,$filefullname,$width,$height_now)){
					return false;
				}else{
					//watermask($filefullname,watermask,'rb',40);
					return $filefullname;
				}
			}else{
				$rate = number_format($h/$height,2);
				$width_now = ceil($w/$rate);
				if(!imageresize($file,$filefullname,$width_now,$h)){
					return false;
				}else{
					//watermask($filefullname,watermask,'rb',40);
					return $filefullname;
				}
			}
		}else{
			return false;
		}
	}
}

# 返回IP所在国家、地区等等，比ip2addr更为精确，如清华大学 202.38.126.112
function ip2address($ip) {
 		include_once(Plugins."geoip/geoipcity.php");
		include_once(Plugins."geoip/geoipregionvars.php");
		$gi = geoip_open(Plugins."GeoIPCity.dat",GEOIP_STANDARD);
		$record = geoip_record_by_addr($gi,$ip);
		//$country= $record->country_name;
		$area= $record->city;		
		geoip_close($gi);	
		return $area;
}


###########################返回Ip地址的所在地区 2007年 By Dz
function ip2addr($ip) {
	if(!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip))return '';
	if(!defined('ipdata')){
		define('ipdata','../ipdata/',true);
	}

	if($fd = @fopen(Plugins.'wry.DAT', 'rb')) {
		

		$ip = explode('.', $ip);
		$ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

		$DataBegin = fread($fd, 4);
		$DataEnd = fread($fd, 4);
		$ipbegin = implode('', unpack('L', $DataBegin));
		if($ipbegin < 0) $ipbegin += pow(2, 32);
		$ipend = implode('', unpack('L', $DataEnd));
		if($ipend < 0) $ipend += pow(2, 32);
		$ipAllNum = ($ipend - $ipbegin) / 7 + 1;

		$BeginNum = 0;
		$EndNum = $ipAllNum;
		$ip1num = $ip2num = $ipAddr2 = $ipAddr1 = 0;
		while($ip1num > $ipNum || $ip2num < $ipNum) {
			$Middle= intval(($EndNum + $BeginNum) / 2);

			fseek($fd, $ipbegin + 7 * $Middle);
			$ipData1 = fread($fd, 4);
			if(strlen($ipData1) < 4) {
				fclose($fd);
				return '- System Error';
			}
			$ip1num = implode('', unpack('L', $ipData1));
			if($ip1num < 0) $ip1num += pow(2, 32);

			if($ip1num > $ipNum) {
				$EndNum = $Middle;
				continue;
			}

			$DataSeek = fread($fd, 3);
			if(strlen($DataSeek) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
			fseek($fd, $DataSeek);
			$ipData2 = fread($fd, 4);
			if(strlen($ipData2) < 4) {
				fclose($fd);
				return '- System Error';
			}
			$ip2num = implode('', unpack('L', $ipData2));
			if($ip2num < 0) $ip2num += pow(2, 32);

			if($ip2num < $ipNum) {
				if($Middle == $BeginNum) {
					fclose($fd);
					return '- Unknown';
				}
				$BeginNum = $Middle;
			}
		}

		$ipFlag = fread($fd, 1);
		if($ipFlag == chr(1)) {
			$ipSeek = fread($fd, 3);
			if(strlen($ipSeek) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
			fseek($fd, $ipSeek);
			$ipFlag = fread($fd, 1);
		}

		if($ipFlag == chr(2)) {
			$AddrSeek = fread($fd, 3);
			if(strlen($AddrSeek) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$ipFlag = fread($fd, 1);
			if($ipFlag == chr(2)) {
				$AddrSeek2 = fread($fd, 3);
				if(strlen($AddrSeek2) < 3) {
					fclose($fd);
					return '- System Error';
				}
				$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
				fseek($fd, $AddrSeek2);
			} else {
				fseek($fd, -1, SEEK_CUR);
			}

			while(($char = fread($fd, 1)) != chr(0))
				$ipAddr2 .= $char;

			$AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
			fseek($fd, $AddrSeek);

			while(($char = fread($fd, 1)) != chr(0))
				$ipAddr1 .= $char;
		} else {
			fseek($fd, -1, SEEK_CUR);
			while(($char = fread($fd, 1)) != chr(0))
				$ipAddr1 .= $char;

			$ipFlag = fread($fd, 1);
			if($ipFlag == chr(2)) {
				$AddrSeek2 = fread($fd, 3);
				if(strlen($AddrSeek2) < 3) {
					fclose($fd);
					return '- System Error';
				}
				$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
				fseek($fd, $AddrSeek2);
			} else {
				fseek($fd, -1, SEEK_CUR);
			}
			while(($char = fread($fd, 1)) != chr(0))
				$ipAddr2 .= $char;
		}
		fclose($fd);

		if(preg_match('/http/i', $ipAddr2)) {
			$ipAddr2 = '';
		}
		$ipaddr = "$ipAddr1 $ipAddr2";
		$ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
		$ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
		$ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
		$ipaddr = preg_replace('/^0|0$/i','',$ipaddr);
		$ipaddr = preg_replace('/ 0/i',' ',$ipaddr);
		if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
			$ipaddr = '- Unknown';
		}

		return iconv('GBK//IGNORE','UTF-8//IGNORE',$ipaddr);
	}
}
## 查看用户ID分配
function membersub($id)
{
	return $id % 1024;
}
function is_utf8($string) { 
	return preg_match('%^(?: 
	  [\x09\x0A\x0D\x20-\x7E]            # ASCII 
	| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte 
	|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs 
	| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte 
	|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates 
	|  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3 
	| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15 
	|  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16 
	)*$%xs', $string); 
}
#############################返回操作系统和浏览器版本
//Author Piaofen By 2007 珠海
/*
返回数组：Array ( [sys] => Windows XP [browser] => Internet Explorer 6.0 )//[search]=> google 等待加入
搜索引擎 头部：'googlebot'=>Google 'msnbot'=>msn 'slurp'=>yahoo 'baiduspider'=>baidu 'sohu-search'=>soho 'lycos'=>Lycos 'robozilla'=>Robozilla
*/
function getbrowser($info='')
{
	$info = $info ? $info : $_SERVER['HTTP_USER_AGENT'];
	################获得浏览器信息
	#####搜索引擎浏览信息
	$re = "/googlebot|msnbot|sogou|slurp|baiduspider|sohu-search|lycos|robozilla/i";
	$search = '';
	preg_match($re,$info,$match);
	switch(isset($match[0]))
	{
		case 0:#############正常用户浏览器信息
			$re = "/Opera|lynx|links|elinks|jbrowser|konqueror|Opera|Poco|(gecko)/i";//|Mozilla\/5\.0
			preg_match($re,$info,$match);
			if(!empty($match[1]))//gecko: 类型浏览器
			{
				$re = "/aol|BonEcho|netscape|firefox|chimera|camino|galeon|k\-meleon/i";
				preg_match($re,$info,$result);
				if(!empty($result[0]))$browser = $result[0];
				else $browser = $match[1];
			}else{
				$browser = !empty($match[0])?$match[0]:'';
			};
			if(empty($browser) && eregi("(msie\s?[\d\.]*)",$info))//msie:类型浏览器
			{
				$re = "/tencenttraveler|slimbrowser|greenbrowser|avant browser|sleipnir|netcaptor|gosurf|myie2|webtv|aol|msn|TheWorld|KuGooSoft/i";
				preg_match($re,$info,$result);
				if(!empty($result[0]))$browser = $result[0];
				else
				{
					preg_match("/msie\s?[\d\.]*/i",$info,$arr);
					if($arr[0])$browser = $arr[0];
				}
				$browser = str_replace(array("msie","MSIE"),"Internet Explorer",$browser);
			}
			break;
		default:#############搜索引擎
			$search_name = array("/googlebot/i","/msnbot/i","/slurp/i","/baiduspider/i","/sohu-search/i","/lycos/i","/robozilla/i",'/sogou/i');
			$search_value= array("Google","Msn","Yahoo","Baidu","Soho","Lycos","Robozilla",'Sogou');
			$search = preg_replace($search_name,$search_value,$match[0]);
			break;
	}//end switch
	################获得系统信息
	$re = "/palmsource|WAP|AIX|PC|OS\/2|BeOS|SunOS|FreeBSD|Unix|Red Hat|Linux|Mac|windows[^;]\s?(?:nt|\s)?\s?[\d\.]*/i";
	preg_match($re,$info,$match);
    $sys_window = !empty($match[0])?$match[0]:'';
	switch(stristr($sys_window,"windows"))
	{
		case false://不是windows系列
			$sys = $sys_window;
			break;
		default://windows系列
			$sys = eregi_replace(" |nt","",$sys_window);
			$version = array("6.0","xp","5.3","5.2","5.1","5.01","5.0","4.9","4","98","95","ce",);
			$name = array(" Vista"," XP"," Vista"," 2003"," XP"," 2000 SP1"," 2000"," ME"," NT"," 98"," 95"," CE");
			$sys = str_replace($version,$name,$sys);
			break;
	}
	/********** 获得 Alexa Toolbar 信息2007-7-3 *************/
	$alexa = strstr($info,"Alexa Toolbar") ? 1 : '';
	return array("search"=>(!empty($search)?ucwords($search):''),"sys"=>(!empty($sys)?ucwords($sys):''),"browser"=>(!empty($browser)?ucwords($browser):''),"agent"=>(!empty($info)?ucwords($info):''),"alexatoolbar"=>(!empty($alexa)?$alexa:''));
}

function getbuffer($url, $second = 8)
{
    $ch    = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_HEADER,0);
    curl_setopt($ch,CURLOPT_TIMEOUT,$second);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    $content    = curl_exec($ch);
    curl_close($ch);
    return $content;
}

function postbuffer($url, $postdata, $second = 8){
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_HEADER,0);
    curl_setopt($ch,CURLOPT_TIMEOUT,$second);
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}
/* alipay in 2008-12-18*/
function  log_result($word) {
	$fp = fopen("./log.txt","a");	
	flock($fp, LOCK_EX) ;
	fwrite($fp,$word."：执行日期：".strftime("%Y%m%d%H%I%S",time())."\t\n");
	flock($fp, LOCK_UN); 
	fclose($fp);
}

function saddslashes($var){
    if(is_array($var)){
        foreach($var as $key=>$val){
            $var[$key] = saddslashes($val);
        }
    }else{
        $var = addslashes($var);
    }
    
    return $var;
}


	/**
	 * 		by kernaling.wong
	 * 		2012.09.22
	 * 
	 * 		格式化字符
	 */
	function clearFormate($input){
//		$input = preg_replace("/\s+/" , "" , $input);
		$input = preg_replace("/<.*?>/" , "" , $input);
		$input = preg_replace("/&\w+?;/" , "" , $input);
		
		$input = trim($input); 
		return $input;
	}

	function patternGet($input ,  $pattern){
		$matches = Array();
		preg_match("/".$pattern."/", $input , $matches, PREG_OFFSET_CAPTURE);
		return $matches[0][0];
//		print_r($matches);
	}
	
	function formateDay($timeInSec , $pattern = 'Y.m.d'){
			return date($pattern , $timeInSec);
	}
	
	
	function subStrEx($src_Str,$cutlen){
		$len=0;
		$ret_str="";
		for($l=0;$l<strlen($src_Str);$l++){
			if(ord($src_Str[$l])>127){
				$ret_str.=$src_Str[$l].$src_Str[$l+1].$src_Str[$l+2];
				$l+=2;
				$len++;
			}else{
				$len+=0.5;
				$ret_str.=$src_Str[$l];
			}		
			if($len>$cutlen)return substr($src_Str,0,strlen($ret_str)-3)." ... ";
			if($len==$cutlen)return $ret_str."..";
		}
		return $ret_str;
	}
    
    function getval($key){
        return $_GET[$key]?$_GET[$key]:$_POST[$key];
    }
    
    /**
    * @desc 生成指定长度的随机串
    * @var  length 随机串长度
    * @var  letter 是否加入字母
    * Author by LeonYu 2010-03-30
    */
    function strrand($length,$letter = 0){
        $str = '1234567890';
        $letter == 1 && $str .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $letter == 2 && $str .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $len = strlen($str);
        for($i=0; $i<$length; $i++){
            $result .= $str[rand(0,$len-1)];
        }
        return $result;
    }
        
    function ysetcookie($key, $value, $expire){
        $domain = '';
        if(domain == 'www.yaowan.com') $domain = '.yaowan.com';
        setcookie($key,$value,time()+$expire,'/',$domain);
    }
    
    function Paylog( $logarr, $filename, $filepath, $debug = false){
        $content = implode("\n",$logarr);
        $log = "$content\n\n";
        if(!isdir($filepath = Root.'data/PayLog/'.$filepath,0777)) $debug && printf("\nCannot Create directory"); 
        $date = date('Y-m');
        if($fp = @fopen($filepath."{$filename}_{$date}.log",'a+')){
            fwrite($fp,$log);
            fclose($fp);
        }else{
            $debug && printf("\nCannot Create log file");
        }
    }
    
    function writelog($logarr, $filename){
        $content = implode("\n", $logarr);
        $content = "$content\n\n";
        isdir(dirname($filename), 0777);
        if($fp = @fopen($filename, 'a+')){
            flock($fp,LOCK_EX);
            fwrite($fp,$content);
            flock($fp,LOCK_UN);
            fclose($fp);
        }
    }
    
//数组转换成字串
function arrayeval($array, $level = 0) {
    $space = '';
    for($i = 0; $i <= $level; $i++) {
        $space .= "\t";
    }
    $evaluate = "array\n$space(\n";
    $comma = $space;
    foreach($array as $key => $val) {
        $key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
        $val = !is_array($val) && (!preg_match("/^\-?\d+$/", $val) || strlen($val) > 12 || substr($val, 0, 1)=='0') ? '\''.addcslashes($val, '\'\\').'\'' : $val;
        if(is_array($val)) {
            $evaluate .= "$comma$key => ".arrayeval($val, $level + 1);
        } else {
            $evaluate .= "$comma$key => $val";
        }
        $comma = ",\n$space";
    }
    $evaluate .= "\n$space)";
    return $evaluate;
}

function getsize($len){
    $i = 0;
    while ( $tmp = floor($len /1024)){$i++; $len = $tmp;}
    switch($i){
        case 0: $suf='B'; break;
        case 1: $suf='KB'; break;
        case 2: $suf='MB'; break;
        case 3: $suf='GB';break;
    }
    return $len.$suf;
}       

function get_client_ip(){
	if ($_SERVER['REMOTE_ADDR']) {
		$cip = $_SERVER['REMOTE_ADDR'];
	} elseif (getenv("REMOTE_ADDR")) {
		$cip = getenv("REMOTE_ADDR");
	} elseif (getenv("HTTP_CLIENT_IP")) {
		$cip = getenv("HTTP_CLIENT_IP");
	} else {
		$cip = "unknown";
	}
	return $cip;
}








?>

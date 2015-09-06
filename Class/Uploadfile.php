<?PHP
Class Uploadfile
{
	/* Create By Author by Piaofen */
	//思路：
	// 上传文件 判断大小 类型
	// 验证通过 写入地址 地址 为 时间+IP+未一 MD5 值.文件类型
	// 如果是图片是否生成缩列图 有个高度和宽度
	// 是否需要上传到另外的服务器 ftp  暂时不考虑 -_-!!!
	// 最后返回文件名
	public $updatefile = "";//文件名称
	public $name = "";//本地文件
	public $filetype = array('zip','jpg','gif','png','doc','txt');//运行上传的文件类型
	public $filesize = '';//最大上传文件大小
	public $ftp = false;//是否需要传到备份服务器
	public $filepath = '';//上传的目录
	public $err = '';//上传错误信息
	public $resetimage = false;//如果打开了宽度高度重新设置 那么$this->width $this->height 重新生效
	public $width = '';//如果是图片那么把他的宽度设置为xxx
	public $height = '';//如果是图片那么把他的高度设置为xxx
	public $tuckimage = false;//生成缩列图？
	public $tuckwidth = 100;//缩列图宽度
	public $tuckheight = 125;//缩列图高度
	public $maxtuck = false;//生成缩图的开光
	public $maxwidth = 90;//最大宽度是:90
	public $maxheight = 90;//最大宽度是:90
	public $tuckpath = "";//缩列图目录

	/* ======== $name 表单名 $filepage 是文件路径 $filesize 最大大小 $filetype 允许格式 ============ */
	function __construct($name=array(),$filepath='',$filetype=array(),$filesize=0)
	{
		if($name){
			$this->name = $name;
		}
		if($filesize){
			$this->filesize = $filesize;
		}else{
			$this->filesize = $this->getmaxsize();
		}
		if($filetype){
			//默认上传类型为图片
			$this->filetype = $filetype;
		}else{
			$this->filetype = array("jpg","gif","png");
		}
		if($filepath){
			$this->filepath = $filepath;
		}
		if($filesize){
			$this->filesize = $filesize;
		}
	}
	/* =============== 上传 ================== */
	function upload($name=array(),$filepath='',$filetype=array(),$filesize=0)
	{
		
		//上传文件夹
		$this->filetype = $this->array_tolower($this->filetype);#小写化 数组
		if($this->tuckwidth && $this->tuckheight){
			$this->tuckpath = $this->filepath.$this->tuckwidth."x".$this->tuckheight."/";
		}
		if($this->name && !$name){$name = $this->name;}
		if($this->filepath && !$filepath){$filepath = $this->filepath;}
		if(!$this->isdir($filepath)){return '创建文件夹错误';}
		if($this->filesize && !$filesize){$filesize = $this->filesize;}
		if($this->filetype && !$filetype){$filetype = $this->filetype;}
		$t_name = $name;
		$suc = array();
		foreach((array)$t_name as $t => $name){
			//echo "<font color=red>".$name."</font>";
			if(is_array($_FILES[$name]['error'])){//传递了多个表单名称的数组
				foreach($_FILES[$name]['error'] as $key => $value){
					if(!$this->geterror($_FILES[$name]['error'][$key])){//上传正常
						$type = $this->getfilestype($_FILES[$name]['name'][$key]);
						if((!empty($filetype[0]) && $filetype[0]=='*') || in_array($type,$filetype,false)){//检查类型文件

							if($_FILES[$name]['size'][$key]<=$filesize){//检查上传大小
								//开始保存文件
								if(is_uploaded_file($_FILES[$name]['tmp_name'][$key])){//是上传的文件
									$file_name = md5(uniqid().time().$_SERVER['REMOTE_ADDR']).".".$type;//上传文件名保证唯一 ^_^
									$full_file_name = $filepath.$file_name;
									if(COPY($_FILES[$name]['tmp_name'][$key],$full_file_name)){
										//echo($full_file_name);
										//exit();
										//成功的复制文件
										//如果是图片，并却开启了按最大高度最大宽度来重新生成图片
										if($this->maxtuck  && $this->is_image($full_file_name)){
											//
											list($width,$height,$false,$false) = getimagesize($full_file_name);
											if(($width>=$height && $this->maxwidth>0) || $this->maxheight<=0){//宽度>高度
												$rate = number_format($width /$this->maxwidth,2);
												$height_now = ceil($height/$rate);
												if(!$this->imageresize($full_file_name,$full_file_name,$this->maxwidth,$height_now)){
												$this->err[$name][$key]['image'] = '按 宽度 最大缩图失败.';
												}else{
													$suc[$name][$key] = $full_file_name;
												}
											}else{
												$rate = number_format($height/$this->maxheight,2);
												$width_now = ceil($width/$rate);
												if(!$this->imageresize($full_file_name,$full_file_name,$width_now,$this->maxheight)){
												$this->err[$name][$key]['image'] = '按 高度 最大缩图失败.';
												}else{
													$suc[$name][$key] = $full_file_name;
												}
											}
										}
										//如果是图片，并却重新开启了图片生成
										if($this->resetimage && $this->is_image($full_file_name)){
											if(!$this->imageresize($full_file_name,$full_file_name,$this->width,$this->height)){
												$this->err[$name][$key]['image'] = '重新设置图片错误.';
											}else{
												$suc[$name][$key] = $full_file_name;
											}
										}
										//开启了所列图，并却是个图片
										if($this->tuckimage && $this->is_image($full_file_name)){
											if($this->tuckwidth && $this->tuckheight && $this->isdir($this->tuckpath)){//制定生成缩列图
												if(!$this->imageresize($full_file_name,$this->tuckpath.$file_name,$this->tuckwidth,$this->tuckheight)){
													$this->err[$name][$key]['image'] = "生成图片文件成功！生成缩列图失败！";
												}else{
													$suc[$name][$key.'_small'] = $this->tuckpath.$file_name;
												}
											}
										}
										$suc[$name][$key] = $full_file_name;//文件路径
									}else{
										$this->err[$name][$key] = "上传文件移动失败";
										$suc[$name][$key] = '';
									}
								}else{
									$this->err[$name][$key] = "转移的文件不是上传的文件！";
									$suc[$name][$key] = '';
								}
							}else{
								$this->err[$name][$key] = "上传文件大小超过限制,允许大小：".$this->getsize($filesize)." 上传文件的大小：".$this->getsize($_FILES[$name]['size'][$key]);
								$suc[$name][$key] = '';
							}
						}else{
							$this->err[$name][$key] = "上传文件的类型不正确";
							$suc[$name][$key] = '';
						}
					}elseif($_FILES[$name]['size'][$key]){
						$this->err[$name][$key] = "上传错误，错误信息：".$this->geterror($_FILES[$name]['type'][$key]);
						$suc[$name][$key] = '';
					}
				}
			}else{
				//表名不是数组，没有多个上传信息
				if(!$this->geterror($_FILES[$name]['error'])){//上传正常
					$type = $this->getfilestype($_FILES[$name]['name']);
					if((!empty($filetype[0]) && $filetype[0]=='*') || in_array($type,$filetype,false)){//检查类型文件
						if($_FILES[$name]['size']<=$filesize){//检查上传大小
							//开始保存文件
							if(is_uploaded_file($_FILES[$name]['tmp_name'])){//是上传的文件
								$file_name = md5(uniqid().time().$_SERVER['REMOTE_ADDR']).".".$type;//上传文件名保证唯一 ^_^
								$full_file_name = $filepath.$file_name;
								if(COPY($_FILES[$name]['tmp_name'],$full_file_name)){
									//如果是图片，并却开启了按最大高度最大宽度来重新生成图片
									if($this->maxtuck  && $this->is_image($full_file_name)){
										list($width,$height,$false,$false) = getimagesize($full_file_name);
										if(($width>=$height && $this->maxwidth>0) || $this->maxheight<=0){//宽度>高度
											$rate = number_format($width /$this->maxwidth,2);
											$height_now = ceil($height/$rate);
											if(!$this->imageresize($full_file_name,$full_file_name,$this->maxwidth,$height_now)){
											$this->err[$name][$key]['image'] = '按 宽度 最大缩图失败.';
											}else{
												$suc[$name][$key] = $full_file_name;
											}
										}else{
											$rate = number_format($height/$this->maxheight,2);
											$width_now = ceil($width/$rate);
											if(!$this->imageresize($full_file_name,$full_file_name,$width_now,$this->maxheight)){
											$this->err[$name][$key]['image'] = '按 高度 最大缩图失败.';
											}else{
												$suc[$name][$key] = $full_file_name;
											}
										}
									}
									//如果是图片，并却重新开启了图片生成 2008-3-25
									if($this->resetimage && $this->is_image($full_file_name)){
										if(!$this->imageresize($full_file_name,$full_file_name,$this->width,$this->height)){
											$this->err[$name]['image'] = '重新设置图片错误.';
										}else{
											$suc[$name]['image'] = $full_file_name;
										}
									}
									//开启了所列图，并却是个图片
									if($this->tuckimage && $this->is_image($full_file_name)){
											if($this->tuckwidth && $this->tuckheight && $this->isdir($this->tuckpath)){//制定生成缩列图
												if(!$this->imageresize($full_file_name,$this->tuckpath.$file_name,$this->tuckwidth,$this->tuckheight)){
													$this->err[$name]['image'] = "生成图片文件成功！生成缩列图失败！";
												}else{
													$suc[$name.'_small'] = $this->tuckpath.$file_name;
												}
											}
										}
									$suc[$name] = $full_file_name;//文件路径
								}else{
									$this->err[$name] = "上传文件移动失败";
									$suc[$name] = '';
								}
							}else{
								$this->err[$name] = "转移的文件不是上传的文件！";
								$suc[$name] = '';
							}
						}else{
							$this->err[$name] = "上传文件大小超过限制,允许大小：".$this->getsize($filesize)." 上传文件的大小：".$this->getsize($_FILES[$name]['size']);
							$suc[$name] = '';
						}
					}else{
						$this->err[$name] = "上传文件的类型不正确";
						$suc[$name] = '';
					}
				}elseif($_FILES[$name]['size']){
					$this->err[$name] = "上传错误，错误信息：".$this->geterror($_FILES[$name]['type']);
					$suc[$name] = '';
				}
			}//结束
		}
		return $suc;
	}

	/* ========== 获得系统支持的最大文件大小 2007-10-15 返回最大字节数 ============= */
	private function getmaxsize()
	{
		$maxsize = ini_get('upload_max_filesize');
		if (!is_numeric($maxsize)) {
		   if (strpos($maxsize, 'M') !== false)
			   $maxsize = intval($maxsize)*1024*1024;
		   elseif (strpos($maxsize, 'K') !== false)
			   $maxsize = intval($maxsize)*1024;
		   elseif (strpos($maxsize, 'G') !== false)
			   $maxsize = intval($maxsize)*1024*1024*1024;
		}
		return $maxsize;
	}

	public function getfilestype($file)
	{
		preg_match('/\.([a-zA-Z0-9]*)$/i',$file,$match);
		return strtolower($match[1]);
	}
	/* =============== 返回类型 ============ */
	private function getfiletype($filetype)
	{

		switch(strtolower($filetype))
		{
			case "application/octet-stream":
				return "exe";

			case "video/x-msvideo":
				return "avi";

			case "image/bmp":
				return "bmp";

			case "text/css":
				return "css";

			case "application/msword":
				return "doc";

			case "application/x-msdownload":
				return "dll";

			case "application/x-gzip":
				return "gz";

			case "text/html":
				return "html";

			case "text/htm":
				return "htm";

			case "image/x-icon":
				return "ico";

			case "image/pjpeg":
			case "image/jpeg":
				return "jpg";

			case "application/x-javascript":
				return "js";

			case "audio/mpeg":
				return "mp3";

			case "application/pdf":
				return "pdf";

			case "application/vnd.ms-powerpoint":
				return "ppt";

			case "text/plain":
				return "txt";

			case "application/vnd.ms-excel":
				return "xls";

			case "application/zip":
				return "zip";

			case "application/x-shockwave-flash":
				return "swf";

			case "image/x-png":
				return "png";

			case "image/gif":
				return "gif";
			case "audio/x-ms-wma":
				return "wma";
		}
		return '';
	}
	/* ============ 获得上传报错信息 返回空就是上传成功 2007-10-16 =============== */
	private function geterror($infomation='')
	{
		switch(strtolower($infomation))
		{
			case 1:
			case "UPLOAD_ERR_INI_SIZE":
				$msg = "上传的文件过大";
				break;
			case 2:
			case "UPLOAD_ERR_FORM_SIZE":
				$msg = "上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值";
				break;
			case 3:
			case "UPLOAD_ERR_PARTIAL":
				$msg = "文件只有部分被上传";
				break;
			case 4:
			case "UPLOAD_ERR_NO_FILE":
				$msg = "没有文件被上传";
				break;
			case 6:
			case "UPLOAD_ERR_NO_TMP_DIR":
				$msg = "找不到临时文件夹";
				break;
			case 7:
			case "UPLOAD_ERR_CANT_WRITE":
				$msg = "文件写入失败。";
				break;
			default:
				$msg = '';
				break;
		}
		return $msg;//返回空就是上传成功
	}
	/* ============= 获得文件大小 ============= */
	public function getsize($size,$unit=0)
	{
		$type = array(' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
		if(!$size || !(int)$size)return 0;
		$result = $size/1024;
		return $result>=1024 ? getsize($result,++$unit) : sprintf("%.2f",$result).$type[$unit];
	}
	/* =============================== 生成缩列图 ============================ */
	/*
	$img 原文件
	$target 目标文件
	$width 宽度
	$height 高度
	$percent 比例
	*/
	public function imageresize($img,$target="",$width=0,$height=0,$percent=0)
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
	#######################判断文件夹是否存在不存在就创建。[可判断多个文件夹]
	function isdir($dir)
	{
		return is_dir($dir) ? true : ($this->isdir(dirname($dir)) ?  mkdir(ucfirst($dir)): false);
	}
	/* ============================ 判断是否一个图片 2007-7-25==================== */
	function is_image($image)
	{
		if(preg_match("/[^\\|\/:\*\?\"<>\|]*\.(?:gif|jpg|jpeg|png|bmp)$/i",$image))return true;
	}
	public function array_tolower($arr)
	{
		foreach($arr as $key=>$value){
			if(is_array($value)){
				$arr[$key] = array_tolower($value);
			}else{
				$arr[$key] = strtolower($value);
			}
		}
		return $arr;
	}
}
?>
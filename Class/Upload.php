<?php
/**
 * 
 * 上传文件类,xml文件配置路径，进行校验 ...
 * @author macbookpro
 * @version v.1.0
 */
class Upload
{
	public $pathes;
	private $xml;
	
	private $filepath;
	private $limit_file_size = 1024000;
	
	const US_SUCCESS 				= 0;
	const US_FAILED_OPTION			= 1;
	const US_FAILED_PATH			= 2;
	const US_FAILED_FILESIZE		= 3;
	const US_FAILED_EXTENSION		= 4;
	const US_FAILED_NEW_EXTENSION	= 5;
	const US_FAILED_ACCIDENT		= 6;
	const US_FAILED_CONFIG			= 7;
	const US_FAILED_MKDIR			= 8;
	const US_FAILED_FILENAME		= 9;
	
	private $upload_status_title = array(
		self::US_SUCCESS => '成功',
		self::US_FAILED_OPTION => '上传选项失败',
		self::US_FAILED_PATH => '文件路径失败',
		self::US_FAILED_FILESIZE => '文件大小失败',
		self::US_FAILED_EXTENSION => '上传文件扩展名无效',
		self::US_FAILED_NEW_EXTENSION => '文件扩展名无效',
		self::US_FAILED_ACCIDENT => '上传文件意外失败',
		self::US_FAILED_CONFIG => '配置文件',
		self::US_FAILED_MKDIR => '创建文件夹失败',
		self::US_FAILED_FILENAME => '无效的新文件名',
	);
	

	function __construct()
	{
		$this->xml = simplexml_load_file(Config. 'Config4upload.xml');
		if ($this->xml) $this->get_file_setting($this->xml);
	}	
	
	/*
	 *  开始上传 
	 *  $field 上传域字段名
	 *  $filepath 上传路径
	 *  $filename 上传新文件名
	 */
	function startUpload($field, $filepath, $filename, $type = 0)
	{
		if (!$this->xml) return $this->getUploadStatus(self::US_FAILED_CONFIG, 'xml');

		if (is_uploaded_file($_FILES[$field]['tmp_name']))
		{
			if(!array_key_exists($filepath, $this->pathes))	return $this->getUploadStatus(self::US_FAILED_PATH, $filepath);

			$old_filename = $_FILES[$field]['name'];
			$allow_extension = explode(',', $this->pathes[$filepath]['ext']);
			$extension = pathinfo($old_filename, PATHINFO_EXTENSION);
			$filesize = @filesize($_FILES[$field]['tmp_name']);
			
			if (!in_array(strtolower($extension), $allow_extension))
			{
				return $this->getUploadStatus(self::US_FAILED_EXTENSION, $this->pathes[$filepath]['ext']);
			} 
			if (!in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $allow_extension)) 
			{
				return $this->getUploadStatus(self::US_FAILED_NEW_EXTENSION, sprintf("can't not allow '%s' on '%s'", $filename, $this->pathes[$filepath]['ext']));
			}
			if ($filesize > $this->limit_file_size) 
			{
				return $this->getUploadStatus(self::US_FAILED_FILESIZE, $this->limit_file_size);
			}
			
			# 后续的上传交付处理
			return $this->_localUp($field, $filepath, $filename);
		}
		else 
		{
			return $this->getUploadStatus(self::US_FAILED_OPTION, $filepath);
		}
	}
	
	/* 本地上传 */
	private function _localUp($field, $filepath, $filename)
	{
		
		# 针对本地化目录, 处理真正的上传路径
		if (true == intval($this->pathes[$filepath]['auto']))
		{
			$new_filepath = 'UploadFile/'. $filepath.  date('Ym').'/';
			$filename = $new_filepath. date('YmdHis'). rand(1000, 9999). '.'. pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);  
		}
		else 
		{
			$new_filepath = 'UploadFile/'. $filepath;
			$filename = $new_filepath. $filename;
		}
		
		if (!is_dir($new_filepath) && !file_exists($new_filepath))
		{
			$mkdir = mkdir($new_filepath, 0755, true);
			if (!$mkdir) return $this->getUploadStatus(self::US_FAILED_MKDIR, $new_filepath);
		}
	
		#开始上传
		if (move_uploaded_file($_FILES[$field]['tmp_name'], $filename))
		{
			return $this->getUploadStatus(self::US_SUCCESS, $filename);
		}
		else 
		{
			return $this->getUploadStatus(self::US_FAILED_ACCIDENT);
		}
	}
	
	private function _ftpUp(){}
	
	private function _svnUp(){}
	
	private function make_avail_path($dom)
	{
		
	}
	
	
	/* 获取xml文件配置 映射成配置数组*/
	private function get_file_setting($xml, $name = '')
	{
		$fullname = $name. $xml->name. '/';
		$attributes = $xml->name->attributes(); 
		$this->pathes[$fullname] = array('auto' => (boolean)(int)$attributes['auto'], 'ext' => (string)$attributes['ext']);
		if (isset($xml->path))
		{
			foreach ($xml->path as $val) 
			{
				$this->get_file_setting($val, $fullname);
			}
		}
	}
	
	/* 文件上传状态返回 数组 */
	function getUploadStatus($status = 0, $msg = '')
	{
		$status = (int)$status;
		$signal = 0 === $status ? 'SUC' : 'ERR';
		
		return array('signal' => $signal, 'errno' => $status, 'msg' => $msg);
	}
	
	/* 文件上传状态文字 */
	function getUploadStatusTitle($status = 0)
	{
		return $this->upload_status_title[$status];
	}
	
	/*
	 *  swfupload 组件参数输出函数
	 *  说明： 在窗体加载(window.onload事件)完成时，可以通过输出该函数来配置及生成swfupload控件
	 *  @param $id 组件标识
	 *  @param $file_post_name 文件上传域字段名，默认filedata  对应 $_FILE['Filedata']
	 *  @param $upload_url 文件上传响应的路径 
	 *  @param $button 控件按钮设置
	 *		array(
	 *			'span_id' 	//按钮的id
	 *			'img'		//按钮的图片的路径
	 *			'width'		//按钮的宽
	 *			'height'	//按钮的高
	 *		)
	 *	@param $params array 发出请求的附带参数
	 * ---------------------------------------------------------
	 * 触发事件:
	 * 		
	 *    swfu_start_function (obj) {} 				#
	 *    swfu_error_function (obj, err, msg) {}
	 *    swfu_success_function (obj, msg) {}
	 *    swfu_complete_function (obj) {}
	 *    
	 *    obj:      (File Object包含了一组可用的文件属性)
	 *    {
			id : string,			// SWFUpload控制的文件的id,通过指定该id可启动此文件的上传、退出上传等
			index : number,			// 文件在选定文件队列（包括出错、退出、排队的文件）中的索引，getFile可使用此索引
			name : string,			// 文件名，不包括文件的路径。
			size : number,			// 文件字节数
			type : string,			// 客户端操作系统设置的文件类型
			creationdate : Date,		// 文件的创建时间
			modificationdate : Date,	// 文件的最后修改时间
			filestatus : number		// 文件的当前状态，对应的状态代码可查看SWFUpload.FILE_STATUS
		  }
		  err:		(错误代码)
		  msg:		(服务器返回的信息)
	 */
	static function widget($id = 'swfu1', $file_post_name='Filedata', $upload_url = '', $button = array(), $params = array())
	{
		if (empty($upload_url)) $upload_url = '/?c=api&m=upload&action=test_upload';
		if (empty($file_post_name)) $file_post_name = 'Filedata';
		
		$parse_url = parse_url($upload_url);
		parse_str($parse_url['query'], $paramt);
		while (list($k, $v) = each($paramt)) $params1 .= ",\n\"$k\":\"$v\"";
		while (list($k, $v) = each($params)) $params2 .= ",\n\"$k\":\"$v\"";
		
	    $button_d = array(
	    	'img' => 'Plugins/SWFUpload/btn.png',
	    	'span_id' => 'swfu1_span',
	    	'height' =>'32',
	    	'width' => '175',
	    );
	    $session_id = session_id();
	    
	    $button = array_merge($button_d, $button);
		
		$script = <<<SCRIPT
		
		{$id} = new SWFUpload({ 
			upload_url : "{$upload_url}",
			flash_url : "Plugins/SWFUpload/Flash/swfupload.swf", 
			file_size_limit : "1024 KB",
			file_types : "*.*",
			file_types_description : "All Files",
			file_post_name:'{$file_post_name}',
			debug: false,

			// Button Settings
			//<span class='redText'>点击此处上传</span>
			button_image_url : "{$button['img']}",
			button_placeholder_id : "{$button['span_id']}",
			button_width: {$button['width']},
			button_height: {$button['height']},
			button_text : '<font class="uploadredText">{$button['text']}</font>' ,
			button_text_style : '.uploadredText {height:22px; color: #FFFFFF; font-size:18px; font-family: "", Arial, Helvetica, sans-serif,; font-weight:bold; }',
			button_text_left_padding : 32,
			button_text_top_padding : 4,
			
			post_params: {
				"PHPSESSID" : "{$session_id}" {$params1} {$params2}
			},

			upload_start_handler : swfu_start_function,
			upload_error_handler : swfu_error_function,
			upload_success_handler: swfu_success_function,
			upload_complete_handler : swfu_complete_function,
			file_dialog_complete_handler: function(ns,nq){this.startUpload()}
		});
		function swfu_start_function(){}
		function swfu_error_function(){}
		function swfu_success_function(){}
		function swfu_complete_function(){}
		
SCRIPT;
		return $script;
	}
}
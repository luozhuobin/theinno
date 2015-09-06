<?php
class CKeditor
{
	const MAX_UPLOAD_SIZE = 812000;
	private $fn;
	
	function __construct($key)
	{
		if ($key != md5('%T^YY&UU')) exit ('no key');
		$this->fn = $_GET['CKEditorFuncNum']; 
	}
	
	function uploadImage($filepath = 'Template/UploadFiles/UpImg/')
	{
		
		if (is_uploaded_file($_FILES['upload']['tmp_name']))
		{
			$info  = getimagesize($_FILES['upload']['tmp_name']);
			$filename = md5(date('YmdHis').rand(1000,9999)). '.'. pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);

			if ($_FILES['upload']['error'] != 0) $this->mkhtml('', '上传文件常规出错');
			if ($_FILES['upload']['size'] > self::MAX_UPLOAD_SIZE) $this->mkhtml('', "上传的文件不能超过".intval(self::MAX_UPLOAD_SIZE / 1024)."KB！");
			if (!$info) $this->mkhtml('', '不是图片类型,请上传 jpg,bmp,png,gif 类型的文件');
			if (move_uploaded_file($_FILES['upload']['tmp_name'], $filepath.'/'.$filename))
			{
				$this->mkhtml($filepath.'/'.$filename ,'上传成功!'); 
			}
			else {
				$this->mkhtml('' ,'上传失败'); 
			}
		}
		exit;
	}

	function mkhtml($fileurl,$message)
	{ 
		$str='<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.$this->fn.', \''.$fileurl.'\', \''.$message.'\');</script>';
		exit($str);
	}
	
}
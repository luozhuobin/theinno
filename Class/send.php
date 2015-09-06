<?php
		// 测试时显示错误
		//ini_set('display_errors','true');
		//error_reporting( E_ALL);

		
		//$from = $_GET["from"];
		$to = $_GET["to"];
		$code = $_GET["code"];
		//$subject = $_GET["subject"];
		$bodyId = $_GET["bodyId"];			
		$email = $_GET['email'];
		$pwd = $_GET['pwd'];
		$identity = $_GET['identity'];
		$link = "http://hrh.theinno.org/?m=".$identity."&action=step2&code=".$code;
		
		$body = "undefined";
		$subject = "undefined";
		var_dump($bodyId);
		if($bodyId == 1){	// 用于注册激活使用
		
				$body = '<p>亲爱的用户：您好!</p>
            		<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 感谢您注册残疾人就业热线平台，您的登录帐号是<a href="mailto:'.$to.'" target="_blank">'.$to.'</a><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 点击下面的按钮完成注册：<br>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="'.$link.'" target="_blank">'.$link.'</a><br>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (如果点击链接无反应，请复制链接到浏览器里直接打开)<br><br>
					</p>
					<p class="gray" style="text-align: center;">温馨提示：此邮件由系统发送，请勿直接回复。</p>';
					
				$subject = "激活邮箱 - 残疾人就业热线平台";				
		}else if($bodyId == 2){
				$body = '<p>亲爱的用户：您好!</p>
            		<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 感谢您注册残疾人就业热线平台，您的登录帐号是<a href="mailto:'.$to.'" target="_blank">'.$to.'</a><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 您的登录密码是：'.$pwd.'<br>										
					</p>
					<p class="gray" style="text-align: center;">温馨提示：此邮件由系统发送，请勿直接回复。</p>';
					
				$subject = "忘记密码 - 残疾人就业热线平台";
		}
		
		include_once("Email_163.php");

		//$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $to,$subject , $body);
		//echo $result;
?>
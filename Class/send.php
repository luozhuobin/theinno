<?php
		// ����ʱ��ʾ����
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
		if($bodyId == 1){	// ����ע�ἤ��ʹ��
		
				$body = '<p>�װ����û�������!</p>
            		<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ��л��ע��м��˾�ҵ����ƽ̨�����ĵ�¼�ʺ���<a href="mailto:'.$to.'" target="_blank">'.$to.'</a><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; �������İ�ť���ע�᣺<br>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="'.$link.'" target="_blank">'.$link.'</a><br>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (�����������޷�Ӧ���븴�����ӵ��������ֱ�Ӵ�)<br><br>
					</p>
					<p class="gray" style="text-align: center;">��ܰ��ʾ�����ʼ���ϵͳ���ͣ�����ֱ�ӻظ���</p>';
					
				$subject = "�������� - �м��˾�ҵ����ƽ̨";				
		}else if($bodyId == 2){
				$body = '<p>�װ����û�������!</p>
            		<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ��л��ע��м��˾�ҵ����ƽ̨�����ĵ�¼�ʺ���<a href="mailto:'.$to.'" target="_blank">'.$to.'</a><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ���ĵ�¼�����ǣ�'.$pwd.'<br>										
					</p>
					<p class="gray" style="text-align: center;">��ܰ��ʾ�����ʼ���ϵͳ���ͣ�����ֱ�ӻظ���</p>';
					
				$subject = "�������� - �м��˾�ҵ����ƽ̨";
		}
		
		include_once("Email_163.php");

		//$result = Email_163::getInstance()->send(Email_163::MAIL_USER, $to,$subject , $body);
		//echo $result;
?>
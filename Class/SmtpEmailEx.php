<?php
/**
 *描述:SMTP邮件发送类
 *作者:TroublismEx
 *创建时间:2008/12/23
 *最后修改:2010/09/06
 */
	class SmtpEmailEx{
		
		private $smtp_sock;
		private $smtp_srv_addr="119.145.130.139";//;"mail.yaowan.com";
		private $smtp_srv_port=25;
		private $cmdArr=array("HELO TroublismEx\r\n"=>250,"AUTH LOGIN\r\n"=>334);
		
		private $Debug=FALSE;
		
		private $AUTH_user="admin";
		private $AUTH_pass="^20acot";
		
		function __construct($Debug=FALSE,$Auth_user="",$Auth_pwd=""){
			$this->Debug=$Debug;
			$Auth_user!=""?$this->AUTH_user=$Auth_user:null;
			$Auth_pwd!=""?$this->AUTH_pass=$Auth_pwd:null;

			$this->cmdArr=array_merge($this->cmdArr,array(base64_encode($this->AUTH_user)."\r\n"=>334,base64_encode($this->AUTH_pass)."\r\n"=>235));
		}
		
		function __destruct(){
			$this->smtp_sock!=FALSE?socket_close($this->smtp_sock):exit();
		}
		
		public function SendEmail($From,$To,$Format,$Subject,$Body,$AttachFileArr=null){

			if($AttachFileArr!=null&&!is_array($AttachFileArr))
				return "附件参数必须为一个数组";
			
			$this->cmdArr=array_merge($this->cmdArr,array("MAIL FROM:<".$From.">\r\n"=>250,"RCPT TO:<".$To.">\r\n"=>250,"DATA\r\n"=>354));
			
			if(($this->smtp_sock=@socket_create(AF_INET,SOCK_STREAM,SOL_TCP))==FALSE)
				return $this->PickupErr();
			
			socket_set_nonblock($this->smtp_sock);
			//if(@socket_connect($this->smtp_sock,$this->smtp_srv_addr,$this->smtp_srv_port)==FALSE)
			//	return $this->PickupErr();
			@socket_connect($this->smtp_sock,$this->smtp_srv_addr,$this->smtp_srv_port);
			socket_set_block($this->smtp_sock);
			if(socket_select($r=null,$w=array($this->smtp_sock),$e=null,5)!=1)
				return "连接超时或被拒绝.";

			socket_recv($this->smtp_sock,$retStr,256,0);
			
			foreach($this->cmdArr as $CmdStr=>$retCode)
				if(($retVal=$this->SendCmd($CmdStr,$retCode))!==TRUE)
					return $retVal;
			
			$EmailBody="From:".$From."\r\nTo:".$To."\r\nSubject:=?UTF-8?B?".base64_encode($Subject)."?=\r\nMime-Version: 1.0\r\n";
			
			$Boundary=uniqid();
			
			if($AttachFileArr!=null){
				$EmailBody.="Content-type:multipart/mixed;boundary=".$Boundary."\r\n\r\n";
				$EmailBody.="--".$Boundary."\r\n";
			}
			
			$EmailBody.="Content-Type:text/".($Format=="HTML"?"html":"plain").";charset=\"UTF-8\"\r\nContent-transfer-encoding:8bit\r\n\r\n";
			
			$EmailBody.=$Body."\r\n";
			
			if($AttachFileArr!=null)
				foreach($AttachFileArr as $AttachFileItem){
					if(!file_exists($AttachFileItem)){
						if($this->Debug)
							echo("文件[".$AttachFileItem."]不存在<br>");
						continue;
					}
					
					$EmailBody.="--".$Boundary."\r\n";
					$EmailBody.="Content-type:application/unknown;name=".$AttachFileItem."\r\n"; 
					$EmailBody.="Content-disposition:attachment;filename=".$AttachFileItem."\r\n";
					$EmailBody.="Content-transfer-encoding:base64\r\n\r\n";
					$EmailBody.=chunk_split(base64_encode(file_get_contents($AttachFileItem)))."\r\n";
				}
				
			$EmailBody.=".\r\n";
			
			if(($retVal=$this->SendCmd($EmailBody,250))!==TRUE)
				return $retVal;
				
			socket_send($this->smtp_sock,"QUIT\r\n",6,0);
			
			return TRUE;
		}
		
		private function SendCmd($CmdStr,$retCode){
			socket_send($this->smtp_sock,$CmdStr,strlen($CmdStr),0);
			socket_recv($this->smtp_sock,$retStr,256,0);
			
			if(intval(substr($retStr,0,3))==$retCode){
				if($this->Debug)
					echo("发送命令[".$CmdStr."]时成功:".$retStr."<br>");
				return TRUE;
			}else{
				$errStr="发送命令[".$CmdStr."]时失败:".$retStr;
				if($this->Debug)
					echo($errStr."<br>");
				return FALSE;
			}
		}
		
		private function PickupErr(){
			return socket_strerror(socket_last_error($this->smtp_sock));
		}

	}

?>
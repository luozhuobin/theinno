<?php
/**
 * 
 * 用法  EMail_163::getInstance()->send()   1.OK      2.NOTOK
 * @author ct
 *
 */
class Email_163
{
	private static $instance;
	const MAIL_USER = 'theinnojob@163.com';
	
	public $host			= 'smtp.163.com';
	public $port			= 25;
	public $user			= 'theinnojob@163.com';
	public $pass			= 'www123456';
	private $debug_string;
	private $debug_content;
	
	private $in;
	private $rs;
	private $conn;
	private $mailformat = 1;  
	private $socket;
	
	private function __construct()
	{
		$this->user   = base64_encode($this->user);
		$this->pass   = base64_encode($this->pass);
		
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
//		$this->socket = socket_create(2, 1, 6);
		if($this->socket)
		{
			$this->debug("创建SOCKET:".socket_strerror(socket_last_error()));
		}
		else 
		{
			exit("初始化失败，请检查您的网络连接和参数");
		}
		
		$this->conn = socket_connect($this->socket,$this->host,$this->port);
		if($this->conn)
		{
			$this->debug("创建SOCKET连接:".socket_strerror(socket_last_error()));
		} 
		else
		{
			exit("初始化失败，请检查您的网络连接和参数");
		}
		$this->debug("服务器应答：<font color=#cc0000>".socket_read ($this->socket, 1024)."</font>");
	}
	
	function __destruct()
	{
		socket_close($this->socket);
	}
	
	static function getInstance()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	
	private function command($in = '')
	{
		$this->in = $in;
		socket_write ($this->socket, $this->in, strlen($this->in));
		$this->debug("服务器应答：<font color=#cc0000>".socket_read ($this->socket, 1024)."</font>");
	}
	private function debug($error = '')
	{
		$this->debug_string = $error;
		$this->debug_content .= $error. '<br/>';
	}
	
	
	function getDebugString()
	{
		return $this->debug_string;
	}
	
	function getDebugContent()
	{
		return $this->debug_content;
	}
	/**
	*
	*			用于代理发送邮件
	**/
	function sendBy( $to = '', $code='' , $bodyId = ''){
		//http://211.154.153.59/email_send/send.php?code=".$code."&to=".$to."&bodyId=1";
		$url = "http://211.154.153.59/email_send/send.php?code=".$code."&to=".$to."&bodyId=".$bodyId;
		//echo $url;
		$result = file_get_contens($url);
		//echo "send..".$result;
		return $result;
	}
	
	function send($from ='', $to = '', $subject ='', $body='')
	{
		if($from == "" || $to == "")
		{
			$this->debug('请输入信箱地址');
			return  0;
		}
		if($subject == "") $sebject = "无标题";
		if($body    == "") $body    = "无内容";
		
		//$subject = iconv('utf-8', 'gbk', $subject);
		
		$All          = "From:".$from."\r\n";
		$All          .= "To:".$to."\r\n";
		$All          .= "Subject:".$subject."\r\n";
		$All		  .=  $this->mailformat==1 ? "Content-Type: text/html;\r\n" : "Content-Type: text/plain;\r\n";
		$All          .= "charset=utf-8\r\n\r\n";
		$All          .= $body;
		
		$this->command("EHLO HELO\r\n");
		$this->command("AUTH LOGIN\r\n");
		$this->command("{$this->user}\r\n");
		$this->command("{$this->pass}\r\n");
		
		if(!eregi("235", $this->debug_string))
		{
	    	$this->debug("smtp 认证失败");
	   		return -1;
		}
		
		$this->command("MAIL FROM:<". $from. ">\r\n");
		$this->command("RCPT TO:<". $to. ">\r\n");
		$this->command("DATA\r\n");
		$this->command($All. "\r\n.\r\n");
		
		if(!eregi("250",$this->debug_string))
		{
			$this->debug("邮件发送失败");
			return -2;
		}
		
		$this->command("QUIT\r\n");
		return 1;
	}
}
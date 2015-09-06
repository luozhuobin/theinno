<?PHP
Class Email
{
	public $phpemailer;//phpemailer����
	public $server;
	public $username;
	public $password;
	public $type = 'SMTP';
    public $port = '';
    public $FromName = '';
	public $FromEmail;
	public $error;

	public function __construct($server='',$port = 25,$username='',$password='',$FromEmail='',$type='SMTP')
	{
		require_once ClassEmail."class.phpmailer.php";
		if($server){
			$this->server = $server;
		}
        if($port){
            $this->port = $port;
        }
		if($type){
			$this->type = strtoupper($type);
		}
		if($username){
			$this->username = $username;
		}
		if($password){
			$this->password = $password;
		}
		if($FromEmail){
			$this->FromEmail = $FromEmail;
		}
	}

	##�ڲ���ʼ��
	public function Init()
	{
		$this->phpemailer = new PHPMailer();
		$this->phpemailer->Host = $this->server;
        $this->phpemailer->Port = $this->port;
		$this->phpemailer->Username = $this->username; // SMTP username
		$this->phpemailer->Password = $this->password; // SMTP password
		$this->phpemailer->From = $this->FromEmail; //������
		$this->phpemailer->CharSet = "utf-8";
		if(strtoupper($this->type)=='SMTP'){
			$this->phpemailer->IsSMTP(true);
			$this->phpemailer->SMTPAuth = true;
		}else{
			$this->phpemailer->IsMail(true);
		}
	}

	public function addemail($email='',$user='')
	{
		if(!$this->phpemailer){
			$this->Init();
		}
		$this->phpemailer->AddAddress($email,$user);
	}

	public function send($title,$content,$html=true)
	{
		if(!$this->phpemailer){
			$this->Init();
		}
        $this->phpemailer->FromName = $this->FromName;
        $this->phpemailer->From     = $this->FromEmail; //������
		$this->phpemailer->Subject	= $title;
		$this->phpemailer->Body		= $content;
		$this->phpemailer->IsHTML($html===true?true:false);
		if(!$this->phpemailer->Send()){
			$this->error = $this->phpemailer->ErrorInfo;
			return false;
		}else{
			return true;
		}
	}

	public function addfile($file,$newfilename='')
	{
		if(!$this->phpemailer){
			$this->Init();
		}
		if(!$newfilename){
			$newfilename = getfilename($file);
		}
		return $this->phpemailer->AddAttachment($file,$newfilename);
	}

	public function __call($method,$param)
	{
		return call_user_func_array(array($this->phpemailer,$method),$param);
	}
}
?>
<?PHP
##定义文件目录的地址
define('Root',dirname(__FILE__).'/',true);
date_default_timezone_set('Asia/Shanghai');//设置为北京时间
!defined("root") && define('root','./Drupal7/innojob/',true);
define('Func',root.'Func/',true);
define('Init',root.'Init/',true);
define('Model',root.'Model/',true);##模型目录
define('Table',root.'Table/',true);##数据库表操作目录
define('Config',root.'Config/',true);#配置文件目录
define('Plugins',root.'Plugins/',true);##插件目录
define('Classpath',root.'Class/',true);##类目录
define('Language',root.'Language/',true);#语言包目录
define('Template',root.'Template/',true);#模板目录
define('UploadFile',root.'Template/UploadFiles/',true);##上传图片的文件夹
define('CacheFile',root.'CacheFile/',true);//缓存HTML目录
define('CacheDataBase',root.'CacheFile/DataBase/',true);//缓存数据库的数组
define('ClassEmail',Plugins.'PHPMailer_v2.0.0/',true);//PHPEmailer发送组件的目录
define('Control',root.'Control/',true);//控制机目录
define('Tinymce',Plugins.'tinymce/',true);//Tinymce 保存的地址
define('Keyword',CacheFile.'Keyword/',true);//关键字数据库保存目录
define('watermask',Template.'Default/images/watermask.gif',true);//水印图片
define('checkcode',Plugins.'checkcode.php',true);//验证码
define('yeepay_bank',Func.'YeepayCommonBank.php',true);//Yeepay 银行接口目录
define('alipay',Plugins.'alipay/',true);//alipay 核心存放目录
define('kq',Plugins.'kq/',true);//kq 核心存放目录
define('superman','admin',true);//超级管理员
define('MainAddr','116.28.64.142,112.90.177.206',true);
define('CurrentAddr', $_SERVER['SERVER_ADDR'],true);
define('ImgDomain', 'test.yaowan.com');
//define('WEBDOMAIN','http://www.cantop.org/innojob/Default.php');
//define('WEBDOMAIN','http://hrh.theinno.cc/Default.php');
define('WEBDOMAIN','http://hrh.theinno.org');
//define('WEBDOMAIN','http://hrh.theinno.com');
define('EffectiveTime',30*60);//激活邮件的有效时间
define('max_upload_file_size',61440);
## 统计图
define('Stat1',Plugins.'open-flash-chart.swf',true);//统计图1.
$mainhost = explode(',',MainAddr);



#if($_SERVER['HTTP_HOST'] == 'yaowan.com')header("location:http://www.yaowan.com");
## 当前域名
define('domain',$_SERVER['HTTP_HOST']!='www.yaowan.com'?$_SERVER['HTTP_HOST']:'www.yaowan.com');
## 图片服务器
//switch(!empty($_SERVER["SERVER_ADDR"])?$_SERVER["SERVER_ADDR"]:'')
//{
    
//    case "192.168.1.57":
//	case "127.0.0.1":
		
        //if(isset($_REQUEST['c']) && $_REQUEST['c'] == 'admin'){
		define('imagedomain','./yaowan');
//		define('mysql_server','localhost');
//		define('mysql_server','220.231.194.66');//61.134.43.85');//数据库主机
//		define('mysql_user','cantopor_bin');//登陆用户
//		define('mysql_pass','hellobin');//登陆密码
		
		define('mysql_server',$_SERVER['DATABASE_NAME']);//61.145.119.150');//数据库主机
		define('mysql_user',$_SERVER['USERNAME']);//登陆用户
		define('mysql_pass',$_SERVER['PASSWORD']);//登陆密码
		
		define('mysql_dbname','hrh_theinno_org');//数据库名
		define('dfh_mysql_user',mysql_user);//dfh登陆用户
		define('dfh_mysql_pass',mysql_pass);//dfh登陆密码
		define('dfh_mysql_dbname','dfhyaowan');//dfh数据库名
		define('sns_mysql_user',mysql_user);//dfh登陆用户
		define('sns_mysql_pass',mysql_pass);//dfh登陆密码
		define('sns_mysql_dbname','yaowanbbs');//dfh数据库名
//		break;
        //}*/
//	default:
//		define('imagedomain','./');
//
//		define('sel_mysql_server','127.0.0.1');//61.134.43.85');  127.0.0.1,  127.0.0.1//从数据库主机 
//        	define('sel_mysql_user','yaowan');//从登陆用户
//        	define('sel_mysql_pass','yaowan23a05');//从登陆密码
//
//		define('sel2_mysql_server','127.0.0.1');//61.134.43.85');//从数据库主机 
//                define('sel2_mysql_user','yaowan');//从登陆用户
//                define('sel2_mysql_pass','yaowan23a05');//从登陆密码
//
//        //if(in_array(CurrentAddr,$mainhost))
//		    define('mysql_server','127.0.0.1');//61.134.43.85');//数据库主机
//        //else
//            //define('mysql_server','192.168.3.13');//61.134.43.85');//数据库主机
//		define('mysql_user','yaowan');//登陆用户
//		define('mysql_pass','yaowan23a05');//登陆密码
//		define('mysql_dbname','yaowan');//数据库名
//        define('dfh_mysql_user',mysql_user);//dfh登陆用户
//        define('dfh_mysql_pass',mysql_pass);//dfh登陆密码
//        define('dfh_mysql_dbname','dfhyaowan');//dfh数据库名
//		define('sns_mysql_user',mysql_user);//dfh登陆用户
//		define('sns_mysql_pass',mysql_pass);//dfh登陆密码
//		define('sns_mysql_dbname','yaowanbbs');//dfh数据库名
//		
//		break;
//}

#开启静态？
define('PHPTOHTML',false);//是否开启静态

define('mysql_charset','utf8');//字符类型
define('mysql_sqlmode','');
##开启数据库缓存？
define('mysql_opencache',true);//是否打开缓存

##Memcache 配置
define('memcache_server','127.0.0.1');
define('memcache_port','11211');
define('memcache_user','');
define('memcache_pass','');

#发送邮件的配置

define('Email_Server','203.83.250.203',true);
define('Email_Port',25,true);
define('Email_User','sia@views.sg',true);#QQ Num:623093320
define('Email_Pass','sia',true);
define('Email_From','sia@views.sg',true);//发送人Email
define('Email_Type','SMTP',true);
define('Email_random',false,true);

#FTP 设置
define('Ftp_server','127.0.0.1',true);
define('Ftp_port','21',true);
define('Ftp_user','123',true);
define('Ftp_pass','123',true);

#UC 设置
if($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '192.168.1.230'){
    define('UC_CONNECT', 'mysql');
    define('UC_DBHOST', 'localhost');
    define('UC_DBUSER', 'root');
    define('UC_DBPW', '123456');
    define('UC_DBNAME', 'yaowanbbs');
    define('UC_DBCHARSET', 'utf8');
    define('UC_DBTABLEPRE', '`yaowanbbs`.uc_');
    define('UC_DBCONNECT', '0');
    define('UC_KEY', 'yfC4obB6wb6dKdA15aC7AdPaR499M09132S28ev2H1I9U698a7Ufq1G7h1u2L9Y7');
    define('UC_API', 'http://uc.leonyu.com');
    define('UC_CHARSET', 'utf-8');
    define('UC_APPID', '1');
    define('UC_PPP', '20');
    //define('UC_IP', '127.0.0.1');
    define('BBS_URL','http://bbs.leonyu.com');
}else{
    define('UC_CONNECT', 'mysql');
    define('UC_DBHOST', '121.9.245.154');
    define('UC_DBUSER', 'keyrunvip');
    define('UC_DBPW', 'vipadzskeyrun2009');
    define('UC_DBNAME', 'yaowanbbs');
    define('UC_DBCHARSET', 'utf8');
    define('UC_DBTABLEPRE', '`yaowanbbs`.uc_');
    define('UC_DBCONNECT', '0');
    define('UC_KEY', 'm88aZ88dmc58d9D2F9p5s286Ec7dU3NaGfsdK4Y9I6L6V6Se97o2zdx10fz189Tb');
    define('UC_API', 'http://'.domain.'/sns/_abcuc');
    define('UC_CHARSET', 'utf-8');
    define('UC_IP', '121.9.245.147');
    define('UC_APPID', '2');
    define('UC_PPP', '20');
    //define('UC_IP', $_SERVER['SERVER_ADDR']);
    define('BBS_URL','http://'.domain.'/sns/bbs');
}

//COOKIE域 
define('cookie_domain','.theinno.org');
?>
<?php

/**
 * 
 * @author kernlaing.wong 2010.05.10
 * 		数据库工具类
 * 
 * 		mysqlSqlUtils::init($host,$user,$passwd,$dbanme)
 * 		先要调用些方法表示初始化
 *
 */
	class MySqlUtils{
		
		private $host = NULL;
		private $user = NULL;
		private $passwd = NULL;
		private $dbname = NULL;
		private $conn = NULL;
		function __construct($host,$dbname,$user,$passwd){
			$this->host = $host;
			$this->dbname = $dbname;
			$this->user = $user;
			$this->passwd = $passwd;

			$this->conn = mysql_connect($this->host ,$this->user ,$this->passwd);

			mysql_db_query($this->dbname,"SET names utf8;" , $this->conn);
			if(!$this->conn){
				echo "connection to ".$this->host." failed .... ";
			}
//			echo "connection to ".$this->host." success .... <br/>";
		}
		
		/**
		 * 
		 * @param $sql
		 * 		执行 sql 语句		by kernaling.wong@gmail.com 2010.05.10
		 */
		public function executeQuery($sql){
			
//			$conn = mysql_connect($this->host ,$this->user ,$this->passwd);
//			$isSelected = mysql_select_db($dbName,$conn);
			$result = mysql_db_query($this->dbname , $sql , $this->conn);
			
			$fields = array();
			for($i=0;$i<mysql_num_fields($result);$i++){
				$fieldName = mysql_field_name($result,$i);
				$fields[] = $fieldName;
			}
			$queryResult = array();
			while(TRUE){
				
				$rows = mysql_fetch_row($result);
				if(!$rows){
					break;
				}
				
				$rowsMap = array();				
				for($i=0;$i<count($fields);$i++){
					$fieldName = $fields[$i];
					$rowsMap[$fieldName]=trim($rows[$i]);
				}
				
				if(!empty($rowsMap)){					
					$queryResult[] = $rowsMap;
				}
			}
				mysql_free_result($result);
				return $queryResult;
		}
		
		/**
		 * 
		 * @param unknown_type $sql
		 * 		执行sql插入语句,是否成功
		 */
		public function executeInsert($sql){
			
//			$isSelected = mysql_select_db($dbName,$conn);
//			mysql_query("SET names utf8;");
			
			try{				
				$result = mysql_db_query($this->dbname , $sql , $this->conn);
				return mysql_insert_id();
			}catch (Exception $e){
				echo $e->getTrace();
			}
			
				return NULL;
			
		}
		
	/**
		 * 
		 * @param unknown_type $sql
		 * 		执行sql插入语句,返回受影响的数量
		 */
		public function executeDelete($sql){
//			$conn = mysql_connect($this->host ,$this->user ,$this->passwd);
//			$isSelected = mysql_select_db($dbName,$conn);
			$result = mysql_db_query($this->dbname , $sql , $this->conn);
			$affNum = mysql_affected_rows($this->conn);
			return $affNum;
		}
		
		
	/**
		 * 
		 * @param unknown_type $sql
		 * 		执行sql插入语句,返回受影响的数量
		 */
		public function executeUpdate($sql){
//			$conn = mysql_connect($this->host ,$this->user ,$this->passwd);
//			$isSelected = mysql_select_db($dbName,$conn);
			$result = mysql_db_query($this->dbname , $sql , $this->conn);
			$affNum = mysql_affected_rows($this->conn);
//			mysql_free_result($this->conn);
			return $affNum;
		}
		
		public function close(){
			mysql_close($this->conn);
		}
	}
	
		if(FALSE){
			$host = "192.168.1.111";
			$user = "kernaling.wong";
			$db = "mixue_site";
			$passwd = "ilovehua";
		//$sql = "SELECT EB_TypeName ,EB_Name  FROM mixue.mx_edu_books LIMIT 10";
			$mysql = new MySqlUtils($host,$db,$user,$passwd);
			
//			$sql = "INSERT INTO mixue_site.mxs_feedback(MF_Type,MF_User,MF_Content,MF_Link,MF_AddTime) VALUES('网站建议','sdfsdf','fdsfsfadfa','首发式',1276588484);";
			$sql = "SELECT * FROM mixue_site.mxs_news";
			$result = $mysql->executeQuery($sql);
			
			print_r($result);
			
			$mysql->close();
			
//		$param = array();
//		$param['hello'] = "www.it.com.cn";
//		$param['google'] = "你好吗";
//		$param['param'] = "你好吗,不太好吧";
//		$keywordParam = serialize($param);
//		$sql = "INSERT INTO mixue.mx_keywords_related(KR_keyWords,KR_SearchType,KR_ClickCount,KR_UpdateTime)". 
//		" VALUES('".$keywordParam."' , 1 , 3 , 123456789);";
		
//		$sql = "SELECT KR_keyWords FROM mixue.mx_keywords_related WHERE KR_ID = 1";
//		echo "sql:".$sql."<br/>";
//		$result = $mysql->executeQuery($sql);
//		
//		if(!empty($result)){
//			$ta = $result[0]['KR_keyWords'];
//			$ta = unserialize($ta);
//			print_r($ta);
//		}
		
//		print_r($result);
//		echo "TimeInUsed:".$re;		
		}

?>
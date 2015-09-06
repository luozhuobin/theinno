<?php
class Model_platads extends Init{
    
    public function __contruct(){
        parent::__construct(get_class($this));
    }
    public function init(){
    }
    public function getads(){
    	$scriptname = rawurldecode($_GET['key']);
        $jsfile = "./Template/2012/js/$scriptname.js";
        if(is_readable($jsfile) && filesize($jsfile) > 0){
            @include($jsfile);
            $date = date("Y-m-d");
            $imageurl = isset($imageurl) ? rawurldecode($imageurl) : '';
        }
    }
    
    public function gettgtourl(){
        $scriptname = rawurldecode($_GET['key']);
        $tourl = trim(rawurldecode($_GET['tourl']));
        $imageurl = trim(rawurldecode($_GET['i']));
        $date = date("Y-m-d");
        $sql = "INSERT IGNORE INTO platads_stat SET date = '$date', ads_mark = '$scriptname', imageurl = '$imageurl', click_num = 1 ON DUPLICATE KEY UPDATE click_num = click_num + 1";
        $this->db->query($sql);
        
        if($tourl){
            header("Location:$tourl");
        }
        exit;
    }
    /**
     * @desc 获取广告列表
     */
    public function getAdsList($mark){
    	$sql = "SELECT * FROM platformads WHERE mark = '{$mark}' AND status = 1";
    	$query = $this->db->query($sql);
    	$tmp = array();
    	while($row = $this->db->fetch_assoc($query)){
    		$tmp[] = $row;
    	}
    	return $tmp;
    }
}
<?php
class Model_Platads extends Control_Admin{
    
    private $imagehost = 'hrh.theinno.org';
    
    public function __construct(){
        parent::__construct(get_class($this));
        $this->tpl->assign('imagehost', $this->imagehost);
    }
    
    public function init(){
        $this->ls();
    }
    
    public function ls(){
        $sql = "SELECT * FROM platformAds p ORDER BY mark ASC";
        $query = $this->db->query($sql);
        while($value = $this->db->fetch_assoc($query)){
            $list[] = $value;
        }
        
        $this->tpl->assign('list', $list);
        $this->tpl->display("platads_ls");
    }
    
    public function edit(){
        if($_POST){
            $this->doedit();
            return;
        }
        $_Q_DATA = $_GET;
        $adsinfo = array();
        if($adsid = intval($_Q_DATA['id'])){
            $sql = "SELECT * FROM platformAds WHERE ads_id = $adsid LIMIT 1";
            $query = $this->db->query($sql);
            $adsinfo = $this->db->fetch_assoc($query);
            
        }
        $this->tpl->assign('data', $adsinfo);
        $this->tpl->display('platads_modify');    
    }
    
    public function doedit(){
            $mark = $_POST['mark'];
            $target_url = mysql_escape_string(trim($_POST['target_url']));
            $target_way = (int)$_POST['target_way'];
            $status = (int)$_POST['status'];
            if (empty($target_url)) $this->message('正确填写广告跳转目的地');
    		$dir = "./Template/UploadFiles/Images/UpImg/";
			if($_FILES['file']['size']>0){
				if(!empty($_FILES['file']['name'])){
					$ads_url = $this->checkUploadFile($_FILES['file'],$dir);
				}
			}
            $create_admin = $this->admin['username'];
            $ads_id = $_POST['id'];
            $sql = "UPDATE platformAds SET target_url='$target_url',target_way='$target_way',status='$status', updatetime='".time()."'";
            if(!empty($ads_url)){
            	$sql .= ",ads_url = '{$ads_url}'";
            }
            $sql .= "WHERE ads_id='$ads_id'";
            $this->db->query($sql);
            //生成JS 文件
            $this->generate_js_file($ads_id);
            $this->message('修改成功', 'Suc', '首页对联广告', 'platads&action=ls');
    }
    
    function upload_img()
    {
        $field = isset($_FILES['upfile1']) ? 'upfile1' : 'upfile2';
        $upObj = new Upload();
        $result = $upObj->startUpload($field, 'Upfile/gameImg/', 'default.jpg');    
        if ($result['errno'] !== 0) $result['error'] = $upObj->getUploadStatusTitle($result['errno']);
        exit(implode('|', $result));
    }
    
    public function del(){
    }
    
    public function op(){
        $op = $_GET['op'];
        $ads_id = intval($_GET['id']);
        if($op == 'switch'){
            $sql = "UPDATE platformAds SET status = if(status =1,0,1) WHERE ads_id = '$ads_id' LIMIT 1";
            $this->db->query($sql);
            $this->generate_js_file($ads_id);
            $this->ls();
            return;   
        }elseif($op == 'generate_js'){
            $this->generate_js_file($ads_id);
            $this->message('生成成功');
        }
        
    }
    
    private function generate_js_file($ads_id = 0){
        if($ads_id){
            $sql = "SELECT * FROM platformAds WHERE ads_id = '$ads_id' LIMIT 1";
            $query = $this->db->query($sql);
            $adsinfo = $this->db->fetch_assoc($query);
            
            $jsfiledir = './Template/2012/js/';
            $jsfile = $jsfiledir.$adsinfo['mark'].'.js';
            isdir($jsfiledir);
            
            if($adsinfo['status']==1){ 
                $tourl = "?m=platads&action=gettgtourl&key={$adsinfo['mark']}&tourl=".rawurlencode($adsinfo['target_url'])."&i=".rawurlencode($adsinfo['ads_url']);
                $this->tpl->assign('mark', $adsinfo['mark']);
                $this->tpl->assign('material_type', $adsinfo['ads_type']);
                $this->tpl->assign('width', $adsinfo['width']);
                $this->tpl->assign('height', $adsinfo['height']);
                $this->tpl->assign('imageurl', $adsinfo['ads_url']);
                $this->tpl->assign('tourl', $tourl);
                $this->tpl->assign('target', $adsinfo['target_way'] == 2 ? '_self':'_blank');
                $this->tpl->assign('islogin', $adsinfo['islogin']);
                $js_content = "<? \$imageurl = rawurlencode('{$adsinfo['ads_url']}'); ?".">\n";
                $js_content .= $this->tpl->get("platjs/platads_js_couplte");
                file_put_contents($jsfile, $js_content);
            }else{
                if(!@unlink($jsfile)){
                    $js_content = "";
                    if(file_exists($jsfile)){
                    	echo $jsfile."文件存在";
                    }else{
                    	echo $jsfile."文件不存在";
                    }
                    if(is_writable($jsfile)){
                    	echo $jsfile."可写";
                    }else{
                    	echo $jsfile."不可写";
                    }
                    $result = file_put_contents($jsfile,$js_content);
                    var_dump($result);
                }
            }
            return $adsinfo;
        }
    }
    
    public function stat(){
        $_Q_DATA = $_POST;
        $date1 = $_Q_DATA['date1'] ? $_Q_DATA['date1'] : date('Y-m-d');
        $date2 = $_Q_DATA['date2'] ? $_Q_DATA['date2'] : date('Y-m-d');
        
        $sql = "SELECT pa.title, ps.* FROM platads_stat ps LEFT JOIN platformAds pa ON (ps.ads_mark = pa.mark) WHERE date between '$date1' AND '$date2';";
        $query = $this->db->query($sql);
        while($value = $this->db->fetch_assoc($query)){
            $list[$value['date']][$value['ads_mark']] = $value;
            
            $subtotal[$value['date']]['pv_num'] += $value['pv_num'];
            $subtotal[$value['date']]['click_num'] += $value['click_num'];
            
            $total['pv_num'] += $value['pv_num'];
            $total['click_num'] += $value['click_num'];
        }
        
        $this->tpl->assign('date1', $date1);
        $this->tpl->assign('date2', $date2);
        
        $this->tpl->assign('list', $list);
        $this->tpl->assign('subtotal', $subtotal);
        $this->tpl->assign('total', $total);
        $this->tpl->display('platads_stat');    
    }
}
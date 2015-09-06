var js_WEBDOMAIN = 'http://hrh.theinno.org';
//var js_WEBDOMAIN ='http://hrh.theinno.cc/Default.php';
/**
 * 验证电子邮箱是否正确
 * @param id 获取Email的id
 * @param showID 显示提示信息的ID
 */
function emailVerify(id,showId){
	var email = jQuery("#"+id).val();
	if(email == ''){
		ShowMsg(showId,0,'请输入电子邮箱。');
		return false;
	}
	var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/;
    if(reg.test(email)){
    	ShowMsg(showId,1,'');
		return true;
    }else{
    	ShowMsg(showId,0,'无效的电子邮箱。');
		return false;
    }
}

/**
 * 提示语
 */
function ShowMsg(IdStr,ErrNum,ErrStr){
    var ImgStr=new Array("<img style=\"vertical-align: middle;\" src=\"./Template/2012/images/note_error.gif\">","<img style=\"vertical-align: middle;\" src=\"./Template/2012/images/okicon.gif\">","<img style=\"vertical-align: middle;\" src=\"./Template/2012/images/001.gif\">")
    var ClrArr=new Array("#9A1616","#339900","#413F3F");
    $("#"+IdStr).css({fontWeight:"bold",fontSize:"9pt",color:ClrArr[ErrNum],display:""});
    $("#"+IdStr).html(ImgStr[ErrNum]+ErrStr);
    return false;
}

/**
 * 密码验证
 * @param id 获取密码值的id
 * @param showID 显示提示信息的ID
 */
function passwordVerify(id,showID){
	var password = jQuery("#"+id).val();
	if(password.length ==0){
		ShowMsg(showID,0,'请输入密码。');
		return false;
	}else if(password.length<6||password.length>16){
		ShowMsg(showID,0,'密码长度为6至16个字符。');
		return false;
	}
	if(password.indexOf(" ") >= 0){
		ShowMsg(showID,0,'密码不能存在空格。');
		return false;
	}
	//登录不验证密码的复杂度
	if(id=='password'){
		var secure = checkPwdSecure(password);
	    if(secure ==1){
	    	ShowMsg(showID,1,"密码还可以更复杂。");
	    }else if(secure == 2){
	    	ShowMsg(showID,1,"密码复杂度还可以。");
	    }else if(secure == 3){
	    	ShowMsg(showID,1,"密码很完美！");
	    }
	}else{
		ShowMsg(showID,1,"");
	}
	return true;
}
function checkPwdSecure(pwd_str){
    var tmp_secure_level=0;
    
    if(pwd_str.match(/[a-zA-Z]/)){
        tmp_secure_level++;
    }
    if(pwd_str.match(/[0-9]/)){
        tmp_secure_level++;
    }
    if(pwd_str.match(/[~!@#$%^&*()_+|{}:"<>?`=-\\\[\];',./]/)){
        tmp_secure_level++;
    }
    
    return tmp_secure_level;
}
/**
 * 确认密码验证
 * @param id 获取密码值的id
 * @param confirmId 获取确认密码值的id
 * @param showId 显示提示信息的ID
 * @param confirmShowId 确认密码显示提示信息的ID
 */
function confirmPasswordVerify(id,showId,confirmId,confirmShowId){
	var confirmPassword = jQuery("#"+confirmId).val();
	var password = jQuery("#"+id).val();
	var isPasswordOK = passwordVerify(id,showId);
	if(isPasswordOK){
		if(password === confirmPassword){
			ShowMsg(confirmShowId,1,'');
			return true;
		}else{
			ShowMsg(confirmShowId,0,'密码不一致，请重新输入。');
			return false;
		}
	}else{
		ShowMsg(confirmShowId,0,'密码不一致，请重新输入。');
		return false;
	}
}
/**
 * 验证码验证
 *
 */
function codeVerify(id,showId){
	var code = jQuery("#"+id).val();
	if(code == ''){
		ShowMsg(showId,0,'请输入验证码。');
		return false;
	}
	if(code.length==4){
		 ShowMsg(showId,1,'');
 		 return true;
	}else{
		ShowMsg(showId,0,'验证码有误。');
		return false;
	}
}
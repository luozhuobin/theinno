/**
 * 验证电子邮箱是否已经注册
 * @param email 电子邮箱
 * @param identity 身份类型：personal(个人),company(企业)
 */
function emailIsExist(identity){
	var email = jQuery("#email").val();
	var name = jQuery("#name").val();
	var phone = jQuery("#phone").val();
	var isEmail = emailVerify('email','resultInfo');
	if(isEmail){
		jQuery("#submit").removeAttr('onclick');
		jQuery("#submit").attr('disable',true);
		$.ajax({
			   type: "POST",
			   url: "?m="+identity+"&action=emailIsExist",
			   dataType:"json",
			   data: "email="+email+"&name="+name+"&phone="+phone,
			   dataType:"json",
			   success: function(data){
			    	switch(data['result']){
			    		case '-1':
			    			ShowMsg('resultInfo',0,data['msg']);
				    		break;
			    		case '1':
							jQuery("#resultInfo").html('60秒后可重新发送邮件。');
							var i = 60;
							var Interval = setInterval(function(){
								i--;
								if(i == 0){
									clearInterval(Interval);
									jQuery("#resultInfo").html('');
									jQuery("#submit").attr('onclick','emailIsExist(\''+identity+'\');');
									jQuery("#submit").attr('disable',false);
								}else{
									jQuery("#resultInfo").html(i+'秒后可重新发送邮件。');
								}
							},1000);
			    			//ShowMsg('resultInfo',1,data['msg']);
				    		break;
			    	}
			   }
		});
	}
}

/**
 * @desc 登录提示
 */
function loginShow(identity,showId){
	
}
function loginBoxLogin(email,password,identity){
	var tourl = window.location.href;
	var login_email = jQuery("#"+email).val();
	var login_password = jQuery("#"+password).val();
	if(login_email=='请输入登录帐号') login_email='';
	if(login_password=='请输入密码') login_password='';
	var m = $("input[name='login_box_identity']:checked").val();
	if(m == undefined){
		ShowMsg('login_box_identity_info',0,"请选择帐号类型");
		return false;
	}
	ShowMsg('login_box_identity_info',1,"");
	$.ajax({
		   type: "POST",
		   url: "?m="+m+"&action=doLogin",
		   data: "email="+login_email+"&password="+login_password,
		   dataType:"json",
		   success: function(data){
		     	switch(data['code']){
		     		case '1':
		     			window.location.href=tourl;
		     			break;
		     		default:
		     			ShowMsg('loginInfo',0,data['msg']);
		     			break;
		     	}
		   }
		});
}
/**
 * 用户登录
 */
function doLogin(email,password,identity){
	var tourl = window.location.href;
	var login_email = jQuery("#"+email).val();
	var login_password = jQuery("#"+password).val();
	if(login_email=='请输入登录帐号') login_email='';
	if(login_password=='请输入密码') login_password='';
	$.ajax({
		   type: "POST",
		   url: "?m="+identity+"&action=doLogin",
		   data: "email="+login_email+"&password="+login_password,
		   dataType:"json",
		   success: function(data){
		     	switch(data['code']){
		     		case '-1':
		     		case '-2':
		     			var pck = '';
		     			pck = $("#"+identity+"Ck").html();
		     			$("#"+identity+"Ck").html('<p style="color:red;">'+data['msg']+'</p>');
		     			setTimeout(function(){$("#"+identity+"Ck").html(pck)},3000);
		     			break;
		     		case '1':
		     			window.location.href="?m="+identity;
		     			break;
		     	}
		   }
		});
}

/**
 * 注册流程第二步
 * @param identity personal,company
 * @return
 */
function step2Submit(identity,formid){
	var formdata = $("#"+formid).serialize();
	$.ajax({
		   type: "POST",
		   url: "?m="+identity+"&action=step2finish",
		   data: formdata,
		   dataType:"json",
		   success: function(data){
		     	switch(data['result']){
		     		case '-1':
		     			ShowMsg('passwordSubmit',0,data['msg']);
			     		break;
		     		case '-2':
		     			ShowMsg('passwordSubmit',0,data['msg']);
			     		break;
		     		case '-3':
		     			ShowMsg('passwordSubmit',0,data['msg']);
			     		break;
		     		case '1':
			     		window.location.href="http://hrh.theinno.org/Default.php?m="+identity;
			     		break;
		     	}
		   }
		});
}

<!-- $Id: comment_list.htm 13256 2007-10-29 08:18:56Z wj $ --><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta name="robots" content="noindex, nofollow">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="./Template/Admin/css/General.css" rel="stylesheet" type="text/css" />
<link href="./Template/Admin/css/Main.css" rel="stylesheet" type="text/css" /></head><body>
<h1>
	<span class="action-span"><a href="#;" onclick='javascript:history.back();'>返回上一页</a></span>
	<span class="action-span1">Manage - 消息中心</span><div style="clear:both"></div></h1>
<div class="list-div">
  <div style="background:#FFF; width:100%;padding: 20px 50px; margin: 2px;" width=100%>
    <table align="center" id="table1" width=100%>
      <tr>
        <td width="50" valign="top">
                    <img src="Template/Admin/images/information.gif" width="32" height="32" border="0" alt="information" />
                  </td>
        <td style="font-size: 14px; font-weight: bold"><?PHP echo $message;?></td>
      </tr>
      <tr>
        <td></td>
        <td id="redirectionMsg"><? if($expire){?>操作提示： <span id="spanSeconds"><font color=red style='font-weight:bold'><?=$expire?></font></span>秒后返回到<?PHP echo $urlarr[0]['title'];}?></td>
      </tr>
      <tr>
        <td></td>
        <td>
          <ul style="margin:0; padding:0 10px" class="msg-link">
						<?PHP foreach($urlarr as $arr){;?>
						<li><a href="?c=admin&m=<?PHP echo $arr['url'];?>"><?PHP echo $arr['title'];?></a></li>
						<?PHP };?>
                        <li><a href="#;" onclick='javascript:history.back();'>返回</a></li>
                      </ul>

        </td>
      </tr>
    </table>
  </div>
</div>
<? if($expire){?>
<script>
var url = '?c=admin&m=<?PHP echo $urlarr[0]["url"];?>';
setTimeout('go()',1000);
var time=<?=$expire?>;
function go()
{
	var obj = document.getElementById('spanSeconds');

	if(obj.outerHTML<=0 || time<=0){
		window.location=url;
	}else{
		time -= 1;
		obj.innerText= obj.outerText-1;
		setTimeout('go()',1000);
	}
}
</script>
<? }?>
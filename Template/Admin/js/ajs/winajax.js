// winajax.js
var userAgent = navigator.userAgent.toLowerCase();
var is_opera = userAgent.indexOf('opera') != -1 && opera.version();
var is_moz = (navigator.product == 'Gecko') && userAgent.substr(userAgent.indexOf('firefox') + 8, 3);
var is_ie = (userAgent.indexOf('msie') != -1 && !is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);

function doShowWin(html, webads){
    $("#ajaxwait").empty();
    
    var s_top, position;
    if(is_ie){ s_top = document.body.scrollTop; position = 'absolute';}
    else if(is_moz){ s_top = document.documentElement.scrollTop; position = 'fixed';}
    var c_height = document.documentElement.clientHeight;
    var c_width = document.body.clientWidth;
    var b_height = 50;
    var width = 600, height = c_height - b_height*2;
    var top = b_height + s_top ;
    var left = Math.floor(c_width/2 - width/2);
    var div_id = 'ajaxshow_'+webads;  
    //var html = 'this is';
    var position = 'absolute' ;
    //if(typeof $.brower != 'undefined') position = 'fixed'; 
    $("#ajaxwait").append('<div id="'+div_id+'">'+html+'</div>').show();
    $('#'+div_id).css({
        width:width,
        height:height,
        top:top,
        left:left,
        'position':position,
        'z-index':'999',
        'background':'white',
        'border':'1px solid #000'
    });
    
    $('select').hide();
    //响应关闭
    $("#closeWin_"+webads).live('click', function(){
        $('select').show();
        $("#closeWin_"+webads).die('click');
        $("#ajaxshow_"+webads).remove();
        $(this).die('click');
        return false;
    });
    
    //响应提交
    $("#submitform_"+webads).live('click', function(){
        var obj,param='';
        var district = $("input[name=district]").val();
        var game = $("input[name=game]").val();
        var webads = $("input[name=webads]").val();
        param = 'district='+district+'&game='+game+'&webads='+webads;
        
        var chk_length = $("input[name^=material]:checked").length;
        for(var i=0; i<chk_length; i++){
            obj = $($("input[name^=material]:checked").get(i));
            param += '&'+obj.attr('name')+"="+obj.attr('value');
        }
        $.get('./?c=admin&m=adsv2&action=setmaterial', param, function(str){
            var result = str.split('|');
            if(result[0] == 'success'){
                $("#submitform_"+webads).die('click');
                $("#ajaxshow_"+webads).remove();
                $('select').show();
                $.facebox("操作成功");
            }else{
                $.facebox("操作失败!"+result[1]);
            }
            
            return false;    
        });
        return false;
    });
    
    //响应全选
    $("#chkall_"+webads).live('click', function(){
        var chkval = $(this).attr('checked');
        $("input[id^=chk_]").each(function(){
            $(this).attr('checked', chkval);
        });
    });
    
    //响应勾选
    $("input[id^=chk_"+webads+"_]").live('click', function(){
        var tmpidprif = "chk_"+webads+"_";
        var size = $("input[id^="+tmpidprif+"]").length;
        var chksize = $("input[id^="+tmpidprif+"]:checked").length;
        if(size == chksize) $("#chkall_"+webads).attr('checked', true);
        else $("#chkall_"+webads).attr('checked', false);
    });
    
    //
}

function closeWin(){
    
}

function showWin(webads){
    var game = $("#game_"+webads).val();
    var district = $("#dist_"+webads).val(); 
    $.get("./?c=admin&m=adsv2&action=showlist&ajax=1", {'webads':webads,'game':game, 'district':district} , function(str){
        doShowWin(str, webads);
        //$.facebox(str);
    });
}

function submitData(webads){
}
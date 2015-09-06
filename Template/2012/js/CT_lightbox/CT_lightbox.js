/* require jQuery 1.2.6 Or 1.3.2 */

function CT_pop(id){
	CT_close();
	$('.CT_pop_window .CT_content').hide();
	$('.CT_pop_window #'+id).show();
	
	$(".CT_pop_window").css({display:"inline"});
	$(".CT_mask").css("height", $(document).height());
	$(".CT_mask").css({display:"inline"});
	$(".CT_mask").click(function(){CT_close(); });
	$(".CT_pop_window .CT_close").click(function(){CT_close(); });

	CT_pop_resize() ;
	window.onresize = function(event) { CT_pop_resize() ; };
}

function CT_pop_resize() 
{
	var st=document.documentElement.scrollTop;//滚动条距顶部的距离
	var sl=document.documentElement.scrollLeft;//滚动条距左边的距离
	var ch=document.documentElement.clientHeight;//屏幕的高度
	var cw=document.documentElement.clientWidth;//屏幕的宽度
	var oh=document.body.offsetHeight;
	var ow=document.body.offsetWidth;
	
	var objH=$(".CT_pop_window").height();//浮动对象的高度
	var objW=$(".CT_pop_window").width();//浮动对象的宽度
	
	$(".CT_pop_window").css({top:  (ch-objH) / 2, left: (ow-objW) / 2});
}

function CT_close()
{
	$(".CT_pop_window").css({display:"none"});
	$(".CT_mask").css({display:"none"});
	window.onresize = null;
}
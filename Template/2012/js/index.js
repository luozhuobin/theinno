$(function(){
		   var st = 180;
		   //
		   $('#uboxstyle').mouseenter(function () {
										 
					$('.erlist').stop(false, true).slideDown(st);
	  }).mouseleave(function () {
		 
        $('.erlist').stop(false, true).slideUp(st);
				});
		   
		    $('.erlist dl dd a').click(function () {
					 $('#c_input').val($(this).html());
					 $('.erlist').stop(false, true).slideUp(st);
					 
				});
		   //
		    $('a').bind("focus", function() {
            $(this).blur();
       		 })
		$('.ck').click(function(){
									var lan=$(this).attr("lang");
									if(lan==0){
											$(this).addClass('ckon');
											$(this).attr('lang',1);
											$('#ck').val(1);
										}
									if(lan==1){
											$(this).removeClass('ckon');
											$(this).attr('lang',0);
											$('#ck').val(0);
										}
								});
		 
				 $("#tabtop li").click(function(){
					$(".logindiv").hide();  			 
				  var index = $("#tabtop li").index(this);
				  $(this).addClass("tabon").siblings().removeClass("tabon");
				    
				   $(".logindiv").eq(index).show();
				 });
				 //
				 $(".comtab li").click(function(){
					$(".comlist").hide();  			 
				  var index = $(".comtab li").index(this);
				  $(this).addClass("tabon").siblings().removeClass("tabon");
				    
				   $(".comlist").eq(index).show();
				 });
//menu fn
	
    $('.menu li').mouseenter(function () {
										  $('.menu li a.biga').removeClass('bigaon');
										  $(this).children('a.biga').addClass('bigaon');
        $(this).find('dl').stop(false, true).slideDown(st);
		 
    }).mouseleave(function () {
		$('.menu li a.biga').removeClass('bigaon');
        $(this).find('dl').stop(false, true).slideUp(st);
		 
    });
	/*$('.view').click(function() {
            showDiv1($(".showbox"));

        });*/
        $("#mp_close").click(function() {
            $(".showbox").hide();
            $(".mask").hide();
        });
	 
		   });
 
    function AddFavorite(sURL, sTitle) {
        try {
            window.external.addFavorite(sURL, sTitle);
        }
        catch (e) {
            try {
                window.sidebar.addPanel(sTitle, sURL, "");
            }
            catch (e) {
                alert("加入收藏失败，请使用Ctrl+D进行添加");
            }
        }
    }
    function SetHome(obj, vrl) {
        try {
            obj.style.behavior = 'url(#default#homepage)';
            obj.setHomePage(vrl);
        }
        catch (e) {
            if (window.netscape) {
                try {
                    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
                }
                catch (e) {
                    alert("此操作被浏览器拒绝！\n请在浏览器地址栏输入“about:config”并回车\n然后将 [signed.applets.codebase_principal_support]的值设置为'true',双击即可。");
                }
                var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
                prefs.setCharPref('browser.startup.homepage', vrl);
            }
        }
    }
function showmask1() {
        var bh = $(document).height();
        var bw = $(document).width();
        $(".mask").css({
            height: bh,
            width: bw
        });
        $('.mask').show();
    }

    function showDiv1(obj) {

        center1(obj);
        $(obj).show();
        showmask1();

        $(window).scroll(function() {
            center1(obj);
        });

        $(window).resize(function() {
            center1(obj);
        });
    }

    function center1(obj) {
        var windowWidth = document.documentElement.clientWidth;
        var windowHeight = document.documentElement.clientHeight;
        var popupHeight = $(obj).height();
        var popupWidth = $(obj).width();

        $(obj).css({
            "position": "absolute",
            "top": (windowHeight - popupHeight) / 2 + $(document).scrollTop(),
            "left": (windowWidth - popupWidth) / 2
        });
    } 
/* $Id : utils.js 5052 2007-02-03 10:30:13Z weberliu $ */

var Browser = new Object();

Browser.isMozilla = (typeof document.implementation != 'undefined') && (typeof document.implementation.createDocument != 'undefined') && (typeof HTMLDocument != 'undefined');
Browser.isIE = window.ActiveXObject ? true : false;
Browser.isFirefox = (navigator.userAgent.toLowerCase().indexOf("firefox") != - 1);
Browser.isSafari = (navigator.userAgent.toLowerCase().indexOf("safari") != - 1);
Browser.isOpera = (navigator.userAgent.toLowerCase().indexOf("opera") != - 1);

var Utils = new Object();

Utils.htmlEncode = function(text)
{
  return text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

Utils.trim = function( text )
{
  if (typeof(text) == "string")
  {
    return text.replace(/^\s*|\s*$/g, "");
  }
  else
  {
    return text;
  }
}

Utils.isEmpty = function( val )
{
  switch (typeof(val))
  {
    case 'string':
      return Utils.trim(val).length == 0 ? true : false;
      break;
    case 'number':
      return val == 0;
      break;
    case 'object':
      return val == null;
      break;
    case 'array':
      return val.length == 0;
      break;
    default:
      return true;
  }
}

Utils.isNumber = function(val)
{
  var reg = /^[\d|\.|,]+$/;
  return reg.test(val);
}

Utils.isInt = function(val)
{
  if (val == "")
  {
    return false;
  }
  var reg = /\D+/;
  return !reg.test(val);
}

Utils.isEmail = function( email )
{
  var reg1 = /([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)/;

  return reg1.test( email );
}

Utils.isTel = function ( tel )
{
  var reg = /^[\d|\-|\s|\_]+$/; //只允许使用数字-空格等

  return reg.test( tel );
}

Utils.fixEvent = function(e)
{
  var evt = (typeof e == "undefined") ? window.event : e;
  return evt;
}

Utils.srcElement = function(e)
{
  if (typeof e == "undefined") e = window.event;
  var src = document.all ? e.srcElement : e.target;

  return src;
}

Utils.isTime = function(val)
{
  var reg = /^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/;

  return reg.test(val);
}

function rowindex(tr)
{
  if (Browser.isIE)
  {
    return tr.rowIndex;
  }
  else
  {
    table = tr.parentNode.parentNode;
    for (i = 0; i < table.rows.length; i ++ )
    {
      if (table.rows[i] == tr)
      {
        return i;
      }
    }
  }
}

document.getCookie = function(sName)
{
  // cookies are separated by semicolons
  var aCookie = document.cookie.split("; ");
  for (var i=0; i < aCookie.length; i++)
  {
    // a name/value pair (a crumb) is separated by an equal sign
    var aCrumb = aCookie[i].split("=");
    if (sName == aCrumb[0])
      return decodeURIComponent(aCrumb[1]);
  }

  // a cookie with the requested name does not exist
  return null;
}

document.setCookie = function(sName, sValue, sExpires)
{
  var sCookie = sName + "=" + encodeURIComponent(sValue);
  if (sExpires != null)
  {
    sCookie += "; expires=" + sExpires;
  }

  document.cookie = sCookie;
}

document.removeCookie = function(sName,sValue)
{
  document.cookie = sName + "=; expires=Fri, 31 Dec 1999 23:59:59 GMT;";
}

function getPosition(o)
{
    var t = o.offsetTop;
    var l = o.offsetLeft;
    while(o = o.offsetParent)
    {
        t += o.offsetTop;
        l += o.offsetLeft;
    }
    var pos = {top:t,left:l};
    return pos;
}

function cleanWhitespace(element)
{
  var element = element;
  for (var i = 0; i < element.childNodes.length; i++) {
   var node = element.childNodes[i];
   if (node.nodeType == 3 && !/\S/.test(node.nodeValue))
     element.removeChild(node);
   }
}
function changelist(arr)
{
	for(key in arr){
		if(document.getElementById(arr[key])){
			var obj = document.getElementById(arr[key]);
			if(obj){
				changetoobj(obj,arr[key]);
			}
		}
		try{
			if(document.getElementsByName(arr[key]).length>0){
				for(var j=0;j<document.getElementsByName(arr[key]).length;j++){
					var listobj = 	document.getElementsByName(arr[key])[j];
					changetoobj(listobj,arr[key]);
				}
			}
		}catch(e){}
	}
}
function changetoobj(obj,listname)
{
	var on_click = '#ffcc66';
	var over_mouse = '#fff';
	obj.onmouseover = function(e){
		var obj = Utils.srcElement(e);
		if(obj){
			if (obj.parentNode.tagName.toLowerCase() == "tr") row = obj.parentNode;
			else if (obj.parentNode.parentNode.tagName.toLowerCase() == "tr") row = obj.parentNode.parentNode;
			else return;
			for(var i=0;i<row.cells.length;i++){
				 if (row.cells[i].tagName != "TH"){
					 row.cells[i].style.backgroundColor = row.cells[i].style.backgroundColor==on_click?on_click:'#F4FAFB';//'#F4FAFB';
				}
			}
		}
	}
	obj.onmouseout = function(e){
	var obj = Utils.srcElement(e);
		if(obj){
			if (obj.parentNode.tagName.toLowerCase() == "tr") row = obj.parentNode;
			else if (obj.parentNode.parentNode.tagName.toLowerCase() == "tr") row = obj.parentNode.parentNode;
			else return;
			for(var i=0;i<row.cells.length;i++){
				if (row.cells[i].tagName != "TH"){
					row.cells[i].style.backgroundColor = row.cells[i].style.backgroundColor==on_click?on_click:over_mouse;
				}
			}
		}
	}
	obj.onmousedown = function(e){
	var obj = Utils.srcElement(e);
		if(obj){
			if (obj.parentNode.tagName.toLowerCase() == "tr") row = obj.parentNode;
			else if (obj.parentNode.parentNode.tagName.toLowerCase() == "tr") row = obj.parentNode.parentNode;
			else return;
			for(var i=0;i<row.cells.length;i++){
				if (row.cells[i].tagName != "TH"){
					row.cells[i].style.backgroundColor = row.cells[i].style.backgroundColor==on_click?over_mouse:on_click;
				}
			}
		}
	}
}
window.onload=function(){
	//页面加在完毕
	try{
		if(typeof(listarr)=='undefined'){
			var listarr = ['listdiv'];
		}
		changelist(listarr);
	}catch(e){alert(e.message);}
}
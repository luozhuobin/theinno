/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$RCSfile: iframe.js,v $
	$Revision: 1.4.2.2 $
	$Date: 2007/03/21 15:53:02 $
*/

function refreshmain(e) {
	e = e ? e : window.event;
	actualCode = e.keyCode ? e.keyCode : e.charCode;
	if(actualCode == 116) {
		parent.main.location.reload();
		if(is_ie) {
			e.keyCode = 0;
			e.returnValue = false;
		} else {
			e.cancelBubble = true;
			//e.calcelable = true;
			e.preventDefault();
		}
	}
}

function _attachEvent(obj, evt, func) {
	if(obj.addEventListener) {
		obj.addEventListener(evt, func, true);
	} else if(obj.attachEvent) {
		obj.attachEvent("on" + evt, func);
	} else {
		eval("var old" + func + "=" + obj + ".on" + evt + ";");
		eval(obj + ".on" + evt + "=" + func + ";");
	}
}

_attachEvent(document.documentElement, "keydown", refreshmain);
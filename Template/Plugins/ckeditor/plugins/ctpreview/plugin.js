/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @file ctpreview plugin.
 */

(function()
{
	var previewCmd =
	{
		modes : { wysiwyg:1, source:1 },
		canUndo : false,
		exec : function( editor )
		{
			var iWidth	= 640,	// 800 * 0.8,
			iHeight	= 420,	// 600 * 0.7,
			iLeft	= 80;	// (800 - 0.8 * 800) /2 = 800 * 0.1.
			
			try
			{
				var screen = window.screen;
				iWidth = Math.round( screen.width * 0.8 );
				iHeight = Math.round( screen.height * 0.7 );
				iLeft = Math.round( screen.width * 0.1 );
			}
			catch ( e ){}
			
			var prv_data = document.getElementById('prv_data');
			var preview_form = document.getElementById('preview_form');
			prv_data.value = editor.getData();
			
			var sOpenUrl = '/?c=admin&m=list&action=preview';
			var oWindow = window.open(sOpenUrl, 'CKeditor_Win', 'toolbar=no,location=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=' +
				iWidth + ',height=' + iHeight + ',left=' + iLeft );
			
			if(preview_form.onsubmit()!=false) preview_form.submit();
		}
	};

	var pluginName = 'ctpreview';

	// Register a plugin named "ctpreview".
	CKEDITOR.plugins.add( pluginName,
	{
		init : function( editor )
		{
			editor.addCommand( pluginName, previewCmd );
			editor.ui.addButton( 'ctpreview',
				{
					label : editor.lang.preview,
					icon:	this.path + 'images/anchor.png',
					command : pluginName
				});
		}
	});
})();

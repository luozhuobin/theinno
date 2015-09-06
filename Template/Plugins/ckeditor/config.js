/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.width = 860;
	config.height = 400;
	config.skin = 'office2003';
	config.extraPlugins = 'ctpreview';
	config.toolbar = 'Full' ; 
	config.toolbar_Full = 
	[
		[ 'Source' , '-' , 'Save' , 'NewPage' , 'ctpreview' , '-' , 'Templates' , 'Maximize'], 			//,  'ShowBlocks'
		[ 'Cut' , 'Copy' , 'Paste' , 'PasteText' , 'PasteFromWord' , '-' , 'SpellChecker' , 'Scayt' ],  //, 'Print' 
		[ 'TextColor' , 'BGColor' ],
		[ 'Bold' , 'Italic' , 'Underline' , 'Strike' , '-' , 'Subscript' , 'Superscript' ], 
		[ 'Undo' , 'Redo' , '-' , 'Find' , 'Replace' , '-' , 'SelectAll' , 'RemoveFormat' ],
		'/',
		[ 'Styles' , 'Font' , 'FontSize' ],//'Format' , 
		[ 'NumberedList' , 'BulletedList' , '-' , 'Outdent' , 'Indent'  ], //, 'Blockquote'
		[ 'JustifyLeft' , 'JustifyCenter' , 'JustifyRight', 'JustifyBlock'  ], //, 
		[ 'Link' , 'Unlink' , 'Anchor' ], 
		[ 'Image' , 'Flash' , 'Table' ,  'Smiley' , 'SpecialChar'] //,  
	];
};
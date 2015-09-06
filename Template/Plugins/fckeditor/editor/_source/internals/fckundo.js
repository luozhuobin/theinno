/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2009 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 */

var FCKUndo = new Object() ;

FCKUndo.SavedData = new Array() ;
FCKUndo.CurrentIndex = -1 ;
FCKUndo.TypesCount = 0 ;
FCKUndo.Changed = false ;	// Is the document changed in respect to its initial image?
FCKUndo.MaxTypes = 25 ;
FCKUndo.Typing = false ;
FCKUndo.SaveLocked = false ;

FCKUndo._GetBookmark = function()
{
	FCKSelection.Restore() ;

	var range = new FCKDomRange( FCK.EditorWindow ) ;
	try
	{
		// There are some tricky cases where this might fail (e.g. having a lone empty table in IE)
		range.MoveToSelection() ;
	}
	catch ( e )
	{
		return null ;
	}
	if ( FCKBrowserInfo.IsIE )
	{
		var bookmark = range.CreateBookmark() ;
		var dirtyHtml = FCK.EditorDocument.body.innerHTML ;
		range.MoveToBookmark( bookmark ) ;
		return [ bookmark, dirtyHtml ] ;
	}
	return range.CreateBookmark2() ;
}

FCKUndo._SelectBookmark = function( bookmark )
{
	if ( ! bookmark )
		return ;

	var range = new FCKDomRange( FCK.EditorWindow ) ;
	if ( bookmark instanceof Object )
	{
		if ( FCKBrowserInfo.IsIE )
			range.MoveToBookmark( bookmark[0] ) ;
		else
			range.MoveToBookmark2( bookmark ) ;
		try
		{
			// this does not always succeed, there are still some tricky cases where it fails
			// e.g. add a special character at end of document, undo, redo -> error
			range.Select() ;
		}
		catch ( e )
		{
			// if select restore fails, put the caret at the end of the document
			range.MoveToPosition( FCK.EditorDocument.body, 4 ) ;
			range.Select() ;
		}
	}
}

FCKUndo._CompareCursors = function( cursor1, cursor2 )
{
	for ( var i = 0 ; i < Math.min( cursor1.length, cursor2.length ) ; i++ )
	{
		if ( cursor1[i] < cursor2[i] )
			return -1;
		else if (cursor1[i] > cursor2[i] )
			return 1;
	}
	if ( cursor1.length < cursor2.length )
		return -1;
	else if (cursor1.length > cursor2.length )
		return 1;
	return 0;
}

FCKUndo._CheckIsBookmarksEqual = function( bookmark1, bookmark2 )
{
	if ( ! ( bookmark1 && bookmark2 ) )
		return false ;
	if ( FCKBrowserInfo.IsIE )
	{
		var startOffset1 = bookmark1[1].search( bookmark1[0].StartId ) ;
		var startOffset2 = bookmark2[1].search( bookmark2[0].StartId ) ;
		var endOffset1 = bookmark1[1].search( bookmark1[0].EndId ) 
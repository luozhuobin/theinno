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
 *
 * Utility functions. (IE version).
 */

FCKTools.CancelEvent = function( e )
{
	return false ;
}

// Appends one or more CSS files to a document.
FCKTools._AppendStyleSheet = function( documentElement, cssFileUrl )
{
	return documentElement.createStyleSheet( cssFileUrl ).owningElement ;
}

// Appends a CSS style string to a document.
FCKTools.AppendStyleString = function( documentElement, cssStyles )
{
	if ( !cssStyles )
		return null ;

	var s = documentElement.createStyleSheet( "" ) ;
	s.cssText = cssStyles ;
	return s ;
}

// Removes all attributes and values from the element.
FCKTools.ClearElementAttributes = function( element )
{
	element.clearAttributes() ;
}

FCKTools.GetAllChildrenIds = function( parentElement )
{
	var aIds = new Array() ;
	for ( var i = 0 ; i < parentElement.all.length ; i++ )
	{
		var sId = parentElement.all[i].id ;
		if ( sId && sId.length > 0 )
			aIds[ aIds.length ] = sId ;
	}
	return aIds ;
}

FCKTools.RemoveOuterTags = function( e )
{
	e.insertAdjacentHTML( 'beforeBegin', e.innerHTML ) ;
	e.parentNode.removeChild( e ) ;
}

FCKTools.CreateXmlObject = function( object )
{
	var aObjs ;

	switch ( object )
	{
		case 'XmlHttp' :
			// Try the native XMLHttpRequest introduced with IE7.
			if ( document.location.protocol != 'file:' )
				try { return new XMLHttpRequest() ; } catch (e) {}

			aObjs = [ 'MSXML2.XmlHttp', 'Microsoft.XmlHttp' ] ;
			break ;

		case 'DOMDocument' :
			aObjs = [ 'MSXML2.DOMDocument', 'Microsoft.XmlDom' ] ;
			break ;
	}

	for ( var i = 0 ; i < 2 ; i++ )
	{
		try { return new ActiveXObject( aObjs[i] ) ; }
		catch (e)
		{}
	}

	if ( FCKLang.NoActiveX )
	{
		alert( FCKLang.NoActiveX ) ;
		FCKLang.NoActiveX = null ;
	}
	return null ;
}

FCKTools.DisableSelection = function( element )
{
	element.unselectable = 'on' ;

	var e, i = 0 ;
	// The extra () is to avoid a warning with strict error checking. This is ok.
	while ( (e = element.all[ i++ ]) )
	{
		switch ( e.tagName )
		{
			case 'IFRAME' :
			case 'TEXTAREA' :
			case 'INPUT' :
			case 'SELECT' :
				/* Ignore the above tags */
				break ;
			default :
				e.unselectable = 'on' ;
		}
	}
}

FCKTools.GetScro
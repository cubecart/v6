/*
 *  "showprotected" CKEditor plugin
 *  
 *  Created by Matthew Lieder (https://github.com/IGx89)
 *  
 *  Licensed under the MIT, GPL, LGPL and MPL licenses
 *  
 *  Icon courtesy of famfamfam: http://www.famfamfam.com/lab/icons/mini/
 */

// TODO: configuration settings
// TODO: show the actual text inline, not just an icon?
// TODO: improve copy/paste behavior (tooltip is wrong after paste)

CKEDITOR.plugins.add( 'showprotected', {
	requires: 'dialog,fakeobjects',
	onLoad: function() {
		// Add the CSS styles for protected source placeholders.
		var pluginPath = this.path;
		var iconPath = CKEDITOR.getUrl( pluginPath + 'images/smarty_unknown.svg' );
		var typedIcons = {
			loop_open:  CKEDITOR.getUrl( pluginPath + 'images/smarty_loop_open.svg' ),
			loop_close: CKEDITOR.getUrl( pluginPath + 'images/smarty_loop_close.svg' ),
			if_open:    CKEDITOR.getUrl( pluginPath + 'images/smarty_if_open.svg' ),
			if_else:    CKEDITOR.getUrl( pluginPath + 'images/smarty_if_else.svg' ),
			if_close:   CKEDITOR.getUrl( pluginPath + 'images/smarty_if_close.svg' ),
			'var':      CKEDITOR.getUrl( pluginPath + 'images/smarty_var.svg' )
		};
		var baseStyle = 'background:url(' + iconPath + ') no-repeat %1 center;background-size:18px 18px;border:0;';

		var template = '.%2 img.cke_protected' +
			'{' +
				baseStyle +
				'width:18px;' +
				'height:18px;' +
				'min-height:18px;' +
				// Opera works better with "middle" (even if not perfect)
				'vertical-align:' + ( CKEDITOR.env.opera ? 'middle' : 'text-bottom' ) + ';' +
			'}';
		for ( var key in typedIcons ) {
			template +=
				'.%2 img.cke_protected.cke_smarty_' + key +
				'{background-image:url(' + typedIcons[key] + ');}';
		}

		// Styles with contents direction awareness.
		function cssWithDir( dir ) {
			return template.replace( /%1/g, dir == 'rtl' ? 'right' : 'left' ).replace( /%2/g, 'cke_contents_' + dir );
		}

		CKEDITOR.addCss( cssWithDir( 'ltr' ) + cssWithDir( 'rtl' ) );
	},

	init: function( editor ) {
		CKEDITOR.dialog.add( 'showProtectedDialog', this.path + 'dialogs/protected.js' );
		
		editor.on( 'doubleclick', function( evt ) {
			var element = evt.data.element;

			if ( element.is( 'img' ) && element.hasClass( 'cke_protected' ) ) {
				evt.data.dialog = 'showProtectedDialog';
			}
		} );
	},

	afterInit: function( editor ) {
		// Register a filter to displaying placeholders after mode change.

		var dataProcessor = editor.dataProcessor,
			dataFilter = dataProcessor && dataProcessor.dataFilter;

		if ( dataFilter ) {
			dataFilter.addRules( {
				comment: function( commentText, commentElement ) {
					if(commentText.indexOf(CKEDITOR.plugins.showprotected.protectedSourceMarker) == 0) {
						// Foster-parenting trap: if this protected comment is a DIRECT child of
						// table/tbody/thead/tfoot/tr, converting it to a fake <img> would yank
						// it out of the table, corrupting {foreach}/{if} blocks that wrap <tr>
						// rows. Inside <td>/<th> cells the <img> is fine, so only skip when the
						// immediate parent triggers foster-parenting.
						var parentName = commentElement.parent && commentElement.parent.name;
						if ( parentName && /^(table|tbody|thead|tfoot|tr)$/i.test( parentName ) ) {
							return null;
						}

						commentElement.attributes = [];
						var fakeElement = editor.createFakeParserElement( commentElement, 'cke_protected', 'protected' );

						var cleanedCommentText = CKEDITOR.plugins.showprotected.decodeProtectedSource( commentText );
						fakeElement.attributes.title = fakeElement.attributes.alt = cleanedCommentText;

						var typeClass = CKEDITOR.plugins.showprotected.classifySmartyTag( cleanedCommentText );
						if ( typeClass ) {
							fakeElement.attributes[ 'class' ] = ( fakeElement.attributes[ 'class' ] || 'cke_protected' ) + ' ' + typeClass;
						}

						return fakeElement;
					}

					return null;
				}
			} );
		}
	}
} );

/**
 * Set of showprotected plugin's helpers.
 *
 * @class
 * @singleton
 */
CKEDITOR.plugins.showprotected = {
		
	protectedSourceMarker: '{cke_protected}',
		
	decodeProtectedSource: function( protectedSource ) {
		if(protectedSource.indexOf('%3C!--') == 0) {
			return decodeURIComponent(protectedSource).replace( /<!--\{cke_protected\}([\s\S]+?)-->/g, function( match, data ) {
                return decodeURIComponent( data );
			} );
		} else {
			return decodeURIComponent(protectedSource.substr(CKEDITOR.plugins.showprotected.protectedSourceMarker.length));
		}
	},
	
	encodeProtectedSource: function( protectedSource ) {
		return '<!--' + CKEDITOR.plugins.showprotected.protectedSourceMarker +
        	encodeURIComponent( protectedSource ).replace( /--/g, '%2D%2D' ) +
        	'-->';
	},

	// Map a decoded Smarty tag to a CSS class so showprotected can pick the right icon.
	// Returns null for unrecognized tags (falls back to code.gif).
	classifySmartyTag: function( source ) {
		var s = String( source ).replace( /^\s+|\s+$/g, '' );
		if ( /^\{(foreach|section|for|while)\b/i.test( s ) )       return 'cke_smarty_loop_open';
		if ( /^\{\/(foreach|section|for|while)\b/i.test( s ) )     return 'cke_smarty_loop_close';
		if ( /^\{(foreachelse|sectionelse)\b/i.test( s ) )         return 'cke_smarty_if_else';
		if ( /^\{if\b/i.test( s ) )                                return 'cke_smarty_if_open';
		if ( /^\{(else|elseif)\b/i.test( s ) )                     return 'cke_smarty_if_else';
		if ( /^\{\/if\b/i.test( s ) )                              return 'cke_smarty_if_close';
		if ( /^\{\$/.test( s ) )                                   return 'cke_smarty_var';
		return null;
	}

};
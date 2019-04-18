/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	config.filebrowserBrowseUrl = document.location.pathname+'?_g=filemanager&mode=fck';
	config.protectedSource.push(/{\S*?.*\S}/g);
	config.extraPlugins = 'showprotected';
	config.filebrowserWindowHeight  = 500;
	config.filebrowserWindowWidth  = 650;
	config.allowedContent = true;
};

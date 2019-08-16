/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	config.filebrowserBrowseUrl = document.location.pathname+'?_g=filemanager&mode=fck';
	config.extraPlugins = 'placeholder';
	config.filebrowserWindowHeight  = 500;
	config.filebrowserWindowWidth  = 650;
	config.allowedContent = true;
};

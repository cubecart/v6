/*
* Image Cropping Plugin
* Version 1.1
* Martin Purcell <martin@devellion.com>
*
* Description:	Adapted (heavily) from the JQuery UI example script (ui.jquery.com)
* Takes a source image, and creates a draggable, resizable image editor
*
* Usage:		Add class="cropper" to any image tag, and set an ID, which will be used for the input fields
* Handling:		Creates 4 hidden input fields:
*					<id>[x]	- defines crop left
*					<id>[y]	- defines crop top
*					<id>[h]	- defines crop height
*					<id>[w]	- defines crop width
*
* This work is licensed under a Creative Commons Attribution-Share Alike 2.0 license (creativecommons.org)
*/
jQuery.fn.cropper	= function(){
	this.each(function(){
		var img		= new Image();
		var name	= jQuery(this).attr('id');
		img.src		= jQuery(this).attr('src');
		
		var cropper	= document.createElement('div');
		jQuery(cropper).addClass('crop_wrapper').css({width: img.width, height: img.height});
		var input_x	= document.createElement('input');
		jQuery(input_x).attr({id: name+'_x', name: name+'[x]', type: 'hidden'});
		jQuery(cropper).append(input_x);
		var input_y	= document.createElement('input');
		jQuery(input_y).attr({id: name+'_y', name: name+'[y]', type: 'hidden'});
		jQuery(cropper).append(input_y);
		var input_h	= document.createElement('input');
		jQuery(input_h).attr({id: name+'_h', name: name+'[h]', type: 'hidden'});
		jQuery(cropper).append(input_h);
		var input_w	= document.createElement('input');
		jQuery(input_w).attr({id: name+'_w', name: name+'[w]', type: 'hidden'});
		jQuery(cropper).append(input_w);
		var source	= document.createElement('div');
		jQuery(source).addClass('crop_source').css({
			width: img.width,
			height: img.height,
			background: 'transparent url('+img.src+') no-repeat scroll 0%',
			opacity: 0.3
		});
		var select	= document.createElement('div');
		jQuery(select).addClass('crop_select').resizable({
			containment: 'parent',
			handles: 'all',
			knobHandles: true,
			ghost: false,
			autoHide: false,
			minWidth: 75,
			minHeight: 75,
			resize: function(e, ui) {
				var self = jQuery(this).data('resizable');
				this.style.backgroundPosition = '-' + (self.position.left) + 'px -' + (self.position.top) + 'px';
				jQuery(input_x).val(self.position.left);
				jQuery(input_y).val(self.position.top);
				jQuery(input_h).val(self.size.height);
				jQuery(input_w).val(self.size.width);
			},
			stop: function(e, ui) {
				var self = jQuery(this).data('resizable');
				this.style.backgroundPosition = '-' + (self.position.left) + 'px -' + (self.position.top) + 'px';
			}
		}).draggable({		
			cursor: 'move',
			containment: 'parent',
			drag: function(e, ui) {
				var pos		= jQuery(this).data('draggable');
				jQuery(input_x).val(pos.position.left);
				jQuery(input_y).val(pos.position.top);
				this.style.backgroundPosition = '-' + (pos.position.left) + 'px -' + (pos.position.top) + 'px';
			}
		}).css({background: 'transparent url('+img.src+') no-repeat scroll 0px 0px'});
		jQuery(cropper).append(source);
		jQuery(cropper).append(select);
		jQuery(this).replaceWith(cropper);
	});
}
jQuery(document).ready(function(){
	jQuery('img.cropper').cropper();
});
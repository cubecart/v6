/*
jQuery File Tree Plugin
Version 1.8 - 27th June 2012

Martin Purcell & Bill Wheeler - CubeCart (http://cubecart.com)
- re-engineered specifically for CubeCart to use a JSON response, instead of html
- modified to only insert form tags when images change

Based on the original plugin by Cory S.N. LaViska - A Beautiful Site (http://abeautifulsite.net/)

Usage:
	$('div.filetree').fileTree([options]);

-- TERMS OF USE --
jQuery File Tree is licensed under a Creative Commons License and is copyrighted (C)2008 by Cory S.N. LaViska.
For details, visit http://creativecommons.org/licenses/by/3.0/us/
*/

function in_array(val, ar, strict) {
	if (strict) {
		function equals(a,b){return a === b}
	} else {
		function equals(a,b){return a == b}
	}
	for (var i in ar) {
		if (equals(ar[i], val)) return true;
	}
	return false;
}

function array_search( needle, haystack, strict ) {
    var strict = !!strict;
    for (var key in haystack){
        if( (strict && haystack[key] === needle) || (!strict && haystack[key] == needle) ){
            return true;
        }
    }
    return false;
}

if (jQuery)(function($){

	var image_dir = 'skins/'+$('#val_skin_folder').text()+'/images/';
	
	if ($('#val_admin_folder').length) {
		image_dir = $('#val_admin_folder').text()+'/'+image_dir;
	} else if ($('#val_store_url').length) {
		image_dir = $('#val_store_url').text()+'/'+image_dir;
	}
	if ($('#val_skin_common_images').length) {
		image_dir = image_dir+$('#val_skin_common_images').text()+'/';
	}

	$.extend($.fn, {
		fileTree: function(o, h) {
			if (!o) var o = {};
			if (!h) var h = function(h){return};
			if (!o.root) o.root = '/';
			if (!o.name) o.name = 'image';
			if (!o.group) o.group = 1;
			if (!o.script) o.script = $('#val_admin_file').text();
			if (!o.unique) {
				o.unique = ($(this).hasClass('unique')) ? true : false;
			}
			$(this).each(function() {
				function showTree(c, t) {
					$(c).addClass('wait');
					$('.filetree.start').remove();
					if ($(c).children('ul').length >= 1) {
						// Already Loaded
						$(c).removeClass('wait').children('ul').slideDown();
					} else {
						// AJAX request
						$.ajaxSetup({
							complete: function(XMLHttpRequest, textStatus){$('.wait').removeClass('wait');},
							dataType: 'json',
							global: false
						});
						$.getJSON(o.script, {'_g': 'xml', type: 'files', q: 'list', dir: t, group: o.group}, function(data){
							$(c).find('.start').html('');
							var ul	= document.createElement('ul');
							$(ul).addClass('filetree');
							$.each(data, function(i, item){
								var li	= document.createElement('li');
								var a	= document.createElement('a');
								switch (item.type) {
									case 'directory':
										$(li).addClass('directory collapsed');
										$(a).attr({href: '#', rel: item.path}).text(item.name);
										break;
									case 'file':
										var span	= document.createElement('span');
										var img		= document.createElement('img');
										var status	= '0'; // added to fix bug 2367
										if (typeof(file_default) != 'number') file_default = 0;
										if (file_default == item.id) {
											var bool = '2';
										} else {
											var bool	= (typeof(file_list) == 'object' && array_search(item.id, file_list)) ? 1 : 0
										}
										$(span).addClass('actions');

										$(img).addClass('imgtoggle').data({
											id: o.name+'_'+item.id,
											rel: item.id,
											name: o.name+'['+item.id+']',
											value: bool
										});
										
										switch (bool) {
											case '2':
												status = 'star'; break;
											default:
												status = bool;
										}
										img.src = image_dir+status+'.png';
										$(img).attr({rel: '#'+o.name+'_'+item.id}).addClass('checkbox');
										if (o.unique) $(img).addClass('unique');
										$(span).append(img);
										$(li).append(span).addClass('file');
										$(a).attr({href: item.path+item.name, rel: item.path, title: item.description}).text(item.file);
										if (item.mime.match(/^image/)) {
											$(li).addClass('image');
											$(a).bind('click', function() {
												$.fn.colorbox({href:$(a).attr('href'), open:true});
												return false;
											});
										}
										break;
								}
								$(li).append(a);
								$(ul).append(li);
							});
							if (o.root == t) $(c).find('ul:hidden').show(); else $(c).find('ul:hidden').slideDown('slow');
							$(c).append(ul).removeClass('wait');
							bindTree(c);
						});
					}
				}
				function bindTree(t) {
					$(t).find('li>a').bind('click', function(){
						if ($(this).parent().hasClass('directory')) {
							if ($(this).parent().hasClass('collapsed')) {
								$(this).parent('ul').remove();
                                                                /* BUG 2814 fixed */
                                                                showTree($(this).parent(), $(this).attr('rel'));
								$(this).parent().removeClass('collapsed').addClass('expanded');
							} else {
								$(this).parent().find('ul').slideUp('slow');
								$(this).parent().removeClass('expanded').addClass('collapsed');
							}
						}
						return false;
					});
				}
				$(this).html('<ul class="filetree start"><li class="wait">&nbsp;<li></ul>');
				showTree($(this), escape(o.root));
			});
		}
	});

	/* Set up status toggle images */
	$('input.toggle:hidden').each(function(){
		var img_status = ($(this).val() == '1') ? '1' : '0';
		var img			= document.createElement('img');
		img.src = image_dir+img_status+'.png';
		if (img_status == '1') {
			img.alt = img.title = 'Disable';
		} else {
			img.alt = img.title = 'Enable';
	    }
		$(img).addClass('checkbox');
		if ($(this).hasClass('unique')) $(img).addClass('unique');
		$(img).attr('rel', '#'+$(this).attr('id'));
		$(this).after(img);
	}).change(function(){
		switch ($(this).val()) {
			case '1':
				var status = '1'; var alt = 'Disable'; break;
			case '2':
				var status = 'star'; var alt = ''; break;
			default:
				var status = '0'; var alt = 'Enable'; break;
		}
		var controller = 'img.checkbox[rel=#'+$(this).attr('id')+']';
		$(controller).attr({'src': image_dir+status+'.png', 'alt' : alt, 'title' : alt});
	});

	// handle special insertion of form element only when an image is changed from current
	$('img.imgtoggle').live('click', function(){
		var id_val = $(this).data('id');
		var is_unique = $(this).hasClass('unique');
		var input = document.createElement('input');
		var status = '0';
		if (is_unique) $(input).addClass('unique');
		$(input).addClass('toggle').attr({
			type: 'hidden',
			id: id_val,
			rel: $(this).data('rel'),
			name: $(this).data('name'),
			value: $(this).data('value')
		}).change(function(){
			switch ($(this).val()) {
				case '1':
					status = '1'; break;
				case '2':
					status = 'star'; 
					if($('#master_image_preview').exists()) {
						var master_image = $(this).closest(".image").find("a").attr('href');
						$('#master_image_preview').attr('src', master_image);
					}
					break;
				default:
					status = '0'; break;
			}
			var controller = 'img.checkbox[rel=#'+id_val+']';
			$(controller).attr({'src': image_dir+status+'.png'});
		});
		var parent_span = $(this).parent('span.action');
		$(this).before(input);
		$(this).removeClass('imgtoggle').addClass('toggle');
	});

	$('img.checkbox').live('click', function(){
		var parent = $(this).attr('rel');

		var is_filemanager	= $(this).parents('div:first').hasClass('fm-filelist');
		var is_unique		= $(this).hasClass('unique');
		switch ($(parent).val()) {
			case '1':
				if (is_unique || !is_filemanager) {
					var new_value = '0';
				} else {
					$('input[value=2].toggle').each(function(){
						$('img[rel='+$(this).attr('rel')+'].checkbox').val('1').change();
					});
					var new_value = '2';
				}
				break;
			case '2':
				var new_value = '0';
				break;
			default:
				
				if (is_unique) $('.fm-container input.toggle').val('0').change();
				var new_value = '1';
				break;
		}
		$(parent).val(new_value).change();
	});
})(jQuery);
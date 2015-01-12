//Keep page at top
$(window).load(function() {
  $('html, body').animate({scrollTop:0}, 0);
});

$(document).ready(function() {

	$('#rule-eu').click(function() {		
		$( "#country-region" ).toggle( "slow", function() {});
	});
	$('a.colorbox.hostip').colorbox({
		iframe:true,
		innerHeight:'100px',
		innerWidth:'320px'
	});
	$('a.colorbox.wiki').bind('cbox_complete', function(){
		window.scrollTo(0,0);
	}).colorbox({
		iframe:true,
		innerHeight:'450px',
		innerWidth:'650px'
	});

	$('a.colorbox.paypal').colorbox({
		height:'433px',
		iframe:true,
		scrolling:false,
		width:'602px'
	});
	$('a.colorbox.address-form').colorbox({
		href:'#address-form',
		inline:true,
		innerHeight:'400px',
		innerWidth:'650px'
	});

	$('#loading_content').hide();

	$('div#progress_bar>img.newsletter').each(function(){
		window.onbeforeunload	= function(){return true;};
		ajaxNewsletter($('#newsletter_id').val(), 1);
	});

	/* MultiFile Upload */
	$('input:file.multiple').MultiFile({
		max: 4,
		namePattern: '$name$i',
		remove: '<i class="fa fa-trash-o"></i>'
	});

	/* Load the CKEditor */
	$('textarea.fck').each(function(){
		/* Start fixing bug 2872 */
		if($(this).hasClass('fck-full')){							
			var ckconfig = {
				path: 'includes/ckeditor/',			
				fullPage: true,
				toolbar: 'CubeCart',
				selector: 'textarea.fck'
			};
		}else{
			var ckconfig = {
				path: 'includes/ckeditor/',			
				fullPage: false,
				toolbar: 'CubeCart',
				selector: 'textarea.fck'
			};
		}
		$(this).ckeditor(ckconfig);
	});

	/* Filemanager inline widgets */
	$('div.fm-filelist').each(function(){
		var admin_file = $('#val_admin_file').text();
		$(this).fileTree({'root':'/', 'script':'./'+admin_file, 'group':$(this).attr('rel'), 'name':$(this).attr('id')});
	});

	/* Tab Controller */
	if (!$('div.tab').exists()) {
		var anchor = $('div.tab_content:first').show().attr('id');
		var current_tab = '#'+anchor;
		$('#tab_'+anchor).addClass('tab-selected');
	}
	$('div.tab').each(function(){
		if (window.location.hash !== '' && $(window.location.hash).length > 0) {
			var anchor		= window.location.hash;
			var current_tab = anchor;
			$('div.tab_content:not('+anchor+')').hide();
			$('div.tab_content'+anchor).show();
			$('#tab_'+anchor.replace('#', '')).addClass('tab-selected');
		} else {
			$('div.tab_content:not(:first)').hide();
			var anchor = $('div.tab_content:first').show().attr('id');
			var current_tab = '#'+anchor;
			$('#tab_'+anchor).addClass('tab-selected');
		}
		if ($('#wikihelp').exists()) {
			var wiki = $('#wikihelp').attr('href');
			var url = wiki.split('#');
			$('#wikihelp').attr('href', url[0] + current_tab);
		}
		$('#previous-tab').val(current_tab);
		$('input.previous-tab').val(current_tab);
		window.scrollTo(0,0);
	}).on('click', function(){
		var target = $(this).children('a').attr('href');
		if (target == '#sidebar') {
			$('#sidebar_control').click();
			return false;
		} else if (target.match(/^#/)) {
			document.location.hash=target;
			$('.tab').removeClass('tab-selected');
			$(this).addClass('tab-selected');
			$('div.tab_content').hide();
			$(target).show();
			window.scrollTo(0,0);
			$('#previous-tab').val(target);
			$('input.previous-tab').val(target);
			if ($('#wikihelp').exists()) {
				var wiki = $('#wikihelp').attr('href');
				var url = wiki.split('#');
				$('#wikihelp').attr('href', url[0]+target);
			}
			return false;
		}
	});

	/* Smart skin selector */
	$('select.select-skin').each(function(){
		var styles	= $(this).siblings('select.select-style');
		var select	= $(this).siblings('input[type=hidden].default-style').val();
		if (json_skins[$(this).val()]) {
			for (value in json_skins[$(this).val()]) {
				var name	= json_skins[$(this).val()][value];
				var option	= document.createElement('option');
				$(option).val(value).text(name).addClass('dynamic');
				if (value == select) $(option).attr('selected','selected');
				$(styles).append(option);
			}
		} else {
			if ($(this).hasClass('no-drop')) $(styles).hide();
		}
		$(this).on("change",function(){
			$(styles).children('option.dynamic').remove();
			if (json_skins[$(this).val()]) {
				for (value in json_skins[$(this).val()]) {
					var name	= json_skins[$(this).val()][value];
					var option	= document.createElement('option');
					$(option).val(value).text(name).addClass('dynamic');
					$(styles).append(option);
				}
				$(styles).show();
			} else {
				if ($(this).hasClass('no-drop')) $(styles).hide();
			}
		});
	});
	
	/* Smart mobile skin selector */
	$('select.select-skin-mobile').each(function(){
		var styles	= $(this).siblings('select.select-style-mobile');
		var select	= $(this).siblings('input[type=hidden].default-style-mobile').val();
		if (json_skins[$(this).val()]) {
			for (value in json_skins[$(this).val()]) {
				var name	= json_skins[$(this).val()][value];
				var option	= document.createElement('option');
				$(option).val(value).text(name).addClass('dynamic');
				if (value == select) $(option).attr('selected','selected');
				$(styles).append(option);
			}
		} else {
			if ($(this).hasClass('no-drop')) $(styles).hide();
		}
		$(this).on("change",function(){
			$(styles).children('option.dynamic').remove();
			if (json_skins[$(this).val()]) {
				for (value in json_skins[$(this).val()]) {
					var name	= json_skins[$(this).val()][value];
					var option	= document.createElement('option');
					$(option).val(value).text(name).addClass('dynamic');
					$(styles).append(option);
				}
				$(styles).show();
			} else {
				if ($(this).hasClass('no-drop')) $(styles).hide();
			}
		});
	});

	$('span.editable').each(function(){
		if ($(this).html() == '') $(this).html('<em>null</em>');
	});

	/* Inline text editor */
	$('span.editable').each(function(){
		$(this).attr('title', 'Click to edit');
	}).on('click', function(){
		var value	= $(this).html();
		if (value == '<em>null</em>') value = '';
		var name	= $(this).attr('name');
		var classes	= $(this).attr('class');
		if ($(this).hasClass('select')) {
			var element	= document.createElement('select');
			for (var i=0; i<select_data.length; i++) {
				$(element).append('<option value="'+i+'">'+select_data[i]+'</option>');
			}
			$(element).children(':contains('+value+')').attr('selected', 'selected');
		} else {
			var element	= document.createElement('input');
			$(element).attr({type: 'text', value: value}).addClass(classes);
		}
		$(element).addClass('textbox');
		$(element).attr('name', name);
		$(this).replaceWith(element);
	});

	/* Reorder - Used for categories & documents */
	$('.reorder-list').sortable({
		axis: 'y',
		handle: 'a.handle',
		opacity: 0.7,
		placeholder: 'reorder-position',
		placeholderElement: '> tr',
		revert: true,
		scroll: true,
		stop: function(event, ui){updateStriping();}
	});

	/* Funky language string code */
	$('.revert').each(function(){
		var name	= $(this).attr('rel');
		if ($('#defined_'+name).val() == '0') {
			$('#row_'+name).addClass('list-changed');
		} else {
			if ($('#string_'+name).val() != $('#default_'+name).val()) {
				$('#row_'+name).addClass('custom-phrase');
			} else {
				$(this).hide();
			}
		}
	}).on('click', function(){
		var target	= $(this).attr('rel');
		var basis	= $('#default_'+target).val();
		$('#string_'+target).val(basis);
		$('#row_'+target).removeClass('custom-phrase');
		$(this).hide();
		return false;
	});
	$('.editable_phrase').focusout(function() {
		var name	= $(this).attr('rel');
		if ($(this).val() != $('#default_'+name).val()) {
			$('#row_'+name).addClass('custom-phrase');
			$('#revert_'+name).show();
		} else {
			$('#revert_'+name).hide();
		}
	});


	/* AJAX lookups */
	$('input.ajax').autocomplete({
		timeout: 5000,
		ajax_get : ajaxSuggest,
		callback: ajaxSelected
    });

	$('select.field_select').each(function(){
		$(this).find('option:first').attr('selected', 'selected');
		$(this).parent().parent().find('.field_select_target:not(:first)').hide();
		if ($(this).attr('id') == 'select_group_id') {
			var group_id	= $('option:selected', $(this)).val();
			$('#attr_source').attr('name', 'add_attr['+group_id+']');
			$('#group_target').attr('target', 'group_'+group_id);
		}
	}).on("change",function(){
		if ($(this).attr('id') == 'select_group_id') {
			var group_id	= $('option:selected', $(this)).val();
			$('#attr_source').attr('name', 'add_attr['+group_id+']');
			$('#group_target').attr('target', 'group_'+group_id);
		}
		$(this).parent().parent().find('.field_select_target').hide();
		if ($(this).val() != '') {
			var target	= '#' + $(this).attr('rel') + $(this).val();
			$(target).show();
			$('#'+$(target).attr('rel')).show();
		}
	});
	/* Date picker */
	$.datepicker.setDefaults({
		changeMonth: true,
		constrainInput: true,
		dateFormat: 'yy-mm-dd',
		hideIfNoPrevNext: true,
		onSelect: function(date){
			var parts	= date.split('-',3);
			$(this).nextAll('input.date:first').datepicker('option', 'minDate', new Date(parts[0], parts[1]-1, parts[2]));
		},
		showStatus: false
	});
	$('input.date').datepicker();
	window.scrollTo(0,0);

	var nav_height = $('#navigation').height();
	var content_height = $('#page_content').height();
	if(nav_height > content_height) {
		$('#page_content').height(nav_height+100);
	}

	$('input[type="checkbox"]').each(function(){
        if(!$(this).parent().hasClass("custom-checkbox")) {
        	$(this).wrap("<div class='custom-checkbox'></div>");
        }
        if($(this).is(':checked')){
            $(this).parent().addClass("selected");
        } else {
        	$(this).parent().removeClass("selected");
        }
    });
	
    $('body').on('click','img.checkbox, .check-primary, .check_cat, .check-all, .custom-checkbox', function() {
    	$('input[type="checkbox"]').each(function(){
	        if($(this).is(':checked')){
	            $(this).parent().addClass("selected");
	        } else {
	        	$(this).parent().removeClass("selected");
	        }
	    });
    });    
});
/* Update Form Fields for Address */
function updateAddressValues(key,i,data) {
	if (i == "country") {
		$('#'+key+'_'+i+' option').filter(function () { return $(this).html() == data[i]; }).first().attr('selected', 'selected');
		$('#'+key+'_'+i).trigger("change");
		if ($('#'+key+'_state').get(0).tagName == "INPUT") {
			$('#'+key+'_state').val(data["state"]);
		} else {
			$("#"+key+"_state option").filter(function () { return $(this).html() == data["state"]; }).attr('selected', 'selected');
		}
	} else if (i != "state") {
		$('#'+key+'_'+i).val(data[i]);
	}
}

function inlineRemove(element) {
	/* Remove items without round-tripping - still need to save though... */
	var msg		= $(element).attr('title');
	var rel		= $(element).attr('rel');
	var url		= $(element).attr('href');
	var name	= $(element).attr('name');

	if (msg != '' && !confirm(msg)) return false;
	if (rel && !$(element).hasClass('dynamic')) {
		var input	= document.createElement('input');
		$(input).attr({type: 'hidden', name: name+'[]'}).val(rel);
		$(element).parents('form:first').append(input);
	} else {
		pageChanged(element);
	}
	var parent = $(element).parents('tr:first,div:first:not(.tab_content)').get(0);
	$(parent).remove();
	updateStriping();

	return false;
}


var data = false;
if (!addresses || typeof(addresses) != 'object') {
	var addresses = new Object();
}

var options_added = 0;

function optionAdd(source, target) {
	var target	= $('#'+target);
	var source	= $('#'+source);
	/* Display the group:value */

	var type	= $('#opt_mid :selected').parent().attr('label');
	var name	= $('#opt_mid :selected').text();

	var display	= (typeof(type) == ('undefined' || '')) ? name : '<strong>'+type+'</strong>: '+name;

	var option_value = $('#opt_mid').val();
	if (option_value != '' && option_value != 0) {
		var row	= $(source).clone();
		/* Set Display value & ID */
		$(row).find('.name').append(display).find('input:first').val(option_value).removeAttr('disabled');
		/* Set t'others */
		var data = $('input.data');
		for (i=0; i<data.length; i++) {
			var rel	= $(data[i]).attr('rel');
			var value = ($(data[i]).val() == (null || '')) ? '0' : $(data[i]).val();
			var this_field = $(row).find('.'+rel).find('input:first');

			if(rel == "matrix_include") {
				this_field.attr('name', 'option_add['+rel+']['+options_added+']');
			} else if(rel == "set_enabled") {
				this_field.removeAttr('disabled');
				if (value==1) {
					this_field.parent().addClass("selected");
					this_field.attr('checked', 'checked');
				}
				this_field.attr('name', 'option_add['+rel+']['+options_added+']');
			} else if(rel == "negative") {
				this_field.removeAttr('disabled');
				if ($(data[i]).is(':checked')) {
					this_field.parent().addClass("selected");
					this_field.attr('checked', 'checked');
				}
				this_field.attr('name', 'option_add['+rel+']['+options_added+']');
			} else {
				value = parseFloat(value, 10).toFixed(2);
				$(row).find('.'+rel).append(value).find('input:first').val(parseFloat(value)).removeAttr('disabled');
			}
			$(data[i]).val('');
		}
		$(row).find('a.remove').on("click",function(){inlineRemove(this);});
		$(row).removeAttr('id'); //.removeClass('hidden');
		$('#opt_mid :selected').removeAttr('selected');
		$('#opt_mid:first-child').attr('selected', 'selected');
		$(target).append($(row));
		options_added++;
	}
	return false;
}

function ajaxSelected(v, id, rel) {
	var admin_file = $('#val_admin_file').text();
	$('#result_'+id).val(v.id);
	switch (rel.toLowerCase()) {
		case 'user':
			$.getJSON('./'+admin_file, {'_g':'xml','type':'address','q':v.id, 'function':'search'}, function(data){
				$('select.address-list>option.temporary').remove();
				for (var i=0;i<data.length;i++) {
					var option = document.createElement('option');
					$(option).val(i);
					$(option).html(data[i].description);
					$(option).addClass('temporary');
					$('.address-list').append(option);
				}
				addresses = data;
			});
			break;
		case 'product':
			$('#add-price').val(v.data.price);
			$('#add-subtotal').html(($('#add-quantity').val()*v.data.price).toFixed(2));
			data = v.data;
			break;
	}
	for (key in v.data) {
		if (v.data[key] != '') {
			$('#ajax_'+key).val(v.data[key]).trigger("change");
		}
	}
	if($('#result_'+id).hasClass('clickSubmit')) {
		$('#result_'+id).closest("form").submit();
	}
}
function ajaxSuggest(key, cont, rel) {
	var script_name	= './'+$('#val_admin_file').text();
	var params		= { '_g': 'xml', 'type': rel, 'q':key, 'function':'search' };
	$.get(script_name, params, function(obj){
		var res	= [];
		for (var i=0;i<obj.length;i++) {
			res.push({ id:obj[i].value , value:obj[i].display, info:obj[i].info, data:obj[i].data});
		}
		cont(res);
	}, 'json');
}

function ajaxNewsletter(news_id, cycle) {
	var admin_file = $('#val_admin_file').text();
	$.getJSON('./'+admin_file, {'_g': 'xml', type: 'newsletter', q: news_id, page: cycle, 'function':'search'}, function(data){
		$('div#progress_bar').css({width: data.percent+'%'});
		$('div#progress_bar_percent').text(Math.round(data.percent)+'%');
		if (data.percent == 100 || data.complete == 'true') {
			window.onbeforeunload	= null;
			window.location			= '?_g=customers&node=email';
		} else {
			ajaxNewsletter(news_id, cycle+1);
		}
	});
}

function updateOrderTotals(thisitem) {
	if (!thisitem.hasClass('quantity')) thisitem.val(($(this).val()*1).toFixed(2));
	var parent		= thisitem.parents('.update-subtotal:first');
	var quantity	= $(parent).find('input.quantity').val();
	var lineprice	= $(parent).find('input.lineprice').val();
	var target		= $(parent).find('input.subtotal:first');
	var value		= (quantity*lineprice).toFixed(2);
	$(target).val(value);

	var subtotal	= 0;
	$('input.subtotal').each(function(){
		var value = ($(this).val()*1);
		subtotal += value;
	});

	var discount = $('#discount').val();
	discount = discount * 1;
	var type = $('#discount_type').val();
	if (type == "p") {
		if (discount > 100) {
			$('#discount').val("100");
			discount = 100;
		}
		discount = (discount / 100) * subtotal;

		$('#discount_percent').html("%");
	} else {
		$('#discount_percent').html("");
	}

	$('#subtotal').val(subtotal.toFixed(2));
	var shipping	= $('#shipping').val();
	var tax			= 0;
	$('.update-subtotal input.tax').each(function(){
		var tax_amount	= $(this).val();
		tax += tax_amount*1;
	});
	var total		= subtotal*1 - discount + shipping*1 + tax*1;

	$('#total_tax').val(tax.toFixed(2));
	$('#total').val(total.toFixed(2));
}



$('#inventory-list').on('change','select.options_calc', function() {
	productOptionPrices($(this).parent().attr('rel'));
});
$('#inventory-list').on('focusout','.text_calc', function() {
	productOptionPrices($(this).parent().attr('rel'));
});

function productOptionPrices(product_id) {
	
	var price_field = $('#'+product_id+'_price');
	var final_price = +price_field.attr('original');
	var sign = '';
	
	$('span[rel='+product_id+'] select').each(function() {
		option_price = $(this).find('option:selected').attr('rel');
		
		if(option_price) {
	    	sign = option_price.substr(0, 1);
	    	value = +option_price.substr(1);
	    	if(value>0) {	    	
		    	if (sign == '+') {
			       final_price = (+final_price + +value);
			    } else if (sign == '-') {
			       final_price = (+final_price - +value);
			    }
		    }
	    }			
	});
	$('span[rel='+product_id+'] input, span[rel='+product_id+'] textarea').each(function() {
		
		var text_value = $(this).val();
		var option_price = $(this).attr('rel');
		
		if(text_value!='') {
		    if(option_price) {
		    	sign = option_price.substr(0, 1);
		    	value = +option_price.substr(1);
	
		    	if(value>0) {	    	
			    	if (sign == '+') {
				       final_price = (+final_price + +value);
				    } else if (sign == '-') {
				       final_price = (+final_price - +value);
				    }
			    }
		    }
	    }
	});
	
	price_field.val(final_price.toFixed(2));
	
	$('.update-subtotal input.number').trigger("change");
	
	return false;
}

var inline_add_offset = 0;

$('a.add, a.inline-add, input[type="button"].add').on('click', function(){
	var target	= $(this).attr('target');
	var parent	= $(this).parents('.inline-add:first');
	var source	= $(parent).next('.inline-source');
	var inputs	= new Array();
	var proceed	= true;
	
	$('.inline-add').removeClass('highlight');
	$(':input', parent).each(function(){
		$(this).removeClass('required-error');
		var rel		= $(this).attr('rel');
		var value	= $(this).val();
		if ($(this).hasClass('not-empty') && value == $(this).attr('original')) {
			$(this).on("change",function(){
				if ($(this).val() != $(this).attr('original')) $(this).removeClass('required-error');
			}).addClass('required-error');
			proceed = false;
		}
		inputs[rel]	= value;
	});

	if (proceed == false) return false;
	$(parent).removeClass('highlight');
	if ($(source).length == 1) {
		var varname	= $(source).attr('name');
		var content	= $(source).clone(true).attr({name: ''}).removeAttr('id').removeClass('inline-source');
		var admin_file = $('#val_admin_file').text();
		$(parent).find(':input').each(function(){
			var rel		= $(this).attr('rel');
			var value	= $(this).val();
			var options_html;
			if ($(this).is('select')) {
				var display	= $(this).find('option:selected').text();
			} else {
				var display	= $(this).val();
			}
			
			if(rel=='product_id' && value>0) {
				$.ajax({
					url: './'+admin_file,
					type: 'GET',
					cache: false,
					data: { '_g': 'xml', 'type': 'prod_options','q':value,'function':'template' },
					complete: function(returned) {
						options_html = returned.responseText.split('inv[]').join('inv_add['+(inline_add_offset-1)+']');
						options_html = options_html.split('rel=""').join('rel="'+(inline_add_offset-1)+'"');
						$(content).find('[rel=product_options]').html(options_html);
					}
				});
			} else if (rel=='price') {
				$(content).find(':input[rel='+rel+']').attr('id',inline_add_offset+'_price');
			}
			$(content).find(':input[rel='+rel+']').val(value).attr({name: varname+'['+inline_add_offset+']['+rel+']',original:value});
			$(content).find('[rel='+rel+']:not(:input)').text(display);
			
		});
		$(parent).find(':input').each(function(){
			$(this).val($(this).attr('original'));
		});
	} else {
		var content	= document.createElement('div');
		var actions	= document.createElement('span');
		var remove	= document.createElement('a');
		var i	= document.createElement('i');

		if($('input[name="add_div_class"]')) {
			$(content).addClass($('input[name="add_div_class"]').val());
		}
		$(i).attr({class: 'fa fa-trash'});
		$(remove).attr({href: '#'}).addClass('remove dynamic').append(i);
		$(actions).addClass('actions').append(remove);

		$(this).parents('div:first,tr:first').find('.add:input').each(function(){
			if ($(this).hasClass('display')) {
				if($(this).val()=='') {
					proceed = false;
				}
				var display	= ($(this).is('select')) ? $(this).find(':selected').text() : '<strong>'+$(this).val()+'</strong>';
				$(content).append(display);
			}
			if ($(this).attr('name')) {
				var data = document.createElement('input');
				$(data).attr({type: 'hidden', name: $(this).attr('name'), value: $(this).val()});
				$(content).append(data);
			}
			$(this).val('');
		});
		$(content).prepend(actions);
	}
	if (proceed ==  true && target.length > 1 && $('#'+target).length == 1) {
		$('#'+target).append(content);
	} else {
		$(parent).before(content);
	}
	$('.update-subtotal input.number').trigger("change");
	inline_add_offset++;
	updateStriping();
	return false;
});

$('a.duplicate').on('click', function(){
	var rel		= $(this).attr('rel');
	var prefix	= ($(this).attr('target').length >= 1) ? $(this).attr('target') : '';
	$('.'+rel+':input').each(function(){
		var target	= $('#'+prefix+$(this).attr('id'));
		$(target).val($(this).val());
		if ($(this).attr('id') == "sum_country") {
			$(target).trigger("change");
		}
	});
	return false;
});

$('#search-placeholder').on("click",function(){
	$('#sidebar_contain').animate({left:'0px'});
	return false;
});
$('#sidebar_contain').on("mouseleave",function(){
	$(this).animate({left:'-340px'});
	return false;
});
$('div#tab_sidebar').on("click",function(){
	var sidebar = $('#sidebar_contain');
	var position = sidebar.position();
	if(position.left==0) {
		sidebar.animate({left:'-340px'});
	} else {
		sidebar.animate({left:'0px'});
	}
	return false;
});

/* Option Controller */
$('.option-edit').on('click', function(){
	var option_id	= $(this).attr('rel');
	var data		= $('#data_'+option_id).val().split('|');
	$('#opt_assign_id').val(option_id);
	$('#opt_mid').val(data[0]);
	$('#opt_price').val(data[1]);
	$('#opt_weight').val(data[2]);
	$('#opt_stock').val(data[3]);
	$(this).parent().parent().remove();
});

$('img.toggle-add').on('click', function(){
	$(this).attr('src', ($(this).attr('src').match('add')) ? 'images/icons/delete.png' :  'images/icons/add.png');
});

$('.delete_disabled').on("click",function() {
  alert($(this).attr('title'));
});

$('#product_check').on("change",function() {
	$('.list').find('input[name="product[]"]').attr('checked', this.checked);
});

$('input#product_code').on("keyup",function() {
    if($('input#product_code').val().length > 0) {
    	$('input#product_code_auto').attr('checked', false);
    } else {
    	$('input#product_code_auto').attr('checked', true);
    }
});

//Clear entered code if checked
$('input#product_code_auto').on("click",function() {
	var old_code = $('input#product_code_old').val();
	var current_code = $('input#product_code').val();
	if(current_code.length > 0) {
		$('input#product_code_old').val(current_code);
		$('input#product_code').val('');
	} else {
		$('input#product_code').val(old_code);
	}
});

$('#gui_message').on('click', function(){
	$(this).slideUp();
});

$('#seo').on("change",function() {
	var admin_file = $('#val_admin_file').text();
	if ($('#seo').val() == 1) {
		seo = "seo_code";
	} else {
		seo = "no_seo_code";
	}
	$.getJSON('./'+admin_file, {'_g': 'xml', type: seo, 'function':'get'}, function(data){
		$('#htaccess').val(data.content);
	});
});

$('#cat_name').on("change",function() {
	$('#cat_save').on("click",function() {
		$( "#dialog-seo" ).dialog({
			modal: true,
			buttons: {
				Yes: function() {
					$( this ).dialog( "close" );
					$('#gen_seo').val("1");
					document.cat_form.submit();
				},
				No: function() {
					$( this ).dialog( "close" );
					document.cat_form.submit();
				}
			}
		});
		return false;
	});
});
$('#parent').on("change",function() {
	$('#cat_save').on("click",function() {
		$( "#dialog-seo" ).dialog({
			modal: true,
			buttons: {
				Yes: function() {
					$( this ).dialog( "close" );
					$('#gen_seo').val("1");
					document.cat_form.submit();
				},
				No: function() {
					$( this ).dialog( "close" );
					document.cat_form.submit();
				}
			}
		});
		return false;
	});
});

$('#cat_subset').on("change",function(){
    $location = document.URL.replace(/&?page=[0-9]/, '');
    if ($location.indexOf('cat_id') != -1) {
           $location = removeVariableFromURL($location, 'cat_id');
    }
    if ($(this).val() != 'any') {
        $location += "&cat_id=" + $(this).val();
    }
    window.location.replace($location);
});

$('select.address-list').on("change",function(){
	var addr	= $(this).val();
	var data	= addresses[addr];
	var rel		= ($(this).attr('rel') == '') ? 'sum' : $(this).attr('rel');
	var bits	= null;
	var pos		= rel.indexOf(':');
	if (pos > 1) bits = rel.split(':');
	for (var i in data) {
		data[i] = jQuery.trim(data[i]);
		if (bits != null) {
			for (j=0;j<bits.length;j++) {
				updateAddressValues(bits[j],i,data);
			}
		} else {
			updateAddressValues(rel,i,data);
		}
	}
});


/* FCK Filemanager functionality */
$('a.select').on('click', function(){
	var url = $(this).attr('href');
	var num = $('#ckfuncnum').val();
	window.opener.CKEDITOR.tools.callFunction(num, url);
	window.close();
	return false;
});

$('#discount_type, .lineprice').on("change",function(){
	$('.update-subtotal input.number').trigger("change");
});

$('.update-subtotal input.number').on("change",function(){
	updateOrderTotals($(this));
});

/* Generic remove code - removes (almost) any row, and appends a hidden value to the form, if one is needed */
$('body').on('click','a.remove', function() {
	var msg		= $(this).attr('title');
	var rel		= $(this).attr('rel');
	var url		= $(this).attr('href');
	var name	= $(this).attr('name');
	if (msg != '' && !confirm(msg)) return false;
	if (rel && !$(this).hasClass('dynamic')) {
		var input	= document.createElement('input');
		$(input).attr({type: 'hidden', name: name+'[]'}).val(rel);
		$(this).parents('form:first').append(input);
	} else {
		pageChanged(this);
	}
	if (name == "inv_remove") {
		$(this).parents('form:first').append("<input type=\"hidden\" name=\"inv_remove[]\" value=\""+url.substring(1)+"\" />");
	}
	var target = $(this).parents('tr:first,div:first:not(.tab_content)');
	$(target).remove();
	$('.update-subtotal input.number').trigger("change");
	return false;
});

$('a.refresh').on('click', function(){
	$('.update-subtotal input.number').trigger("change");
	return false;
});
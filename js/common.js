jQuery(document).ready(function() {
	var form_error = false;

	jQuery.debug = function(message){
		if (window.console){console.debug('CubeCart: '+message);}else{alert(message);}
	};
	jQuery.fn.insertAtCaret = function(myValue) {
		return this.each(function(){
			if (document.selection) {
				this.focus();
				sel = document.selection.createRange();
				sel.text = myValue;
				this.focus();
			} else if (this.selectionStart || this.selectionStart == '0') {
				var startPos = this.selectionStart;
				var endPos = this.selectionEnd;
				var scrollTop = this.scrollTop;
				this.value = this.value.substring(0, startPos)+ myValue+ this.value.substring(endPos,this.value.length);
				this.focus();
				this.selectionStart = startPos + myValue.length;
				this.selectionEnd = startPos + myValue.length;
				this.scrollTop = scrollTop;
			} else {
				this.value += myValue;
				this.focus();
			}
		});
	};
	jQuery.fn.checkUncheck	= function(settings) {
		jQuery(this).each(function(){
			var onAction	= (jQuery(this).is(':checkbox')) ? 'change' : 'click';
			var controller	= jQuery(this);
			jQuery(this).bind(onAction, function(){
				var target	= jQuery(this).attr('rel');
				if (jQuery(controller).is('input:checkbox')) {
					if (jQuery(controller).is(':checked')) {
						jQuery('input:checkbox.'+target).attr('checked', 'checked');
					} else {
						jQuery('input:checkbox.'+target).removeAttr('checked').change(function(){
							jQuery(controller).removeAttr('checked');
						});
					}
				} else {
					if (jQuery(controller).is(':checked') || jQuery(controller).attr('checked')) {
						jQuery('input:checkbox.'+target).removeAttr('checked');
						jQuery(controller).removeAttr('checked');
					} else {
						jQuery('input:checkbox.'+target).attr('checked','checked').change(function(){
							jQuery(controller).removeAttr('checked');
						});
						jQuery(controller).attr({'checked':'checked'});
					}
					return false;
				}
			});
		});
	};
	jQuery.fn.confirmPassword = function(settings) {
		var options = jQuery.extend({
			updateOn: 'keyup'
		}, settings);

		this.bind(options.updateOn, function() {
			jQuery(this).removeClass('ps-match ps-nomatch error');
			if (jQuery(this).val() != '') {
				var confirmed = jQuery(this).attr('rel');
				if (jQuery('#'+confirmed).val() === jQuery(this).val() && jQuery(this).val() != '') {
					jQuery(this).addClass('ps-match');
				} else {
					jQuery(this).addClass('ps-nomatch error');
				}
			}
		});
	};
	jQuery.fn.exists = function(){ return (jQuery(this).length != 0); };

	/* Break out of iframe - mainly used for iFrame payment gateways to stop confirmed page showing within CC */
	if (top.location.href!=self.location.href) {
			top.location = self.location.href;
	}

	/* Set default values for fields like mailing list and search */
	$('input:text,input:password,textarea').each(function(){
		var title = $(this).attr('title');
		if (typeof title  != "undefined" && title.length >= 1) {
			if ($(this).val() == '') $(this).val(title);
			$(this).focus(function(){
				if ($(this).val() == title) $(this).val('');
			}).blur(function(){
				if ($(this).val() == '') $(this).val(title);
			}).parents('form:first').submit(function(){
				$('input:text,input:password,textarea', this).each(function(){
					if ($(this).val() == title) $(this).val('');
				});
			});
		}
	});

	/* Notify the user if they're navigating away without submitting a form */
	$(':input, :input:hidden').each(function(){
		$(this).attr('original', $(this).val());
	}).change(function(){
		pageChanged(this);
	});

	$('input:submit.update').click(function(){
		$('select.required').removeClass('required');
	});
	$('select.update_form').change(function(){
		$('input.required').removeClass('required');
		$(this).parents('form').submit();
	});
	$('form').submit(function(){
		var submit	= true;
		form_error = false;

		// Code to force product to be added correctly on manual order form
		if($('#inventory-list').exists() && !$('input[name*=inv]').exists()){
			$('.inline-add:first').addClass('highlight');
			return false;
		}
		
		$('.required-error').removeClass('required-error');
		$(':checkbox.ignore').each(function(){
			if ($(this).not(':checked')) $(this).attr('disabled','disabled');
		});
		var form	= ($('div.tab_content').exists()) ? $('div.tab_content:visible') : $(this);
		$(form).find('.required:input:not(:hidden)').each(function(){
			var current		= $(this).val();
			var original	= $(this).attr('original');

			if (current.replace(/\s/i,'') == '') {
				var id	= $(this).attr('id');
				$(this).addClass('required-error').change(function(){
					if ($(this).val() != $(this).attr('original')) {
						$(this).removeClass('required-error');
						$('#error_'+id+'.error').hide('fast');
					}
				});
				$('#error_'+id+'.error').show('fast');
				submit = false;
			}
		});
		$('.inline-add:input').each(function(){
			var required	= ($(this).hasClass('not-empty'));
			if ($(this).val() != $(this).attr('original')) {
				$(this).parents('.inline-add:first').addClass('highlight');
				submit = false;
			}
		});

        $(form).find('select.required:not(:hidden)').each(function(){
            if ($(this).val() == 0) {
                var id	= $(this).attr('id');
                $(this).addClass('required-error').change(function(){
                    if ($(this).val() != $(this).attr('original')) {
                        $(this).removeClass('required-error');
                        $('#error_'+id+'.error').hide('fast');
                    }
                });
                $('#error_'+id+'.error').show('fast');
                submit = false;
            }
        });
		if (submit) {
			window.onbeforeunload = null;
			return true;
		} else {
			var first_error = $(".required-error:first");
			var first_error_position = first_error.position();
			$('html, body').animate({scrollTop:first_error_position.top-50}, 'slow');
		}
		form_error = true;
		return false;
	});

	$('.check-all').checkUncheck();

	$('select.auto_submit').each(function(){
		if(!$(this).hasClass('show_submit')) {
			$(this).parents('form:first').find('input:submit').hide();
		}
	}).change(function(){
		$(this).parents('form:first').submit();
	});

	$('.insert-text').live('click', function(){
		var target	= '#'+$(this).attr('target');
		var text	= $(this).text();
		$(target).insertAtCaret(text);
		return false;
	});

	$('img.autosubmit').each(function(){
		if ($(this).hasClass('form-name')) {
			var formname = $(this).attr('rel');
			$('form#'+formname).submit();
		} else {
			$(this).parents('form').submit();
		}
	});


	$('.duplicate').click(function(){
		var source = $(this).attr('rel');
	});

	/* Global Error/Information System HAndler - Highlight inline errors using JSON data */
	if (typeof gui_message_json != 'undefined' && typeof gui_message_json == 'object') {
		for (var id in gui_message_json){ $('#'+id).addClass('required-error').val(''); }
	}

	/* Check as you proceed */
	$(':input.required').blur(function(){
		var id	= $(this).attr('id');
		if ($(this).val().replace(/\s/i,'')==''){
			$(this).addClass('required-error');
		} else {
			$(this).removeClass('required-error');
		}
	});

	/* gift cert selector thing for delivery */
	$('select.certificate-delivery').change(function(){
		if ($(this).val() == 'm') {
			$('#gc-method-e').slideUp().find('input').removeClass('required');
		} else {
			$('#gc-method-e').slideDown().find('input').addClass('required');
		}
	});

	/* Initial setup of country/state menu */
	$('select#country-list, select.country-list').each(function(){
		if (typeof(county_list) == 'object') {
			var counties = county_list[$(this).val()];
			var target = ($(this).attr('rel') && $(this).attr('id') != 'country-list') ? '#'+$(this).attr('rel') : '#state-list';
			if (typeof(counties) == 'object') {
				var setting	= $(target).val();
				var select	= document.createElement('select');
				$(target).replaceWith($(select).attr({'name':$(target).attr('name'),'id':$(target).attr('id'),'class':$(target).attr('class')}));
				if ($(this).attr('title')) {
					var option = document.createElement('option');
					$('select'+target).append($(option).val('0').text($(this).attr('title')));
				}
				for (i in counties) {
					var option = document.createElement('option');
					if (setting == counties[i].name || setting == counties[i].id) {
						$('select'+target).append($(option).val(counties[i].id).text(counties[i].name).attr('selected', 'selected'));
					} else {
						$('select'+target).append($(option).val(counties[i].id).text(counties[i].name));
					}
				}

			} else {
				if ($(this).hasClass('no-custom-zone')) $(target).attr({'disabled': 'disabled'}).val($(this).attr('title'));
			}
		}
	}).change(function(){
		if (typeof(county_list) == 'object') {
			var list	= county_list[$(this).val()];
			var target	= ($(this).attr('rel') && $(this).attr('id') != 'country-list') ? '#'+$(this).attr('rel') : '#state-list';
			if (typeof(list) == 'object' && typeof(county_list[$(this).val()]) != 'undefined' && county_list[$(this).val()].length >= 1) {
				var setting	= $(target).val();
				var select	= document.createElement('select');
				$(target).replaceWith($(select).attr({'name':$(target).attr('name'),'id':$(target).attr('id'),'class':$(target).attr('class')}));
				if ($(this).attr('title')) {
					var option = document.createElement('option');
					$('select'+target).append($(option).val('0').text($(this).attr('title')));
				}

				for (var i=0; i<list.length; i++) {
					var option = document.createElement('option');
					$('select'+target).append($(option).val(list[i].id).text(list[i].name));
				}
				$('select'+target+' > option[value='+setting+']').attr('selected', 'selected');
			} else {
				var input		= document.createElement('input');
				var replacement	= $(input).attr({'type':'text','id':$(target).attr('id'),'name': $(target).attr('name'),'class': $(target).attr('class')});
				if ($(this).hasClass('no-custom-zone')) $(replacement).attr('disabled', 'disabled').val($(this).attr('title'));
				$(target).replaceWith($(replacement));
			}
		}
	});

	/* Rating Stars */
	$('input[type=radio].rating').rating({required: true});

	/* Zebra Stripes */
	updateStriping('.list,table,.reorder-list');

	$('a.preview').click(function(){
		$('#img-preview').attr('src', $(this).attr('href'));
		return false;
	});

	$('a.delete, a.confirm, .submit_confirm, .install_confirm').click(function(){
		var message	= $(this).attr('title');
		if (message != '') {
			return confirm(message.replace(/\\n/ig, "\n"));
		}
	});

	$('input:password.strength').pstrength();
	$('input:password.confirm').confirmPassword();

	$('.sublist').hide();
	$('.list-master').click(function(){$('#'+$(this).attr('rel')).toggle();});

	$('.contentswitch:not(:input)').hide();
	$('.contentswitch:input').click(function(){
		var value	= $(this).val();
		$('.contentswitch:not(:input)').hide();
		$('#'+value+'.contentswitch').show();
	});


	$('input.contentswitch:radio').attr('checked', false).parent().hide();
	$('#methods').hide();

	$('.selector:input').change(function(){
		$('input.contentswitch:radio').attr('checked', false).parent().hide();
		$('.contentswitch:not(:input)').hide();
		var transid = $(this).val();
		if (transid != '') {
			var methods	= transactions[transid].methods.split(',');
			$('input.contentswitch:radio').each(function(){
				for (i=0;i<methods.length;i++) {
					if (methods[i] == $(this).val()) {
						$(this).parent().show();
						if (methods.length == 1) $(this).click();
					}
					$('.transaction-amount').val(transactions[transid].amount);
					$('#methods').show();
				}
			});
		}
	});

	$('.section-content').hide();
	$('select.section-select').change(function(){
		var value	= $(this).val();
		$('.section-content').hide();
		$('#'+value+'.section-content').show();
	});

	var magnify_options = {lensWidth:250, lensHeight:250, link:true, delay:250};
	$('a.magnify').magnify(magnify_options);
	$('a.gallery').hover(function(){
		var id	= $(this).attr('id');
		if (typeof gallery_json == 'object') {
			$('a.magnify > img#preview').attr({src: gallery_json[id].medium});
			$('a.magnify').attr({href: gallery_json[id].source}).unbind().magnify(magnify_options);
		}
	});

	$('a.colorbox').colorbox({
		photo:true,
		slideshow:true,
		slideshowAuto:false
	});
	
	$("a.colorbox_iframe").colorbox({iframe:true, width:"80%", height:"80%"});
	$("a.colorbox_inline").colorbox({inline:true, width:"50%"});

	$('.login-toggle').each(function(){
		$('.login-method:not(:first)').slideUp();
	}).click(function(){
		if (!$(this).next('.login-method').is(':visible')) {
			$('.login-method:visible').slideUp();
			$(this).next('.login-method').slideDown();
		}
	});
	if ($('div#basket_summary').exists()) {
		$('form.addForm').submit(function(){
			//This is from the required form checker
			if (form_error) {
				return false;
			}
			
			var add = $(this).serialize();
			var action = $(this).attr('action').replace(/\?.*/, '');			
			var basket = $('div#basket_summary');
			// added to accommodate no existing query string
			var parts = action.split("?");
			if(parts.length > 1) {
				action += "&";
			} else {
				action += "?";
			}
			
			$.ajax({
				url: action + '_g=ajaxadd',
				type: 'POST',
				cache: false,
				data: add,
				complete: function(returned) {
					if(returned.responseText.match("Redir")) {
						window.location = returned.responseText.substr(6);
					} else {
						basket.replaceWith(returned.responseText);
						//Remove error message if it exists
						$("#gui_message").slideUp();
						//Shake basket
						$(".animate_basket").effect("shake", { times:4, distance: 3 }, 70);
					}
				}
			});
			return false;
		});
	}

	/*
	$('#login-controller > div.control').each(function(){
		$('div.login-method:not(:first)').slideUp();
		$(this).click(function(){
			$('div.login-method').slideUp();
			$(this).next('div.login-method').slideDown();
		});
	});
	*/

});

var new_option = 0;

function updateStriping(element) {
	/* New and imporved zebra striping */
	// Cleanup
	$('.list-even,.list-odd').removeClass('list-even list-odd');
	// Apply the hover class to ALL elements
	$('.list,.reorder-list').find('>div,tbody>tr').hover(function(){$(this).addClass('list-hover');},function(){$(this).removeClass('list-hover');});
	// alternate the rows on everything else
	$('.list,.reorder-list').find('>div:nth-child(even),tbody>tr:nth-child(even)').addClass('list-even');
	$('.list,.reorder-list').find('>div:nth-child(odd),tbody>tr:nth-child(odd)').addClass('list-odd');
}

function checkUncheck(filter) {
	var filter_on = (filter.length > 0) ? filter  : '';
	if ($(filter+':checkbox').attr('checked')) {
		$(filter+':checkbox').removeAttr('checked');
	} else {
		$(filter+':checkbox').attr('checked', 'checked');
	}
}

function pageChanged(element) {
	var parent	= $(element).parents('form:first');
	if (parent.length == 1) {
		if(parent.attr('title') !== undefined) {
			var title	= parent.attr('title');
			var message	= (title.length > 1) ? title : '';
		}
		if (!parent.hasClass('no-change')) {
			window.onbeforeunload = function(){return message;};
		}
	}
}
/* Click and check a check box */
$('.check-primary').live('click', function(){
   var rel = $(this).attr('rel');
   $('#'+rel+':checkbox').attr('checked','checked');
});

/**
 * Strips a parameter from a URL
 * By - Stuart Eske
 * http://stuarteske.com/2011/02/how-to-remove-variables-from-a-url-with-javascript/
 *
 * @param url_string
 * @param variable_name
 */
function removeVariableFromURL(url_string, variable_name) {
    var URL = String(url_string);
    var regex = new RegExp( "\\?" + variable_name + "=[^&]*&?", "gi");
    URL = URL.replace(regex,'?');
    regex = new RegExp( "\\&" + variable_name + "=[^&]*&?", "gi");
    URL = URL.replace(regex,'&');
    URL = URL.replace(/(\?|&)$/,'');
    regex = null;
    return URL;
}

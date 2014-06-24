jQuery(document).ready(function(){

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
	}

	$('div.click-select input:radio').hide();
	$('div.click-select').click(function(){
		$('div.selected').removeClass('selected');
		$(this).addClass('selected');
		$(this).children('input:radio').attr('checked', 'checked');

	});
	if ($('div.click-select').size() == 1) $('div.click-select').click();
	$('input.cancel:submit').click(function(){
		$('.required:input').removeClass('required');
	});

	jQuery.fn.getinfo	= function() {

		return this;
	}

	$('input:password.strength').pstrength(false);
	$('input:password.confirm').confirmPassword();

	$('form').submit(function(){
		var proceed = true;
		$('.required:input').removeClass('required-error');
		$(this).find('.required:input').each(function(){
			if ($(this).val().replace(/\s/i,'')==''){
				$(this).addClass('required-error').change(function(){
					$(this).removeClass('required-error');
				});
				proceed = false;
			}
		});
		$(this).find('.error:input').each(function(){
			$(this).addClass('required-error');
			proceed = false;
		});
		return (proceed) ? true : false;
	});

	$('label.help').click(function(){
	//	alert('TO DO: load up a help dialogue, explaining what the field is for.');
	});
});
/* All In One Shipping by Estelle Winterflood */

$(document).ready(function() {

	$(".chzn-select").chosen();

	$('a.aios-remove-zone').on('click', function(){
		var msg = $(this).attr('title');
		if (msg != '' && !confirm(msg)) return false;
			return true;
	});

	// $('span.editable').each(function(){
	// 	if ($(this).html() == '') $(this).html('<em>null</em>');
	// });

	/* Inline text editor */

	$('span.aios-editable').each(function(){
		if ($(this).html() == '') $(this).html('<em>null</em>');
	});

	$('span.aios-editable').each(function(){
		$(this).attr('title', 'Click to edit');
	}).on('click', function(){
		var value	= $(this).html();
		if (value == '<em>null</em>') value = '';
		var name	= $(this).attr('name');
		var classes	= $(this).attr('class');

		var element	= document.createElement('input');
		$(element).attr({type: 'text', value: value}).addClass(classes);
		$(element).attr('name', name);
		if ($(this).hasClass('aios-number')) {
			$(element).attr('size', '7');
		}
		if ($(this).hasClass('aios-text')) {
			$(element).attr('size', '25');
		}
		$(this).replaceWith(element);
	});

	$('input.aios-editable').on('change', function(){
		var target = $(this).parents('tr:first').find('input[name^="update_rates"]');
		$(target).val(1); // Set flag to indicate that this row in the database should be updated when form is submitted
	});

	function escape_name(myname) { 
		return myname.replace(/(\[|\]|:|\.)/g,'\\$1');
	}

	$('a.aios-edit').on('click', function(){
		var target = $(this).parents('tr:first').find('span.aios-editable');
		$(target).click();
		var target = $(this).parents('tr:first').find('input.aios-editable');
		$(target).data('original', $(target).val());
		return false;
	});

	var AIOS_ROWS_TO_ADD = 5;

	$('tr.aios-add-rates-row').hide();

	$('a.aios-add').on('click', function(){
		var last_row = $(this).parents('tbody:first').find('tr:last');
		if ($(last_row).is(':visible')) {
			var row_html = $(last_row).html();
			for(var rows=1; rows<=AIOS_ROWS_TO_ADD; rows++) {
				var new_html = row_html.replace(/\]\[(\d+)\]/g, function(match, last_index){ new_index = parseInt(last_index,10) + rows; return ']['+new_index+']'; });
				$(this).parents('tbody:first').append('<tr>'+new_html+'</tr>');
			}
			$('.list').find('tbody>tr:nth-child(even)').addClass('list-even');
			$('.list').find('tbody>tr:nth-child(odd)').addClass('list-odd');
		} else {
			$(this).parents('tbody:first').find('tr.aios-add-rates-row').show();
		}

		return false;
	});

// Haven't figured this out
//$('a.aios-undo-edit').on('click', function(){
//	var target = $(this).parents('tr:first').find('input.aios-editable');
//	$(target).val($(target).data('original'));
//	return false;
//});

	$('a.aios-remove').on('click', function(){
		var rel		= $(this).attr('rel');
		var target = $('input[name='+escape_name(rel)+']');
		$(target).val(1); // Set flag to indicate that this row in the database should be deleted when form is submitted

		var target = $(this).parents('tr:first');
		$(target).addClass('show-removed');

		var target = $(this).parents('td').children('a.aios-undo-remove');
		$(target).show();
		$(this).hide();

		return false;
	});

	$('a.aios-undo-remove').on('click', function(){
		var rel = $(this).attr('rel');
		// we have a 
		var target = $('input[name='+escape_name(rel)+']');
		$(target).val(0); // Clear flag to indicate that this row in the database should NOT be deleted

		var target = $(this).parents('tr:first');
		$(target).removeClass('show-removed');

		var target = $(this).parents('td').children('a.aios-remove');
		$(target).show();
		$(this).hide();

		return false;
	});

	$('a.aios-undo-remove').hide();

	/* When adding new rows, copy max to min of next row */

	$('input.max-weight').on('change', function(){
		var target = $(this).parents('tr:first').next().find('input.min-weight');
		if (target) {
			$(target).val($(this).val());
			/* bit messy to duplicate this line and hardcode the colour, but will do for now */
			$(target).stop().css('background-color', '#FFFF00').animate({backgroundColor: '#FFFFFF'}, 500);
		}
	});
	$('input.max-value').on('change', function(){
		var target = $(this).parents('tr:first').next().find('input.min-value');
		if (target) {
			$(target).val($(this).val());
			$(target).stop().css('background-color', '#FFFF00').animate({backgroundColor: '#FFFFFF'}, 500);
		}
	});
	$('input.max-items').on('change', function(){
		var target = $(this).parents('tr:first').next().find('input.min-items');
		if (target) {
			$(target).val(parseInt($(this).val())+1);
			$(target).stop().css('background-color', '#FFFF00').animate({backgroundColor: '#FFFFFF'}, 500);
		}
	});

});


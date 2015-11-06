(function($){
	$(document).on('click', '.rowgroup-header .rowgroup-arrow', function(){
		var tr = $(this).closest('tr');
		tr.toggleClass('open');
		var id = tr[0].className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
		
		if ($(tr).is('.open')) {
			// opening: show all subs
			$('.rowgroup-content-'+id).show();
			// opening: hide all subs which are still closed
			$('.rowgroup-header').not('.open').each(function(){
				var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
				$('.rowgroup-content-'+id).hide();
			});
		} else {
			// closing: hide all subs
			$('.rowgroup-content-'+id).hide();
		}
	});
	
	$(document).on('click', '.selectall', function(){
		var tr = $(this).closest('tr');
		var id = tr[0].className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');

		if (!$(tr).is('.open')) {
			tr.addClass('open');

			// opening: show all subs
			$('.rowgroup-content-'+id).show();
			// opening: hide all subs which are still closed
			$('.rowgroup-header').not('.open').each(function(){
				var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
				$('.rowgroup-content-'+id).hide();
			});
		}
		
		if ($('.topiccheckbox-'+id).length == $('.topiccheckbox-'+id+':checked').length) {
			// if all checked, uncheck all
			$('.topiccheckbox-'+id).prop('checked', false);
		} else {
			$('.topiccheckbox-'+id).prop('checked', true);
		}
	});
	
	$(function(){
		var $form = $('#course-selection');

		// reopen selected groups
		$form.find('.rowgroup-content').has(':checkbox:checked').each(function(){
			$.each(this.className.match(/rowgroup-content-([0-9]+)/g), function(tmp, match){
				match.match(/([0-9]+)/);
				var id = RegExp.$1;
				$form.find('.rowgroup-header-'+id).addClass('open');
				$form.find('.rowgroup-content-'+id).show();
			});
		});
		// opening: hide all subs which are still closed
		$('.rowgroup-header').not('.open').each(function(){
			var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
			$('.rowgroup-content-'+id).hide();
		});
		
		
		// submit open groups
		$form.submit(function(){
			if (confirm(M.util.get_string('delete_unconnected_examples', 'block_exacomp'))) {
				// find ids
				var ids = '';
				$form.find('.rowgroup-header.open').each(function(){
					if ($(this).prop('class').match(/rowgroup-header-([0-9]+)/)) {
						ids += ','+RegExp.$1
					}
				});
				
				// save to hidden input
				$form.find('input[name=open_row_groups]').val(ids);
			}else {
				event.preventDefault();
			}
		});
		
		// reopen open groups
		$.each($form.find('input[name=open_row_groups]').val().split(','), function(tmp, id){
			$form.find('.rowgroup-header-'+id).addClass('open');
			$form.find('.rowgroup-content-'+id).show();
		});
		// opening: hide all subs which are still closed
		$('.rowgroup-header').not('.open').each(function(){
			var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
			$('.rowgroup-content-'+id).hide();
		});
	});
})(jQueryExacomp);

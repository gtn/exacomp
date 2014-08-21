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
			tr.toggleClass('open');

			// opening: show all subs
			$('.rowgroup-content-'+id).show();
			// opening: hide all subs which are still closed
			$('.rowgroup-header').not('.open').each(function(){
				var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
				$('.rowgroup-content-'+id).hide();
			});
		}
		
		$('.topiccheckbox-'+id).prop('checked', true);
	});
	
	$(function(){
		var $form = $('#course-selection');

		// submit open groups
		$form.submit(function(){
			
			// find ids
			var ids = '';
			$form.find('.rowgroup-header.open').each(function(){
				if ($(this).prop('class').match(/rowgroup-header-([0-9]+)/)) {
					ids += ','+RegExp.$1
				}
			});
			
			// save to hidden input
			$form.find('input[name=open_row_groups]').val(ids);
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

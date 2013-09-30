
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
	
	$(function(){
		var $form = $('#edit-activities');

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
	});
})(jQueryExacomp);

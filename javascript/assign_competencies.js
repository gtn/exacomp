
(function($){
	window.Exacomp.onlyShowColumnGroup = function(group) {
		if (group === null) {
			$('.colgroup').show();
		} else {
			$('.colgroup').not('.colgroup-'+group).hide();
			$('.colgroup-'+group).show();
		}

		$('.colgroup-button').css('font-weight', 'normal');
		$('.colgroup-button-'+(group===null?'all':group)).css('font-weight', 'bold');
	}
	
	$(document).on('click', '.rowgroup-header .rowgroup-arrow', function(){
		var tr = $(this).closest('tr');
		tr.toggleClass('open');
		
		var id = tr[0].className.replace(/^.*(rowgroup-[0-9]+).*$/, '$1');
		
		$('.rowgroup-header, .rowgroup-content').show();
		$($('.rowgroup-header').not('.open').get().reverse()).each(function(){
			var id = this.className.replace(/^.*(rowgroup-[0-9]+).*$/, '$1');
			console.log(id);
			$('.content-'+id).hide();
		});
	});
	
	$(function(){
	
		var $form = $('#assign-competencies');

		// submit open groups
		$form.submit(function(){
			
			// find ids
			var ids = '';
			$form.find('.rowgroup-header.open').each(function(){
				if ($(this).prop('class').match(/rowgroup-([0-9]+)/)) {
					ids += ','+RegExp.$1
				}
			});
			
			// save to hidden input
			$form.find('input[name=open_row_groups]').val(ids);
		});
		
		// reopen open groups
		$.each($form.find('input[name=open_row_groups]').val().split(','), function(tmp, id){
			$form.find('.rowgroup-header.rowgroup-'+id).addClass('open');
			$form.find('.rowgroup-content-rowgroup-'+id).show();
		});
	});
})(jQueryExacomp);

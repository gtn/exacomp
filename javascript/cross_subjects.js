if (block_exacomp.get_param('action') == 'share') {
	$(document).on('click', 'form#share input[name=share_all]', function(){
		// disable if checked
		$("input[name='studentids[]']").attr('disabled', this.checked);
	});
}

if (block_exacomp.get_param('action') == 'descriptor_selector') {
	$(function(){
		// disable subitems, which also prevents them from getting submitted
		$('table.rg2 :checkbox').click(function(){
			exabis_rg2.get_children(this, true).find(':checkbox')
				.prop('disabled', $(this).is(':checked'))
				.prop('checked', $(this).is(':checked'));
		});
		$('table.rg2 :checkbox:checked').triggerHandler('click');
	});
}

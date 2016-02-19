$(function(){
	$('input[name=uses_activities]').change(function(){
		if ($(this).is(':checked')) {
			$('input[name=show_all_descriptors]').prop('disabled', false);
		} else {
			$('input[name=show_all_descriptors]').prop('disabled', true).prop('checked', true);
		}
	}).triggerHandler('change');
});

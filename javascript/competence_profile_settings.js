
jQueryExacomp(function($){
	$('input[name=useexaport]').change(function(){
		if ($(this).is(':checked')) {
			$("input[name='profile_settings_items[]']").prop('checked', true).prop('disabled', false);
		} else {
			$("input[name='profile_settings_items[]']").prop('disabled', true).prop('checked', false);
		}
	}).change();
	
	$('input[name=useexastud]').change(function(){
		if ($(this).is(':checked')) {
			$("input[name='profile_settings_periods[]']").prop('checked', true).prop('disabled', false);
		} else {
			$("input[name='profile_settings_periods[]']").prop('disabled', true).prop('checked', false);
		}
	}).change();
	
});

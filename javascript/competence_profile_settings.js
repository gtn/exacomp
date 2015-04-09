
jQueryExacomp(function($){
	$('input[name=useexastud]').change(function(){
		if ($(this).is(':checked')) {
			$("input[name='profile_settings_periods[]']").prop('disabled', false);
		} else {
			$("input[name='profile_settings_periods[]']").prop('disabled', true).prop('checked', true);
		}
	}).change();
	$('input[name=usebadges]').change(function(){
		if ($(this).is(':checked')) {
			$("input[name='profile_settings_onlygainedbadges']").prop('disabled', false);
		} else {
			$("input[name='profile_settings_onlygainedbadges']").prop('disabled', true).prop('checked', true);
		}
	}).change();
	
});


jQueryExacomp(function($){
	$('input[name=uses_activities]').change(function(){
		if ($(this).is(':checked')) {
			$('input[name=show_all_descriptors]:disabled').prop('checked', false);
			$('input[name=show_all_descriptors]').prop('disabled', false);
			$('input[name=usedetailpage]').prop('disabled', false);
		} else {
			$('input[name=show_all_descriptors]').prop('disabled', true).prop('checked', true);
			$('input[name=usedetailpage]').prop('disabled', true);
		}
	}).change();
	$('input[name=profoundness]').change(function() {
		//if profoundness is ticked scheme must become 2 and deactivated
		if($(this).is(':checked')) {
			$('input[name=grading]').prop('readonly', true);
			$('input[name=grading]').val(2);
		} 
		//if profoundness is unticked scheme is free for manual change again
		else {
			$('input[name=grading]').prop('readonly', false);
		}
	}).change();
});

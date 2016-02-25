/*
 * copyright exabis
 */

(function($){
	exabis_rg2.options.reopen_checked = true;

	$(function(){
		// submit warning message
		var $form = $('#course-selection');
		$form.submit(function(){
			return confirm(M.util.get_string('delete_unconnected_examples', 'block_exacomp'));
		});
	});

})(jQueryExacomp);

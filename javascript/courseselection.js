jQueryExacomp(function($) {
	//$('input[name=selectall]').click(function() {
		$(document).on('click', '.selectall', function(){
		checkboxes = document.getElementsByClassName('topiccheckbox');
		for ( var i = 0, n = checkboxes.length; i < n; i++) {
			checkboxes[i].checked = true;
		}
	}).click();
	;
});

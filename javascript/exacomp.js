
window.jQueryExacomp = jQuery.noConflict(true);

(function($){
	window.Exacomp = {};

	window.Exacomp.onlyShowColumnGroup = function(group) {
		if (group === null) {
			$('.colgroup').show();
		} else {
			$('.colgroup').not('.colgroup-'+group).hide();
			$('.colgroup-'+group).show();
		}
	}
	
	$('.rowgroup-header .rowgroup-arrow').live('click', function(){
		var tr = $(this).closest('tr');
		tr.toggleClass('open');
		
		var id = tr[0].className.replace(/^.*(rowgroup-[0-9]+).*$/, '$1');
		
		console.log('.rowgroup-content .'+id);
		if (tr.hasClass('open')) {
			$('.rowgroup-content.'+id).show();
		} else {
			$('.rowgroup-content.'+id).hide();
		}
	});
})(jQueryExacomp);

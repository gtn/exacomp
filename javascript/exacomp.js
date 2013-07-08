
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

		$('.colgroup-button').css('font-weight', 'normal');
		$('.colgroup-button-'+(group===null?'all':group)).css('font-weight', 'bold');
	}
	
	$('.rowgroup-header .rowgroup-arrow').on('click', function(){
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

	if ($().tooltip) {
		// only if we have the tooltip function
		$(function(){
			$('.exabis-tooltip').tooltip({
				// retreave content as html
				content: function () {
					return $(this).prop('title');
				}
			});
		});
	}
})(jQueryExacomp);

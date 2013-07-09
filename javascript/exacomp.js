
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
	
	$(document).on('click', '.rowgroup-header .rowgroup-arrow', function(){
		var tr = $(this).closest('tr');
		tr.toggleClass('open');
		
		var id = tr[0].className.replace(/^.*(rowgroup-[0-9]+).*$/, '$1');
		
		if (tr.hasClass('open')) {
			$('.rowgroup-content.'+id).show();
		} else {
			$('.rowgroup-content.'+id).hide();
		}
	});
	
	$(function() {
		$.datepicker.setDefaults({
			showOn: "both",
			buttonImageOnly: true,
			buttonImage: "pix/calendar_alt_stroke_12x12.png",
			buttonText: "Calendar"
		});
		$( ".datepicker" ).datepicker();
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

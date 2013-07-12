
window.jQueryExacomp = jQuery.noConflict(true);

(function($){
	window.Exacomp = {};

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

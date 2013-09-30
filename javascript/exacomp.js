window.jQueryExacomp = jQuery.noConflict(true);

(function($) {
	window.Exacomp = {};

	$(function() {
		// handle: de-du, de, en, en-us,... and strip -du, ...
		var lang = $('html').prop('lang').replace(/\-.*/, '');
		
		$.datepicker.setDefaults({
			dateFormat : 'yy-mm-dd'
		});

		if (lang == 'de') {
			$.datepicker.setDefaults({
				showOn : "both",
				buttonImageOnly : true,
				buttonImage : "pix/calendar_alt_stroke_12x12.png",
				buttonText : "Calendar",
				prevText : '&#x3c;zurück',
				prevStatus : '',
				prevJumpText : '&#x3c;&#x3c;',
				prevJumpStatus : '',
				nextText : 'Vor&#x3e;',
				nextStatus : '',
				nextJumpText : '&#x3e;&#x3e;',
				nextJumpStatus : '',
				currentText : 'heute',
				currentStatus : '',
				todayText : 'heute',
				todayStatus : '',
				clearText : '-',
				clearStatus : '',
				closeText : 'schließen',
				closeStatus : '',
				monthNames : [ 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
						'Juli', 'August', 'September', 'Oktober', 'November',
						'Dezember' ],
				monthNamesShort : [ 'Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun',
						'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez' ],
				dayNames : [ 'Sonntag', 'Montag', 'Dienstag', 'Mittwoch',
						'Donnerstag', 'Freitag', 'Samstag' ],
				dayNamesShort : [ 'So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa' ],
				dayNamesMin : [ 'So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa' ],
				showMonthAfterYear : false,
				showOn : 'both'
			});
		}
		$(".datepicker").datepicker();
	});

	if ($().tooltip) {
		// only if we have the tooltip function
		$(function() {
			$('.exabis-tooltip').tooltip({
				// retreave content as html
				content : function() {
					return $(this).prop('title');
				}
			});
		});
	}
})(jQueryExacomp);

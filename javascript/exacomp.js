
(function($){
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

		// set minDate to today for datepicker-mindate class
		$(".datepicker.datepicker-mindate").datepicker("option", "minDate", 0);
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
	$(document).on('click', '.rowgroup-header .rowgroup-arrow', function(){
		var tr = $(this).closest('tr');
		tr.toggleClass('open');
		
		var id = tr[0].className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
		
		if ($(tr).is('.open')) {
			// opening: show all subs
			$('.rowgroup-content-'+id).show();
			// opening: hide all subs which are still closed
			$('.rowgroup-header').not('.open').each(function(){
				var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
				$('.rowgroup-content-'+id).hide();
			});
		} else {
			// closing: hide all subs
			$('.rowgroup-content-'+id).hide();
		}
	});
	
	$(function(){
		var $form = $('#edit-activities');

		// reopen selected groups
		$form.find('.rowgroup-content').has(':checkbox:checked').each(function(){
			$.each(this.className.match(/rowgroup-content-([0-9]+)/g), function(tmp, match){
				match.match(/([0-9]+)/);
				var id = RegExp.$1;
				$form.find('.rowgroup-header-'+id).addClass('open');
				$form.find('.rowgroup-content-'+id).show();
			});
		});
		// opening: hide all subs which are still closed
		$('.rowgroup-header').not('.open').each(function(){
			var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
			$('.rowgroup-content-'+id).hide();
		});
		
		$('#only_show_file_modules').click(function(){
			var module_type = 'file';
			var $table = $(this).closest('table');
			
			// show/hide columns
			$table.find('.ec_tableheadwidth[module-type!='+module_type+']').toggle();
			$table.find('.rowgroup-content td[module-type][module-type!='+module_type+']').toggle();
			
			// switch button text
			var html = $(this).html();
			$(this).html($(this).attr('alternate-text'));
			$(this).attr('alternate-text', html);
		});
	});
	$(document).on('click', '.selectall', function(){
		checkboxes = document.getElementsByClassName('topiccheckbox');
		for ( var i = 0, n = checkboxes.length; i < n; i++) {
			checkboxes[i].checked = true;
		}
	}).click();
	;
	$(document).on('click', '.switchtextalign', function(){
		if( $( "span[class='rotated-text']" ).length > 0 ) {
		$( "span[class='rotated-text']" ).attr("class","rotated-text-disabled");
		$( "span[class='rotated-text__inner']" ).attr("class","rotated-text__inner_disabled");
		} else {
			$( "span[class='rotated-text-disabled']" ).attr("class","rotated-text");
			$( "span[class='rotated-text__inner_disabled']" ).attr("class","rotated-text__inner");
		}
	});
	
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
		
		var id = tr[0].className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
		
		if ($(tr).is('.open')) {
			// opening: show all subs
			$('.rowgroup-content-'+id).show();
			// opening: hide all subs which are still closed
			$('.rowgroup-header').not('.open').each(function(){
				var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
				$('.rowgroup-content-'+id).hide();
			});
		} else {
			// closing: hide all subs
			$('.rowgroup-content-'+id).hide();
		}
	});

	// update same examples: checkboxes (bewertungsdimensionen == 1)
	$(document).on('click', 'input[name^=dataexamples]', function(){
		var $this = $(this);
		$('input[name="'+$this.attr("name")+'"]').prop('checked', $this.prop('checked'));
	});
	// update same examples: selects (bewertungsdimensionen > 1)
	$(document).on('click', 'select[name^=dataexamples]', function(){
		var $this = $(this);
		$('select[name="'+$this.attr("name")+'"]').val($this.val());
	});

	// called from the add example popup-window, after the example was added
	window.Exacomp.newExampleAdded = function() {
		// reload form by submitting it
		var $form = $('#assign-competencies');
		$form.submit();
	}
	
	$(function(){
		var $form = $('#assign-competencies');

		// submit open groups
		$form.submit(function(){
			
			// find ids
			var ids = '';
			$form.find('.rowgroup-header.open').each(function(){
				if ($(this).prop('class').match(/rowgroup-header-([0-9]+)/)) {
					ids += ','+RegExp.$1
				}
			});
			
			// save to hidden input
			$form.find('input[name=open_row_groups]').val(ids);
		});
		
		// reopen open groups
		$.each($form.find('input[name=open_row_groups]').val().split(','), function(tmp, id){
			$form.find('.rowgroup-header-'+id).addClass('open');
			$form.find('.rowgroup-content-'+id).show();
		});
		// opening: hide all subs which are still closed
		$('.rowgroup-header').not('.open').each(function(){
			var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
			$('.rowgroup-content-'+id).hide();
		});
	});
	$('input[name=uses_activities]').change(function(){
		if ($(this).is(':checked')) {
			$('input[name=show_all_descriptors]:disabled').prop('checked', false);
			$('input[name=show_all_descriptors]').prop('disabled', false);
		} else {
			$('input[name=show_all_descriptors]').prop('disabled', true).prop('checked', true);
		}
	}).change();
})(jQueryExacomp);

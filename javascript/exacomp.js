window.jQueryExacomp = jQuery;

Storage.prototype.setObject = function(key, value) {
    this.setItem(key, JSON.stringify(value));
};
Storage.prototype.getObject = function(key) {
    var value = this.getItem(key);
    return value && JSON.parse(value);
};

window.block_exacomp = {
	get_param: function(name) {
			name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
				results = regex.exec(location.search);
			return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, " "));
	},
	
	call_ajax: function(data, done, error) {
		data.courseid = block_exacomp.get_param('courseid');
		data.sesskey = M.cfg.sesskey;
		
		return $.ajax({
			method : "POST",
			url : "ajax.php",
			data : data
		})
		.done(function(msg) {
			console.log(data.action, 'msg', msg);
			if (done) done(msg);
		}).error(function(msg) {
			console.log("Error: " + data.action, 'msg', msg);
			if (error) error(msg);
		});
	},
	
	popup_iframe: function(config) {
		
		// allow passing of an url
		if (typeof config == 'string') {
			config = {
				url: config
			};
		}
		
		var popup = new M.core.dialogue({
            headerContent: config.headerContent || config.title || 'Popup', // M.str.moodle.loadinghelp, // previousimagelink + '<div id=\"imagenumber\" class=\"imagetitle\"><h1> Image '
            // + screennumber + ' / ' + this.imageidnumbers[imageid] + ' </h1></div>' + nextimagelink,
            
			bodyContent: '<iframe src="'+config.url+'" width="100%" height="100%" frameborder="0"></iframe>',
            visible: true, //by default it is not displayed
            modal: false, // sollte true sein, aber wegen moodle bug springt dann das fenster immer nach oben
            zIndex: 1000,
            // ok: width: '80%',
            // ok: width: '500px',
            // ok: width: null, = automatic
            height: '90%',
            width: '90%',
            // closeButtonTitle: 'clooose'
    	});
		
		this.last_popup = popup;
		
		return popup;
	},
	
	popup_close: function() {
		var parent = window.opener || window.parent;
		
		// close inline popup
		if (parent.block_exacomp.last_popup) {
			parent.block_exacomp.last_popup.hide();
		} else {
			// OR close real window
			window.close();
		}
	},

	popup_close_and_notify: function(func, args) {
		var parent = window.opener || window.parent;
	
		// notify parent
		parent.block_exacomp[func].apply(parent.block_exacomp, args);

		this.popup_close();
	}
};

(function($) {
	$(function() {
		// handle: de-du, de, en, en-us,... and strip -du, ...
		var lang = $('html').prop('lang').replace(/\-.*/, '');
		
		if ($.datepicker) {
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
		}
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
	
	// student selector
	$(function(){
		$('select[name=exacomp_competence_grid_select_student]').change(function(){
			document.location.href = this.getAttribute('data-url') + '&studentid='+this.value;
		});
	});
	
})(jQueryExacomp);

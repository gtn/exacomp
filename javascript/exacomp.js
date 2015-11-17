
(function() {

window.jQueryExacomp = jQuery;
var $ = jQuery;

Storage.prototype.setObject = function(key, value) {
    this.setItem(key, JSON.stringify(value));
};
Storage.prototype.getObject = function(key) {
    var value = this.getItem(key);
    return value && JSON.parse(value);
};

/**
 * $.disablescroll
 * Author: Josh Harrison - aloof.co
 *
 * Disables scroll events from mousewheels, touchmoves and keypresses.
 * Use while jQuery is animating the scroll position for a guaranteed super-smooth ride!
 */(function(e){"use strict";function r(t,n){this.opts=e.extend({handleWheel:!0,handleScrollbar:!0,handleKeys:!0,scrollEventKeys:[32,33,34,35,36,37,38,39,40]},n);this.$container=t;this.$document=e(document);this.lockToScrollPos=[0,0];this.disable()}var t,n;n=r.prototype;n.disable=function(){var e=this;e.opts.handleWheel&&e.$container.on("mousewheel.disablescroll DOMMouseScroll.disablescroll touchmove.disablescroll",e._handleWheel);if(e.opts.handleScrollbar){e.lockToScrollPos=[e.$container.scrollLeft(),e.$container.scrollTop()];e.$container.on("scroll.disablescroll",function(){e._handleScrollbar.call(e)})}e.opts.handleKeys&&e.$document.on("keydown.disablescroll",function(t){e._handleKeydown.call(e,t)})};n.undo=function(){var e=this;e.$container.off(".disablescroll");e.opts.handleKeys&&e.$document.off(".disablescroll")};n._handleWheel=function(e){e.preventDefault()};n._handleScrollbar=function(){this.$container.scrollLeft(this.lockToScrollPos[0]);this.$container.scrollTop(this.lockToScrollPos[1])};n._handleKeydown=function(e){for(var t=0;t<this.opts.scrollEventKeys.length;t++)if(e.keyCode===this.opts.scrollEventKeys[t]){e.preventDefault();return}};e.fn.disablescroll=function(e){!t&&(typeof e=="object"||!e)&&(t=new r(this,e));t&&typeof e=="undefined"?t.disable():t&&t[e]&&t[e].call(t)};window.UserScrollDisabler=r})(jQuery);
 
window.block_exacomp = {
	get_param: function(name) {
			name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
				results = regex.exec(location.search);
			
			return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, " "));
	},
	
	get_studentid: function() {
		studentid = block_exacomp.get_param('studentid');
		
		if(studentid === null)
			studentid = $( "#menuexacomp_competence_grid_select_student" ).val();
		
		return studentid;
	},
	
	call_ajax: function(data) {
		data.courseid = block_exacomp.get_param('courseid');
		data.sesskey = M.cfg.sesskey;
		
		var ajax = $.ajax({
			method : "POST",
			url : "ajax.php",
			data : data
		})
		.done(function(ret) {
			console.log(data.action, 'ret', ret);
		}).error(function(ret) {
			var errorMsg = '';
			if (ret.responseText[0] == '<') {
				// html
				errorMsg = $(ret.responseText).find('.errormessage').text();
			}
			console.log("Error in action '" + data.action +"'", errorMsg, 'ret', ret);
		});
		
		return ajax;
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
            height: '80%',
            width: '85%',
            // closeButtonTitle: 'clooose'
    	});
		
		// disable scrollbars
		$(window).disablescroll();
		
		// hack my own overlay, because moodle dialogue modal is not working
		var overlay = $('<div style="opacity:0.7; filter: alpha(opacity=20); background-color:#000; width:100%; height:100%; z-index:10; top:0; left:0; position:fixed;"></div>')
			.appendTo('body');
		// hide popup when clicking overlay
		overlay.click(function(){
			popup.hide();
		});
		
		
		var orig_hide = popup.hide;
		popup.hide = function() {
			// remove overlay, when hiding popup
			overlay.remove();
			
			// enable scrolling
			$(window).disablescroll('undo');
			
			// call original popup.hide()
			orig_hide.call(popup);
		};

		
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
	
		// first notify parent
		parent.block_exacomp[func].apply(parent.block_exacomp, args);

		// then close popup and unload iframe etc.
		this.popup_close();
	},

	popup_close_and_forward: function(url) {
		var parent = (window.opener || window.parent);

		this.popup_close();
		parent.location.href = url;
	},

	popup_close_and_reload: function() {
		var parent = window.opener || window.parent;

		this.popup_close();
		if (parent.block_exacomp && parent.block_exacomp.reload_action) {
			parent.block_exacomp.reload_action();
		} else {
			parent.location.reload(true);
		}
	}
};

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
	
$(function(){
	$('div[class^=slider\-]').each(function(){
		var sid = $(this).attr('class').split("-")[1];
		var eid = $(this).attr('class').split("-")[2];
		var did = $(this).attr('class').split("-")[3];
		//get select
    	var select = $('select[id=additionalinfo\-'+sid+'\-'+eid+'\-'+did+']');
    	var selects = $('select[id^=additionalinfo\-'+sid+'\-'+eid+']');
		//bind to select
    	$(this).slider({
  	      min: 0,
  	      max: 100,
  	      range: "min",
  	      value: select[ 0 ].selectedIndex + 1,
  	      slide: function( event, ui ) {
  	        select[ 0 ].selectedIndex = ui.value - 1;
  	        selects.each(function(index, value) {
  	        	value.selectedIndex = ui.value - 1;
  	        });
  	      }
  	    });
	});
	
    $( "div[class^=dialog]" ).dialog({autoOpen:false,height: 75});
    
    $('select[id^=additionalinfo\-]').click(function(){
    	var sid = $(this).attr('id').split("-")[1];
    	var eid = $(this).attr('id').split("-")[2];
    	var did = $(this).attr('id').split("-")[3];
    	
    	$('.dialog-'+sid+'-'+eid+'-'+did).dialog('open');
    });
    
    $('select[id^=additionalinfo\-]').change(function() {
    	var sid = $(this).attr('id').split("-")[1];
    	var eid = $(this).attr('id').split("-")[2];
    	var did = $(this).attr('id').split("-")[3];
    	var slider = $('div[id=slider-'+sid+'-'+eid+'-'+did+']');
	    slider.slider( "value", this.selectedIndex + 1 );
	});
});
})();

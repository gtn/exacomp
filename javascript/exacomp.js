/*
 * copyright exabis
 */

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

	get_location: function(params) {
		var url = document.location.href;

		$.each(params, function(name, value){
			name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			var regex = new RegExp("[\\?&](" + name + "=([^&#]*))"),
				results = regex.exec(location.search);

			if (results === null) {
				url += (url.indexOf('?') ? '&' : '?')+name+'='+encodeURIComponent(value);
			} else {
				url = url.replace(results[1], name+'='+encodeURIComponent(value));
			}
		});

		return url;
	},
	
	set_location_params: function(params) {
		document.location.href = this.get_location(params);
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
			height: config.height || '80%',
			width: config.width || '85%',
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
	},
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

	if ($().tooltip) {
		// only if we have the tooltip function
		$('.exabis-tooltip').tooltip({
			// retreave content as html
			content : function() {
				return $(this).prop('title');
			}
		});
	}

	// student selector
	$('select[name=exacomp_competence_grid_select_student]').change(function(){
		block_exacomp.set_location_params({ studentid: this.value });
	});

	// convert exa-tree to rg2
	$('ul.exa-tree').each(function(){
		var $tree = $(this);
		var $table = $('<table class="exabis_comp_comp rg2 '+$tree.attr('class').replace(/exa\-tree/g, 'rg2')+'"></table>');
		$table.insertAfter(this).wrap('<div class="exabis_competencies_lis"></div>');

		$tree.find('li').each(function(){
			var level = $(this).parentsUntil($tree, 'ul').length;
			$('<tr class="'+$(this).attr('class')+' rg2-level-'+level+'"><td class="rg2-arrow rg2-indent"><div></div></td></tr>')
				.appendTo($table).find('div').append($(this).clone().find('>ul').remove().end().contents());
		});

		$table.trigger('rg2.init');
		$table.find('tr.rg2-header').addClass('highlight');

		$tree.remove();
	});

	// show tree menus
	/*
	var trees = $('ul.exa-tree');
	// ddtree needs those, why didn't they do it with css?
	ddtreemenu.closefolder = M.cfg.wwwroot+"/blocks/exacomp/javascript/simpletreemenu/closed.gif";
	ddtreemenu.openfolder = M.cfg.wwwroot+"/blocks/exacomp/javascript/simpletreemenu/open.gif";

	// mark open elements, ddtreemenu opens them automatically
	trees.filter('.exa-tree-reopen-checked').find('ul').has("input[type=checkbox]:checked").attr('rel', 'open');

	// init the trees
	trees.addClass('treeview').each(function(i){
		var id = 'simple-tree-'+i;
		$(this).attr('id', id);
		ddtreemenu.createTree(id, false);
	});
	trees.show();

	// prevent item from open/close when clicking checkbox
	trees.find('input').click(function(e){
		e.stopPropagation();
	});

	// open all
	trees.filter('.exa-tree-open-all').each(function(){
		ddtreemenu.flatten($(this).attr('id'), 'expand');
	});
	/* */
});
	
// collapsible
$(document).on('click', '.exa-collapsible > legend', function(){
	$(this).parent().toggleClass('exa-collapsible-open');
});


// rowgroup2
(function(){

	var options = {
		check_uncheck_parents_children: false,
		reopen_checked: false,
	};
	window.exabis_rg2 = {
		options: options,
		get_row: get_row,
		get_children: get_children,
		get_parents: get_parents,
	};

	var id_i = 0;
	function get_row(dom) {
		var $tr = $(dom).closest('tr.rg2');
		$tr.level = get_level($tr) || 0;
		if ($tr.attr('exa-rg2-id')) {
			$tr.id = $tr.attr('exa-rg2-id');
		} else {
			id_i++;
			$tr.id = 'no-id-'+id_i;
			$tr.attr('exa-rg2-id', $tr.id);
		}
		return $tr;
	}
	function get_level($tr) {
		$tr = $($tr);
		if (!$tr.attr('class')) return null;
		var matches = $tr.attr('class').match(/(^|\s)rg2-level-([0-9]+)(\s|$)/);
		return matches ? parseInt(matches[2]) : null;
	}
	function get_parents(item) {
		var $row = get_row(item),
			level = $row.level - 1,
			parents = [];
		$row.prevAll('tr.rg2').each(function(){
			if (get_row(this).level == level) {
				parents[level] = this;
				level--;
			}
		});
		return $(parents);
	}
	function get_children(item, deep) {
		var $row = get_row(item);
		var children = [];
		$row.nextAll('tr.rg2').each(function(){
			var $child_row = get_row(this);
			if ($child_row.level > $row.level + 1) {
				if (deep) {
					children.push(this);
				}
			} else if ($child_row.level == $row.level + 1) {
				children.push(this);
			} else {
				return false;
			}
		});
		return $(children);
	}
	function get_table_storageid($table) {
		return 'rg2-id-storage-' + ($table.attr('exa-rg2-storageid') || document.location.pathname);
	}
	function update_table($table) {
		var visible_level = 0;

		$table.find('tr.rg2').each(function() {
			var $tr = $(this),
				level = get_level($tr);

			if (level === null) {
				visible_level = 0;
				return;
			}

			if (level+1 === get_level($tr.next())) {
				// is header
				$tr.addClass('rg2-header');
				if (visible_level >= level) {
					if ($tr.is('.open')) {
						visible_level = level + 1;
					} else {
						visible_level = level;
					}
				}
			}

			if (visible_level >= level) {
				$tr.show();
			} else {
				$tr.hide();
			}
		});
	}
	function get_tables() {
		return $('table.rg2');
	}
	function get_table(dom) {
		return $(dom).closest('table.rg2');
	}

	$(document).on('click', '.rg2-header .rg2-arrow', function(event){
		if (event.isDefaultPrevented() || $(event.target).is('input, select') || $(this).closest('.rg2-locked').length) {
			// the click handler on an edit button is called, so don't open/close menu
			return;
		}

		$(this).closest('.rg2-header').toggleClass('open');
		update_table(get_table(this));
	});
	// stop labels in headers to check/uncheck. we just want open/close here!
	$(document).on('click', '.rg2-header label', function(event){
		event.preventDefault();

		$(this).closest('.rg2-header').toggleClass('open');
		update_table(get_table(this));
	});

	$(window).unload(function(){
		// save state before unload
		get_tables().each(function(){
			var ids = $(this).find('.rg2.open:not(.rg2-locked)').map(function(){ return get_row(this).id; }).toArray();
			localStorage.setObject(get_table_storageid($(this)), ids);
		});
	});

	$(document).on('click', '.rg2 .selectallornone', function(){
		$(this).trigger('rg2.open');

		var $children = get_children(this);
		$children.find(':checkbox').prop('checked', $children.find(':checkbox:not(:checked)').length > 0);
	});

	// init
	$(document).on('rg2.init', 'table.rg2', function(){
		var $table = $(this);

		// add class to rows
		$table.find('> tr, > tbody > tr').addClass('rg2');

		$table.on('rg2.update', function(){
			update_table($table);
		});
		$table.on('rg2.open', 'tr.rg2', function(){
			$(this).addClass('open');
			update_table($table);
		});
		$table.on('rg2.close', 'tr.rg2', function(){
			$(this).removeClass('open');
			update_table($table);
		});
		$table.on('rg2.open-parents', 'tr.rg2', function(){
			get_parents(this).addClass('open');
			update_table($table);
		});
		$table.on('rg2.lock', 'tr.rg2', function(){
			$(this).addClass('rg2-locked');
			$(this).removeClass('open');
			$(this).find('.rg2-arrow-disabled').addClass('rg2-arrow').removeClass('rg2-arrow-disabled');

			update_table($table);
		});
		$table.on('rg2.unlock', 'tr.rg2', function(){
			$(this).removeClass('rg2-locked');

			update_table($table);
		});

		$table.find('.rg2-level-0').show();

		if (options.check_uncheck_parents_children || $table.is('.rg2-check_uncheck_parents_children')) {
			$table.find(':checkbox').click(function(){
				get_children(this, true).find(":checkbox").prop('checked', $(this).prop('checked'));
				if (!$(this).prop('checked')) {
					// parents, only for uncheck
					get_parents(this).find(":checkbox").prop('checked', false);
				}
			});
		}

		// reopen saved states
		var ids = localStorage.getObject(get_table_storageid($table));
		if (ids) {
			$.each(ids, function(tmp, id){
				// only open if not locked
				$table.find('.rg2:not(.rg2-locked)[exa-rg2-id="'+id+'"]').addClass('open');
			});
		}

		// reopen checked
		if (options.reopen_checked || $table.is('.rg2-reopen-checked')) {
			$table.find(':checkbox:checked').trigger('rg2.open-parents');
		}

		// reopen checked
		if ($table.is('.rg2-open-all')) {
			$table.find('.rg2-header').addClass('open');
		}

		// close locked
		$table.find('tr.rg2-locked').removeClass('open');

		update_table($table);

		// if just one item, always open and hide arrow
		if ($table.find('.rg2-level-0.rg2-header:not(.rg2-locked)').length == 1) {
			$table.find('.rg2-level-0.rg2-header:not(.rg2-locked)').addClass('open').find('.rg2-arrow').removeClass('rg2-arrow').addClass('rg2-arrow-disabled');
			if ($table.find('.rg2-level-1.rg2-header:not(.rg2-locked)').length == 1) {
				$table.find('.rg2-level-1.rg2-header:not(.rg2-locked)').addClass('open').find('.rg2-arrow').removeClass('rg2-arrow').addClass('rg2-arrow-disabled');
			}
			update_table($table);
		}
	});

	$(function(){
		// add class to tables
		// $('tr.rg2, table.rg2, .rg2-level-0').closest('table').addClass('rg2');

		get_tables().trigger('rg2.init');
	});
})();


})();

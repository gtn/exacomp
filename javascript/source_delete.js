(function($){
	
	$(function(){
		$('.rowgroup-level-0').addClass('open');

		$('table.rowgroup :checkbox').click(function(event){
			// subs
			var row = rowgroup_parse_row(this);
			$('table.rowgroup tr.rowgroup-content-for-'+row.id+' :checkbox')
				.prop('checked', $(this).prop('checked'));

			// parents, only for uncheck
			if (!$(this).prop('checked')) {
				var parents = rowgroup_get_parents(this);
				$.each(parents, function(){
					$(this.dom).find(':checkbox').prop('checked', false);
				})
			}
			
			event.stopPropagation();
		});
		/*
		$('.exabis_comp_teilcomp.rowgroup-header').addClass('rowgroup-level-0');
		$('.exabis_comp_aufgabe.rowgroup-content').addClass('rowgroup-level-1');
		*/
	});
	
	var reopen_selector = ':checkbox:checked';
	// var remember_state = ['action', 'courseid'];
	
	/***********************************************************************/
	
	var id_i = 0;

	$.fn.eachRow = function(f){
		return this.each(function(){
			if (!(row = rowgroup_parse_row(this))) return;
			return f.apply(this, [row]);
		});
	};
	function rowgroup_parse_row(dom) {
		if (dom.tagName != 'TR') {
			dom = $(dom).closest('tr')[0];
		}

		var ret, data = {level: null, id: null, dom: dom};

		if (ret = dom.className.match(/rowgroup-level-([0-9]+)/)) {
			data.level = parseInt(ret[1]);
		} else {
			return;
		}
		
		if (ret = dom.className.match(/rowgroup-id-([^\s]+)/)) {
			data.id = ret[1]; 
		} else {
			id_i++;
			data.id = 'x-'+id_i;
		}
		
		return data;
	}
	
	function rowgroup_get_parents(dom) {
		var row, level, parents = [];
		if (!(row = rowgroup_parse_row(dom))) return;

		level = row.level - 1;
		$(row.dom).prevAll().eachRow(function(row){
			if (row.level == level) {
				parents[level] = row;
				level--;
			}
		});
		return parents;
	}
	
	function rowgroup_open_parents(dom) {
		var parents = rowgroup_get_parents(dom);
		
		$.each(parents, function(){
			$(this.dom).addClass('open');
			level--;
		});
	}
	
	function rowgroup_init() {
		var lastRow, row, levelIds = [];
		$('table.rowgroup tr').each(function(){
			if (!(row = rowgroup_parse_row(this))) return;
			
			levelIds[row.level] = row.id;
			$(this).addClass('rowgroup-id-'+row.id);
			
			if (lastRow && (row.level > lastRow.level)) {
				// it is a header
				$(lastRow.dom).addClass('rowgroup-header');
			}
			
			if (row.level > 0) {
				$(this).addClass('rowgroup-content');
				for (var i = 0; i < row.level; i++) {
					$(this).addClass('rowgroup-content-for-'+levelIds[i]);
				}
			}

			lastRow = row;
		});
	}
	
	function rowgroup_redraw() {
		var levelOpen = [];
		$('table.rowgroup tr').eachRow(function(row){
			if ((row.level == 0 ) || levelOpen[row.level-1]) {
				$(this).show();
			} else {
				$(this).hide();
			}
			
			levelOpen[row.level] = $(this).is('.open:visible');
			for (var i = row.level+1; i <= 10; i++) {
				levelOpen[i] = false;
			}
		});
		
		var ids = [];
		$('table.rowgroup tr.open').eachRow(function(row){
			ids.push(row.id);
		})
		localStorage.setObject(storageid, ids);
	}
	
	$(document).on('click', '.rowgroup-header .rowgroup-arrow', function(){
		$(this).closest('tr').toggleClass('open');
		rowgroup_redraw();
	});
	
	$(document).on('click', '.selectall', function(){
		var row;
		if (!(row = rowgroup_parse_row(this))) return;

		$(row.dom).addClass('open');
		
		var i;
		$(row.dom).nextAll().eachRow(function(sub){
			// finished?
			if (sub.level <= row.level) return false;
			
			if ($(this).is('.rowgroup-header')) $(this).addClass('open');
			
			$(this).find(':checkbox').prop('checked', true);
		});
		
		rowgroup_redraw();
	});
	
	
	var storageid = document.location.href;
	
	$(function(){
		rowgroup_init();

		// reopen selected groups
		if (reopen_selector) {
			$('table.rowgroup tr').has(reopen_selector).each(function(){
				rowgroup_open_parents(this);
			});
		}
		
		// reopen saved states
		var ids;
		if (ids = localStorage.getObject(storageid)) {
			$.each(ids, function(tmp, id){
				$('table.rowgroup tr.rowgroup-id-'+id).addClass('open');
			});
		}

		rowgroup_redraw();
	});
	
})(jQueryExacomp);

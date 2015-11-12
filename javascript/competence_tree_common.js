
(function($){
	var storageid = document.location.pathname;
	
	window.block_exacomp.reload_action = function() {
		i_want_my_reload = true;
		$('#assign-competencies input[type="submit"]').click();
	}

	$( window ).load(function() {
		var group = block_exacomp.get_param('group');
		block_exacomp.onlyShowColumnGroup(group);
	});
	$(document).on('click', '.colgroup-button', function(){
		block_exacomp.onlyShowColumnGroup(this.getAttribute('exa-groupid'));
		return false;
	});
	window.block_exacomp.onlyShowColumnGroup = function(group) {
		// block_exacomp.current_colgroup_id = group;
		
		if(group == -2) {
			$('.colgroup').not('.colgroup-5555').hide();
		}
		if (!group) {
			group = 0;
			$('.colgroup').not('.colgroup-0').hide();
			$('.colgroup-0').show();
		} else if(group== -1){
			// show all
			$('.colgroup').show();
		} else{
			$('.colgroup').not('.colgroup-'+group).hide();
			$('.colgroup-'+group).show();
		}

		//chage form 
		$('#assign-competencies').attr('action', function(i, value) {
			//if group is contained -> change value
			if(value.indexOf("group") > -1){
				value = value.substr(0, value.indexOf("group")+6);
				return value + group;
			}
			return value + "&group=" + group;
		});

		// change url
		history.replaceState(null, null, location.href.replace(/&group=[^&]*/i, '') + "&group=" + group);

		$('.colgroup-button').css('font-weight', 'normal');
		$('.colgroup-button[exa-groupid='+(group*1)+']').css('font-weight', 'bold');
	}
	
	$(document).on('click', '.rowgroup-header .rowgroup-arrow', function(event){
		if (event.isDefaultPrevented()) {
			// the click handler on an edit button is called, so don't open/close menu
			return;
		}
		
		var tr = $(this).closest('tr');
		//only show subs if descriptor is not hidden
		if(!$(tr).is('.hidden_temp')){	
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
			
			var ids = [];
			$('#assign-competencies').find('.rowgroup-header.open').each(function(){
				var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
				ids.push(id);
			});
			localStorage.setObject(storageid, ids);
		}
	});

	// update same examples: checkboxes (bewertungsdimensionen == 1)
	$(document).on('click', 'input[name^=dataexamples]', function(){
		var $this = $(this);
		$('input[name="'+$this.attr("name")+'"]').prop('checked', $this.prop('checked'));
	});
	// update same examples: selects (bewertungsdimensionen > 1)
	$(document).on('change', 'select[name^=dataexamples]', function(){
		var $this = $(this);
		$('select[name="'+$this.attr("name")+'"]').val($this.val());
	});
	$(document).on('change', 'input[name^=dataexamples]', function(){
		var $this = $(this);
		$('input[name="'+$this.attr("name")+'"]').val($this.val());
	});
	
	$(function(){
		var $form = $('#assign-competencies');

		// reopen selected groups
		$form.find('.rowgroup-content').has(':checkbox:checked').each(function(){
			$.each(this.className.match(/rowgroup-content-([0-9]+)/g), function(tmp, match){
				match.match(/([0-9]+)/);
				var id = RegExp.$1;
				$form.find('.rowgroup-header-'+id).addClass('open');
				$form.find('.rowgroup-content-'+id).show();
			});
		});
		// reopen saved states
		var ids;
		if (ids = localStorage.getObject(storageid)) {
			$.each(ids, function(tmp, id){
				// only open if not hidden?
				if(!$form.find('.rowgroup-header-'+id).hasClass('hidden_temp')) {
					$form.find('.rowgroup-header-'+id).addClass('open');
					$form.find('.rowgroup-content-'+id).show();
				}
			});
		}
		// opening: hide all subs which are still closed
		$('.rowgroup-header').not('.open').each(function(){
			var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
			$('.rowgroup-content-'+id).hide();
		});
		
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
			if(!$form.find('.rowgroup-header-'+id).hasClass('hidden_temp')) {
				$form.find('.rowgroup-header-'+id).addClass('open');
				$form.find('.rowgroup-content-'+id).show();
			}
		});
		// opening: hide all subs which are still closed
		$('.rowgroup-header').not('.open').each(function(){
			var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
			$('.rowgroup-content-'+id).hide();
		});
	});
})(jQueryExacomp);

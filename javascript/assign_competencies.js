
(function($){
	var storageid = document.location.pathname;

	$( window ).load(function() {
		var group = block_exacomp.get_param('group');
		block_exacomp.onlyShowColumnGroup(group);
	});
	window.block_exacomp.onlyShowColumnGroup = function(group) {
		if(group == -2) {
			$('.colgroup').not('.colgroup-5555').hide();
		}
		if (group === null || group==0) {
			$('.colgroup').not('.colgroup-0').hide();
			$('.colgroup-0').show();
			//chage form 
			$('#assign-competencies').attr('action', function(i, value) {
				//if group is contained -> change value
				if(value.indexOf("group") > -1){
					value = value.substr(0, value.indexOf("group")+6);
					return value + "0";
				}
				return value + "&group=0";
			});
			//change onchange from selects
			/*
			var value = String(document.getElementById('menulis_subjects').onchange);
			value = value.substr(value.indexOf('href')+6);
			if(value.indexOf("group") > -1){
				value = value.substr(0, value.indexOf("group")+6);
				value = value + "0";
			}else{
				value = value.substr(0, value.length-3);
				value = value + '+\'&group=0';
			}
			value = "document.location.href='"+value+"';";
			$("#menulis_subjects")[0].setAttribute("onchange", value);
			
			var value = String(document.getElementById('menulis_topics').onchange);
			value = value.substr(value.indexOf('href')+6);
			if(value.indexOf("group") > -1){
				value = value.substr(0, value.indexOf("group")+6);
				value = value + "0";
			}else{
				value = value.substr(0, value.length-3);
				value = value + '+\'&group=0';
			}
			value = "document.location.href='"+value+"';";
			$("#menulis_topics")[0].setAttribute("onchange", value);*/
		} else if(group== -1){
			$('.colgroup').show();
			$('#assign-competencies').attr('action', function(i, value) {
				//only append if action does not contain group already
				//if group is contained -> change value
				if(value.indexOf("group") > -1){
					value = value.substr(0, value.indexOf("group")+6);
					return value + "-1";
				}
			    return value + "&group=-1";
			});
			//change onchange from selects
			var value = String(document.getElementById('menulis_subjects').onchange);
			value = value.substr(value.indexOf('href')+6);
			if(value.indexOf("group") > -1){
				value = value.substr(0, value.indexOf("group")+6);
				value = value + "-1";
			}else{
				value = value.substr(0, value.length-3);
				value = value + '+\'&group=-1';
			}
			value = "document.location.href='"+value+"';";
			$("#menulis_subjects")[0].setAttribute("onchange", value);
			
			var value = String(document.getElementById('menulis_topics').onchange);
			value = value.substr(value.indexOf('href')+6);
			if(value.indexOf("group") > -1){
				value = value.substr(0, value.indexOf("group")+6);
				value = value + "-1";
			}else{
				value = value.substr(0, value.length-3);
				value = value + '+\'&group=-1';
			}
			value = "document.location.href='"+value+"';";
			$("#menulis_topics")[0].setAttribute("onchange", value);
		} else{
			$('.colgroup').not('.colgroup-'+group).hide();
			$('.colgroup-'+group).show();
			$('#assign-competencies').attr('action', function(i, value) {
				//only append if action does not contain group already
				//if group is contained -> change value
				if(value.indexOf("group") > -1){
					value = value.substr(0, value.indexOf("group")+6);
					return value + "1";
				}
			    return value + "&group=1";
			});
			//change onchange from selects
			var value = String(document.getElementById('menulis_subjects').onchange);
			value = value.substr(value.indexOf('href')+6);
			if(value.indexOf("group") > -1){
				value = value.substr(0, value.indexOf("group")+6);
				value = value + "1";
			}else{
				value = value.substr(0, value.length-3);
				value = value + '+\'&group=1';
			}
			value = "document.location.href='"+value+"';";
			$("#menulis_subjects")[0].setAttribute("onchange", value);
			
			var value = String(document.getElementById('menulis_topics').onchange);
			value = value.substr(value.indexOf('href')+6);
			if(value.indexOf("group") > -1){
				value = value.substr(0, value.indexOf("group")+6);
				value = value + "1";
			}else{
				value = value.substr(0, value.length-3);
				value = value + '+\'&group=1';
			}
			value = "document.location.href='"+value+"';";
			$("#menulis_topics")[0].setAttribute("onchange", value);
		}

		$('.colgroup-button').css('font-weight', 'normal');
		$('.colgroup-button-'+(group===null?'0':(group==(-1)?'all':group))).css('font-weight', 'bold');
	}
	$(document).on('click', '.rowgroup-header .rowgroup-arrow', function(){
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
	
	// called from the add example popup-window, after the example was added
	window.block_exacomp.newExampleAdded = function() {
		// reload form by submitting it
		var $form = $('#assign-competencies');
		$form.submit();
	}
	
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

(function($){
	// not needed anymore?
	/*
	$( window ).load(function() {
		var group = block_exacomp.get_param('group');
		block_exacomp.onlyShowColumnGroup(group);
	});
	window.block_exacomp.onlyShowColumnGroup = function(group) {
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
			var value = document.getElementById('menulis_subjects') ? String(document.getElementById('menulis_subjects').onchange) : '';
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
			$("#menulis_topics")[0].setAttribute("onchange", value);
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
	*/

	// uncheck all other checkboxes with the same name
	$(document).on('click', 'input[name^=data]', function(){
		$('input[name="'+$(this).attr("name")+'"]').not(this).prop('checked', false);
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
	
})(jQueryExacomp);

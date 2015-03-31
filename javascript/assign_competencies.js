
(function($){
	$( window ).load(function() {
		var group = Exacomp.getParameterByName('group');
		Exacomp.onlyShowColumnGroup(group);
		
		var competencies = new Array();
	});
	window.Exacomp.onlyShowColumnGroup = function(group) {
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
	window.Exacomp.getParameterByName = function(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
	        results = regex.exec(location.search);
	    return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, " "));
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
	$(document).on('change', 'select[name^=dataexamples]', function(){
		var $this = $(this);
		$('select[name="'+$this.attr("name")+'"]').val($this.val());
	});
	$(document).on('change', 'input[name^=dataexamples]', function(){
		var $this = $(this);
		$('input[name="'+$this.attr("name")+'"]').val($this.val());
	});
	
	// called from the add example popup-window, after the example was added
	window.Exacomp.newExampleAdded = function() {
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
			$form.find('.rowgroup-header-'+id).addClass('open');
			$form.find('.rowgroup-content-'+id).show();
		});
		// opening: hide all subs which are still closed
		$('.rowgroup-header').not('.open').each(function(){
			var id = this.className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
			$('.rowgroup-content-'+id).hide();
		});
	});
	// ## AJAX
	// # COMPETENCIES
	var competencies = [];
	$(document).on('click', 'input[name^=data\-]', function(){
		var values = $(this).attr("name").split("-");
		
		if($(this).prop("checked"))
			competencies[competencies.length] = {userid: values[2], compid: values[1], value: 1};
		else
			competencies[competencies.length] = {userid: values[2], compid: values[1], value: 0};
	});
	$(document).on('change', 'select[name^=data\-]', function(){
		var values = $(this).attr("name").split("-");
		competencies[competencies.length] = {userid: values[2], compid: values[1], value: $(this).val()};
	});
	// # TOPICS
	var topics = [];
	$(document).on('click', 'input[name^=datatopics\-]', function(){
		var values = $(this).attr("name").split("-");
		
		if($(this).prop("checked"))
			topics[topics.length] = {userid: values[2], compid: values[1], value: 1};
		else
			topics[topics.length] = {userid: values[2], compid: values[1], value: 0};
	});
	$(document).on('change', 'select[name^=datatopics\-]', function(){
		var values = $(this).attr("name").split("-");
		topics[topics.length] = {userid: values[2], compid: values[1], value: $(this).val()};
	});
	// # EXAMPLES
	var examples = [];
	$(document).on('click', 'input[name^=dataexamples\-]', function(){
		var values = $(this).attr("name").split("-");
		
		if($(this).prop("checked"))
			examples[examples.length] = {userid: values[2], exampleid: values[1], value: 1};
		else
			examples[examples.length] = {userid: values[2], exampleid: values[1], value: 0};
	});
	$(document).on('change', 'select[name^=dataexamples\-]', function(){
		var values = $(this).attr("name").split("-");
		
		if(values[3] == 'studypartner')
			examples[examples.length] = {userid: values[2], exampleid: values[1], studypartner: $(this).val()};
		else if(values[3] == 'starttime')
			examples[examples.length] = {userid: values[2], exampleid: values[1], starttime: $(this).val()};
		else if(values[3] == 'endtime')
			examples[examples.length] = {userid: values[2], exampleid: values[1], endtime: $(this).val()};
		else
			examples[examples.length] = {userid: values[2], exampleid: values[1], value: $(this).val()};

	});
	
	$(document).on('submit', '#assign-competencies', function(event){
		courseid = getUrlVars()['courseid'];
		  competencies.forEach(function(entry) {
			    $.ajax({
					  method: "POST",
					  url: "ajax.php",
					  data: { userid: entry['userid'], compid: entry['compid'], courseid: courseid, value: entry['value'], comptype: 0 }
					})
					  .done(function( msg ) {
					    console.log( "Competence Saved: " + msg );
					  });
			});
		  competencies = [];
		  topics.forEach(function(entry) {
			    $.ajax({
					  method: "POST",
					  url: "ajax.php",
					  data: { userid: entry['userid'], compid: entry['compid'], courseid: courseid, value: entry['value'], comptype: 1 }
					})
					  .done(function( msg ) {
					    console.log( "Topic Saved: " + msg );
					  });
			});
		  topics = [];
		  examples.forEach(function(entry) {
			    $.ajax({
					  method: "POST",
					  url: "ajax.php",
					  data: { userid: entry['userid'], exampleid: entry['exampleid'], courseid: courseid, value: entry['value'], action: 'example', studypartner: entry['studypartner'] }
					})
					  .done(function( msg ) {
					    console.log( "Example Saved: " + msg );
					  });
			});
		  examples = [];
		  
		  event.preventDefault();
		});
	
	
	// Read a page's GET URL variables and return them as an associative array.
	function getUrlVars()
	{
	    var vars = [], hash;
	    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	    for(var i = 0; i < hashes.length; i++)
	    {
	        hash = hashes[i].split('=');
	        vars.push(hash[0]);
	        vars[hash[0]] = hash[1];
	    }
	    return vars;
	}
	$(window).bind('beforeunload', function(){
		if(competencies.length > 0 || topics.length > 0 || examples.length > 0)
		  return 'Ungespeicherte Änderungen gehen verloren';
	});
})(jQueryExacomp);

// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competence Grid is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

(function($) {

	// ## AJAX
	// # COMPETENCIES
	//cannot hide anymore, as soon as competency is checked
	var competencies = {};
	
	var prev_val;

	// TODO: add additional_grading info to competencies array!
	var competencies_additional_grading = {};
	var topics_additional_grading = {};
	var subjects_additional_grading = {};
	var crosssubs_additional_grading = {};

	$(document).on('focus', 'input[name^=datadescriptors\-]', function() {
		prev_val = $(this).val();
	});
	
	$(document).on('change', 'input[name^=datadescriptors\-]', function() {
		
		// check if anyone else has edited the competence before. if so, ask for confirmation
		if($(this).attr("reviewerid")) {
			if (!confirm(M.util.get_string('override_notice', 'block_exacomp'))) {
				$(this).prop("checked",prev_val);
				return;
			}
			else {
				//remove reviewer attribute
				$(this).removeAttr("reviewerid");
			}
		}
		
		var tr = $(this).closest('tr');
		var hide = $(tr).find('input[name~="hide-descriptor"]');

		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');	
		var niveauid = $('select[name=niveau_descriptor-'+compid+'-'+userid).val();

		if ($(this).prop("checked")) {
			if (competencies[compid + "-" + userid]) {
				competencies[compid + "-" + userid].value = $(this).val();
			} else {
				competencies[compid + "-" + userid] = {
					userid : userid,
					compid : compid,
					value : $(this).val(),
					niveauid : niveauid
				};
			}
			//check comp->hide descriptor not possible
			hide.addClass("hidden");
		} else {
			if (competencies[compid + "-" + userid]) {
				competencies[compid + "-" + userid].value = -1;
			} else {
				competencies[compid + "-" + userid] = {
					userid : userid,
					compid : compid,
					value : 0,
					niveauid : niveauid
				};
			}
			//uncheck comp -> hide possible again
			hide.removeClass("hidden");
		}
		
	});
	$(document).on('focus', 'select[name^=datadescriptors\-]', function() {
		prev_val = $(this).val();
	});
	
	$(document).on('change', 'select[name^=datadescriptors\-]', function() {
		// check if anyone else has edited the competence before. if so, ask for
		// confirmation
		if ($(this).attr("reviewerid")) {
			if (!confirm(M.util.get_string('override_notice', 'block_exacomp'))) {
				$(this).val(prev_val);
					return;
			} else {
			// remove reviewer attribute
				$(this).removeAttr("reviewerid");
			}
		}
			
		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');	
		var niveauid = $('select[name=niveau_descriptor-'+compid+'-'+userid).val();

		if(!competencies[compid + "-" + userid])
			competencies[compid + "-" + userid] = {
					userid : this.getAttribute('exa-userid'),
					compid : this.getAttribute('exa-compid'),
					value : $(this).val(),
					niveauid : niveauid
			};
		else
			competencies[compid + "-" + userid]['value'] = $(this).val();
	});
	
	$(document).on('change', 'select[name^=niveau_descriptor\-]', function(event) {
		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');		
		var niveauid = $(this).val();
		var value = $('select[name=datadescriptors-'+compid+'-'+userid+'-teacher').val();
		// in case of checkboxes instead of selects:
		if(value === undefined)
			if ($('input[name=datadescriptors-'+compid+'-'+userid+'-teacher').prop("checked"))
				value = $('input[name=datadescriptors-'+compid+'-'+userid+'-teacher').val();
			else
				value = -1;
			
		if(!competencies[compid + "-" + userid]) {
			competencies[compid + "-" + userid] = {
					userid : userid,
					compid : compid,
					value : value,
					niveauid : niveauid
				};
		}
		else
			competencies[compid + "-" + userid]['niveauid'] = niveauid;
	});

	$(document).on('change', 'input[name^=add-grading\-]', function(event) {
		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');	
		var value = $(this).val();
		
		value = value.replace(",", ".");
		
		// check for valid grading input range
		if (value > 6.0) {
			alert(M.util.get_string('value_too_large','block_exacomp'));
			value = null;
			$(this).val(value);
		}
		else if(value < 1.0 && value != "") {
			alert(M.util.get_string('value_too_low','block_exacomp'));
			value = null
			$(this).val(value);
		} else if(value != "" && !$.isNumeric(value)) {
			alert(M.util.get_string('value_not_allowed','block_exacomp'));
			value = null
			$(this).val(value);
		}
		
		var type = this.getAttribute('exa-type');
		if(type == 0) {
			if(!competencies_additional_grading[compid])
				competencies_additional_grading[compid] = {};
			competencies_additional_grading[compid][userid] = value;
		} else if(type == 1) {
			if(!topics_additional_grading[compid])
				topics_additional_grading[compid] = {};
			topics_additional_grading[compid][userid] = value;
		} else if(type == 2) {
			if(!crosssubs_additional_grading[compid])
				crosssubs_additional_grading[compid] = {};
			crosssubs_additional_grading[compid][userid] = value;
		} else if(type == 3) {
			if(!subjects_additional_grading[compid])
				subjects_additional_grading[compid] = {};
			subjects_additional_grading[compid][userid] = value;
		} 
	});
	
	// # TOPICS
	var topics = {};
	$(document).on('click', 'input[name^=datatopics\-]', function() {
		var id = this.getAttribute('exa-compid')+"-"+this.getAttribute('exa-userid');
		
		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');	
		var niveauid = $('select[name=niveau_topic-'+compid+'-'+userid+']').val();
		
		if ($(this).prop("checked")) {
			if (topics[this.name]) {
				topics[this.name].value = $(this).val();
			} else
				topics[this.name] = {
					userid : this.getAttribute('exa-userid'),
					compid : this.getAttribute('exa-compid'),
					value : $(this).val(),
					niveauid : niveauid
				};
		} else {
			if (topics[this.name])
				topics[this.name].value = -1;
			else
				topics[this.name] = {
					userid : this.getAttribute('exa-userid'),
					compid : this.getAttribute('exa-compid'),
					value : 0,
					niveauid : niveauid
				};
		}
	});
	$(document).on('change', 'select[name^=datatopics\-]', function() {
		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');	
		var niveauid = $('select[name=niveau_topic-'+compid+'-'+userid+']').val();
		
		if(!topics[this.name]) {
			topics[this.name] = {
				userid : userid,
				compid : compid,
				value : $(this).val(),
				niveauid : niveauid
			};
		} else
			topics[this.name]['value'] = $(this).val();
	});
	
	$(document).on('change', 'select[name^=niveau_topic\-]', function(event) {
		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');		
		var niveauid = $(this).val();
		var name = 'datatopics-'+compid+'-'+userid+'-teacher';
		var value = $('select[name='+name+']').val();
		
		// in case of checkboxes instead of selects:
		if(value === undefined)
			if ($('input[name='+name+']').prop("checked"))
				value = $('input[name='+name+']').val();
			else
				value = -1;
		
		if(!topics[name]) {
			topics[name] = {
					userid : userid,
					compid : compid,
					value : value,
					niveauid : niveauid
				};
		}
		else
			topics[name]['niveauid'] = niveauid;
	});
	// # SUBJECTS
	var subjects = {}
	$(document).on('click', 'input[name^=datasubjects\-]', function() {
		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');	
		var niveauid = $('select[name=niveau_subject-'+compid+'-'+userid+']').val();
		
		if ($(this).prop("checked")) {
			if (subjects[this.name]) {
				subjects[this.name].value = $(this).val();
			} else
				subjects[this.name] = {
					userid : this.getAttribute('exa-userid'),
					compid : this.getAttribute('exa-compid'),
					value : $(this).val(),
					niveauid : niveauid
				};
		} else {
			if (subjects[this.name])
				subjects[this.name].value = -1;
			else
				subjects[this.name] = {
					userid : this.getAttribute('exa-userid'),
					compid : this.getAttribute('exa-compid'),
					value : 0,
					niveauid : niveauid
				};
		}
	});
	$(document).on('change', 'select[name^=datasubjects\-]', function() {
		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');	
		var niveauid = $('select[name=niveau_subject-'+compid+'-'+userid).val();
		
		if(!subjects[this.name]) {
			subjects[this.name] = {
				userid : userid,
				compid : compid,
				value : $(this).val(),
				niveauid : niveauid
			};
		} else
			subjects[this.name]['value'] = $(this).val();
		
	});
	
	$(document).on('change', 'select[name^=niveau_subject\-]', function(event) {
		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');		
		var niveauid = $(this).val();
		var name = 'datasubjects-'+compid+'-'+userid+'-teacher';
		var value = $('select[name='+name+']').val();
		
		// in case of checkboxes instead of selects:
		if(value === undefined)
			if ($('input[name='+name+']').prop("checked"))
				value = $('input[name='+name+']').val();
			else
				value = -1;
		
		if(!subjects[name]) {
			subjects[name] = {
					userid : userid,
					compid : compid,
					niveauid : niveauid,
					value : value
				};
		}
		else
			subjects[name]['niveauid'] = niveauid;
	});
	
	//#CROSSSUBJECTS
	var crosssubs = {}
	
	$(document).on('change', 'select[name^=niveau_crosssub\-]', function(event) {
		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');		
		var niveauid = $(this).val();
		var name = 'datacrosssubs-'+compid+'-'+userid+'-teacher';
		var value = $('select[name='+name+']').val();
		
		// in case of checkboxes instead of selects:
		if(value === undefined)
			if ($('input[name='+name+']').prop("checked"))
				value = $('input[name='+name+']').val();
			else
				value = -1;
		
		if(!crosssubs[name]) {
			crosssubs[name] = {
					userid : userid,
					compid : compid,
					niveauid : niveauid,
					value : value
				};
		}
		else
			crosssubs[name]['niveauid'] = niveauid;
	});
	$(document).on('click', 'input[name^=datacrosssubs\-]', function() {
		var values = $(this).attr("name").split("-");
		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');	
		var niveauid = $('select[name=niveau_crosssub-'+compid+'-'+userid+']').val();
		if ($(this).prop("checked")) {
			if (crosssubs[this.name]) {
				crosssubs[this.name].value = $(this).val();
			} else
				crosssubs[this.name] = {
					userid : values[2],
					compid : values[1],
					value : $(this).val(),
					niveauid : niveauid
				};
		} else {
			if (crosssubs[this.name])
				crosssubs[this.name].value = 0;
			else
				crosssubs[this.name] = {
					userid : values[2],
					compid : values[1],
					value : -1,
					niveauid : niveauid
				};
		}
	});
	$(document).on('change', 'select[name^=datacrosssubs\-]', function() {
		var compid = this.getAttribute('exa-compid');
		var userid = this.getAttribute('exa-userid');	
		var niveauid = $('select[name=niveau_crosssub-'+compid+'-'+userid+']').val();
		var values = $(this).attr("name").split("-");
		crosssubs[this.name] = {
			userid : values[2],
			compid : values[1],
			value : $(this).val(),
			niveauid : niveauid
		};
	});
	
	// # EXAMPLES
	var examples = {};
	$(document).on('click', 'input[name^=dataexamples\-]', function() {
		var values = $(this).attr("name").split("-");
		var tr = $(this).closest('tr');
		var hide = $(tr).find('input[name~="hide-descriptor"]');
		
		examples[this.name] = {
			userid : values[2],
			exampleid : values[1],
			value : $(this).prop("checked")
		};
		if ($(this).prop("checked")) {
			//check comp->hide descriptor not possible
			hide.addClass("hidden");
		} else {
			//uncheck comp -> hide possible again
			hide.removeClass("hidden");
		}
	});
	$(document).on('change', 'select[name^=dataexamples\-]', function() {
		var values = $(this).attr("name").split("-");
		if(!examples[this.name]) {
			// get current niveau
			var niveauid = $('select[name=niveau_examples-'+values[1]+'-'+values[2]).val();
			examples[this.name] = {
				userid : values[2],
				exampleid : values[1],
				value : $(this).val(),
				niveauid : niveauid
			};
		}
		else
			examples[this.name]['value'] = $(this).val();
	});
	$(document).on('change', 'select[name^=niveau_examples\-]', function(event) {
		
		var name = this.name.replace("niveau_","data") + "-teacher"
		var niveauid = $(this).val();
		
		if(!examples[name]) {
			var values = name.split("-");

			var value = $('select[name='+name+']').val();
			
			examples[name] = {
					userid : values[2],
					exampleid : values[1],
					value : value,
					niveauid : niveauid
				};
		}
		else
			examples[name]['niveauid'] = niveauid;
	});
	$(document).on('keydown', ':text[exa-type="new-descriptor"]', function(event) {
		if (event.keyCode == 13) {
			// enter
			$(this).siblings(':button[exa-type="new-descriptor"]').trigger('click');
		}
	});
	$(document).on('click', ':button[exa-type="new-descriptor"]', function() {
		var $input = $(this).siblings(':text');
		if (!$input.length) {
			alert('Error: input not found');
		}

		var value = this.value.trim();
		if (!value) {
			return;
		}

		block_exacomp.call_ajax({
			action: 'multi',
			// send data as json, because php max input setting
			data: JSON.stringify({new_descriptors: [{
				parentid: $input.attr('parentid'),
				topicid: $input.attr('topicid'),
				niveauid: $input.attr('niveauid'),
				title: $input.val(),
			}]}),
		}).done(function() {
			location.reload();
		});
	});

	// global var hack
	i_want_my_reload = false;
	
	$(document).on('click', '#assign-competencies input[type=submit], #assign-competencies input[type=button]', function(event) {
		if ($(this).is('.allow-submit')) return;
		event.preventDefault();
		var courseid = block_exacomp.get_param('courseid');

		// only for crosssubjects
		var crosssubjid = block_exacomp.get_param('crosssubjid');

		switch ($(this).attr('id')) {
		case 'btn_submit':
			var reload = i_want_my_reload;

			function all_done() {
				if (reload) {
					location.reload();
				} else {
					document.location.href='#';
					alert('Änderungen wurden gespeichert!');
				}
			}
			
			var multiQueryData = {};
			
			if (!$.isEmptyObject(examples)) {
				multiQueryData.examples = examples;
				examples = {};
			}

			var competencies_by_type = [];
			
			if (!$.isEmptyObject(competencies)) {
				competencies_by_type[0] = competencies;
				competencies = {};
			}

			if (!$.isEmptyObject(topics)) {
				competencies_by_type[1] = topics;
				topics = {};
			}
			
			if (!$.isEmptyObject(crosssubs)) {
				competencies_by_type[2] = crosssubs;
				crosssubs = {};
			}
			
			if (!$.isEmptyObject(subjects)) {
				competencies_by_type[3] = subjects;
				subjects = {};
			}
			
			if (competencies_by_type.length) {
				multiQueryData.competencies_by_type = competencies_by_type
			}
			
			if(!$.isEmptyObject(competencies_additional_grading)){
				multiQueryData.competencies_additional_grading = competencies_additional_grading;
			}

			if(!$.isEmptyObject(topics_additional_grading)){
				multiQueryData.topics_additional_grading = topics_additional_grading;
			}
			
			if(!$.isEmptyObject(crosssubs_additional_grading)){
				multiQueryData.crosssubs_additional_grading = crosssubs_additional_grading;
			}
			
			if(!$.isEmptyObject(subjects_additional_grading)){
				multiQueryData.subjects_additional_grading = subjects_additional_grading;
			}
			
			if (!$.isEmptyObject(multiQueryData)) {
				block_exacomp.call_ajax({
					action: 'multi',
					// send data as json, because php max input setting
					data: JSON.stringify(multiQueryData)
				}).done(function(msg) {
					all_done();
				});
			} else {
				all_done();
			}
			
			break;
		/*
		case 'save_as_draft':
			if (crosssubjid > 0) {
				block_exacomp.call_ajax({
					crosssubjid : crosssubjid,
					action : 'save_as_draft'
				});
			}
			event.preventDefault();
			alert("Thema wurde als Vorlage gespeichert!");
			break;
		case 'delete_crosssub':
			message = $(this).attr('message');
			if (confirm(message)) {
				block_exacomp.call_ajax({
					crosssubjid : crosssubjid,
					action : 'delete-crosssubject'
				}).done(function(msg) {
					location.href = 'cross_subjects_overview.php?courseid='+block_exacomp.get_param('courseid');
				});
			} 
			break;
		*/
		}

		return false;
	});

	// Add Descriptor to crosssubjects
	$(document).on('click', '#crosssubjects', function(event) {
		event.preventDefault();
		var crosssubjects = [];
		var not_crosssubjects = [];
		var descrid = block_exacomp.get_param('descrid');
		$("input[name='crosssubject']").each(function() {
			if (this.checked == "1")
				crosssubjects.push($(this).val());
			else
				not_crosssubjects.push($(this).val());
		});

		block_exacomp.call_ajax({
			crosssubjects : JSON.stringify(crosssubjects),
			not_crosssubjects : JSON.stringify(not_crosssubjects),
			descrid : descrid,
			action : 'crosssubj-descriptors',
		}).done(function(msg) {
			block_exacomp.popup_close_and_reload();
		});
	});

	$(document).on('click', 'a[id^=competence-grid-link]', function(event) {
		if($(this).hasClass('deactivated')){
			event.preventDefault();
		}
	});
	
	$(document).keydown(function(event) {
		console.log(event.which);
		if ( event.which == 13 ) {
			   event.preventDefault();
		}
	});
	
	$(document).on('click', '[exa-type=iframe-popup]', function(event) {
		event.preventDefault();

		// open iframe from exa-url OR href attribute
		block_exacomp.popup_iframe({
			url: this.getAttribute('exa-url') || this.getAttribute('href'),
			width: this.getAttribute('exa-width'),
			height: this.getAttribute('exa-height'),
		});
	});
	
	$(document).on('click', '[exa-type=link]', function(event) {
		event.preventDefault();

		document.location.href = this.getAttribute('exa-url') || this.getAttribute('href');
	});

	$(document).on('click', '#hide-descriptor', function(event) {
		event.preventDefault();

		var tr = $(this).closest('tr');

		var courseid = block_exacomp.get_param('courseid');
		var studentid = block_exacomp.get_studentid() || 0;
		var descrid = $(this).attr('descrid');
		var val = $(this).attr('state');
		var select = document
			.getElementById("menulis_topics");
		
		if(studentid < 0) {
			// no negative studentid: (all users, gesamtübersicht,...)
			studentid = 0;
		}
		
		if(val=='-'){
			$(this).attr('state','+');
			visible = 0;

			$(this).trigger('rg2.lock');

			//disable checkbox for teacher, when hiding descriptor for student
			if(studentid > 0){
				$('input[name=datadescriptors-'+descrid+'-'+studentid+'-'+'teacher]').prop( "disabled", true );
				$('select[name=datadescriptors-'+descrid+'-'+studentid+'-'+'teacher]').prop( "disabled", true );
				$('input[name=add-grading-'+studentid+'-'+descrid+']').prop("disabled", true);
				$('select[name=niveau_descriptor-'+descrid+'-'+studentid+']').prop("disabled", true);
			}
			
			var img = $("img", this);
			img.attr('src',$(this).attr('hideurl'));
			img.attr('alt', M.util.get_string('show','moodle'));
			img.attr('title', M.util.get_string('show','moodle'));
			
			//only for competence grid
			var link = $('#competence-grid-link-'+descrid);
			if(link) {
				 link.addClass('deactivated');
			}
			
			if (select) {
				for(i=0; i<select.options.length; i++){
					if(select.options[i].value == descrid)
						$(select.options[i]).attr('disabled', 'disabled');
				}
			} 
		}else{
			$(this).attr('state','-');
			visible = 1;
			$(this).trigger('rg2.unlock');

			//enable checkbox for teacher, when showing descriptor for student

			$('input[name=add-grading-'+studentid+'-'+descrid+']').prop("disabled", false);
			$('select[name=datadescriptors-'+descrid+'-'+studentid+'-'+'teacher]').prop( "disabled", false );
			$('select[name=niveau_descriptor-'+descrid+'-'+studentid+']').prop("disabled", false);

			var img = $("img", this);
			img.attr('src',$(this).attr('showurl'));
			img.attr('alt', M.util.get_string('hide','moodle'));
			img.attr('title', M.util.get_string('hide','moodle'));
			
			//only for competence grid
			var link = $('#competence-grid-link-'+descrid);
			if(link) {
				 link.removeClass('deactivated');
			}
			
			if (select) {
				for(i=0; i<select.options.length; i++){
					if(select.options[i].value == descrid)
						$(select.options[i]).removeAttr('disabled');
				}
			} 
		}
		
		block_exacomp.call_ajax({
			descrid : descrid,
			value : visible,
			studentid : studentid,
			action : 'hide-descriptor'
		});

	});

	$(document).on('click', 'a[exa-type=example-sorting]', function (event) {
		event.preventDefault();

		if (Object.keys(competencies).length > 0 || Object.keys(topics).length > 0
			|| Object.keys(examples).length > 0) {
			alert(M.util.get_string('example_sorting_notice', 'block_exacomp'));
			return;
		}

		block_exacomp.call_ajax({
			action: 'example-sorting',
			direction: this.getAttribute('exa-direction'),
			descrid: this.getAttribute('exa-descrid'),
			exampleid: this.getAttribute('exa-exampleid'),
		}).done(function () {
			location.reload();
		});
	});

	$(document).on('click', '#hide-example', function(event) {
		event.preventDefault();

		var tr = $(this).closest('tr');

		var courseid = block_exacomp.get_param('courseid');
		var studentid = block_exacomp.get_studentid();
		var exampleid = $(this).attr('exampleid');
		var val = $(this).attr('state');
		
		if(studentid==null)
			studentid = 0;
		
		if(val=='-'){
			$(this).attr('state','+');
			visible = 0;

			exabis_rg2.get_row(this)
				.trigger('rg2.close')
				.addClass('rg2-locked');

			//disable checkbox for teacher, when hiding descriptor for student
			if(studentid > 0)
				$('input[name=dataexamples-'+exampleid+'-'+studentid+'-'+'teacher]').prop( "disabled", true ); 
				$('select[name=dataexamples-'+exampleid+'-'+studentid+'-teacher]').prop("disabled", true);
				$('select[name=niveau_examples-'+exampleid+'-'+studentid+']').prop("disabled", true);

			var img = $("img", this);
			img.attr('src',$(this).attr('hideurl'));
			img.attr('alt', M.util.get_string('show','moodle'));
			img.attr('title', M.util.get_string('show','moodle'));
			
			//only for competence grid
			var link = $('#competence-grid-link-'+exampleid);
			if(link) {
				 link.addClass('deactivated');
			}
		}else{
			$(this).attr('state','-');
			visible = 1;
			tr.removeClass('rg2-locked');
			
			//enable checkbox for teacher, when showing descriptor for student
			$('input[name=dataexamples-'+exampleid+'-'+studentid+'-'+'teacher]').prop( "disabled", false );
			$('select[name=dataexamples-'+exampleid+'-'+studentid+'-teacher]').prop("disabled", false);
			$('select[name=niveau_examples-'+exampleid+'-'+studentid+']').prop("disabled", false);
			
			var img = $("img", this);
			img.attr('src',$(this).attr('showurl'));
			img.attr('alt', M.util.get_string('hide','moodle'));
			img.attr('title', M.util.get_string('hide','moodle'));
			
			//only for competence grid
			var link = $('#competence-grid-link-'+exampleid);
			if(link) {
				 link.removeClass('deactivated');
			}
		}
		
		block_exacomp.call_ajax({
			exampleid : exampleid,
			value : visible,
			studentid : studentid,
			action : 'hide-example'
		});

	});

	$(document).on('click','[exa-type=add-example-to-schedule]', function(event) {
		var exampleid = $(this).attr('exampleid');
		var studentid = $(this).attr('studentid');
		
		if(studentid == -1){
			if (confirm("Möchten Sie das Beispiel wirklich bei allen Schülern auf den Planungsspeicher legen?")) {
				block_exacomp.call_ajax({
					exampleid : exampleid,
					studentid : studentid,
					action : 'add-example-to-schedule'
				}).done(function(msg) { alert(msg) });
			} 
		}else {
			block_exacomp.call_ajax({
				exampleid : exampleid,
				studentid : studentid,
				action : 'add-example-to-schedule'
			}).done(function(msg) { alert(msg) });
		}
		
		event.preventDefault();
		return false;
	});
	
	$(document).on('click','[exa-type=allow-resubmission]', function(event) {
		var exampleid = $(this).attr('exampleid');
		var studentid = $(this).attr('studentid');
		
		
		block_exacomp.call_ajax({
			exampleid : exampleid,
			studentid : studentid,
			action : 'allow-resubmission'
		}).done(function(msg) { alert(msg) });
		
		event.preventDefault();
		return false;
	});
	
	$(document).on('click','[exa-type=send-message-to-course]', function(event) {
		
		message = $('textarea[id=message]').val();
		
		block_exacomp.call_ajax({
			message : message,
			action : 'send-message-to-course'
		}).done(block_exacomp.popup_close());
		
		return false;
	});
	
	$(window).on('beforeunload', function (){
		if (Object.keys(competencies).length > 0 || Object.keys(topics).length > 0
				|| Object.keys(examples).length > 0)
			return M.util.get_string('unload_notice', 'block_exacomp');
	});
	
	block_exacomp.delete_descriptor = function(id) {
		block_exacomp.call_ajax({
			id: id,
			action : 'delete-descriptor'
		}).done(function() {
			location.reload();
		});
	}
	
	block_exacomp.delete_crosssubj = function(id) {
		block_exacomp.call_ajax({
			crosssubjid : id,
			action : 'delete-crosssubject'
		}).done(function() {
			location.reload();
		});
	}

})(jQueryExacomp);

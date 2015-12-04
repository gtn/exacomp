(function($) {

	// ## AJAX
	// # COMPETENCIES
	//cannot hide anymore, as soon as competency is checked
	var competencies = {};
	
	var prev_val;
	
	var competencies_additional_grading = {};
	
	var examples_additional_grading = {};

	$(document).on('focus', 'input[name^=data\-]', function() {
		prev_val = $(this).val();
	});
	
	$(document).on('change', 'input[name^=data\-]', function() {
		
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
		
		var values = $(this).attr("name").split("-");
		var tr = $(this).closest('tr');
		var hide = $(tr).find('input[name~="hide-descriptor"]');
		
		if ($(this).prop("checked")) {
			if (competencies[values[1]+"-"+values[2]]) {
				competencies[values[1]+"-"+values[2]]['value'] = 1;
			} else {
				competencies[values[1]+"-"+values[2]] = {
					userid : values[2],
					compid : values[1],
					value : 1
				};
			}
			//check comp->hide descriptor not possible
			hide.addClass("hidden");
		} else {
			if (competencies[values[1]+"-"+values[2]]) {
				competencies[values[1]+"-"+values[2]]['value'] = -1;
			} else {
				competencies[values[1]+"-"+values[2]] = {
					userid : values[2],
					compid : values[1],
					value : 0
				};
			}
			//uncheck comp -> hide possible again
			hide.removeClass("hidden");
		}
		
	});
	$(document).on('focus', 'select[name^=data\-]', function() {
		prev_val = $(this).val();
	});
	
	$(document).on('change', 'select[name^=data\-]', function() {
								  
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
			
		competencies[this.getAttribute('exa-compid')+"-"+this.getAttribute('exa-userid')] = {
			userid : this.getAttribute('exa-userid'),
			compid : this.getAttribute('exa-compid'),
			value : $(this).val()
		};
	});
	// # TOPICS
	var topics = {};
	$(document).on('click', 'input[name^=datatopics\-]', function() {
		var values = $(this).attr("name").split("-");

		if ($(this).prop("checked")) {
			if (topics[values[1]+"-"+values[2]]) {
				topics[values[1]+"-"+values[2]]['value'] = 1;
			} else
				topics[values[1]+"-"+values[2]] = {
					userid : values[2],
					compid : values[1],
					value : 1
				};
		} else {
			if (topics[values[1]+"-"+values[2]])
				topics[values[1]]['value'] = -1;
			else
				topics[values[1]+"-"+values[2]] = {
					userid : values[2],
					compid : values[1],
					value : 0
				};
		}
	});
	$(document).on('change', 'select[name^=datatopics\-]', function() {
		var values = $(this).attr("name").split("-");
		topics[values[1]+"-"+values[2]] = {
			userid : values[2],
			compid : values[1],
			value : $(this).val()
		};
	});
	
	// # CROSSSUBJECTS
	var crosssubs = {};
	$(document).on('click', 'input[name^=datacrosssubs\-]', function() {
		var values = $(this).attr("name").split("-");

		if ($(this).prop("checked")) {
			if (crosssubs[values[1]+"-"+values[2]]) {
				crosssubs[values[1]+"-"+values[2]]['value'] = 1;
			} else
				crosssubs[values[1]+"-"+values[2]] = {
					userid : values[2],
					compid : values[1],
					value : 1
				};
		} else {
			if (crosssubs[values[1]+"-"+values[2]])
				crosssubs[values[1]+"-"+values[2]]['value'] = 0;
			else
				crosssubs[values[1]+"-"+values[2]] = {
					userid : values[2],
					compid : values[1],
					value : -1
				};
		}
	});
	$(document).on('change', 'select[name^=datacrosssubs\-]', function() {
		var values = $(this).attr("name").split("-");
		crosssubs[values[1]+"-"+values[2]] = {
			userid : values[2],
			compid : values[1],
			value : $(this).val()
		};
	});
	
	// # EXAMPLES
	var examples = {};
	$(document).on('click', 'input[name^=dataexamples\-]', function() {
		var values = $(this).attr("name").split("-");
		var tr = $(this).closest('tr');
		var hide = $(tr).find('input[name~="hide-descriptor"]');
		
		examples[values[1]+"-"+values[2]] = {
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

		examples[values[1]+"-"+values[2]] = {
			userid : values[2],
			exampleid : values[1],
			value : $(this).val()
		};
	});
	
	// global var hack
	i_want_my_reload = false;
	
	$(document).on('click', '#assign-competencies input[type=submit], #assign-competencies input[type=button]', function(event) {
		event.preventDefault();
		var courseid = block_exacomp.get_param('courseid');

		// only for crosssubjects
		var crosssubjid = 0;
		var select = document
				.getElementById("menulis_crosssubs");

		if (select) {
			crosssubjid = select.options[select.selectedIndex].value;
		} else if (block_exacomp.get_param("crosssubjid") !== null) {
			crosssubjid = block_exacomp.get_param('crosssubjid');
		}
		

		switch ($(this).attr('id')) {
		case 'btn_submit':
			var reload = i_want_my_reload;
			
			function all_done() {
				if (reload) {
					location.reload();
				} else {
					alert('Änderungen wurden gespeichert!');
				}
			}
			
			var multiQueryData = {};
			
			if (crosssubjid > 0 && $('input[name^=crosssub-title]').length) {
				// in editmode
				reload = true;

				multiQueryData.update_crosssubj = {
					id: crosssubjid,
					subjectid: $("#menulis_crosssubject_subject option:selected").val(),
					title: $('input[name^=crosssub-title]').val(),
					description: $('input[name^=crosssub-description]').val()
				};
			}

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
			
			if (competencies_by_type.length) {
				multiQueryData.competencies_by_type = competencies_by_type
			}
			
			if(!$.isEmptyObject(competencies_additional_grading)){
				multiQueryData.competencies_additional_grading = competencies_additional_grading;
			}

			if(!$.isEmptyObject(examples_additional_grading)){
				multiQueryData.examples_additional_grading = examples_additional_grading;
			}
			
			//check all new_comp text fields if somewhere new text is entered when saving and create new descriptor
			var new_descriptors = [];
			$( "input[exa-type=new-descriptor]" ).each(function (){
				if (!this.value) return;
				
				new_descriptors.push({
					parentid: this.getAttribute('parentid'),
					topicid: this.getAttribute('topicid'),
					niveauid: this.getAttribute('niveauid'),
					title: this.value
				});

				if (new_descriptors.length) {
					multiQueryData.new_descriptors = new_descriptors;
					reload = true;
				}
			});
			
			if (!$.isEmptyObject(multiQueryData)) {
				block_exacomp.call_ajax({
					action: 'multi',
					// send data as json, because php max input setting
					data: JSON.stringify(multiQueryData)
				}).done(function(msg) {
					all_done();
					
					//im crosssubject neue Teilkompetenz erstellt -> gleich thema zuordnen
					// TODO: was macht das?
					// brauchen wir nicht, weil wir die seite eh nue laden?
					/*
					??? var msg = new_competencies[0].descriptorid

					var select = document
					.getElementById("menulis_crosssubs");

					if (block_exacomp.get_param("crosssubjid") !== null || select) {
						alert('TODO');
						if (select) {
							crosssubjid = select.options[select.selectedIndex].value;
						} else if (block_exacomp.get_param("crosssubjid") !== null) {
							crosssubjid = block_exacomp.get_param('crosssubjid');
						}
						console.log(crosssubjid);
						block_exacomp.call_ajax({
							descrid: msg,
							crosssubjectid : crosssubjid,
							action : 'crosssubj-descriptors-single'
						});
					}
					*/
				});
			} else {
				all_done();
			}
			
			break;
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
		}
		
		return false;
	});

	$(document).on('click', 'input[name=share_all]', function(){
		// disable if checked
		$("input[name='student']").attr('disabled', this.checked);
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

	// Share crosssubject with students
	$(document).on('click', ':button[name=share_crosssubj_students]', function() {
		var students = [];
		var not_students = [];
		var courseid = block_exacomp.get_param('courseid');
		var crosssubjid = block_exacomp.get_param('crosssubjid');

		var data = {
			action: 'crosssubj-share',
			crosssubjid : crosssubjid,
			share_all: $("input[name='share_all']").is(':checked'),
			students: [],
			not_students: []
		};
		
		if (!data.share_all) {
			// send students array if not sharing to all
			$("input[name='student']").each(function() {
				if (this.checked)
					data.students.push($(this).val());
				else
					data.not_students.push($(this).val());
			});
		}

		block_exacomp.call_ajax(data).done(function(msg) {
			block_exacomp.popup_close_and_reload();
		});
	});
	
	$(document).on('click', 'a[id^=competence-grid-link]', function(event) {
		if($(this).hasClass('deactivated')){
			event.preventDefault();
		}
	});
	
	$(document).on('click', '[exa-type=iframe-popup]', function(event) {
		event.preventDefault();

		// open iframe from exa-url OR href attribute
		block_exacomp.popup_iframe(this.getAttribute('exa-url') || this.getAttribute('href'));
	});
	
	$(document).on('click', '#hide-descriptor', function(event) {
		event.preventDefault();

		var tr = $(this).closest('tr');
		var id = tr[0].className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
		
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
			tr.addClass('hidden_temp');
			
			//hide subs 
			tr.removeClass('open');
			$('.rowgroup-content-'+id).hide();
			
			//disable checkbox for teacher, when hiding descriptor for student
			if(studentid > 0){
				$('input[name=data-'+descrid+'-'+studentid+'-'+'teacher]').prop( "disabled", true );
				$('select[name=data-'+descrid+'-'+studentid+'-'+'teacher]').prop( "disabled", true );
				$('input[name=add-grading-'+studentid+'-'+descrid+']').prop("disabled", true);
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
			tr.removeClass('hidden_temp');
			
			//do not show subs
			tr.toggleClass('open');
			
			//enable checkbox for teacher, when showing descriptor for student

			$('input[name=add-grading-'+studentid+'-'+descrid+']').prop("disabled", false);
			$('select[name=data-'+descrid+'-'+studentid+'-'+'teacher]').prop( "disabled", false );
			
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

	$(document).on('click', '#example-up', function(event) {
		if (Object.keys(competencies).length > 0 || Object.keys(topics).length > 0
				|| Object.keys(examples).length > 0)
			alert( M.util.get_string('example_sorting_notice', 'block_exacomp') );
		else
		block_exacomp.call_ajax({
			descrid : $(this).attr('descrid'),
			exampleid : $(this).attr('exampleid'),
			action : 'example-up'
		});
		location.reload();
	});
	
	$(document).on('click', '#example-down', function(event) {
		if (Object.keys(competencies).length > 0 || Object.keys(topics).length > 0
				|| Object.keys(examples).length > 0)
			alert( M.util.get_string('example_sorting_notice', 'block_exacomp') );
		else
		block_exacomp.call_ajax({
			descrid : $(this).attr('descrid'),
			exampleid : $(this).attr('exampleid'),
			action : 'example-down'
		});
		location.reload();
	});
	
	$(document).on('click', '#hide-example', function(event) {
		event.preventDefault();

		var tr = $(this).closest('tr');
		var id = tr[0].className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
		
		var courseid = block_exacomp.get_param('courseid');
		var studentid = block_exacomp.get_studentid();
		var exampleid = $(this).attr('exampleid');
		var val = $(this).attr('state');
		
		if(studentid==null)
			studentid = 0;
		
		if(val=='-'){
			$(this).attr('state','+');
			visible = 0;
			tr.addClass('hidden_temp');
			
			//hide subs 
			tr.removeClass('open');
			$('.rowgroup-content-'+id).hide();
			
			//disable checkbox for teacher, when hiding descriptor for student
			if(studentid > 0)
				$('input[name=dataexamples-'+exampleid+'-'+studentid+'-'+'teacher]').prop( "disabled", true ); 
			
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
			tr.removeClass('hidden_temp');
			
			//do not show subs
			tr.toggleClass('open');
			
			//enable checkbox for teacher, when showing descriptor for student
			$('input[name=dataexamples-'+exampleid+'-'+studentid+'-'+'teacher]').prop( "disabled", false );
			
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
		});
		
		block_exacomp.popup_close();
		return false;
	});
	
	$(document).on('change', 'input[name^=add-grading\-]', function(event) {
		var descrid = $(this).attr('descrid');
		var studentid = $(this).attr('studentid');
		var value = $(this).val();
		
		if(!competencies_additional_grading[descrid])
			competencies_additional_grading[descrid] = {};
		competencies_additional_grading[descrid][studentid] = value;
	});
	
	$(document).on('change', 'input.percent-rating', function(event) {
		var exampleid = $(this).attr('exampleid');
		var studentid = $(this).attr('studentid');
		
		if(!examples_additional_grading[exampleid])
			examples_additional_grading[exampleid] = {};
		examples_additional_grading[exampleid][studentid] = this.value == '' ? -1 : this.value.replace(/[^0-9]/g, '');
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
		}).done(function(msg) {
			location.reload();
		});
	}
	
	block_exacomp.delete_crosssubj = function(id) {
		block_exacomp.call_ajax({
			crosssubjid : id,
			action : 'delete-crosssubject'
		}).done(function(msg) {
			location.reload();
		});
	}

})(jQueryExacomp);
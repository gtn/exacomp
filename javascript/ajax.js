(function($) {

	// ## AJAX
	// # COMPETENCIES
	//cannot hide anymore, as soon as competency is checked
	var competencies = {};
	
	var prev_val;
	
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
		
		if ($(this).prop("checked")) {
			if (examples[values[1]+"-"+values[2]]) {
				examples[values[1]+"-"+values[2]]['value'] = 1;
			} else
				examples[values[1]+"-"+values[2]] = {
					userid : values[2],
					exampleid : values[1],
					value : 1
				};
		
			//check comp->hide descriptor not possible
			hide.addClass("hidden");
		} else {
			if (examples[values[1]+"-"+values[2]])
				examples[values[1]+"-"+values[2]]['value'] = 0;
			else
				examples[values[1]+"-"+values[2]] = {
					userid : values[2],
					exampleid : values[1],
					value : -1
				};
			
			//uncheck comp -> hide possible again
			hide.removeClass("hidden");
		}
	});
	

	$(document).on('change', 'select[name^=dataexamples\-]', function() {
		var values = $(this).attr("name").split("-");

		if (values[3] == 'studypartner')
			examples[values[1]+"-"+values[2]] = {
				userid : values[2],
				exampleid : values[1],
				studypartner : $(this).val()
			};
		else if (values[3] == 'starttime')
			examples[values[1]+"-"+values[2]] = {
				userid : values[2],
				exampleid : values[1],
				starttime : $(this).val()
			};
		else if (values[3] == 'endtime')
			examples[values[1]+"-"+values[2]] = {
				userid : values[2],
				exampleid : values[1],
				endtime : $(this).val()
			};
		else
			examples[values[1]+"-"+values[2]] = {
				userid : values[2],
				exampleid : values[1],
				value : $(this).val()
			};
	});
	 

	// #CrossSubject Title & Description
	var title = "";
	$(document).on('focusout', 'input[name^=crosssub-title]', function() {
		title = $(this).val();
	});

	var description = "";
	$(document).on('focusout', 'input[name^=crosssub-description]', function() {
		description = $(this).val();
	});
	
	// not needed anymore
	/*
	$(document).on('keypress', 'input[exa-type=new-descriptor]', function(event) {
		if(event.which == 13){
			// hitting enter in a new-descriptor field
			// prevent form submit, save and reload
			event.preventDefault();
			insert_descriptor(this);
			// hack: first wait ajax calls, then reload
			window.setTimeout(function() { location.reload() }, 50);
			
			return false;
		}
	});
	*/
	
	$(document).on('click', '#assign-competencies input[type=submit]', function(event) {
		event.preventDefault();
		courseid = block_exacomp.get_param('courseid');

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
			var deprecated_show_changes_saved = true;
			var reload = false;
			
			function saved_alert() {
				alert('Änderungen wurden gespeichert!');
			}
			
			if (!$.isEmptyObject(examples)) {
				block_exacomp.call_ajax({
					examples : JSON.stringify(examples),
					action : 'examples_array'
				});

				examples = {};
			}

			var select = document
			.getElementById("menulis_crosssubject_subject");

			// Cross-Suject subject
			if (select && crosssubjid > 0) {
				crosssubj_subjectid = select.options[select.selectedIndex].value;
			
				block_exacomp.call_ajax({
					crosssubjid: crosssubjid,
					subjectid: crosssubj_subjectid,
					action: 'crosssubj-subject'
				});
			}
			// Cross-Subject title & description
			
			if (title && crosssubjid > 0) {
				
				block_exacomp.call_ajax({
					crosssubjid : crosssubjid,
					title : title,
					action : 'crosssubj-title'
				});

			}
			if (description && crosssubjid > 0) {
				block_exacomp.call_ajax({
					crosssubjid : crosssubjid,
					description : description,
					action : 'crosssubj-description'
				});
			}

			var multiQueryData = {};
			
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

			//check all new_comp text fields if somewhere new text is entered when saving and create new descriptor
			var new_descriptors = [];
			$( "input[exa-type=new-descriptor]" ).each(function (){
				show_changes_saved = false;

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
				deprecated_show_changes_saved = false;
				
				multiQueryData.action = 'multi';
				block_exacomp.call_ajax(multiQueryData).done(function(msg) {
					if (reload) {
						location.reload();
					} else {
						saved_alert();
					}

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
			}
			
			
			//check all edit-descriptor text fields if somewhere new text is entered when saving and change descriptor
			// not needed anymore
			/*
			$( "input[name^=change-title]" ).each(function( event ) {
				  if($(this).val()){
					  title = $(this).val();
					  descrid = $(this).attr('descrid');
					  
					  block_exacomp.call_ajax({
							descrid : descrid,
							title : title,
							action : 'edit-descriptor-title'
					  });
					  
					  var span = $(this).parent('span');
					  span.text($(this).val());
				  }
			});
			*/
			
			if (deprecated_show_changes_saved) {
				saved_alert();
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
		case 'share_crosssub':
			url = 'select_students.php?courseid='
					+ courseid + '&crosssubjid='
					+ crosssubjid;
			window.open(url, '_blank',
					'width=880,height=660, scrollbars=yes');
			break;
		case 'delete_crosssub':
			message = $(this).attr('message');
			if (confirm(message)) {
				if (crosssubjid > 0) {
					block_exacomp.call_ajax({
						crosssubjid : crosssubjid,
						action : 'delete-crosssubject'
					}).done(function(msg) {
						location.reload();
					});
				}
			} 
			break;
		}
		
		return false;
	});

	$(document).on('click', 'input[name=share_all]', function(){
		if(this.checked == "1"){
			console.log('checked');
			$("input[name='student']").each(function() {
				this.disabled = true;
			});
		}else{
			console.log('not checked');
			$("input[name='student']").each(function() {
				this.disabled = false;
			});
		}
	});
	
	// Add Descriptor to crosssubjects
	$(document).on('click', '#crosssubjects', function(event) {
		event.preventDefault();
		var crosssubjects = [];
		var not_crosssubjects = [];
		descrid = block_exacomp.get_param('descrid');
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
	$(document).on('click', 'input[name=students]', function() {
		var students = [];
		var not_students = [];
		courseid = block_exacomp.get_param('courseid');
		crosssubjid = block_exacomp.get_param('crosssubjid');

		if ($("input[name='share_all']").is(':checked')) {
			block_exacomp.call_ajax({
				crosssubjid : crosssubjid,
				value : 1,
				action : 'crosssubj-share'
			}).done(function(msg) {
				window.opener.location.reload(true);
				window.close();
			});
		} else {
			block_exacomp.call_ajax({
				crosssubjid : crosssubjid,
				value : 0,
				action : 'crosssubj-share'
			});
			
			$("input[name='student']").each(function() {
				if (this.checked == "1")
					students.push($(this).val());
				else
					not_students.push($(this).val());
			});

			block_exacomp.call_ajax({
				students : JSON.stringify(students),
				not_students : JSON.stringify(not_students),
				crosssubjid : crosssubjid,
				action : 'crosssubj-students'
			}).done(function(msg) {
				window.opener.location.reload(true);
				window.close();
			});
		}
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
		
		courseid = block_exacomp.get_param('courseid');
		studentid = block_exacomp.get_studentid() || 0;
		descrid = $(this).attr('descrid');
		val = $(this).attr('state');
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
			if(studentid > 0)
				$('input[name=data-'+descrid+'-'+studentid+'-'+'teacher]').prop( "disabled", true ); 
			
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
			$('input[name=data-'+descrid+'-'+studentid+'-'+'teacher]').prop( "disabled", false );
			
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
		
		courseid = block_exacomp.get_param('courseid');
		studentid = block_exacomp.get_studentid();
		exampleid = $(this).attr('exampleid');
		val = $(this).attr('state');
		
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

	$(document).on('click','#add-example-to-schedule', function() {
		exampleid = $(this).attr('exampleid');
		studentid = $(this).attr('studentid');
		
		if(studentid == -1){
			if (confirm("Möchten Sie das Beispiel wirklich bei allen Schülern auf den Planungsspeicher legen?")) {
				block_exacomp.call_ajax({
					exampleid : exampleid,
					studentid : studentid,
					action : 'add-example-to-schedule'
				},function(msg) { alert(msg) });
			} 
		}else {
			block_exacomp.call_ajax({
				exampleid : exampleid,
				studentid : studentid,
				action : 'add-example-to-schedule'
			},function(msg) { alert(msg) });
		}
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

})(jQueryExacomp);
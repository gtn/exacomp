(function($) {

	// ## AJAX
	// # COMPETENCIES
	//cannot hide anymore, as soon as competency is checked
	var competencies = [];
	$(document).on('click', 'input[name^=data\-]', function() {
		var values = $(this).attr("name").split("-");
		var tr = $(this).closest('tr');
		var hide = $(tr).find('input[name~="hide-descriptor"]');
		
		if ($(this).prop("checked")) {
			if (competencies[values[1]]) {
				competencies[values[1]]['value'] = 1;
			} else {
				competencies[values[1]] = {
					userid : values[2],
					compid : values[1],
					value : 1
				};
			}
			//check comp->hide descriptor not possible
			hide.addClass("hidden");
		} else {
			if (competencies[values[1]]) {
				competencies[values[1]]['value'] = 0;
			} else {
				competencies[values[1]] = {
					userid : values[2],
					compid : values[1],
					value : 0
				};
			}
			//uncheck comp -> hide possible again
			hide.removeClass("hidden");
		}
	});
	  $(document).on('change', 'select[name^=data\-]', function() {
		var values = $(this).attr("name").split("-");
		competencies[values[1]] = {
			userid : values[2],
			compid : values[1],
			value : $(this).val()
		};
	});
	// # TOPICS
	var topics = [];
	$(document).on('click', 'input[name^=datatopics\-]', function() {
		var values = $(this).attr("name").split("-");

		if ($(this).prop("checked")) {
			if (topics[values[1]]) {
				topics[values[1]]['value'] = 1;
			} else
				topics[values[1]] = {
					userid : values[2],
					compid : values[1],
					value : 1
				};
		} else {
			if (topics[values[1]])
				topics[values[1]]['value'] = 0;
			else
				topics[values[1]] = {
					userid : values[2],
					compid : values[1],
					value : 0
				};
		}
	});
	$(document).on('change', 'select[name^=datatopics\-]', function() {
		var values = $(this).attr("name").split("-");
		topics[values[1]] = {
			userid : values[2],
			compid : values[1],
			value : $(this).val()
		};
	});
	
	// # CROSSSUBJECTS
	var crosssubs = [];
	$(document).on('click', 'input[name^=datacrosssubs\-]', function() {
		var values = $(this).attr("name").split("-");

		if ($(this).prop("checked")) {
			if (crosssubs[values[1]]) {
				crosssubs[values[1]]['value'] = 1;
			} else
				crosssubs[values[1]] = {
					userid : values[2],
					compid : values[1],
					value : 1
				};
		} else {
			if (crosssubs[values[1]])
				crosssubs[values[1]]['value'] = 0;
			else
				crosssubs[values[1]] = {
					userid : values[2],
					compid : values[1],
					value : 0
				};
		}
	});
	$(document).on('change', 'select[name^=datacrosssubs\-]', function() {
		var values = $(this).attr("name").split("-");
		crosssubs[values[1]] = {
			userid : values[2],
			compid : values[1],
			value : $(this).val()
		};
	});
	
	// # EXAMPLES
	var examples = [];
	$(document).on('click', 'input[name^=dataexamples\-]', function() {
		var values = $(this).attr("name").split("-");
		var tr = $(this).closest('tr');
		var hide = $(tr).find('input[name~="hide-descriptor"]');
		
		if ($(this).prop("checked")) {
			if (examples[values[1]]) {
				examples[values[1]]['value'] = 1;
			} else
				examples[values[1]] = {
					userid : values[2],
					exampleid : values[1],
					value : 1
				};
		
			//check comp->hide descriptor not possible
			hide.addClass("hidden");
		} else {
			if (examples[values[1]])
				examples[values[1]]['value'] = 0;
			else
				examples[values[1]] = {
					userid : values[2],
					exampleid : values[1],
					value : 0
				};
			
			//uncheck comp -> hide possible again
			hide.removeClass("hidden");
		}
	});
	

	$(document).on('change', 'select[name^=dataexamples\-]', function() {
		var values = $(this).attr("name").split("-");

		if (values[3] == 'studypartner')
			examples[values[1]] = {
				userid : values[2],
				exampleid : values[1],
				studypartner : $(this).val()
			};
		else if (values[3] == 'starttime')
			examples[values[1]] = {
				userid : values[2],
				exampleid : values[1],
				starttime : $(this).val()
			};
		else if (values[3] == 'endtime')
			examples[values[1]] = {
				userid : values[2],
				exampleid : values[1],
				endtime : $(this).val()
			};
		else
			examples[values[1]] = {
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
	
	$(document).on('keydown', 'input[name^=new_comp]', function(event) {
		if(event.which == 13){
			title_new_comp = $(this).val();
			descriptorid = $(this).attr('descrid');
			
			call_ajax({
				descriptorid: descriptorid,
				title : title_new_comp,
				action : 'new-comp'
			}).done(function(msg) {
				//im crosssubject neue Teilkompetenz erstellt -> gleich thema zuordnen
				var select = document
				.getElementById("menulis_crosssubs");

				if (get_param("crosssubjid") !== null || select) {
					if (select) {
						crosssubjid = select.options[select.selectedIndex].value;
					} else if (get_param("crosssubjid") !== null) {
						crosssubjid = get_param('crosssubjid');
					}
					console.log(crosssubjid);
					call_ajax({
						descrid: msg,
						crosssubjectid : crosssubjid,
						action : 'crosssubj-descriptors-single'
					});
				}
				location.reload();
			});
			
		}
	});
	
	$(document).on('click', '#assign-competencies input[type=submit]', function(event) {
							event.preventDefault();
							courseid = get_param('courseid');

							// only for crosssubjects
							var crosssubjid = 0;
							var select = document
									.getElementById("menulis_crosssubs");

							if (select) {
								crosssubjid = select.options[select.selectedIndex].value;
							} else if (get_param("crosssubjid") !== null) {
								crosssubjid = get_param('crosssubjid');
							}

							switch ($(this).attr('id')) {
							case 'btn_submit':
								
								if (competencies.length > 0) {
									call_ajax({
										competencies : JSON
												.stringify(competencies),
										comptype : 0,
										action : 'competencies_array'
									});

									competencies = [];
								}

								if (topics.length > 0) {
									call_ajax({
										competencies : JSON
												.stringify(topics),
										comptype : 1,
										action : 'competencies_array'
									}).done(function(msg) {
										console.log("Topics Saved: " + msg);
									});

									topics = [];
								}
								
								if (crosssubs.length > 0) {
									call_ajax({
										competencies : JSON
												.stringify(crosssubs),
										comptype : 2,
										action : 'competencies_array'
									}).done(function(msg) {
										console.log("Crosssubs Saved: " + msg);
									});

									crosssubs = [];
								}
								
								if (examples.length > 0) {
									call_ajax({
										examples : JSON.stringify(examples),
										action : 'examples_array'
									});

									examples = [];
								}

								var select = document
								.getElementById("menulis_crosssubject_subject");

								// Cross-Suject subject
								if (select && crosssubjid > 0) {
									crosssubj_subjectid = select.options[select.selectedIndex].value;
								
									call_ajax({
										crosssubjid: crosssubjid,
										subjectid: crosssubj_subjectid,
										action: 'crosssubj-subject'
									});
								}
								// Cross-Subject title & description
								
								if (title && crosssubjid > 0) {
									
									call_ajax({
										crosssubjid : crosssubjid,
										title : title,
										action : 'crosssubj-title'
									});

								}
								if (description && crosssubjid > 0) {
									call_ajax({
										crosssubjid : crosssubjid,
										description : description,
										action : 'crosssubj-description'
									});
								}

								//reload to display hide-buttons properly, especially when the descriptor is formerly used-> hide button not displayed
								//if evaluation removed->hide button should be shown.
								//alert is not necessary any more
								alert('Änderungen wurden gespeichert!');
								break;
							case 'save_as_draft':
								if (crosssubjid > 0) {
									call_ajax({
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
							}

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
		descrid = get_param('descrid');
		$("input[name='crosssubject']").each(function() {
			if (this.checked == "1")
				crosssubjects.push($(this).val());
			else
				not_crosssubjects.push($(this).val());
		});

		call_ajax({
			crosssubjects : JSON.stringify(crosssubjects),
			not_crosssubjects : JSON.stringify(not_crosssubjects),
			descrid : descrid,
			action : 'crosssubj-descriptors',
		}).done(function(msg) {
			window.close();
		});
	});

	// Share crosssubject with students
	$(document).on('click', 'input[name=students]', function() {
		var students = [];
		var not_students = [];
		courseid = get_param('courseid');
		crosssubjid = get_param('crosssubjid');

		if ($("input[name='share_all']").is(':checked')) {
			call_ajax({
				crosssubjid : crosssubjid,
				value : 1,
				action : 'crosssubj-share'
			}).done(function(msg) {
				window.close();
			});
		} else {
			call_ajax({
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

			call_ajax({
				students : JSON.stringify(students),
				not_students : JSON.stringify(not_students),
				crosssubjid : crosssubjid,
				action : 'crosssubj-students'
			}).done(function(msg) {
				window.close();
			});
		}
	});
	
	$(document).on('click', 'a[id^=competence-grid-link]', function(event) {
		if($(this).hasClass('deactivated')){
            event.preventDefault();
        }
	});
	
	$(document).on('click', '#hide-descriptor', function(event) {
		event.preventDefault();

		var tr = $(this).closest('tr');
		var id = tr[0].className.replace(/^.*rowgroup-header-([0-9]+).*$/, '$1');
		
		courseid = get_param('courseid');
		studentid = get_param('studentid');
		descrid = $(this).attr('descrid');
		val = $(this).attr('state');
		var select = document
			.getElementById("menulis_topics");
		
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
		
		call_ajax({
			descrid : descrid,
			value : visible,
			studentid : studentid,
			action : 'hide-descriptor'
		});

	});
	
	$(document).on('click','#add-example-to-schedule', function() {
		exampleid = $(this).attr('exampleid');
		studentid = $(this).attr('studentid');
		
		call_ajax({
			exampleid : exampleid,
			studentid : studentid,
			action : 'add-example-to-schedule'
		},function(msg) { alert(msg) });

	});
	
	function call_ajax(data, done, error) {
		data.courseid = get_param('courseid');
		data.sesskey = M.cfg.sesskey;
		
		return $.ajax({
			method : "POST",
			url : "ajax.php",
			data : data
		})
		.done(function(msg) {
			console.log(data.action + ': ' + msg);
			if (done) done(msg);
		}).error(function(msg) {
			console.log(msg);
			console.log("Error: " + data.action + ': ' + msg);
			if (error) error(msg);
		});
	}
	
	// Read a page's GET URL variables and return them as an associative array.
	function get_param(param) {
		var vars = getUrlVars();
		return typeof vars[param] == 'undefined' ? null : vars[param];
	}
	
	function getUrlVars() {
		var vars = [], hash;
		var hashes = window.location.href.slice(
				window.location.href.indexOf('?') + 1).split('&');
		for ( var i = 0; i < hashes.length; i++) {
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	}

	$(window).bind(
			'beforeunload',
			function() {
				if (competencies.length > 0 || topics.length > 0
						|| examples.length > 0)
					return 'Ungespeicherte Änderungen gehen verloren';
			});
})(jQueryExacomp);
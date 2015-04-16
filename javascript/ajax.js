(function($) {

	// ## AJAX
	// # COMPETENCIES
	var competencies = [];
	$(document).on('click', 'input[name^=data\-]', function() {
		var values = $(this).attr("name").split("-");

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
		}
	});
	// Never entered
	/*
	 * $(document).on('change', 'select[name^=data\-]', function() { var values =
	 * $(this).attr("name").split("-"); competencies[values[1]] = { userid :
	 * values[2], compid : values[1], value : $(this).val() }; });
	 */
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
	// Never entered
	$(document).on('change', 'select[name^=datatopics\-]', function() {
		var values = $(this).attr("name").split("-");
		topics[values[1]] = {
			userid : values[2],
			compid : values[1],
			value : $(this).val()
		};
	});
	// # EXAMPLES
	var examples = [];
	$(document).on('click', 'input[name^=dataexamples\-]', function() {
		var values = $(this).attr("name").split("-");

		if ($(this).prop("checked")) {
			if (examples[values[1]]) {
				examples[values[1]]['value'] = 1;
			} else
				examples[values[1]] = {
					userid : values[2],
					exampleid : values[1],
					value : 1
				};
		} else {
			if (examples[values[1]])
				examples[values[1]]['value'] = 0;
			else
				examples[values[1]] = {
					userid : values[2],
					exampleid : values[1],
					value : 0
				};
		}
	});
	// never entered
	/*
	 * $(document).on('change', 'select[name^=dataexamples\-]', function() { var
	 * values = $(this).attr("name").split("-");
	 * 
	 * if (values[3] == 'studypartner') examples[values[1]] = { userid :
	 * values[2], exampleid : values[1], studypartner : $(this).val() }; else if
	 * (values[3] == 'starttime') examples[values[1]] = { userid : values[2],
	 * exampleid : values[1], starttime : $(this).val() }; else if (values[3] ==
	 * 'endtime') examples[values[1]] = { userid : values[2], exampleid :
	 * values[1], endtime : $(this).val() }; else examples[values[1]] = { userid :
	 * values[2], exampleid : values[1], value : $(this).val() };
	 * 
	 * console.log(examples);
	 * 
	 * });
	 */

	// #CrossSubject Title & Description
	var title = "";
	$(document).on('focusout', 'input[name^=crosssub-title]', function() {
		title = $(this).val();
	});

	var description = "";
	$(document).on('focusout', 'input[name^=crosssub-description]', function() {
		description = $(this).val();
	});

	$(function() {
		$("#assign-competencies input[type=submit]")
				.click(
						function() {
							courseid = getUrlVars()['courseid'];

							// only for crosssubjects
							var crosssubjid = 0;
							var select = document
									.getElementById("menulis_crosssubs");

							if (select) {
								crosssubjid = select.options[select.selectedIndex].value;
							} else if ("crosssubjid" in getUrlVars()) {
								crosssubjid = getUrlVars()['crosssubjid'];
							}

							switch ($(this).attr('id')) {
							case 'btn_submit':
								if (competencies.length > 0) {
									$
											.ajax(
													{
														method : "POST",
														url : "ajax.php",
														data : {
															competencies : JSON
																	.stringify(competencies),
															courseid : courseid,
															comptype : 0,
															action : 'competencies_array'
														}
													})
											.done(
													function(msg) {
														console
																.log("Competence Saved: "
																		+ msg);
													}).error(function(msg) {
												console.log("Error" + msg);
											});

									competencies = [];
								}

								if (topics.length > 0) {
									$
											.ajax(
													{
														method : "POST",
														url : "ajax.php",
														data : {
															competencies : JSON
																	.stringify(topics),
															courseid : courseid,
															comptype : 1,
															action : 'competencies_array'
														}
													})
											.done(
													function(msg) {
														console
																.log("Topics Saved: "
																		+ msg);
													}).error(function(msg) {
												console.log("Error" + msg);
											});

									topics = [];
								}

								if (examples.length > 0) {
									$
											.ajax(
													{
														method : "POST",
														url : "ajax.php",
														data : {
															examples : JSON
																	.stringify(examples),
															courseid : courseid,
															action : 'examples_array'
														}
													})
											.done(
													function(msg) {
														console
																.log("Examples Saved: "
																		+ msg);
													}).error(function(msg) {
												console.log("Error" + msg);
											});

									examples = [];
								}

								// Cross-Subject title & description
								if (title && crosssubjid > 0) {
									$.ajax({
										method : "POST",
										url : "ajax.php",
										data : {
											crosssubjid : crosssubjid,
											title : title,
											courseid : courseid,
											action : 'crosssubj-title'
										}
									}).done(function(msg) {
										console.log("Crosssub-Title changed");
									}).error(function(msg) {
										console.log(msg);
									});
								}
								if (description && crosssubjid > 0) {
									$
											.ajax(
													{
														method : "POST",
														url : "ajax.php",
														data : {
															crosssubjid : crosssubjid,
															description : description,
															courseid : courseid,
															action : 'crosssubj-description'
														}
													})
											.done(
													function(msg) {
														console
																.log("Crosssub-Description changed");
													});
								}

								event.preventDefault();
								alert('Änderungen wurden gespeichert!');
								break;
							case 'save_as_draft':
								if (crosssubjid > 0) {
									$.ajax({
										method : "POST",
										url : "ajax.php",
										data : {
											crosssubjid : crosssubjid,
											courseid : courseid,
											action : 'save_as_draft'
										}
									}).done(function(msg) {
										console.log("Crosssub saved as draft");
									}).error(function(msg) {
										console.log(msg);
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
	$(document).on('click', 'input[name=crosssubjects]', function() {
		var crosssubjects = [];
		var not_crosssubjects = [];
		courseid = getUrlVars()['courseid'];
		descrid = getUrlVars()['descrid'];

		$("input[name='crosssubject']").each(function() {
			if (this.checked == "1")
				crosssubjects.push($(this).val());
			else
				not_crosssubjects.push($(this).val());
		});

		$.ajax({
			method : "POST",
			url : "ajax.php",
			data : {
				crosssubjects : JSON.stringify(crosssubjects),
				not_crosssubjects : JSON.stringify(not_crosssubjects),
				descrid : descrid,
				courseid : courseid,
				action : 'crosssubj-descriptors'
			}
		}).done(function(msg) {
			console.log(msg);
			window.close();
		});

	});

	// Share crosssubject with students
	$(document).on('click', 'input[name=students]', function() {
		var students = [];
		var not_students = [];
		courseid = getUrlVars()['courseid'];
		crosssubjid = getUrlVars()['crosssubjid'];

		if ($("input[name='share_all']").is(':checked')) {
			$.ajax({
				method : "POST",
				url : "ajax.php",
				data : {
					crosssubjid : crosssubjid,
					value : 1,
					courseid : courseid,
					action : 'crosssubj-share'
				}
			}).done(function(msg) {
				console.log(msg);
				window.close();
			});
		} else {
			$.ajax({
				method : "POST",
				url : "ajax.php",
				data : {
					crosssubjid : crosssubjid,
					value : 0,
					courseid : courseid,
					action : 'crosssubj-share'
				}
			}).done(function(msg) {
				console.log(msg);
				window.close();
			});
			
			$("input[name='student']").each(function() {
				if (this.checked == "1")
					students.push($(this).val());
				else
					not_students.push($(this).val());
			});

			console.log(students);
			console.log(not_students)
			$.ajax({
				method : "POST",
				url : "ajax.php",
				data : {
					students : JSON.stringify(students),
					not_students : JSON.stringify(not_students),
					crosssubjid : crosssubjid,
					courseid : courseid,
					action : 'crosssubj-students'
				}
			}).done(function(msg) {
				console.log(msg);
				window.close();
			});
		}
	});
	// Read a page's GET URL variables and return them as an associative array.
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
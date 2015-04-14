(function($){

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
	
	//#CrossSubject Title & Description
	var title = "";
	$(document).on('focusout', 'input[name^=crosssub-title]', function(){
		title = $(this).val();
	});
	
	var description = "";
	$(document).on('focusout', 'input[name^=crosssub-description]', function(){
		description = $(this).val();
	});
	
	$(document).on('submit', '#assign-competencies', function(event){
		courseid = getUrlVars()['courseid'];
		
		var crosssubjid = 0;
		var select = document.getElementById("menulis_crosssubs");
	
		if(select){
			crosssubjid = select.options[select.selectedIndex].value;
		}
		else if("crosssubjid" in getUrlVars()){
			crosssubjid = getUrlVars()['crosssubjid'];
		}
		
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
		  
		  //Cross-Subject title & description
		  if(title && crosssubjid > 0 ) {
				  $.ajax({
					  method: "POST",
					  url: "ajax.php",
					  data: { crosssubjid: crosssubjid, title: title, courseid: courseid, action: 'crosssubj-title'}
					})
					  .done(function( msg ) {
					    console.log( "Crosssub-Title changed");
				  })
				  .error(function (msg) {
					  console.log(msg);
				  });
		  }
		  if(description && crosssubjid > 0){ 
				  $.ajax({
					  method: "POST",
					  url: "ajax.php",
					  data: { crosssubjid: crosssubjid, description: description, courseid: courseid, action: 'crosssubj-description'}
					})
					  .done(function( msg ) {
					    console.log( "Crosssub-Description changed");
				  });
		  }
		  
		  //event.preventDefault();
		});

	// Add Descriptor to crosssubjects
	$(document).on('click', 'input[name=crosssubjects]', function(){
		var crosssubjects = [];
		var not_crosssubjects = [];
		courseid = getUrlVars()['courseid'];
		descrid = getUrlVars()['descrid'];
        
        $("input[name='crosssubject']").each(function () {
        	if(this.checked=="1")
        		crosssubjects.push($(this).val());
        	else
        		not_crosssubjects.push($(this).val());
        });
        
        $.ajax({
			  method: "POST",
			  url: "ajax.php",
			  data: { crosssubjects: JSON.stringify(crosssubjects), not_crosssubjects: JSON.stringify(not_crosssubjects), descrid: descrid, courseid: courseid, action: 'crosssubj-descriptors'}
			})
			  .done(function( msg ) {
			    console.log( msg );
			    window.close();
		  });
        
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
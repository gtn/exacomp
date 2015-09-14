(function($){
	$(document).on('click', '#use_example', function(event) {
		
		if(this.checked){
			$(this).parent().parent().removeClass('not-used');
		}
		else{
			$(this).parent().parent().addClass('not-used');
		}
	});
	
	var students = [];
	$(document).on('click', '#student_examp_mm', function(event) {
		
		var studentid = $(this).attr('studentid');
		
		if(this.checked){
			students[studentid] = studentid;
		}
		else{
			students[studentid] = 0;
		}
	});
	
	$(document).on('click', '#save_pre_planning_storage', function(event) {
		
		$('#sortable').each(function (event) {
		    var list = $(this).find('li');
		    list.each(function(){
		    	 var checkbox = $(this).find('#use_example');
		    	
		    	 if(checkbox[0].checked){
		    		 var scheduleid = checkbox.attr('scheduleid');
		    		 var exampleid = checkbox.attr('exampleid');
		    		 
		    		 students.forEach(function(student){
		    			if(student && student != 0){
		    				block_exacomp_add_to_learning_calendar(student, exampleid);
		    			}
		    		 });
		    		 
		    	 }
		    });
		   
		});
		
		location.reload();
	});
	
	function block_exacomp_add_to_learning_calendar(studentid, exampleid) {
		console.log('exacomp_add_event', studentid, exampleid);
		
		block_exacomp.call_ajax({
			studentid : studentid,
			exampleid: exampleid,
			action : 'add-example-to-schedule'
		},function(msg) {});
	}
	
	function block_exacomp_get_pre_planning_storage(callback){
		block_exacomp.call_ajax({
			creatorid : block_exacomp.get_param('creatorid'),
			action : 'get-pre-planning-storage'
			}, function(storage) {
				callback($.parseJSON(storage));
			});
	}	
	
	function exacomp_calendar_delete_event(event) {
		console.log('exacomp_calendar_delete_event', event.id, event.title, event.start, event.end, event.scheduleid);

		//aus schedule löschen
		block_exacomp.call_ajax({
			scheduleid : event.scheduleid,
			action : 'remove-example-from-schedule'
		},function(msg) {});
	}
	
	$(function() {
	
		/* initialize the external events
		-----------------------------------------------------------------*/
	
		$( "#sortable" ).sortable();
	    $( "#sortable" ).disableSelection();
	    
		var $eventDiv = $( '#external-events' );
		var $trash = $( '#trash' );
		var $sortableUl = $( '#sortable' );
		
		var pool_items;

		block_exacomp_get_pre_planning_storage(function(storage) {
			$.each(storage, function(i, item){ 
				add_pool_item(item); 
			});
		});
		
		function add_pool_item(data) {
			var li = $( "<li class = 'not-used fc-event ui-state-default'>").appendTo($sortableUl).text(data.title);
			
			li.append('	<div class="event-assoc">'+data.assoc_url+' <input type="checkbox" id="use_example" exampleid="'+data.exampleid+'" scheduleid="'+data.id+'"/></div>');
			
			li.data('event', data);
		}
	
	
		/* initialize the calendar
		-----------------------------------------------------------------*/
		$trash.droppable({
			// accept: ".special"
			drop: function(event, ui ) {
				if (confirm('Wirklich löschen?')) {
					exacomp_calendar_delete_event(ui.draggable.data('event'));
					ui.draggable.remove();
				}
			},
			
			hoverClass: 'hover',
		});
		
		function hover_check(e) {
			if (e && isEventOverDiv($eventDiv, e)) {
				$eventDiv.addClass('hover');
			} else {
				$eventDiv.removeClass('hover');
			}
			
			if (e && isEventOverDiv($trash, e)) {
				$trash.addClass('hover');
			} else {
				$trash.removeClass('hover');
			}
		}
		
		function isEventOverDiv($div, event) {
	
			var x = event.pageX, y = event.pageY;
			var offset = $div.offset();
			offset.right = $div.outerWidth() + offset.left;
			offset.bottom = $div.outerHeight() + offset.top;
	
			// Compare
			return (x >= offset.left
				&& y >= offset.top
				&& x <= offset.right
				&& y <= offset .bottom);
		}
	});
})(jQueryExacomp);
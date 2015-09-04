(function($){
	var examples = [];
	$(document).on('click', '#use_example', function(event) {
		
		var scheduleid = $(this).attr('scheduleid');
		var exampleid = $(this).attr('exampleid');
		
		if(this.checked){
			$(this).parent().parent().removeClass('not-used');
			examples[scheduleid] = exampleid;
		}
		else{
			$(this).parent().parent().addClass('not-used');
			examples[scheduleid] = 0;
		}
		
		console.log(examples);
	});
	
	var students = [];
	$(document).on('click', '#student_examp_mm', function(event) {
		
		var studentid = $(this).attr('studentid');
		
		if(this.checked){
			//$(this).parent().parent().removeClass('not-used');
			students[studentid] = studentid;
		}
		else{
			//$(this).parent().parent().addClass('not-used');
			students[studentid] = 0;
		}
		
		console.log(students);
	});
	
	$(document).on('click', '#save_pre_planning_storage', function(event) {
		
		console.log('save');
		students.forEach(function(student) {
		    if(student != 0){
		    	examples.forEach(function(example){
		    		if(example != 0){
		    			block_exacomp_add_to_learning_calendar(student, example);
		    			console.log(example);
		    			console.log(student);
		    		}
		    	});
		    }
		});
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
	
	
	$(function() {
	
		/* initialize the external events
		-----------------------------------------------------------------*/
	
		var $eventDiv = $( '#external-events' );
	
		var pool_items;

		block_exacomp_get_pre_planning_storage(function(storage) {
			$.each(storage, function(i, item){ add_pool_item(item); });
		});
		
		function add_pool_item(data) {
			var el = $( "<div class='not-used fc-event'>" ).appendTo( $eventDiv ).text( 
					data.title);
			
			el.append('	<div>'+data.assoc_url+/*((event.solution)?event.solution:'')+*/' <input type="checkbox" id="use_example" exampleid="'+data.exampleid+'" scheduleid="'+data.id+'"/></div>');
			
			el.data('event', data);
			
			el.draggable({
			  zIndex: 999,
			  revert: true, 
			  revertDuration: 0 
			});
			el.addTouch();
		}
	
	
		/* initialize the calendar
		-----------------------------------------------------------------*/
		
		function hover_check(e) {
			if (e && isEventOverDiv($eventDiv, e)) {
				$eventDiv.addClass('hover');
			} else {
				$eventDiv.removeClass('hover');
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
(function($){
	
	$(document).on('click', '#empty_trash', function(event) {
		studentid = block_exacomp.get_studentid();
		
		block_exacomp.call_ajax({
			studentid: studentid,
			action : 'empty-trash'
		}).done(function(msg) {
			location.reload();
		});
	});
	function exacomp_calendar_add_event(event) {
		console.log('exacomp_calendar_add_event', event.id, event.title, event.start.format('X'), event.end.format('X'), event.scheduleid);
		
		block_exacomp.call_ajax({
			scheduleid : event.scheduleid,
			start: event.start.format('X'),
			end: event.end.format('X'),
			action : 'set-example-start-end'
		});
	}
	
	function exacomp_calendar_update_event_time(event) {
		console.log('exacomp_calendar_update_event_time', event.id, event.title, event.start, event.end, event.scheduleid);

		block_exacomp.call_ajax({
			scheduleid : event.scheduleid,
			start: event.start.format('X'),
			end: event.end.format('X'),
			action : 'set-example-start-end'
		});
	}
	
	function exacomp_calendar_delete_event(event) {
		console.log('exacomp_calendar_delete_event', event.id, event.title, event.start, event.end, event.scheduleid);

		//aus schedule löschen
		block_exacomp.call_ajax({
			scheduleid : event.scheduleid,
			action : 'remove-example-from-schedule'
		});
	}
	
	function exacomp_calendar_remove_event(event, deleted) {
		console.log('exacomp_calendar_remove_event', event.id, event.title, event.start, event.end, event.scheduleid);

		//in pool zurück legen -> timestamps auf null setzen
		block_exacomp.call_ajax({
			scheduleid : event.scheduleid,
			start: 0,
			end: 0,
			deleted: deleted,
			event_course: event.courseid,
			action : 'set-example-start-end'
		});
	}
	
	function block_exacomp_get_configuration(callback){
		studentid = block_exacomp.get_studentid();
		
		block_exacomp.call_ajax({
			studentid : studentid,
			pool_course: block_exacomp.get_param('pool_course'),
			action : 'get-weekly-schedule-configuration'
			}).done(function(config) {
				callback($.parseJSON(config));
			});
	}
	function exacomp_calendar_load_events(start, end, timezone, callback) {
		studentid = block_exacomp.get_studentid();
		
		block_exacomp.call_ajax({
			studentid : studentid,
			start: start.format('X'),
			end: end.format('X'),
			action : 'get-examples-for-start-end'
		}).done(function(calendar_items) {
			//load them
			callback($.parseJSON(calendar_items));
		});
	}

	function exacomp_calendar_loading_done(type) {
		console.log('loading done');
	}
	console.log('loading');
	
	// loaded from server]
	var exacomp_calcendar_config = {
		loading_done: false
	};

	
	exacomp_calcendar = {
		event_slot_to_time: function(origEvent) {
			// clone event
			event = $.extend({}, origEvent);
			
			event.start = this.slot_to_time(event.start, 'start');
			if (event.end) {
				event.end = this.slot_to_time(event.end, 'end');
			} else {
				// get end time from start time
				var nextSlot = moment(origEvent.start).add(1, 'minute');
				event.end = this.slot_to_time(nextSlot, 'end');
			}
			return event;
		},
		event_time_to_slot: function(origEvent) {
			// clone event
			event = $.extend({}, origEvent);
			
			event.start = this.time_to_slot(event.start, 'start');
			if (event.end) event.end = this.time_to_slot(event.end, 'end');
			return event;
		},
		slot_to_time: function(time, type /* start or end */ ) {
			var m = moment(time);
			
			var slot = exacomp_calcendar_config.slots[m.add(type == 'end' ? -1 : 0, 'minute').format('m')];
			if (!slot) {
				console.log('WARNING: Slot not found', time.format(), type);
				slot = exacomp_calcendar_config.slots[0];
			}
			
			return moment(m.format('YYYY-MM-DD')+' '+slot[type]);
		},
		time_to_slot: function(time, type /* start or end */ ) {
			var m = ((time*1) == time ? moment.unix(time) : moment(time));
			var time = m.format('HH:mm');
			var found_slot_i = null;
	
			$.each(exacomp_calcendar_config.slots, function(i, slot) {
				if (time == slot[type]) {
					found_slot_i = i;
				}
			});
			
			if (found_slot_i === null) {
				console.log('WARNING: Slot not found', time, type);
				found_slot_i = 0;
			}
			
			return m.format('YYYY-MM-DD')+' '+exacomp_calcendar.slot_time(type == 'end' ? found_slot_i+1 : found_slot_i);
		},
		slot_time: function(slot) {
			return "00:"+("0"+slot).substr(-2)+":00";
		},
	};
	
	$(function() {
	
		/* initialize the external events
		-----------------------------------------------------------------*/
	
		var $eventDiv = $( '#external-events' );
		var $trash = $( '#trash' );
	
		var pool_items;

		block_exacomp_get_configuration(function(configuration) {
			$.extend(exacomp_calcendar_config, configuration);
			
			$.each(configuration.pool, function(i, item){ add_pool_item(item); });
			$.each(configuration.trash, function(i, item){ add_trash_item(item); });
			
			create_calendar();
		});
		
		function add_pool_item(data) {
			var el = $( "<div class='fc-event'>" ).appendTo( $eventDiv ).text(data.title);
			
			el.append('	<div class="event-assoc">'+data.assoc_url+/*((event.solution)?event.solution:'')+*/'</div>');
			
			if(data.externalurl != null)
				el.append('<div class="event-task">'+data.externalurl+'</div>');
			else if(data.task != null)
				el.append('<div class="event-task">'+data.task+'</div>');		
			if(data.submission_url != null)
				el.append('<div class="event-submission">'+data.submission_url+'</div>');

			if(data.state == 3)
				el.addClass('state3');
						
			if(data.state == 4)
				el.addClass('state4');	
						
			el.data('event', data);
	
			el.draggable({
			  zIndex: 999,
			  revert: true, 
			  revertDuration: 0 
			});
			el.addTouch();
		}
		
		function add_trash_item(data){
			var el = $( "<div class='fc-event'>" ).appendTo( $trash ).text( 
					data.title);
			
			el.append('	<div class="event-assoc">'+data.assoc_url+'</div>');
			
			el.data('event', data);
			
			el.draggable({
				  zIndex: 999,
				  revert: true, 
				  revertDuration: 0 
			});
			el.addTouch();
			
			schedules_to_delete[data.id] = data.id;
			
		}
	
		/* initialize the calendar
		-----------------------------------------------------------------*/
		var schedules_to_delete = [];
		
		$eventDiv.droppable({
			drop: function(event, ui){
				var data = ui.draggable.data('event');
				add_pool_item(data);
				exacomp_calendar_remove_event(data, 0);
				ui.draggable.remove();
			},
			hoverClass: 'hover',
		});
		
		$trash.droppable({
			// accept: ".special"
			drop: function(event, ui ) {
				var data = ui.draggable.data('event');
				
				add_trash_item(data);
				exacomp_calendar_remove_event(data, 1);
				ui.draggable.remove();
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
	
		function create_calendar() {
			$('#calendar').fullCalendar({
				header: {
					left: 'today prev,next',
					center: 'title',
					right: 'month,agendaWeek,agendaDay'
				},
				lang: 'de',
				defaultView: 'agendaWeek',
				minTime: "00:00:00",
				maxTime: exacomp_calcendar.slot_time(exacomp_calcendar_config.slots.length),
				axisWidth: 40,
				slotDuration: "00:01:00",
				hiddenDays: [ 0, 6 ], // no sunday and saturday
				allDaySlot: false,
				defaultTimedEventDuration: '00:01:00', // default event length
				
				contentHeight: "auto",
				
				eventConstraint: {
					start: '00:00:00', // a start time (10am in this example)
					end: exacomp_calcendar.slot_time(exacomp_calcendar_config.slots.length)
				},
		
				editable: true,
				droppable: true, // this allows things to be dropped onto the calendar
				dragRevertDuration: 0,
				
				drop: function() {
					// when dropping an external element remove it
					$(this).remove();
				},
		
				events: function(start, end, timezone, callback){
					exacomp_calendar_load_events(start, end, timezone, function(events){
						// convert to calendar timeslots
						events = $.map(events, function(o){
							var event = exacomp_calcendar.event_time_to_slot(o);
							event.original = event;
							
							event.title = event.courseinfo+':\n'+event.title;
							
							return event;
						});

						// first time we load events, loading is done
						if (!exacomp_calcendar_config.loading_done) {
							exacomp_calcendar_config.loading_done = true;
							exacomp_calendar_loading_done();
						}
						
						callback(events);
					})
				},
				
				eventRender: function(event, element) {

					var courseid = block_exacomp.get_param('pool_course');
					if(!courseid)
						var courseid = block_exacomp.get_param('courseid');
					
					if(event.courseid != courseid)
						element.addClass('different-course');
						
					if(event.state == 3)
						element.addClass('state3');
						
					if(event.state == 4)
						element.addClass('state4');	
						
					// console.log(element.html());
					
					// delete time (actually slot time)
					element.find(".fc-time").remove();
		
					// TODO:
					element.find(".fc-content").append(
						'	<div class="event-extra">' +
						//'	<div class="event-course">Kurs: '+event.courseinfo+'</div>'+
						//'	<div>L: <input type="checkbox" '+((event.teacher_evaluation>0)?'checked=checked':'')+'/> S: <input type="checkbox" '+((event.student_evaluation>0)?'checked=checked':'')+'/></div>' +
						'	<div class="event-assoc">'+event.assoc_url+/*((event.solution)?event.solution:'')+*/'</div>' +
						((event.externalurl != null) ? '	<div class="event-task">'+event.externalurl+'</div>' : '' )+
						((event.task != null) ? '	<div class="event-task">'+event.task+'</div>' : '' )+
						((event.submission_url != null) ? '	<div class="event-submission">'+event.submission_url+'</div>' : '' )+

						'</div>');
					
					$(element).addTouch();
				},
				
				eventDragStart: function() {
					$("html").bind('mousemove', hover_check);
				},
				
				eventDragStop: function( event, jsEvent, ui, view ) {
					$("html").unbind('mousemove', hover_check);
					hover_check(false);
				
					if (isEventOverDiv($eventDiv, jsEvent)) {
						$('#calendar').fullCalendar('removeEvents', event._id);
		
						// fullcalendar bug
						delete event.source;
	
						add_pool_item(event);
						exacomp_calendar_remove_event(event, 0);
					}
		
					if (isEventOverDiv($trash, jsEvent)) {
						$('#calendar').fullCalendar('removeEvents', event._id);
						add_trash_item(event);
						exacomp_calendar_remove_event(event, 1);
						
						/*if (confirm('Wirklich löschen?')) {
							$('#calendar').fullCalendar('removeEvents', event._id);
							
							var event = exacomp_calcendar.event_slot_to_time(event);
							exacomp_calendar_delete_event(event);
						}*/
					}
				},
				
				viewRender: function(view, element) {
					// reset axis labels
					var i = 0, einheit = 0;
					element.find('.fc-time').each(function(){
						var slot = exacomp_calcendar_config.slots[i];
						this.innerHTML = '<span>'+(slot.name ? '<b>' + slot.name + '</b><br />' : '')
							+ '<span style="font-size: 85%">'+'</span>'+'</span>';
						i++;
						if (slot.name) einheit++;
						if (einheit%2)
							$(this).closest('tr').css('background-color', 'rgba(0, 0, 0, 0.08)');
					});
					
					view.updateSize();
				},
				
				eventResize: function(event, delta, revertFunc) {
					var event = exacomp_calcendar.event_slot_to_time(event);
					exacomp_calendar_update_event_time(event);
				},
				eventDrop: function(event, delta, revertFunc) {
					var event = exacomp_calcendar.event_slot_to_time(event);
					exacomp_calendar_update_event_time(event);
				},
				eventReceive: function(event) {
					var event = exacomp_calcendar.event_slot_to_time(event);
					exacomp_calendar_add_event(event);
				},
			});
		}
	});
	
	window.weekly_schedule_print = function() {
		var view = $('#calendar').fullCalendar('getView');
		if (view.intervalUnit != 'week' && view.intervalUnit != 'day') {
			alert('Es können nur die Wochen und Tagesansicht gedruckt werden');
			return;
		}
		window.open(document.location.href+'&print=1&time='+view.start.format('X')+'&interval='+view.intervalUnit);
	}
})(jQueryExacomp);
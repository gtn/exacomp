(function($){
	
	function exacomp_calendar_add_event(event) {
		console.log('exacomp_calendar_add_event', event.id, event.title, event.start.format('X'), event.end.format('X'));
		
		block_exacomp.call_ajax({
			exampleid : event.id,
			studentid : block_exacomp.get_param('studentid'),
			start: event.start.format('X'),
			end: event.end.format('X'),
			action : 'add-example-to-time-slot'
		},function(msg) {});
	}
	
	function exacomp_calendar_update_event_time(event) {
		console.log('exacomp_calendar_update_event_time', event.id, event.title, event.start, event.end);

		block_exacomp.call_ajax({
			exampleid : event.id,
			studentid : block_exacomp.get_param('studentid'),
			start: event.start.format('X'),
			end: event.end.format('X'),
			action : 'add-example-to-time-slot'
		},function(msg) {});
	}
	
	function exacomp_calendar_delete_event(event) {
		console.log('exacomp_calendar_delete_event', event.id, event.title, event.start, event.end);

		//aus schedule löschen
		block_exacomp.call_ajax({
			exampleid : event.id,
			studentid : block_exacomp.get_param('studentid'),
			action : 'remove-example-from-schedule'
		},function(msg) {});
	}
	
	function exacomp_calendar_remove_event(event) {
		console.log('exacomp_calendar_remove_event', event.id, event.title, event.start, event.end);

		//in pool zurück legen -> timestamps auf null setzen
		block_exacomp.call_ajax({
			exampleid : event.id,
			studentid : block_exacomp.get_param('studentid'),
			start: 0,
			end: 0,
			action : 'add-example-to-time-slot'
		},function(msg) { });
	}
	
	function exacomp_calendar_load_events(start, end, timezone, callback) {
		block_exacomp.call_ajax({
			studentid : block_exacomp.get_param('studentid'),
			start: start.format('X'),
			end: end.format('X'),
			action : 'get-examples-for-time-slot'
		}, function(calendar_items) {
			//load them
			callback($.parseJSON(calendar_items));
		});
	}
	function block_exacomp_get_examples_for_pool(callback) {
		block_exacomp.call_ajax({
			studentid : block_exacomp.get_param('studentid'),
			action : 'get-examples-for-pool'
		}, function(calendar_items) {
			//load them
			callback($.parseJSON(calendar_items));
		});
	}
	
	var exacomp_calcendar_config = {
		slots: [
			{
				name: '1. Einheit',
				start: '07:45',
				end: '08:10'
			}, {
				name: '',
				start: '08:10',
				end: '08:35'
			}, {
				name: '2. Einheit',
				start: '08:35',
				end: '09:00'
			}, {
				name: '',
				start: '09:00',
				end: '09:25'
			}, {
				name: '3. Einheit',
				start: '09:30',
				end: '09:55'
			}, {
				name: '',
				start: '09:55',
				end: '10:20'
			}, {
				name: '4. Einheit',
				start: '10:35',
				end: '11:00'
			}, {
				name: '',
				start: '11:00',
				end: '11:25'
			}, {
				name: '5. Einheit',
				start: '11:30',
				end: '11:55'
			}, {
				name: '',
				start: '11:55',
				end: '12:20'
			},
		]
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
	
		block_exacomp_get_examples_for_pool(function(agenda_items) {
			$.each(agenda_items, function(i, item){ add_pool_item(item); });
		});
		
		function add_pool_item(data) {
			var el = $( "<div class='fc-event'>" ).appendTo( $eventDiv ).text( 
					data.title);
			
			el.append('	<div>'+data.assoc_url+/*((event.solution)?event.solution:'')+*/'</div>');
			
			el.data('event', data);
			
			// store data so the calendar knows to render an event upon drop
			
			/*$(this).data('event', {
				id: $.trim($(this).text()), // use the element's text as the event title
				stick: true // maintain when user navigates (see docs on the renderEvent method)
			})*/;
			
	
			el.draggable({
			  zIndex: 999,
			  revert: true, 
			  revertDuration: 0 
			});
			el.addTouch();
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
						return event;
					})
					callback(events);
				})
			},
			
			eventRender: function(event, element) {
				// console.log(element.html());
				
				// delete time (actually slot time)
				element.find(".fc-time").remove();
	
				// TODO:
				element.find(".fc-content").append(
					'	<div class="event-extra">' +
					'	<div>Kurs: '+event.courseinfo+'</div>'+
					//'	<div>L: <input type="checkbox" '+((event.teacher_evaluation>0)?'checked=checked':'')+'/> S: <input type="checkbox" '+((event.student_evaluation>0)?'checked=checked':'')+'/></div>' +
					'	<div>'+event.assoc_url+/*((event.solution)?event.solution:'')+*/'</div>' +
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
					exacomp_calendar_remove_event(event);
				}
	
				if (isEventOverDiv($trash, jsEvent)) {
					if (confirm('Wirklich löschen?')) {
						$('#calendar').fullCalendar('removeEvents', event._id);
						
						var event = exacomp_calcendar.event_slot_to_time(event);
						exacomp_calendar_delete_event(event);
					}
				}
			},
			
			viewRender: function(view, element) {
				// reset axis labels
				var i = 0, einheit = 0;
				element.find('.fc-time').each(function(){
					var slot = exacomp_calcendar_config.slots[i];
					this.innerHTML = '<span>'+(slot.name ? '<b>' + slot.name + '</b><br />' : '')
						+ '<span style="font-size: 85%">'+slot.start+'-'+slot.end+'</span>'+'</span>';
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
	});
})(jQueryExacomp);
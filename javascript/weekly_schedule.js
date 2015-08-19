(function($){
	
	function exacomp_calendar_update_event(event) {
		var event = exacomp_calcendar.event_slot_to_time(event);
		console.log('event change', event.start, event.end);
		
		studentid = get_param('studentid');
		
		if(studentid != null){
			var title = event.title;
			var id = event.id;
			
			var start_split = event.start.split(" ");
			var start_date = start_split[0].split("-");
			var start_time = start_split[1].split(":");
			var start_msc = new Date(start_date[0], start_date[1]-1, start_date[2], start_time[0], start_time[1]);
			var start = start_msc/1000;
			
			var end = start;
			if(event.end != null){
				var end_split = event.end.split(" ");
				var end_date = end_split[0].split("-");
				var end_time = end_split[1].split(":");
				var end_msc = new Date(end_date[0], end_date[1]-1, end_date[2], end_time[0], end_time[1]);
				end = end_msc/1000;
			}
	
			call_ajax({
				exampleid : id,
				studentid : studentid,
				start: start,
				end: end,
				action : 'add-example-to-time-slot'
			},function(msg) { alert(msg) });
			
			//TODO remove event from pool
		}
	}

	var exacomp_calcendar_config = {
		slots: [
			{
				name: '1. Einheit',
				start: '08:00',
				end: '08:25'
			}, {
				name: '',
				start: '08:25',
				end: '08:50'
			}, {
				name: '2. Einheit',
				start: '08:55',
				end: '09:20'
			}, {
				name: '',
				start: '09:20',
				end: '09:45'
			}, {
				name: '3. Einheit',
				start: '10:00',
				end: '10:25'
			}, {
				name: '',
				start: '10:25',
				end: '11:50'
			}, {
				name: 'test Einheit',
				start: '10:00',
				end: '13:25'
			}, {
				name: '3. Einheit',
				start: '10:00',
				end: '14:15'
			}, {
				name: '3. Einheit',
				start: '10:00',
				end: '14:20'
			}, {
				name: '8. Einheit',
				start: '10:00',
				end: '14:25'
			}
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
			
			return m.format('YYYY-MM-DD')+' '+slot[type];
		},
		time_to_slot: function(time, type /* start or end */ ) {
			var m = moment(time);
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
			
			return m.format('YYYY-MM-DD')+' 00:'+(type == 'end' ? found_slot_i+1 : found_slot_i)+':00';
		}
	};

	var eventsFromMoodle = [
		
	];

	$( window ).load(function() {
		/* initialize the external events
		-----------------------------------------------------------------*/

		function init_external_event(el) {
			el = $(el);
			el.draggable({
			  zIndex: 999,
			  revert: true, 
			  revertDuration: 0 
			});
			el.addTouch();
		}
		$('#external-events .fc-event').each(function() {
			
			// store data so the calendar knows to render an event upon drop
			$(this).data('event', {
				title: $.trim($(this).text()), // use the element's text as the event title
				stick: true, // maintain when user navigates (see docs on the renderEvent method)
				id: $(this).attr('exampleid')
			});

			// make the event draggable using jQuery UI
			init_external_event(this);
		});


		/* initialize the calendar
		-----------------------------------------------------------------*/
		var $eventDiv = $( '#external-events' );
		var $trash = $( '#trash' );
		
		$trash.droppable({
			// accept: ".special"
			drop: function(event, ui ) {
				//TODO delete element 
				//nicht sofort löschen, in papierkorb sammeln, symbol zum papierkorb leeren
				if (confirm('Wirklich löschen?')) {
					
					studentid = get_param('studentid');
					
					if(studentid != null){
						//TODO event has no id in here - where to add?
						var id = event.id;
						
						if(id != null){
							call_ajax({
								exampleid : id,
								studentid : studentid,
								action : 'remove-example-from-schedule'
							}).done(function(msg) {
								ui.draggable.remove();
							});
						}
					}
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
			maxTime: "00:"+exacomp_calcendar_config.slots.length+":00",
			axisWidth: 40,
			slotDuration: "00:01:00",
			hiddenDays: [ 0, 6 ], // no sunday and saturday
			allDaySlot: false,
			defaultTimedEventDuration: '00:01:00', // default event length
			
			contentHeight: "auto",
			
			eventConstraint: {
				start: '00:00:00', // a start time (10am in this example)
				end: "00:"+exacomp_calcendar_config.slots.length+":00"
			},

			editable: true,
			droppable: true, // this allows things to be dropped onto the calendar
			dragRevertDuration: 0,
			
			/*
			drop: function() {
				$(this).remove();
			},
			*/
			
			events: $.map(eventsFromMoodle, function(o){ return exacomp_calcendar.event_time_to_slot(o); }),
			
			eventRender: function(event, element) {
				// console.log(element.html());
				element.find(".fc-time").remove();
				// element.find(".fc-event-time").remove();
				element.find(".fc-content").append(
					'	<div class="event-extra">' +
					'	<div>L: <input type="checkbox" /> S: <input type="checkbox" /></div>' +
					'	<div><a href="#">edit</a></div>' +
					'</div>');
				
				$(element).addTouch();
			},
			
			eventDragStart: function() {
				$("html").bind('mousemove', hover_check);
			},
			
			eventDragStop: function( event, jsEvent, ui, view ) {
				$("html").unbind('mousemove', hover_check);
				hover_check(false);
			
				// console.log(jsEvent);
				if(isEventOverDiv($eventDiv, jsEvent)) {
					$('#calendar').fullCalendar('removeEvents', event._id);
					var el = $( "<div class='fc-event'>" ).appendTo( $eventDiv ).text( event.title );
					el.data('event', { title: event.title, id :event.id, stick: true });
					
					init_external_event(el);
				}

				if(isEventOverDiv($trash, jsEvent)) {
					if (confirm('Wirklich löschen?')) {
						$('#calendar').fullCalendar('removeEvents', event._id);
					}
				}
			},
			
			viewRender: function(view, element) {
				// reset axis labels
				var i = 0, einheit = 0;
				element.find('.fc-time span').each(function(){
					this.innerHTML = exacomp_calcendar_config.slots[i].name;
					i++;
					/*
					if (einheit%2)
						$(this).closest('tr').css('background-color', 'rgba(255, 0, 0, 0.2)');
					*/
				});
				
				view.updateSize();
			},
			
			eventResize: function(event, delta, revertFunc) {
				exacomp_calendar_update_event(event);
			},
			eventDrop: function(event, delta, revertFunc) {
				exacomp_calendar_update_event(event);
			},
			/*
				Called when an external element, containing event data, is dropped on the calendar.
			*/
			eventReceive: function(event) {
				exacomp_calendar_update_event(event);
			},
			/*
			eventDrop: function(event, delta, revertFunc) {
			
				// TODO: only allow moving event before 1am and 10am
				return;
				console.log(event.start, event.end);
				var eventStartSlot = event.start.format('H');
				var eventEndSlot = event.end ? event.end.format('H') : (eventStartSlot /* it's a string * / * 1 + 1); // new dragged in events have no end
				console.log(event.start, event.end, eventStartSlot, eventEndSlot);
				
				if (eventStartSlot >= 1 && eventStartSlot <= 10
					&& eventEndSlot >= 1 && eventEndSlot <= 10) {
					// ok
				} else {
					return revertFunc();
				}
			}
			*/
		});
		
		// restrict
		
	});
	
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
})(jQueryExacomp);
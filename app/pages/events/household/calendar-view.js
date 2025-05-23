// calendar-view.js
// Requires FullCalendar.io (https://fullcalendar.io/) to be included in the page

document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    // Load events from the backend
    fetch('get_events.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                calendarEl.innerHTML = '<div class="alert alert-danger">Failed to load events.</div>';
                return;
            }
            // Map events to FullCalendar format
            const events = data.events.map(ev => ({
                id: ev.event_id,
                title: ev.event_name + (ev.assembly_name ? ' (' + ev.assembly_name + ')' : ''),
                start: ev.start_date,
                end: ev.end_date,
                color: ev.is_recurring ? '#17a2b8' : '#007bff',
                extendedProps: {
                    eventType: ev.event_type,
                    assembly: ev.assembly_name,
                    isRecurring: ev.is_recurring
                }
            }));
            // Initialize FullCalendar
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                events: events,
                eventDidMount: function (info) {
                    // Tooltip for event details
                    var tooltip = new bootstrap.Tooltip(info.el, {
                        title: info.event.title + '\nType: ' + (info.event.extendedProps.eventType || '-') + '\nAssembly: ' + (info.event.extendedProps.assembly || '-') + (info.event.extendedProps.isRecurring ? '\nRecurring' : ''),
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                }
            });
            calendar.render();
        })
        .catch(() => {
            calendarEl.innerHTML = '<div class="alert alert-danger">Failed to load events.</div>';
        });
});

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error('Calendar element not found');
        return;
    }

    // Enable debug mode for development
    const DEBUG = true;

    // Helper function for logging
    function debugLog(message, data) {
        if (DEBUG) {
            if (data) {
                console.log(`[Calendar] ${message}`, data);
            } else {
                console.log(`[Calendar] ${message}`);
            }
        }
    }

    // Helper to get filter values
    function getFilters() {
        return {
            startDate: document.getElementById('startDate').value,
            endDate: document.getElementById('endDate').value,
            eventLevel: document.getElementById('eventLevel').value,
            eventType: document.getElementById('eventType').value
        };
    }    // Map level ID to string for event_types table
    function getLevelString(levelId) {
        switch (levelId) {
            case '4': return 'national';
            case '3': return 'zone';
            case '2': return 'assembly';
            case '1': return 'household';
            default: return '';
        }
    }

    // Populate event type options based on level
    function fetchEventTypes(levelId) {
        const levelString = getLevelString(levelId);
        debugLog(`Fetching event types for level: ${levelString}`);

        fetch('get_event_types.php?level=' + encodeURIComponent(levelString))
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! Status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                debugLog('Event types received:', data);
                const eventTypeSelect = document.getElementById('eventType');
                eventTypeSelect.innerHTML = '<option value="">All Types</option>';
                if (data.success && Array.isArray(data.types)) {
                    data.types.forEach(type => {
                        eventTypeSelect.innerHTML += `<option value="${type.event_type_id}">${type.name}</option>`;
                    });
                    debugLog(`Loaded ${data.types.length} event types`);
                } else {
                    debugLog('No event types found or invalid response format');
                }
            })
            .catch(error => {
                console.error('Error fetching event types:', error);
                // Add a user-visible error message
                const eventTypeSelect = document.getElementById('eventType');
                eventTypeSelect.innerHTML = '<option value="">Error loading types</option>';
            });
    }

    // Format date for tooltips
    function formatDateTime(dateStr) {
        if (!dateStr) return 'Not set';

        try {
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return dateStr;

            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            };
            return date.toLocaleDateString(undefined, options);
        } catch (e) {
            console.error('Date formatting error:', e);
            return dateStr;
        }
    }

    // On level change, update event type options
    document.getElementById('eventLevel').addEventListener('change', function () {
        debugLog(`Event level changed to: ${this.value}`);
        fetchEventTypes(this.value);
        calendar.refetchEvents();
    });

    // On event type change, refetch events
    document.getElementById('eventType').addEventListener('change', function () {
        debugLog(`Event type changed to: ${this.value}`);
        calendar.refetchEvents();
    });    // Get loading indicator element
    const loadingIndicator = document.getElementById('calendarLoading');

    // Function to show/hide loading indicator
    function toggleLoading(show) {
        if (loadingIndicator) {
            loadingIndicator.style.display = show ? 'flex' : 'none';
        }
    }

    // FullCalendar initialization
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        aspectRatio: 1.5,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,dayGridDay,listMonth'
        },
        themeSystem: 'bootstrap5',
        timeZone: 'local',
        loading: function (isLoading) {
            toggleLoading(isLoading);
            debugLog('Calendar loading state:', isLoading);
        },
        events: function (info, successCallback, failureCallback) {
            // Debug info about date range
            debugLog(`Fetching events from ${info.startStr} to ${info.endStr}`);            // Fetch events from backend with filters
            const filters = getFilters();

            // Only add calendar's date range if needed
            // If filters are empty, we want to fetch all events
            if (!filters.startDate && !filters.endDate) {
                debugLog('No date filters set, will fetch all events');
                // Leave startDate and endDate empty to fetch all events
            } else {
                // If only one date filter is set, add the other from calendar view
                if (!filters.startDate) {
                    filters.startDate = info.startStr.split('T')[0]; // Just the date part
                }
                if (!filters.endDate) {
                    filters.endDate = info.endStr.split('T')[0]; // Just the date part
                }
            }

            debugLog('Sending filters to server:', filters);

            // Show loading manually in case FullCalendar's loading callback is delayed
            toggleLoading(true);

            fetch('get_events.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(filters)
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Hide loading manually
                    toggleLoading(false);

                    if (data.success) {
                        debugLog(`Received ${data.events ? data.events.length : 0} events from server`);

                        // Check if events array exists
                        if (!data.events || !Array.isArray(data.events)) {
                            console.warn('Server returned success but no events array');
                            successCallback([]);
                            return;
                        }

                        if (data.events.length > 0) {
                            debugLog('First event sample:', data.events[0]);
                        } else {
                            debugLog('No events returned from server');
                            // No "No events found" message will be displayed
                        }// Process events to ensure they have required properties
                        const processedEvents = data.events.map(event => {
                            // Ensure start and end dates are valid
                            if (!event.start) {
                                console.warn('Event missing start date:', event);
                                return null; // Skip invalid events
                            }

                            // Ensure end date exists (fallback to start date if not)
                            if (!event.end) {
                                console.warn('Event missing end date, using start date instead:', event);
                                event.end = event.start;
                            }

                            // Validate dates further
                            try {
                                new Date(event.start);
                                new Date(event.end);
                            } catch (e) {
                                console.warn('Event has invalid date format:', event);
                                return null;
                            }

                            // Add default color if missing
                            if (!event.color) {
                                event.color = '#007bff';
                            }

                            // Add default title if missing
                            if (!event.title || event.title.trim() === '') {
                                event.title = 'Untitled Event';
                            }

                            return event;
                        }).filter(event => event !== null);

                        debugLog(`Processed ${processedEvents.length} valid events for calendar`);
                        successCallback(processedEvents);

                        // Update dashboard counts after events are loaded
                        updateDashboardCounts(processedEvents);
                    } else {
                        console.error('Error from server:', data.message || 'Unknown error');
                        failureCallback(new Error(data.message || 'Unknown error'));

                        // Hide loading indicator manually
                        toggleLoading(false);

                        // Show empty calendar with a message
                        successCallback([]);

                        // Display an error message
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'alert alert-danger m-3';
                        errorMsg.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>${data.message || 'Error loading events'}`;
                        errorMsg.style.position = 'absolute';
                        errorMsg.style.top = '50%';
                        errorMsg.style.left = '50%';
                        errorMsg.style.transform = 'translate(-50%, -50%)';
                        errorMsg.style.zIndex = '5';

                        // Remove any existing message
                        const existingMsg = calendarEl.querySelector('.alert');
                        if (existingMsg) {
                            existingMsg.remove();
                        }

                        // Add the message
                        calendarEl.appendChild(errorMsg);

                        // Log detailed error if available
                        if (data.error) {
                            console.error('Server error details:', data.error);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);

                    // Hide loading indicator manually
                    toggleLoading(false);

                    // Show empty calendar
                    successCallback([]);

                    // Display an error message
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'alert alert-danger m-3';
                    errorMsg.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>Failed to load events: ${error.message}`;
                    errorMsg.style.position = 'absolute';
                    errorMsg.style.top = '50%';
                    errorMsg.style.left = '50%';
                    errorMsg.style.transform = 'translate(-50%, -50%)';
                    errorMsg.style.zIndex = '5';

                    // Remove any existing message
                    const existingMsg = calendarEl.querySelector('.alert');
                    if (existingMsg) {
                        existingMsg.remove();
                    }

                    // Add the message
                    calendarEl.appendChild(errorMsg);
                });
        },
        eventDidMount: function (info) {
            // Show detailed tooltip on hover
            const event = info.event;
            const props = event.extendedProps;
            const startStr = formatDateTime(event.start);
            const endStr = formatDateTime(event.end);

            let tooltipHtml = `
                <div style='font-weight:bold;'>${event.title}</div>
                <div>Type: <b>${props.eventType || '-'}</b></div>
                ${props.assembly ? `<div>Assembly: <b>${props.assembly}</b></div>` : ''}
                ${props.zone ? `<div>Zone: <b>${props.zone}</b></div>` : ''}
                ${props.household ? `<div>Household: <b>${props.household}</b></div>` : ''}
                <div>Start: <b>${startStr}</b></div>
                <div>End: <b>${endStr}</b></div>
                ${props.location ? `<div>Location: <b>${props.location}</b></div>` : ''}
                ${props.description ? `<div>Description: ${props.description}</div>` : ''}
                ${props.isRecurring ? `<div><span class='badge bg-info'>Recurring</span></div>` : ''}
            `;

            try {
                // Use Bootstrap tooltips
                new bootstrap.Tooltip(info.el, {
                    title: tooltipHtml,
                    html: true,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            } catch (e) {
                console.error('Error creating tooltip:', e);
            }
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        },
        dayMaxEventRows: 3,
        views: {
            dayGridMonth: { dayMaxEventRows: 3 },
            timeGridWeek: { dayMaxEventRows: 3 },
            dayGridDay: { dayMaxEventRows: 6 }
        },
        loading: function (isLoading) {
            // Show/hide loading indicator
            const loadingIndicator = document.getElementById('calendarLoading');
            if (loadingIndicator) {
                loadingIndicator.style.display = isLoading ? 'flex' : 'none';
            }
            debugLog(`Calendar ${isLoading ? 'loading events' : 'finished loading'}`);
        }
    });

    calendar.render();
    debugLog('Calendar rendered');

    // Make calendar globally accessible for theme toggle
    window.calendar = calendar;

    // Helper function to update dashboard stats
    function updateDashboardCounts(events) {
        if (!events) return;
        debugLog(`Updating dashboard with ${events.length} events`);

        const now = new Date();
        const thisMonth = now.getMonth();
        const thisYear = now.getFullYear();

        // Count metrics
        const totalEvents = events.length;
        const upcomingEvents = events.filter(e => new Date(e.start) >= now).length;
        const recurringEvents = events.filter(e => e.extendedProps && e.extendedProps.isRecurring).length;
        const thisMonthEvents = events.filter(e => {
            const eventDate = new Date(e.start);
            return eventDate.getMonth() === thisMonth && eventDate.getFullYear() === thisYear;
        }).length;

        // Update dashboard
        document.getElementById('totalEventsCount').textContent = totalEvents;
        document.getElementById('upcomingEventsCount').textContent = upcomingEvents;
        document.getElementById('recurringEventsCount').textContent = recurringEvents;
        document.getElementById('thisMonthEventsCount').textContent = thisMonthEvents;

        debugLog('Dashboard updated', {
            total: totalEvents,
            upcoming: upcomingEvents,
            recurring: recurringEvents,
            thisMonth: thisMonthEvents
        });
    }

    // Refetch events when filters change
    document.getElementById('eventFilters').addEventListener('change', function () {
        debugLog('Filters changed, refetching events');
        calendar.refetchEvents();
    });

    document.getElementById('eventFilters').addEventListener('submit', function (e) {
        e.preventDefault();
        debugLog('Filter form submitted, refetching events');
        calendar.refetchEvents();
    });

    // On page load, fetch all event types and all events
    fetchEventTypes('');    // Don't set initial date range - leave filters empty by default
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';
    debugLog('Date filters set to empty by default');

    calendar.refetchEvents();
});

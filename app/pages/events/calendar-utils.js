// Base calendar configuration
function initializeCalendar(calendarElement, options = {}) {
    if (!calendarElement) return null;

    // Add modern styling to calendar container
    calendarElement.style.background = '#fff';
    calendarElement.style.borderRadius = '18px';
    calendarElement.style.boxShadow = '0 4px 24px 0 rgba(0,0,0,0.10), 0 1.5px 4px 0 rgba(0,0,0,0.08)';
    calendarElement.style.padding = '18px 8px 8px 8px';
    calendarElement.style.margin = '0 auto 24px auto';

    const defaultConfig = {
        initialView: 'dayGridMonth',
        height: 'auto',
        aspectRatio: 1.5,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        },
        displayEventTime: true,
        displayEventEnd: true,
        eventDisplay: 'block',
        dayMaxEventRows: 3,
        views: {
            dayGridMonth: { dayMaxEventRows: 3 },
            timeGridWeek: { dayMaxEventRows: 3 },
            timeGridDay: { dayMaxEventRows: 3 }
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
                <div>Start: <b>${startStr}</b></div>
                <div>End: <b>${endStr}</b></div>
                ${props.description ? `<div>Description: ${props.description}</div>` : ''}
                ${props.isRecurring ? `<div><span class='badge bg-info'>Recurring</span></div>` : ''}
            `;

            new bootstrap.Tooltip(info.el, {
                title: tooltipHtml,
                html: true,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        }
    };

    // Merge user options with defaults
    const calendarConfig = { ...defaultConfig, ...options };

    return new FullCalendar.Calendar(calendarElement, calendarConfig);
}

// Load events with filters
function loadEvents(calendar, filters = {}) {
    const queryParams = new URLSearchParams({
        ...filters,
        startDate: filters.startDate || '',
        endDate: filters.endDate || '',
        level: filters.level || '',
        assemblyId: filters.assemblyId || '',
        zoneId: filters.zoneId || '',
        householdId: filters.householdId || ''
    }).toString();

    fetch(`get_events.php?${queryParams}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear existing events and add new ones
                calendar.removeAllEvents();
                calendar.addEventSource(data.events);
            } else {
                console.error('Failed to load events:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading events:', error);
        });
}

// Helper function to format dates
function formatDateTime(date) {
    if (!date) return 'Not set';

    try {
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
        return date.toString();
    }
}

// Setup filter controls
function initializeFilters(calendar, options = {}) {
    // Watch for changes in date filters
    const startDateFilter = document.getElementById('startDateFilter');
    const endDateFilter = document.getElementById('endDateFilter');
    const levelFilter = document.getElementById('levelFilter');

    const applyFilters = () => {
        const filters = {
            startDate: startDateFilter?.value || '',
            endDate: endDateFilter?.value || '',
            level: levelFilter?.value || '',
            ...options
        };
        loadEvents(calendar, filters);
    };

    // Add event listeners to filters
    startDateFilter?.addEventListener('change', applyFilters);
    endDateFilter?.addEventListener('change', applyFilters);
    levelFilter?.addEventListener('change', applyFilters);

    // Initialize with current filter values
    applyFilters();
}

// Handle dark mode
function initializeDarkMode() {
    const style = document.createElement('style');
    style.textContent = `
        @media (prefers-color-scheme: dark) {
            #calendar {
                background: #23272f;
                color: #fff;
            }
            .fc .fc-toolbar-title {
                color: #fff;
            }
            .fc .fc-daygrid-day {
                background: #23272f;
            }
            .fc .fc-daygrid-day:hover {
                background: #2a2e38;
            }
        }
        [data-bs-theme="dark"] #calendar {
            background: #23272f;
            color: #fff;
        }
        [data-bs-theme="dark"] .fc .fc-toolbar-title {
            color: #fff;
        }
        [data-bs-theme="dark"] .fc .fc-daygrid-day {
            background: #23272f;
        }
        [data-bs-theme="dark"] .fc .fc-daygrid-day:hover {
            background: #2a2e38;
        }
    `;
    document.head.appendChild(style);
}

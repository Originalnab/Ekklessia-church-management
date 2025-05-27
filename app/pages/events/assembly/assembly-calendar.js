// Assembly Calendar View - FullCalendar integration
// This script renders all assembly events in a full-page calendar view

document.addEventListener('DOMContentLoaded', function () {
    // Only initialize if the calendar tab exists
    const calendarEl = document.getElementById('assemblyCalendar');
    if (!calendarEl) return;

    let calendar = null;

    // Helper to fetch all events for the calendar
    function fetchAssemblyEvents() {
        // Get filter values to ensure calendar respects the filters
        const searchInput = document.getElementById('eventSearchInput');
        const typeFilter = document.getElementById('eventTypeFilter');
        const assemblyFilter = document.getElementById('assemblyFilter');
        const startDateFilter = document.getElementById('startDateFilter');
        const endDateFilter = document.getElementById('endDateFilter');

        const searchValue = searchInput ? searchInput.value : '';
        const typeValue = typeFilter ? typeFilter.value : '';
        const assemblyValue = assemblyFilter ? assemblyFilter.value : '';
        const startDateValue = startDateFilter ? startDateFilter.value : '';
        const endDateValue = endDateFilter ? endDateFilter.value : '';

        // Fetch all events (no pagination, all filters applied)
        const params = new URLSearchParams({
            tab: 'calendar',
            page: 1,
            pageSize: 1000, // Load all matching events for calendar
            search: searchValue,
            type: typeValue,
            assembly: assemblyValue,
            startDate: startDateValue,
            endDate: endDateValue
        });

        return fetch('get_events_paginated.php?' + params.toString())
            .then(res => res.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Failed to load events');
                return data.events;
            });
    }// Helper to map events to FullCalendar format
    function mapEventsToCalendar(events) {
        return events.map(ev => ({
            id: ev.event_id,
            title: (ev.title || ev.event_name) + (ev.assembly_name ? ` (${ev.assembly_name})` : ''),
            start: ev.start_date,
            end: ev.end_date,
            extendedProps: ev,
            color: ev.is_recurring == 1 ? '#17a2b8' : '#007bff',
            borderColor: ev.is_recurring == 1 ? '#0f7384' : '#0062cc',
            textColor: '#fff',
            allDay: false
        }));
    }    // Show event details in a modal (reuse view modal if present)
    function showEventDetails(event) {
        if (window.bootstrap && document.getElementById('viewEventModal')) {
            // Trigger the view modal logic from main handlers
            const modal = new bootstrap.Modal(document.getElementById('viewEventModal'));
            const modalBody = document.getElementById('viewEventModalBody');
            modalBody.innerHTML = '<div class="d-flex justify-content-center align-items-center" style="min-height:200px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            modal.show();

            fetch('view_event.php?event_id=' + encodeURIComponent(event.id))
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const ev = data.event;
                        // Detect theme (light/dark)
                        const isDark = document.body.getAttribute('data-bs-theme') === 'dark';
                        let cardStyle, titleStyle, labelStyle, textStyle, descStyle;
                        if (isDark) {
                            cardStyle = 'background: linear-gradient(120deg, #232526 0%, #414345 100%); color: #f3f3f3;';
                            titleStyle = 'color:#aee7ff;';
                            labelStyle = 'color:#b3e5fc!important;';
                            textStyle = 'color:#fff;';
                            descStyle = 'background:rgba(255,255,255,0.07); color:#f3f3f3;';
                        } else {
                            cardStyle = 'background: linear-gradient(120deg, #e0c3fc 0%, #8ec5fc 100%); color: #222;';
                            titleStyle = 'color:#3a3a7c;';
                            labelStyle = 'color:#2b4c7e!important;';
                            textStyle = 'color:#222;';
                            descStyle = 'background:rgba(255,255,255,0.7); color:#222;';
                        }

                        modalBody.innerHTML = `
                            <div class="card shadow border-0" style="border-radius: 1rem; ${cardStyle}">
                                <div class="card-body p-4">
                                    <h4 class="card-title mb-2" style="${titleStyle}"><i class="bi bi-calendar-event me-2"></i>${ev.title}</h4>
                                    <div class="mb-3 text-muted" style="${labelStyle}">${ev.event_type ? `<span class='badge bg-info text-dark me-2'>${ev.event_type}</span>` : ''}${ev.is_recurring == 1 ? `<span class='badge bg-warning text-dark'>Recurring: ${ev.frequency || ''}</span>` : ''}</div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-2">
                                            <strong style="${labelStyle}">Start:</strong> <span style="${textStyle}">${formatDateTime(ev.start_date)}</span>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <strong style="${labelStyle}">End:</strong> <span style="${textStyle}">${formatDateTime(ev.end_date)}</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <strong style="${labelStyle}">Assembly:</strong> <span style="${textStyle}">${ev.assembly_name || ''}</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong style="${labelStyle}">Description:</strong><br>
                                        <div class="border rounded p-2" style="${descStyle}">${ev.description ? ev.description.replace(/\n/g, '<br>') : '<em>No description</em>'}</div>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <button class="btn btn-sm btn-warning edit-event-btn" data-id="${ev.event_id}" title="Edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-event-btn" data-id="${ev.event_id}" data-title="${ev.title}" title="Delete">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                    <div class="text-end text-muted small mt-2" style="${labelStyle}">
                                        <span>Created by: ${ev.created_by_name || 'N/A'}</span> &middot; <span>${formatDateTime(ev.created_at)}</span>
                                    </div>
                                </div>
                            </div>`;

                        // Add event listeners for edit and delete buttons
                        const editBtn = modalBody.querySelector('.edit-event-btn');
                        if (editBtn) {
                            editBtn.addEventListener('click', function () {
                                const eventId = this.getAttribute('data-id');
                                modal.hide();
                                setTimeout(() => {
                                    const editModal = new bootstrap.Modal(document.getElementById('editEventModal'));
                                    const editModalBody = document.getElementById('editEventModalBody');
                                    editModalBody.innerHTML = '<div class="d-flex justify-content-center align-items-center" style="min-height:200px;"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>';
                                    editModal.show();

                                    fetch('get_event.php?event_id=' + encodeURIComponent(eventId))
                                        .then(res => res.json())
                                        .then(data => {
                                            // The edit modal body will be populated by the event-handlers.js edit functionality
                                        });
                                }, 300);
                            });
                        }

                        const deleteBtn = modalBody.querySelector('.delete-event-btn');
                        if (deleteBtn) {
                            deleteBtn.addEventListener('click', function () {
                                const eventId = this.getAttribute('data-id');
                                const eventTitle = this.getAttribute('data-title');
                                modal.hide();

                                // Set event ID and title in delete modal
                                document.getElementById('deleteEventId').value = eventId;
                                document.getElementById('deleteEventTitle').textContent = eventTitle;

                                // Show the delete modal
                                const deleteModal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
                                deleteModal.show();
                            });
                        }
                    } else {
                        modalBody.innerHTML = `<div class='alert alert-danger'>${data.message || 'Could not load event details.'}</div>`;
                    }
                })
                .catch((error) => {
                    console.error('Error loading event details:', error);
                    modalBody.innerHTML = `<div class='alert alert-danger'>Could not load event details. Please try again.</div>`;
                });
        } else {
            // Fallback to simple alert if modal isn't available
            const title = event.title || 'Event';
            const start = formatDateTime(event.start);
            const end = formatDateTime(event.end);
            alert(`${title}\nStart: ${start}\nEnd: ${end}`);
        }
    }    // Helper to format date/time
    function formatDateTime(dt) {
        if (!dt) return '';
        try {
            const d = new Date(dt.replace(' ', 'T'));
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            };
            return d.toLocaleString(undefined, options);
        } catch (e) {
            // Fallback to basic formatting if any error occurs
            return dt;
        }
    }    // Global function to load calendar events
    window.loadAssemblyCalendarEvents = function () {
        if (!calendar) {
            // First time initialization
            initCalendar([]);
        }

        // Refresh events
        fetchAssemblyEvents()
            .then(events => {
                if (calendar) {
                    // Remove all events and add new ones
                    calendar.removeAllEvents();
                    calendar.addEventSource(mapEventsToCalendar(events));

                    // Ensure calendar is rendered
                    calendar.render();
                }
            })
            .catch(error => {
                console.error('Error loading calendar events:', error);
                if (window.showErrorMessage) {
                    window.showErrorMessage('Failed to load calendar events. Please try refreshing the page.');
                }
            });
    };

    // Initialize calendar with events
    function initCalendar(events) {
        if (calendar) return; // Only initialize once

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            events: mapEventsToCalendar(events),
            eventClick: function (info) {
                showEventDetails(info.event);
            },
            eventDisplay: 'block',
            nowIndicator: true,
            selectable: false,
            themeSystem: 'standard',
            // Add a reference to window to allow direct access
            viewDidMount: function () {
                window.assemblyCalendar = calendar;
            }
        });
        calendar.render();
    }

    // Assembly Calendar Implementation
    function loadAssemblyCalendarEvents() {
        // Get filter values
        const searchInput = document.getElementById('eventSearchInput');
        const typeFilter = document.getElementById('eventTypeFilter');
        const assemblyFilter = document.getElementById('assemblyFilter');
        const startDateFilter = document.getElementById('startDateFilter');
        const endDateFilter = document.getElementById('endDateFilter');

        const searchValue = searchInput ? searchInput.value : '';
        const typeValue = typeFilter ? typeFilter.value : '';
        const assemblyValue = assemblyFilter ? assemblyFilter.value : '';
        const startDateValue = startDateFilter ? startDateFilter.value : '';
        const endDateValue = endDateFilter ? endDateFilter.value : '';

        const params = new URLSearchParams({
            tab: 'calendar',
            page: 1,
            pageSize: 1000,
            search: searchValue,
            type: typeValue,
            assembly: assemblyValue,
            startDate: startDateValue,
            endDate: endDateValue
        });

        fetch('get_events_paginated.php?' + params.toString())
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderAssemblyCalendar(data.events);
                } else {
                    renderAssemblyCalendar([]);
                    showErrorMessage(data.message || 'Could not load calendar events.');
                }
            })
            .catch(error => {
                console.error('Error loading calendar events:', error);
                renderAssemblyCalendar([]);
                showErrorMessage('Error loading calendar events. Please try again.');
            });
    }

    // Initialize calendar when calendar tab is shown
    const calendarTab = document.getElementById('calendar-view-tab');
    if (calendarTab) {
        calendarTab.addEventListener('shown.bs.tab', function () {
            setTimeout(() => {
                loadAssemblyCalendarEvents();
            }, 100);
        });
    }

    // Load calendar if Calendar tab is active by default
    if (document.getElementById('calendar-view')?.classList.contains('show')) {
        setTimeout(() => {
            loadAssemblyCalendarEvents();
        }, 100);
    }
});

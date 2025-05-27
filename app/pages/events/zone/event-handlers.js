// Zone Events JS - modeled after household events

// Zone Events Handlers
let currentPage = 1;
const pageSize = 10;
let totalEvents = 0;

document.addEventListener('DOMContentLoaded', function () {
    // Initial load for the default tab
    loadPaginatedTabEvents('upcoming', 1);

    // Tab switching
    document.getElementById('upcoming-events-tab').addEventListener('shown.bs.tab', function () {
        loadPaginatedTabEvents('upcoming', 1);
    });
    document.getElementById('past-events-tab').addEventListener('shown.bs.tab', function () {
        loadPaginatedTabEvents('past', 1);
    });
    document.getElementById('all-events-tab').addEventListener('shown.bs.tab', function () {
        loadPaginatedTabEvents('all', 1);
    });

    // Event creation form submission
    document.getElementById('zoneEventForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = {
            title: document.getElementById('eventTitle').value,
            description: document.getElementById('eventDescription').value,
            start_date: document.getElementById('startDate').value,
            end_date: document.getElementById('endDate').value,
            event_type_id: document.getElementById('eventType').value,
            zone_id: document.getElementById('zoneId').value,
            scope_id: 3, // Set the scope_id for zone level
            level: 'zone', // Set the level for backward compatibility
            is_recurring: document.getElementById('isRecurring').checked,
            recurring_pattern: document.getElementById('recurringPattern').value
        };

        fetch('add_event_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Event created successfully!');
                    loadPaginatedTabEvents('upcoming', 1);
                    $('#createEventModal').modal('hide');
                    document.getElementById('zoneEventForm').reset();
                } else {
                    showAlert('danger', data.message || 'Error creating event');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Error creating event');
            });
    });

    // Search form submission
    document.getElementById('searchForm').addEventListener('submit', function (e) {
        e.preventDefault();
        currentPage = 1;
        loadPaginatedTabEvents('upcoming', 1);
    });

    // Add filter change listeners to reload the current tab
    ['eventSearchInput', 'eventTypeFilter', 'startDateFilter', 'endDateFilter'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', reloadActiveTab);
            el.addEventListener('change', reloadActiveTab);
        }
    });
});

function loadPaginatedTabEvents(tab, page = 1) {
    const searchParams = new URLSearchParams();
    searchParams.append('page', page);
    searchParams.append('pageSize', pageSize);
    searchParams.append('search', document.getElementById('eventSearchInput').value);
    searchParams.append('type', document.getElementById('eventTypeFilter').value);
    searchParams.append('startDate', document.getElementById('startDateFilter').value);
    searchParams.append('endDate', document.getElementById('endDateFilter').value);
    if (tab === 'upcoming' || tab === 'past') searchParams.append('tab', tab);

    console.log(`Loading ${tab} events, page ${page} with params: ${searchParams.toString()}`); // Debug logging

    fetch(`get_events_paginated.php?${searchParams.toString()}`)
        .then(response => response.json())
        .then(data => {
            console.log('Received data:', data); // Debug logging

            if (data.success) {
                const tableId = tab === 'upcoming' ? 'upcomingEventsTable' :
                    tab === 'past' ? 'pastEventsTable' : 'eventsTable';
                const paginationId = tab === 'upcoming' ? 'upcomingEventsPagination' :
                    tab === 'past' ? 'pastEventsPagination' : 'eventsPagination';

                renderEventsTable(tableId, data.events, page);
                renderPagination(data.total, data.page, data.pageSize, p => loadPaginatedTabEvents(tab, p), paginationId);

                // Update dashboard counts if applicable
                if (tab === 'upcoming') {
                    const upcomingCount = document.getElementById('upcomingEventsCount');
                    if (upcomingCount) upcomingCount.textContent = data.total;
                }
                if (tab === 'all') {
                    const totalCount = document.getElementById('totalEventsCount');
                    if (totalCount) totalCount.textContent = data.total;
                }
            } else {
                console.error('Error loading events:', data.message); // Debug logging
                const tableId = tab === 'upcoming' ? 'upcomingEventsTable' :
                    tab === 'past' ? 'pastEventsTable' : 'eventsTable';
                const paginationId = tab === 'upcoming' ? 'upcomingEventsPagination' :
                    tab === 'past' ? 'pastEventsPagination' : 'eventsPagination';

                renderEventsTable(tableId, [], page);
                renderPagination(0, 1, pageSize, p => { }, paginationId);
            }
        })
        .catch(error => {
            console.error('Network error:', error); // Debug logging
            const tableId = tab === 'upcoming' ? 'upcomingEventsTable' :
                tab === 'past' ? 'pastEventsTable' : 'eventsTable';
            const paginationId = tab === 'upcoming' ? 'upcomingEventsPagination' :
                tab === 'past' ? 'pastEventsPagination' : 'eventsPagination';

            renderEventsTable(tableId, [], page);
            renderPagination(0, 1, pageSize, p => { }, paginationId);
        });
}

function renderEventsTable(tableId, events, page) {
    console.log(`Rendering table ${tableId} with ${events ? events.length : 0} events`); // Debug logging

    const table = document.getElementById(tableId);
    if (!table) {
        console.error(`Table not found: ${tableId}`); // Debug logging
        return;
    }

    const tbody = table.querySelector('tbody');
    tbody.innerHTML = '';

    if (!events || events.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No events found</td></tr>';
        return;
    }

    events.forEach((event, idx) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${(page - 1) * pageSize + idx + 1}</td>
            <td>${event.title}</td>
            <td>${event.event_type || 'N/A'}</td>
            <td>${event.start_date}</td>
            <td>${event.end_date}</td>
            <td>${event.is_recurring == 1 ? 'Yes' : 'No'}</td>
            <td class="d-flex">
                <button class="btn btn-sm btn-info me-1" onclick="viewEvent(${event.event_id})" title="View">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-primary me-1" onclick="editEvent(${event.event_id})" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteEvent(${event.event_id})" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function renderPagination(total, page, pageSize, onPageChange, paginationId) {
    const totalPages = Math.ceil(total / pageSize);
    const pag = document.getElementById(paginationId);
    if (!pag) return;
    pag.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = 'page-item' + (i === page ? ' active' : '');
        const a = document.createElement('a');
        a.className = 'page-link';
        a.textContent = i;
        a.href = '#';
        a.onclick = function (e) {
            e.preventDefault();
            onPageChange(i);
        };
        li.appendChild(a);
        pag.appendChild(li);
    }
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    document.getElementById('alertContainer').appendChild(alertDiv);
    setTimeout(() => alertDiv.remove(), 5000);
}

function editEvent(eventId) {
    // TODO: Implement event editing
    console.log('Edit event:', eventId);
}

function deleteEvent(eventId) {
    if (confirm('Are you sure you want to delete this event?')) {
        // TODO: Implement event deletion
        console.log('Delete event:', eventId);
    }
}

function reloadActiveTab() {
    const activeTab = document.querySelector('#eventViewTabs .nav-link.active');
    if (activeTab) {
        if (activeTab.id === 'upcoming-events-tab') loadPaginatedTabEvents('upcoming', 1);
        else if (activeTab.id === 'past-events-tab') loadPaginatedTabEvents('past', 1);
        else if (activeTab.id === 'all-events-tab') loadPaginatedTabEvents('all', 1);
    }
}

// Show event details in modal
function viewEvent(eventId) {
    const modal = new bootstrap.Modal(document.getElementById('viewEventModal'));
    const body = document.getElementById('viewEventModalBody');
    body.innerHTML = '<div class="text-center py-4"><span class="spinner-border"></span> Loading...</div>';
    modal.show();
    fetch(`get_event.php?id=${eventId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const e = data.event;
                // Format dates
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                const sd = new Date(e.start_date);
                const ed = new Date(e.end_date);
                const start = `${sd.toLocaleDateString(undefined, options)} . ${sd.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', hour12: true }).toLowerCase()}`;
                const end = `${ed.toLocaleDateString(undefined, options)} . ${ed.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', hour12: true }).toLowerCase()}`;
                // Build HTML
                body.innerHTML = `
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>${e.title}</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Event Type</dt><dd class="col-sm-8">${e.event_type || '-'}</dd>
                                <dt class="col-sm-4">Zone</dt><dd class="col-sm-8">${e.zone_name || '-'}</dd>
                                <dt class="col-sm-4">Start Date & Time</dt><dd class="col-sm-8">${start}</dd>
                                <dt class="col-sm-4">End Date & Time</dt><dd class="col-sm-8">${end}</dd>
                                <dt class="col-sm-4">Recurring</dt><dd class="col-sm-8">${e.is_recurring == 1 ? 'Yes' : 'No'}</dd>
                                <dt class="col-sm-4">Description</dt><dd class="col-sm-8">${e.description ? e.description.replace(/\n/g, '<br>') : '-'}</dd>
                            </dl>
                        </div>
                    </div>
                `;
            } else {
                body.innerHTML = `<div class="alert alert-warning">${data.message}</div>`;
            }
        })
        .catch(err => {
            console.error('Error fetching event:', err);
            body.innerHTML = '<div class="alert alert-danger">Error loading event details.</div>';
        });
}

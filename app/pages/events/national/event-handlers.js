// event-handlers.js for national events
// Handles loading, filtering, and paginating events for all tabs

document.addEventListener('DOMContentLoaded', function () {
    const tabIds = ['upcoming', 'past', 'all'];
    const tableIds = {
        upcoming: 'upcomingEventsTable',
        past: 'pastEventsTable',
        all: 'eventsTable'
    };
    const paginationIds = {
        upcoming: 'upcomingEventsPagination',
        past: 'pastEventsPagination',
        all: 'eventsPagination'
    };
    let currentTab = 'upcoming';
    let currentPage = { upcoming: 1, past: 1, all: 1 };
    let pageSize = 10;

    // Cache for event data
    const eventCache = {};

    // Filter elements
    const searchInput = document.getElementById('eventSearchInput');
    const typeFilter = document.getElementById('eventTypeFilter');
    const startDateFilter = document.getElementById('startDateFilter');
    const endDateFilter = document.getElementById('endDateFilter');

    // Define globally before any usage
    window.loadPaginatedTabEvents = function (tab, page) {
        currentTab = tab;
        currentPage[tab] = page;
        const params = new URLSearchParams({
            tab: tab,
            page: page,
            pageSize: pageSize,
            search: searchInput ? searchInput.value : '',
            type: typeFilter ? typeFilter.value : '',
            startDate: startDateFilter ? startDateFilter.value : '',
            endDate: endDateFilter ? endDateFilter.value : ''
        });
        // Log tab load to backend debug.log
        fetch('get_events_paginated.php?log_only=1&' + params.toString(), { method: 'GET' });
        fetch('get_events_paginated.php?' + params.toString())
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderEventsTable(tab, data.events, page);
                    renderPagination(data.total, data.page, data.pageSize, p => loadPaginatedTabEvents(tab, p), paginationIds[tab]);
                    // Update dashboard counts if needed
                    if (tab === 'upcoming') {
                        document.getElementById('upcomingEventsCount').textContent = data.total;
                    }
                    if (tab === 'all') {
                        document.getElementById('totalEventsCount').textContent = data.total;
                    }
                } else {
                    renderEventsTable(tab, [], page);
                    renderPagination(0, 1, pageSize, () => { }, paginationIds[tab]);
                }
            })
            .catch(() => {
                renderEventsTable(tab, [], page);
                renderPagination(0, 1, pageSize, () => { }, paginationIds[tab]);
            });
    };

    // Tab switching (update currentTab before calling loader)
    tabIds.forEach(tab => {
        const tabBtn = document.getElementById(tab + '-events-tab');
        if (tabBtn) {
            tabBtn.addEventListener('click', function () {
                currentTab = tab;
                loadPaginatedTabEvents(tab, 1);
            });
        }
    });

    // Filter change
    [searchInput, typeFilter, startDateFilter, endDateFilter].forEach(el => {
        if (el) {
            el.addEventListener('change', function () {
                loadPaginatedTabEvents(currentTab, 1);
            });
        }
    });

    // Initial load
    loadPaginatedTabEvents('upcoming', 1);

    function formatDate(dateStr) {
        try {
            if (!dateStr) return 'Not set';
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return dateStr;
            const day = date.getDate();
            const monthNames = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            const month = monthNames[date.getMonth()];
            const year = date.getFullYear();
            const ordinal = (day > 3 && day < 21) ? 'th' : (['st', 'nd', 'rd'][((day % 10) - 1)] || 'th');
            const hours = date.getHours();
            const minutes = date.getMinutes();
            const ampm = hours >= 12 ? 'pm' : 'am';
            const hour12 = hours % 12 || 12;
            const timePart = `${hour12}:${minutes.toString().padStart(2, '0')} ${ampm}`;
            return `${day}${ordinal} ${month}, ${year} . ${timePart}`;
        } catch (e) {
            return dateStr;
        }
    }

    function renderEventsTable(tab, events, page) {
        const table = document.getElementById(tableIds[tab]);
        if (!table) return;
        const tbody = table.querySelector('tbody');
        tbody.innerHTML = '';
        if (!events || !events.length) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 7;
            td.className = 'text-center';
            td.textContent = 'No events found.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }
        // Cache events for view modal
        events.forEach(ev => {
            eventCache[ev.event_id] = ev;
        });
        events.forEach((ev, i) => {
            const startDate = ev.start_date || '';
            const endDate = ev.end_date || '';
            const isRecurring = ev.is_recurring == 1 ? 'Yes' : 'No';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${(page - 1) * pageSize + i + 1}</td>
                <td>${ev.title || ''}</td>
                <td>${ev.event_type || ''}</td>
                <td>${formatDate(startDate)}</td>
                <td>${formatDate(endDate)}</td>
                <td>${isRecurring}</td>
                <td>
                    <button class="btn btn-sm btn-info me-1" onclick="viewEvent(${ev.event_id})" title="View Event">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-primary me-1" onclick="editEvent(${ev.event_id})" title="Edit Event">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="showDeleteConfirmation(${ev.event_id}, '${(ev.title || '').replace(/'/g, "\\'")}')" title="Delete Event">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
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
    }    // View, Edit and delete event functions
    window.viewEvent = function (eventId) {
        // Show loading state
        document.getElementById('viewEventContent').innerHTML = `
            <div class="text-center my-5">
                <div class="spinner-border text-info" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading event details...</p>
            </div>
        `;
        var viewModal = new bootstrap.Modal(document.getElementById('viewEventModal'));
        viewModal.show();

        // Check if event data is cached
        const eventData = eventCache[eventId];
        if (eventData) {
            // Modern, attractive, professional modal design
            document.getElementById('viewEventContent').innerHTML = `
                <div class="card border-0 shadow-lg rounded-4 animate__animated animate__fadeIn">
                    <div class="card-header bg-gradient-primary text-white d-flex align-items-center rounded-top-4" style="background: linear-gradient(90deg, #007bff 0%, #00d4ff 100%);">
                        <i class="bi bi-calendar-event fs-3 me-2"></i>
                        <h4 class="mb-0 flex-grow-1">${eventData.title || 'Untitled Event'}</h4>
                        <span class="badge bg-light text-primary ms-2">ID: ${eventData.event_id}</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted fw-semibold"><i class="bi bi-tag"></i> Event Type</div>
                            <div class="col-sm-8">
                                <span class="badge bg-primary">${eventData.event_type || '-'}</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted fw-semibold"><i class="bi bi-calendar-check"></i> Start</div>
                            <div class="col-sm-8">${formatDate(eventData.start_date) || '-'}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted fw-semibold"><i class="bi bi-calendar-x"></i> End</div>
                            <div class="col-sm-8">${formatDate(eventData.end_date) || '-'}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted fw-semibold"><i class="bi bi-arrow-repeat"></i> Recurring</div>
                            <div class="col-sm-8">
                                ${eventData.is_recurring == 1 ? '<span class="badge bg-info">Yes</span>' : '<span class="badge bg-secondary">No</span>'}
                            </div>
                        </div>
                        ${eventData.location ? `
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted fw-semibold"><i class="bi bi-geo-alt"></i> Location</div>
                            <div class="col-sm-8">${eventData.location}</div>
                        </div>` : ''}
                        ${eventData.description ? `
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted fw-semibold"><i class="bi bi-card-text"></i> Description</div>
                            <div class="col-sm-8">${eventData.description}</div>
                        </div>` : ''}
                    </div>
                    <div class="card-footer bg-light d-flex justify-content-end gap-2 rounded-bottom-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Close
                        </button>
                        <button type="button" class="btn btn-gradient-primary" id="editFromViewBtn">
                            <i class="bi bi-pencil"></i> Edit Event
                        </button>
                    </div>
                </div>
                <style>
                .btn-gradient-primary {
                    background: linear-gradient(90deg, #007bff 0%, #00d4ff 100%);
                    color: #fff;
                    border: none;
                }
                .btn-gradient-primary:hover {
                    background: linear-gradient(90deg, #0056b3 0%, #00b8d4 100%);
                    color: #fff;
                }
                .card-header.bg-gradient-primary {
                    background: linear-gradient(90deg, #007bff 0%, #00d4ff 100%) !important;
                }
                .rounded-4 { border-radius: 1.25rem !important; }
                </style>
            `;
            document.getElementById('editFromViewBtn').style.display = 'inline-block';
            document.getElementById('editFromViewBtn').onclick = function () {
                viewModal.hide();
                setTimeout(() => {
                    window.loadEventForEdit(eventId);
                }, 300);
            };
        } else {
            // Fallback to server fetch if not cached
            fetch('view_event.php?event_id=' + eventId + '&render=html')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('viewEventContent').innerHTML = html;
                    document.getElementById('editFromViewBtn').style.display = 'inline-block';
                    document.getElementById('editFromViewBtn').onclick = function () {
                        viewModal.hide();
                        setTimeout(() => {
                            window.loadEventForEdit(eventId);
                        }, 300);
                    };
                })
                .catch(() => {
                    document.getElementById('viewEventContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> Error loading event details.
                        </div>
                    `;
                    document.getElementById('editFromViewBtn').style.display = 'none';
                });
        }
    };

    window.editEvent = function (eventId) {
        if (typeof window.loadEventForEdit === 'function') {
            window.loadEventForEdit(eventId);
        } else {
            alert('Edit function not available: ' + eventId);
        }
    }; window.deleteEvent = function (eventId, eventName) {
        // Use the new Bootstrap modal for confirmation
        if (typeof window.showDeleteConfirmation === 'function') {
            window.showDeleteConfirmation(eventId, eventName);
        } else {
            // Fallback to simple confirm if modal function is not available
            if (confirm('Are you sure you want to delete this event?')) {
                fetch('delete_event_process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ eventId: eventId })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            var alert = document.createElement('div');
                            alert.className = 'alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3';
                            alert.style.zIndex = 1050;
                            alert.innerHTML = '<strong>Success!</strong> ' + data.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                            document.body.appendChild(alert);

                            // Reload current tab
                            loadPaginatedTabEvents(currentTab, currentPage[currentTab]);
                        } else {
                            alert(data.message || 'Error deleting event.');
                        }
                    })
                    .catch(() => {
                        alert('Error deleting event.');
                    });
            }
        }
    };
});

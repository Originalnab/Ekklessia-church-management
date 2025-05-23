/**
 * Fixed version of event-handlers.js with corrected object key handling
 * This script properly handles the data returned from the server and displays events in tables
 */

document.addEventListener('DOMContentLoaded', function () {
    // Tab and filter elements
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

    // Make these accessible globally for other scripts
    window.currentTab = currentTab;
    window.currentPage = currentPage;

    // Filter elements
    const searchInput = document.getElementById('eventSearchInput');
    const typeFilter = document.getElementById('eventTypeFilter');
    const assemblyFilter = document.getElementById('assemblyFilter');
    const startDateFilter = document.getElementById('startDateFilter');
    const endDateFilter = document.getElementById('endDateFilter');

    // Initialize tabs and event counts on page load
    initializeEventCounts();

    // Keep session alive with periodic pings to prevent session timeouts
    setInterval(function () {
        fetch('index.php?ping=1')
            .then(res => res.text())
            .catch(() => console.log('Session ping failed'));
    }, 5 * 60 * 1000); // Every 5 minutes

    // Load events for a tab with filters and pagination
    window.loadPaginatedTabEvents = function (tab, page) {
        currentTab = tab;
        window.currentTab = tab;
        currentPage[tab] = page;
        window.currentPage[tab] = page;

        const params = new URLSearchParams({
            tab: tab,
            page: page,
            pageSize: pageSize,
            search: searchInput ? searchInput.value : '',
            type: typeFilter ? typeFilter.value : '',
            assembly: assemblyFilter ? assemblyFilter.value : '',
            startDate: startDateFilter ? startDateFilter.value : '',
            endDate: endDateFilter ? endDateFilter.value : ''
        });

        console.log(`Loading events for tab: ${tab}, page: ${page} with params:`, params.toString());

        fetch('get_events_paginated.php?' + params.toString())
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    console.log(`Tab ${tab}: Loaded ${data.events.length} events`);
                    console.log('Sample event data:', data.events.length > 0 ? data.events[0] : 'No events');

                    // Clean up event data - trim whitespace
                    data.events.forEach(event => {
                        Object.keys(event).forEach(key => {
                            if (typeof event[key] === 'string') {
                                event[key] = event[key].trim();
                            }
                        });
                    });

                    renderEventsTable(tab, data.events);
                    renderPagination(data.total, data.page, data.pageSize, p => loadPaginatedTabEvents(tab, p));

                    // Update event counts
                    if (tab === 'upcoming') {
                        document.getElementById('upcomingEventsCount').textContent = data.total;
                    }
                    if (tab === 'all') {
                        document.getElementById('totalEventsCount').textContent = data.total;
                    }
                } else {
                    console.error('Error loading events:', data.message);
                    renderEventsTable(tab, []);
                    renderPagination(0, 1, pageSize, () => { });
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                renderEventsTable(tab, []);
                renderPagination(0, 1, pageSize, () => { });
            });
    };

    // Initialize event counts
    function initializeEventCounts() {
        // Load counts for each tab
        tabIds.forEach(tab => {
            fetch('get_events_paginated.php?tab=' + tab + '&page=1&pageSize=1')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (tab === 'upcoming') {
                            document.getElementById('upcomingEventsCount').textContent = data.total;
                        } else if (tab === 'all') {
                            document.getElementById('totalEventsCount').textContent = data.total;
                        }
                    }
                })
                .catch(console.error);
        });

        // Get recurring events count
        fetch('get_events_paginated.php?tab=all&page=1&pageSize=1&isRecurring=1')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('recurringEventsCount').textContent = data.total;
                }
            })
            .catch(console.error);

        // Get this month's events
        const now = new Date();
        const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
        const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
        fetch(`get_events_paginated.php?tab=all&page=1&pageSize=1&startDate=${startOfMonth}&endDate=${endOfMonth}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('thisMonthEventsCount').textContent = data.total;
                }
            })
            .catch(console.error);
    }

    // Helper function to get the ordinal suffix (e.g., "st", "nd", "rd", "th")
    function getOrdinalSuffix(day) {
        if (day > 3 && day < 21) return 'th';
        switch (day % 10) {
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
            default: return 'th';
        }
    }

    // Format date as "3rd January, 2024 . 10:25 am" (UPDATED FUNCTION)
    function formatDateWords(dateStr) {
        if (!dateStr) return 'Not set';

        try {
            // Try to create a valid date object - handle different formats
            let date;

            if (dateStr.includes('T')) {
                // ISO format
                date = new Date(dateStr);
            } else if (dateStr.includes('-')) {
                // YYYY-MM-DD HH:MM:SS format
                const parts = dateStr.trim().split(/[- :]/);
                date = new Date(parts[0], parts[1] - 1, parts[2], parts[3] || 0, parts[4] || 0, parts[5] || 0);
            } else if (dateStr.includes('/')) {
                // MM/DD/YYYY format
                date = new Date(dateStr);
            } else {
                date = new Date(dateStr);
            }

            // Check if date is valid
            if (isNaN(date.getTime())) {
                console.error("Invalid date string:", dateStr);
                return dateStr; // Return original if invalid
            }

            const day = date.getDate();
            const monthNames = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            const month = monthNames[date.getMonth()];
            const year = date.getFullYear();
            const ordinal = getOrdinalSuffix(day);

            // Format time
            const hours = date.getHours();
            const minutes = date.getMinutes();
            const ampm = hours >= 12 ? 'pm' : 'am';
            const hour12 = hours % 12 || 12;
            const timePart = `${hour12}:${minutes.toString().padStart(2, '0')} ${ampm}`;

            const formatted = `${day}${ordinal} ${month}, ${year} . ${timePart}`;
            return formatted;
        } catch (e) {
            console.error("Error formatting date:", e, dateStr);
            return dateStr; // Return original if error
        }
    }

    // Make formatDateWords accessible globally for debug purposes
    window.formatDateWords = formatDateWords;

    // Render pagination
    function renderPagination(total, currentPg, pgSize, pageCallback) {
        const totalPages = Math.ceil(total / pgSize);
        const paginationEl = document.getElementById(paginationIds[currentTab]);
        if (!paginationEl) return;

        paginationEl.innerHTML = '';

        if (totalPages <= 1) return;

        // Previous button
        let li = document.createElement('li');
        li.className = 'page-item' + (currentPg <= 1 ? ' disabled' : '');
        let a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = 'Previous';
        a.addEventListener('click', e => {
            e.preventDefault();
            if (currentPg > 1) pageCallback(currentPg - 1);
        });
        li.appendChild(a);
        paginationEl.appendChild(li);

        // Page numbers
        const startPage = Math.max(1, currentPg - 2);
        const endPage = Math.min(totalPages, currentPg + 2);

        for (let i = startPage; i <= endPage; i++) {
            let li = document.createElement('li');
            li.className = 'page-item' + (i === currentPg ? ' active' : '');
            let a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = i;
            a.addEventListener('click', e => {
                e.preventDefault();
                pageCallback(i);
            });
            li.appendChild(a);
            paginationEl.appendChild(li);
        }

        // Next button
        li = document.createElement('li');
        li.className = 'page-item' + (currentPg >= totalPages ? ' disabled' : '');
        a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = 'Next';
        a.addEventListener('click', e => {
            e.preventDefault();
            if (currentPg < totalPages) pageCallback(currentPg + 1);
        });
        li.appendChild(a);
        paginationEl.appendChild(li);
    }

    // Render events in the table for a tab
    function renderEventsTable(tab, events) {
        const tableId = tableIds[tab];
        const table = document.getElementById(tableId);

        console.log(`Rendering ${events.length} events to table ID: ${tableId}`);

        if (!table) {
            console.error(`Table element #${tableId} not found`);
            return;
        }

        const tbody = table.querySelector('tbody');
        if (!tbody) {
            console.error(`Table body not found for table #${tableId}`);
            return;
        }

        tbody.innerHTML = '';

        if (!events || !events.length) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 8;
            td.className = 'text-center';
            td.textContent = 'No events found.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        // Debug: Log the first event object structure
        if (events.length > 0) {
            console.log('First event object:', events[0]);

            // Event key mapping - this handles potential key name mismatches from database
            const getEventName = (ev) => ev.event_name || ev.title || '';
            const getEventType = (ev) => ev.event_type || '';
            const getAssemblyName = (ev) => ev.assembly_name || '';
            const getStartDate = (ev) => ev.start_date || '';
            const getEndDate = (ev) => ev.end_date || '';
            const getIsRecurring = (ev) => ev.is_recurring || 0;
            const getEventId = (ev) => ev.event_id || '';
        }

        events.forEach((ev, i) => {
            // Map database field names to local variables
            const eventName = ev.event_name || ev.title || '';
            const eventType = ev.event_type || '';
            const assemblyName = ev.assembly_name || '';
            const startDate = ev.start_date || '';
            const endDate = ev.end_date || '';
            const isRecurring = ev.is_recurring == 1 ? 'Yes' : 'No';
            const eventId = ev.event_id || '';

            // Format dates
            let startDateFormatted, endDateFormatted;
            try {
                startDateFormatted = formatDateWords(startDate);
                endDateFormatted = formatDateWords(endDate);
            } catch (e) {
                console.error(`Error formatting dates for event ${i + 1}:`, e);
                startDateFormatted = startDate;
                endDateFormatted = endDate;
            }

            // Create table row
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${i + 1}</td>
                <td>${eventName}</td>
                <td>${eventType}</td>
                <td>${assemblyName}</td>
                <td>${startDateFormatted}</td>
                <td>${endDateFormatted}</td>
                <td>${isRecurring}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-primary edit-event" data-event-id="${eventId}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-danger delete-event" data-event-id="${eventId}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Attach event handlers for edit and delete buttons
        attachEventButtonHandlers();
    }

    // Attach event handlers for edit and delete buttons    
    function attachEventButtonHandlers() {
        document.querySelectorAll('.edit-event').forEach(btn => {
            btn.addEventListener('click', function () {
                const eventId = this.getAttribute('data-event-id');
                console.log('Edit button clicked for event ID:', eventId);
                console.log('window.loadEventData exists:', typeof window.loadEventData === 'function');

                // Call the edit modal loader from event-edit-fixed.js
                if (window.loadEventData) {
                    window.loadEventData(eventId);
                } else {
                    console.error('loadEventData function is not available');
                    alert('Edit functionality not available.');
                }
            });
        });

        document.querySelectorAll('.delete-event').forEach(btn => {
            btn.addEventListener('click', function () {
                const eventId = this.getAttribute('data-event-id');
                if (confirm('Are you sure you want to delete this event?')) {
                    // Placeholder for delete functionality
                    alert('Delete event ' + eventId + ' functionality to be implemented');
                }
            });
        });
    }

    // Tab switching
    tabIds.forEach(tab => {
        const tabBtn = document.getElementById(tab + '-events-tab');
        if (tabBtn) {
            tabBtn.addEventListener('click', function () {
                loadPaginatedTabEvents(tab, currentPage[tab]);
            });
        }
    });

    // Filter change
    [searchInput, typeFilter, assemblyFilter, startDateFilter, endDateFilter].forEach(el => {
        if (el) {
            el.addEventListener('change', function () {
                loadPaginatedTabEvents(currentTab, 1);
            });
        }
    });

    // Initial load
    loadPaginatedTabEvents('upcoming', 1);

    // Debug output showing tabs and filters are initialized
    console.log('Event handlers initialized: tabs=', tabIds, 'currentTab=', currentTab);
});

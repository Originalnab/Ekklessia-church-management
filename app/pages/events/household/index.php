<?php
// Household Events Index Page
// Copied and adapted from the old event system
session_start();
$page_title = "Household Event Management";
include "../../../config/db.php";

// Check if user is logged in using member_id
if (!isset($_SESSION['member_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /Ekklessia-church-management/app/pages/login.php?redirect=$redirect_url");
    exit;
}

$logged_in_member_id = $_SESSION['member_id'];

// Fetch assemblies for the dropdown
try {
    $stmt = $pdo->query("SELECT assembly_id, name FROM assemblies WHERE status = 1 ORDER BY name ASC");
    $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $assemblies = [];
    $_SESSION['error_message'] = "Error fetching assemblies: " . $e->getMessage();
}

// Fetch event types for the Add/Edit Event modals (only household level)
try {
    $stmt = $pdo->query("SELECT event_type_id, name, is_recurring, default_frequency, level FROM event_types WHERE level = 'household' ORDER BY name ASC");
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $event_types = [];
    $_SESSION['error_message'] = "Error fetching event types: " . $e->getMessage();
}

// Fetch total household events count for dashboard
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_events FROM events WHERE level = 'household'");
    $total_events = $stmt->fetch(PDO::FETCH_ASSOC)['total_events'];
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching total events: " . $e->getMessage();
    $total_events = 0;
}

$base_url = '/Ekklessia-church-management/app/pages';
?>
<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
<body class="d-flex flex-column min-vh-100">
<main class="container flex-grow-1 py-2">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Success!</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Error!</strong> <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <!-- Navigation Card -->
    <div class="card nav-card" style="margin-top: -30px; position: relative; top: -10px;">
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/members/index.php" class="nav-link-btn">
                        <i class="bi bi-people-fill text-primary"></i>
                        <span>Members</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/assemblies/index.php" class="nav-link-btn">
                        <i class="bi bi-building-fill text-success"></i>
                        <span>Assemblies</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/households/index.php" class="nav-link-btn">
                        <i class="bi bi-house-fill text-warning"></i>
                        <span>Households</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/zones/index.php" class="nav-link-btn">
                        <i class="bi bi-globe-americas text-info"></i>
                        <span>Zones</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/events/index.php" class="nav-link-btn">
                        <i class="bi bi-calendar-event text-danger"></i>
                        <span>Events</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Mini Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #007bff, #00d4ff); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-event fs-2"></i>
                    <h6 class="card-title text-white">Total Events</h6>
                    <h3 class="card-text" id="totalEventsCount"><?= $total_events ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #28a745, #6fcf97); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check fs-2"></i>
                    <h6 class="card-title text-white">Upcoming Events</h6>
                    <h3 class="card-text" id="upcomingEventsCount">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #17a2b8, #4fc3f7); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-repeat fs-2"></i>
                    <h6 class="card-title text-white">Recurring Events</h6>
                    <h3 class="card-text" id="recurringEventsCount">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #dc3545, #ff6b6b); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-clock fs-2"></i>
                    <h6 class="card-title text-white">Events This Month</h6>
                    <h3 class="card-text" id="thisMonthEventsCount">0</h3>
                </div>
            </div>
        </div>
    </div>
    <!-- Events Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Household Events</h5>
            <div class="btn-group float-end" role="group" aria-label="Event Actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <i class="bi bi-plus-circle"></i> Add Event
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Tabs for Table View and Calendar View -->
            <!-- Tabs navigation -->
            <ul class="nav nav-tabs mb-3" id="eventViewTabs">
                <li class="nav-item">
                    <a class="nav-link active" id="upcoming-events-tab" data-bs-toggle="tab" href="#upcoming-events" role="tab">Upcoming Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="past-events-tab" data-bs-toggle="tab" href="#past-events" role="tab">Past Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="all-events-tab" data-bs-toggle="tab" href="#all-events" role="tab">All Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="calendar-view-tab" data-bs-toggle="tab" href="#calendar-view" role="tab">Calendar View</a>
                </li>
            </ul>

            <!-- Filter Controls -->
            <div class="mb-3" id="eventFilters">
                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <input type="text" id="eventSearchInput" class="form-control" placeholder="Search events by name...">
                    </div>
                    <div class="col-md-2">
                        <select id="eventTypeFilter" class="form-select">
                            <option value="">All Event Types</option>
                            <?php foreach ($event_types as $event_type): ?>
                                <option value="<?= htmlspecialchars($event_type['name']) ?>"><?= htmlspecialchars($event_type['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="assemblyFilter" class="form-select">
                            <option value="">All Assemblies</option>
                            <?php foreach ($assemblies as $assembly): ?>
                                <option value="<?= htmlspecialchars($assembly['name']) ?>"><?= htmlspecialchars($assembly['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="startDateFilter" class="form-control" placeholder="Start date">
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="endDateFilter" class="form-control" placeholder="End date">
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Upcoming Events -->
                <div class="tab-pane fade show active" id="upcoming-events" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="upcomingEventsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Event Name</th>
                                    <th>Event Type</th>
                                    <th>Assembly</th>
                                    <th>Start Date & Time</th>
                                    <th>End Date & Time</th>
                                    <th>Recurring</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <nav>
                        <ul class="pagination justify-content-center" id="upcomingEventsPagination"></ul>
                    </nav>
                </div>
                
                <!-- Past Events -->
                <div class="tab-pane fade" id="past-events" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="pastEventsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Event Name</th>
                                    <th>Event Type</th>
                                    <th>Assembly</th>
                                    <th>Start Date & Time</th>
                                    <th>End Date & Time</th>
                                    <th>Recurring</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <nav>
                        <ul class="pagination justify-content-center" id="pastEventsPagination"></ul>
                    </nav>
                </div>
                
                <!-- All Events -->
                <div class="tab-pane fade" id="all-events" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="eventsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Event Name</th>
                                    <th>Event Type</th>
                                    <th>Assembly</th>
                                    <th>Start Date & Time</th>
                                    <th>End Date & Time</th>
                                    <th>Recurring</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamic content will be loaded here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <nav>
                        <ul class="pagination justify-content-center" id="eventsPagination"></ul>
                    </nav>
                </div>
                
                <!-- Calendar View -->
                <div class="tab-pane fade" id="calendar-view" role="tabpanel">
                    <div id="calendar" style="min-height: 600px;"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEventModalLabel">Add Household Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addEventForm">
                        <input type="hidden" name="level" value="1">
                        <div class="mb-3">
                            <label for="assemblySelect" class="form-label">Assembly <span class="text-danger">*</span></label>
                            <select class="form-select" id="assemblySelect" name="assembly_id" required>
                                <option value="">Select Assembly</option>
                                <?php foreach ($assemblies as $assembly): ?>
                                    <option value="<?= $assembly['assembly_id'] ?>"><?= htmlspecialchars($assembly['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Household(s) <span class="text-danger">*</span></label>
                            <div id="householdCheckboxList" class="border rounded p-2" style="min-height: 60px; max-height: 220px; overflow-y: auto; background: #f8f9fa;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAllHouseholdsCheckbox" disabled>
                                    <label class="form-check-label fw-bold" for="selectAllHouseholdsCheckbox">Select All</label>
                                </div>
                                <div id="householdCheckboxes"></div>
                            </div>
                            <small class="form-text text-muted">Tap to select one or more households. Use 'Select All' to quickly select all.</small>
                        </div>
                        <div class="mb-3">
                            <label for="eventName" class="form-label">Event Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="eventName" name="eventName" required>
                        </div>
                        <div class="mb-3">
                            <label for="eventType" class="form-label">Event Type</label>
                            <select class="form-select" id="eventType" name="eventType" required>
                                <option value="">Select Event Type</option>
                                <?php foreach ($event_types as $event_type): ?>
                                    <option value="<?= $event_type['event_type_id'] ?>"><?= htmlspecialchars($event_type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="startDate" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="endDate" name="end_date" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="isRecurring" name="isRecurring">
                            <label class="form-check-label" for="isRecurring">Is this a recurring event?</label>
                        </div>
                        <div class="mb-3" id="recurrenceOptions" style="display: none;">
                            <label for="recurrenceFrequency" class="form-label">Recurrence Frequency</label>
                            <select class="form-select" id="recurrenceFrequency" name="recurrenceFrequency">
                                <option value="">Select Frequency</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Event Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEventModalLabel">Edit Household Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editEventForm">
                        <input type="hidden" id="editEventId" name="eventId">
                        <div class="mb-3">
                            <label for="editEventName" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="editEventName" name="eventName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEventType" class="form-label">Event Type</label>
                            <select class="form-select" id="editEventType" name="eventType" required>
                                <option value="">Select Event Type</option>
                                <?php foreach ($event_types as $event_type): ?>
                                    <option value="<?= $event_type['event_type_id'] ?>"><?= htmlspecialchars($event_type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>                        <div class="mb-3">
                            <label for="editStartDate" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="editStartDate" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEndDate" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="editEndDate" name="end_date" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="editIsRecurring" name="isRecurring">
                            <label class="form-check-label" for="editIsRecurring">Is this a recurring event?</label>
                        </div>
                        <div class="mb-3" id="editRecurrenceOptions" style="display: none;">
                            <label for="editRecurrenceFrequency" class="form-label">Recurrence Frequency</label>
                            <select class="form-select" id="editRecurrenceFrequency" name="recurrenceFrequency">
                                <option value="">Select Frequency</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>    <!-- Scripts -->
    <script src="event-handlers.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="pagination.js"></script>
    <script>
        // Function to format date
        function formatDate(dateStr) {
            try {
                const date = new Date(dateStr);
                // Format: July 1, 2025 . 10:25 am
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                let datePart = date.toLocaleDateString(undefined, options);
                let timePart = date.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', hour12: true });
                return `${datePart} . ${timePart.toLowerCase()}`;
            } catch (e) {
                console.error('Date formatting error:', e);
                return dateStr;
            }
        }

        // Function to update dashboard counts
        function updateDashboardCounts(events) {
            const now = new Date();
            const totalEvents = events.length;
            const upcomingEvents = events.filter(e => new Date(e.start_date) >= now).length;
            const recurringEvents = events.filter(e => e.is_recurring).length;
            const thisMonthEvents = events.filter(e => {
                const eventDate = new Date(e.start_date);
                return eventDate.getMonth() === now.getMonth() && eventDate.getFullYear() === now.getFullYear();
            }).length;
            document.getElementById('totalEventsCount').textContent = totalEvents;
            document.getElementById('upcomingEventsCount').textContent = upcomingEvents;
            document.getElementById('recurringEventsCount').textContent = recurringEvents;
            document.getElementById('thisMonthEventsCount').textContent = thisMonthEvents;
        }

        // Pagination state for each tab
        let upcomingPage = 1;
        let pastPage = 1;
        let eventsPage = 1;
        let eventsPageSize = 10;

        function getFilterParams() {
            return {
                search: document.getElementById('eventSearchInput').value.trim(),
                type: document.getElementById('eventTypeFilter').value,
                assembly: document.getElementById('assemblyFilter').value,
                startDate: document.getElementById('startDateFilter').value,
                endDate: document.getElementById('endDateFilter').value
            };
        }

        function loadPaginatedTabEvents(tab, page = 1) {
            let params = getFilterParams();
            params.page = page;
            params.pageSize = eventsPageSize;
            params.tab = tab;
            const query = new URLSearchParams(params).toString();
            fetch('get_events_paginated.php?' + query)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (tab === 'upcoming') {
                            updateUpcomingEventsTable(data.events, page);
                            renderPagination(data.total, data.page, data.pageSize, p => loadPaginatedTabEvents('upcoming', p), 'upcomingEventsPagination');
                        } else if (tab === 'past') {
                            updatePastEventsTable(data.events, page);
                            renderPagination(data.total, data.page, data.pageSize, p => loadPaginatedTabEvents('past', p), 'pastEventsPagination');
                        }
                    } else {
                        if (tab === 'upcoming') document.querySelector('#upcomingEventsTable tbody').innerHTML = '<tr><td colspan="8" class="text-center">Error loading events</td></tr>';
                        if (tab === 'past') document.querySelector('#pastEventsTable tbody').innerHTML = '<tr><td colspan="8" class="text-center">Error loading events</td></tr>';
                    }
                });
        }

        function updateUpcomingEventsTable(events, page) {
            const tbody = document.querySelector('#upcomingEventsTable tbody');
            tbody.innerHTML = '';
            if (!events || events.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No events found</td></tr>';
                return;
            }
            events.forEach((event, index) => {
                const row = document.createElement('tr');
                let typeBadgeClass = 'bg-secondary';
                if (event.event_type && event.event_type.toLowerCase().includes('outreach')) typeBadgeClass = 'bg-success';
                else if (event.event_type && event.event_type.toLowerCase().includes('meeting')) typeBadgeClass = 'bg-primary';
                else if (event.event_type && event.event_type.toLowerCase().includes('prayer')) typeBadgeClass = 'bg-info';
                else if (event.event_type && event.event_type.toLowerCase().includes('training')) typeBadgeClass = 'bg-warning text-dark';
                let recurringBadge = event.is_recurring
                    ? '<span class="badge bg-info">Recurring</span>'
                    : '<span class="badge bg-secondary">One-time</span>';
                row.innerHTML = `
                    <td>${(page - 1) * eventsPageSize + index + 1}</td>
                    <td>${event.event_name || ''}</td>
                    <td><span class="badge ${typeBadgeClass}">${event.event_type || '-'}</span></td>
                    <td>${event.assembly_name || '-'}</td>
                    <td>${formatDate(event.start_date)}</td>
                    <td>${formatDate(event.end_date)}</td>
                    <td>${recurringBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-warning edit-event-btn me-1" data-id="${event.event_id}" title="Edit Event">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-event-btn" data-id="${event.event_id}" title="Delete Event">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function updatePastEventsTable(events, page) {
            const tbody = document.querySelector('#pastEventsTable tbody');
            tbody.innerHTML = '';
            if (!events || events.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No events found</td></tr>';
                return;
            }
            events.forEach((event, index) => {
                const row = document.createElement('tr');
                let typeBadgeClass = 'bg-secondary';
                if (event.event_type && event.event_type.toLowerCase().includes('outreach')) typeBadgeClass = 'bg-success';
                else if (event.event_type && event.event_type.toLowerCase().includes('meeting')) typeBadgeClass = 'bg-primary';
                else if (event.event_type && event.event_type.toLowerCase().includes('prayer')) typeBadgeClass = 'bg-info';
                else if (event.event_type && event.event_type.toLowerCase().includes('training')) typeBadgeClass = 'bg-warning text-dark';
                let recurringBadge = event.is_recurring
                    ? '<span class="badge bg-info">Recurring</span>'
                    : '<span class="badge bg-secondary">One-time</span>';
                row.innerHTML = `
                    <td>${(page - 1) * eventsPageSize + index + 1}</td>
                    <td>${event.event_name || ''}</td>
                    <td><span class="badge ${typeBadgeClass}">${event.event_type || '-'}</span></td>
                    <td>${event.assembly_name || '-'}</td>
                    <td>${formatDate(event.start_date)}</td>
                    <td>${formatDate(event.end_date)}</td>
                    <td>${recurringBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-warning edit-event-btn me-1" data-id="${event.event_id}" title="Edit Event">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-event-btn" data-id="${event.event_id}" title="Delete Event">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Add missing All Events tab loader
        function loadPaginatedEvents(page = 1) {
            let params = getFilterParams();
            params.page = page;
            params.pageSize = eventsPageSize;
            // No tab param for all events
            const query = new URLSearchParams(params).toString();
            fetch('get_events_paginated.php?' + query)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateEventsTable(data.events, page);
                        renderPagination(data.total, data.page, data.pageSize, loadPaginatedEvents, 'eventsPagination');
                    } else {
                        document.querySelector('#eventsTable tbody').innerHTML = '<tr><td colspan="8" class="text-center">Error loading events. Please try again later.</td></tr>';
                    }
                });
        }
        function updateEventsTable(events, page) {
            const tbody = document.querySelector('#eventsTable tbody');
            tbody.innerHTML = '';
            if (!events || events.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No events found</td></tr>';
                return;
            }
            events.forEach((event, index) => {
                const row = document.createElement('tr');
                let typeBadgeClass = 'bg-secondary';
                if (event.event_type && event.event_type.toLowerCase().includes('outreach')) typeBadgeClass = 'bg-success';
                else if (event.event_type && event.event_type.toLowerCase().includes('meeting')) typeBadgeClass = 'bg-primary';
                else if (event.event_type && event.event_type.toLowerCase().includes('prayer')) typeBadgeClass = 'bg-info';
                else if (event.event_type && event.event_type.toLowerCase().includes('training')) typeBadgeClass = 'bg-warning text-dark';
                let recurringBadge = event.is_recurring
                    ? '<span class="badge bg-info">Recurring</span>'
                    : '<span class="badge bg-secondary">One-time</span>';
                row.innerHTML = `
                    <td>${(page - 1) * eventsPageSize + index + 1}</td>
                    <td>${event.event_name || ''}</td>
                    <td><span class="badge ${typeBadgeClass}">${event.event_type || '-'}</span></td>
                    <td>${event.assembly_name || '-'}</td>
                    <td>${formatDate(event.start_date)}</td>
                    <td>${formatDate(event.end_date)}</td>
                    <td>${recurringBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-warning edit-event-btn me-1" data-id="${event.event_id}" title="Edit Event">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-event-btn" data-id="${event.event_id}" title="Delete Event">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Patch renderPagination to support multiple paginations
        function renderPagination(total, page, pageSize, onPageChange, elementId = 'eventsPagination') {
            const totalPages = Math.ceil(total / pageSize);
            const paginationEl = document.getElementById(elementId);
            if (!paginationEl) return;
            paginationEl.innerHTML = '';
            if (totalPages <= 1) return;
            const createPageBtn = (p, label, active = false, disabled = false) => {
                const li = document.createElement('li');
                li.className = 'page-item' + (active ? ' active' : '') + (disabled ? ' disabled' : '');
                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = '#';
                a.textContent = label;
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (!disabled && p !== page) onPageChange(p);
                });
                li.appendChild(a);
                return li;
            };
            paginationEl.appendChild(createPageBtn(page - 1, '«', false, page === 1));
            for (let p = 1; p <= totalPages; p++) {
                if (p === 1 || p === totalPages || Math.abs(p - page) <= 2) {
                    paginationEl.appendChild(createPageBtn(p, p, p === page));
                } else if (Math.abs(p - page) === 3) {
                    const li = document.createElement('li');
                    li.className = 'page-item disabled';
                    li.innerHTML = '<span class="page-link">…</span>';
                    paginationEl.appendChild(li);
                }
            }
            paginationEl.appendChild(createPageBtn(page + 1, '»', false, page === totalPages));
        }

        // Add pagination containers to the DOM if not present
        if (!document.getElementById('upcomingEventsPagination')) {
            const upPag = document.createElement('ul');
            upPag.className = 'pagination justify-content-center';
            upPag.id = 'upcomingEventsPagination';
            document.querySelector('#upcoming-events .table-responsive').after(upPag);
        }
        if (!document.getElementById('pastEventsPagination')) {
            const pastPag = document.createElement('ul');
            pastPag.className = 'pagination justify-content-center';
            pastPag.id = 'pastEventsPagination';
            document.querySelector('#past-events .table-responsive').after(pastPag);
        }

        // Helper: get current active tab
        function getActiveTab() {
            const activeTab = document.querySelector('#eventViewTabs .nav-link.active');
            if (activeTab) {
                if (activeTab.id === 'upcoming-events-tab') return 'upcoming';
                if (activeTab.id === 'past-events-tab') return 'past';
                if (activeTab.id === 'all-events-tab') return 'all';
                if (activeTab.id === 'calendar-view-tab') return 'calendar';
            }
            return 'upcoming'; // fallback
        }

        // On DOMContentLoaded, load data for the active tab and update dashboard counts
        window.addEventListener('DOMContentLoaded', function () {
            // Always fetch all events for dashboard counts
            fetch('get_events_paginated.php?page=1&pageSize=10000')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateDashboardCounts(data.events);
                    }
                });
            // Load the correct tab
            const tab = getActiveTab();
            if (tab === 'upcoming') loadPaginatedTabEvents('upcoming', 1);
            else if (tab === 'past') loadPaginatedTabEvents('past', 1);
            else if (tab === 'all') loadPaginatedEvents(1);
            else if (tab === 'calendar') loadCalendarEvents();
        });

        // On tab switch, load data for the selected tab
        if (document.getElementById('upcoming-events-tab')) {
            document.getElementById('upcoming-events-tab').addEventListener('shown.bs.tab', function () {
                loadPaginatedTabEvents('upcoming', 1);
            });
        }
        if (document.getElementById('past-events-tab')) {
            document.getElementById('past-events-tab').addEventListener('shown.bs.tab', function () {
                loadPaginatedTabEvents('past', 1);
            });
        }
        if (document.getElementById('all-events-tab')) {
            document.getElementById('all-events-tab').addEventListener('shown.bs.tab', function () {
                loadPaginatedEvents(1);
            });
        }
        if (document.getElementById('calendar-view-tab')) {
            document.getElementById('calendar-view-tab').addEventListener('shown.bs.tab', function () {
                loadCalendarEvents();
            });
        }

        // Calendar View: always show all events
        function loadCalendarEvents() {
            fetch('get_events_paginated.php?page=1&pageSize=1000')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderCalendar(data.events);
                    }
                });
        }

        function renderCalendar(events) {
            var calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;
            // Remove any previous calendar instance
            calendarEl.innerHTML = '';
            // Add modern border shadow and rounded corners
            calendarEl.style.background = '#fff';
            calendarEl.style.borderRadius = '18px';
            calendarEl.style.boxShadow = '0 4px 24px 0 rgba(0,0,0,0.10), 0 1.5px 4px 0 rgba(0,0,0,0.08)';
            calendarEl.style.padding = '18px 8px 8px 8px';
            calendarEl.style.margin = '0 auto 24px auto';
            // Map events to FullCalendar format
            var calendarEvents = (events || []).map(ev => ({
                id: ev.event_id,
                title: ev.event_name + (ev.assembly_name ? ' (' + ev.assembly_name + ')' : ''),
                start: ev.start_date,
                end: ev.end_date,
                color: ev.is_recurring ? '#17a2b8' : '#007bff',
                extendedProps: {
                    eventType: ev.event_type,
                    assembly: ev.assembly_name,
                    isRecurring: ev.is_recurring,
                    startDate: ev.start_date,
                    endDate: ev.end_date
                }
            }));
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                aspectRatio: 1.5,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                events: calendarEvents,
                eventDidMount: function(info) {
                    // Format start and end datetime for tooltip
                    const start = formatDate(info.event.extendedProps.startDate);
                    const end = formatDate(info.event.extendedProps.endDate);
                    let tooltipHtml = `<div style='font-weight:bold;'>${info.event.title}</div>` +
                        `<div>Type: <b>${info.event.extendedProps.eventType || '-'}</b></div>` +
                        `<div>Assembly: <b>${info.event.extendedProps.assembly || '-'}</b></div>` +
                        `<div>Start: <b>${start}</b></div>` +
                        `<div>End: <b>${end}</b></div>` +
                        (info.event.extendedProps.isRecurring ? `<div><span class='badge bg-info'>Recurring</span></div>` : '');
                    new bootstrap.Tooltip(info.el, {
                        title: tooltipHtml,
                        html: true,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                },
                eventDisplay: 'block',
                dayMaxEventRows: 3,
                views: {
                    dayGridMonth: { dayMaxEventRows: 3 },
                    timeGridWeek: { dayMaxEventRows: 3 },
                    timeGridDay: { dayMaxEventRows: 3 }
                }
            });
            calendar.render();
        }

        // Add calendar-specific CSS for modern look
        const calendarStyle = document.createElement('style');
calendarStyle.innerHTML = `
#calendar {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 24px 0 rgba(0,0,0,0.10), 0 1.5px 4px 0 rgba(0,0,0,0.08);
    padding: 18px 8px 8px 8px;
    margin: 0 auto 24px auto;
    transition: box-shadow 0.2s;
}
.fc .fc-toolbar-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #222;
}
.fc .fc-daygrid-day {
    border-radius: 8px;
    transition: background 0.2s;
}
.fc .fc-daygrid-day:hover {
    background: #f0f4ff;
}
.fc .fc-event {
    border-radius: 8px;
    box-shadow: 0 2px 8px 0 rgba(0,0,0,0.08);
    font-size: 0.95rem;
    padding: 2px 6px;
}
.fc .fc-daygrid-event-dot {
    border-radius: 50%;
}
.fc .fc-daygrid-day-number {
    font-weight: 500;
}
@media (prefers-color-scheme: dark) {
    #calendar {
        background: #23272f;
        color: #fff;
        box-shadow: 0 4px 24px 0 rgba(0,0,0,0.30), 0 1.5px 4px 0 rgba(0,0,0,0.18);
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
`;
        document.head.appendChild(calendarStyle);

        // On filter change, reload the current tab
        ['eventSearchInput', 'eventTypeFilter', 'assemblyFilter', 'startDateFilter', 'endDateFilter'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', reloadActiveTab);
                el.addEventListener('change', reloadActiveTab);
            }
        });
        function reloadActiveTab() {
            const tab = getActiveTab();
            if (tab === 'upcoming') loadPaginatedTabEvents('upcoming', 1);
            else if (tab === 'past') loadPaginatedTabEvents('past', 1);
            else if (tab === 'all') loadPaginatedEvents(1);
            else if (tab === 'calendar') loadCalendarEvents();
        }
    </script>
    <style>
        /* Ensure tabs are visible in white mode */
        .nav-tabs .nav-link {
            color: #fff; /* Default text color */
        }
        .nav-tabs .nav-link.active {
            color: #fff; /* Active tab text color */
            background-color: #007bff; /* Active tab background */
        }
        /* Tab text color: black in light mode, white in dark mode */
        .nav-tabs .nav-link {
            color: #222 !important;
        }
        .nav-tabs .nav-link.active {
            color: #222 !important;
            background-color: #e9ecef !important;
        }
        [data-bs-theme="dark"] .nav-tabs .nav-link {
            color: #fff !important;
        }
        [data-bs-theme="dark"] .nav-tabs .nav-link.active {
            color: #fff !important;
            background-color: #007bff !important;
        }
        /* Dark mode support for tabs and filters */
        @media (prefers-color-scheme: dark) {
            .nav-tabs .nav-link {
                color: #fff !important;
            }
            .nav-tabs .nav-link.active {
                color: #fff !important;
                background-color: #007bff !important;
            }
            #eventFilters .form-control,
            #eventFilters .form-select {
                background-color: #222 !important;
                color: #fff !important;
                border-color: #444 !important;
            }
            #eventFilters .form-control::placeholder {
                color: #ccc !important;
            }
        }
        /* Action buttons with icons only */
        .action-btn {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 40px;
            border: none;
            background: none;
            color: #007bff;
            font-size: 1.2rem;
            text-align: center;
            line-height: 40px;
            cursor: pointer;
        }
        .action-btn:hover::after {
            content: attr(data-title);
            position: absolute;
            bottom: -25px;
            left: 50%;
            transform: translateX(-50%);
            background: #000;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            white-space: nowrap;
        }
    </style>
    <?php include "../../../includes/footer.php"; ?>
</main>
</body>
</html>

<?php
// Zone Events Index Page
session_start();
// Keep session alive for AJAX requests
if (isset($_GET['ping'])) {
    echo 'pong';
    exit;
}

$page_title = "Zone Event Management";
include "../../../config/db.php";

if (!isset($_SESSION['member_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /Ekklessia-church-management/app/pages/login.php?redirect=$redirect_url");
    exit;
}

$logged_in_member_id = $_SESSION['member_id'];

try {
    $stmt = $pdo->query("SELECT event_type_id, name, is_recurring, default_frequency, level FROM event_types WHERE level = 'zone' ORDER BY name ASC");
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $event_types = [];
    $_SESSION['error_message'] = "Error fetching event types: " . $e->getMessage();
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_events FROM events WHERE level = 'zone'");
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
                <!-- ...existing navigation links... -->
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
                    <h3 class="card-text" id="totalEventsCount">0</h3>
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
            <h5 class="mb-0">Zone Events</h5>
            <div class="btn-group float-end" role="group" aria-label="Event Actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <i class="bi bi-plus-circle"></i> Add Event
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Tabs for Table View and Calendar View -->
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
                    <h5 class="modal-title" id="addEventModalLabel">Add Zone Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addEventForm">
                        <input type="hidden" name="level" value="3"> <!-- 3 for zone level -->
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
                        </div>
                        <div class="mb-3">
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
    <!-- View Event Modal -->
    <div class="modal fade" id="viewEventModal" tabindex="-1" aria-labelledby="viewEventModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="viewEventModalLabel">View Event</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="viewEventModalBody">
            <!-- Event details will be injected here -->
          </div>
        </div>
      </div>
    </div>
    <!-- Include Edit Event Modal -->
    <?php /* You can include a similar edit modal as in assembly if needed */ ?>
    <?php include '../../../includes/footer.php'; ?>
</main>

<script src="event-handlers.js?v=<?= time() ?>"></script>
<script src="pagination.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
// Helper to format date as '3rd January, 2024 . 10:25 am'
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
// --- ZONE CALENDAR FINAL FIX ---
let zoneCalendarInstance = null;
function renderZoneCalendar(events) {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;
    // Destroy previous calendar instance if it exists
    if (zoneCalendarInstance) {
        zoneCalendarInstance.destroy();
        zoneCalendarInstance = null;
    }
    calendarEl.innerHTML = '';
    calendarEl.style.background = '#fff';
    calendarEl.style.borderRadius = '18px';
    calendarEl.style.boxShadow = '0 4px 24px 0 rgba(0,0,0,0.10), 0 1.5px 4px 0 rgba(0,0,0,0.08)';
    calendarEl.style.padding = '18px 8px 8px 8px';
    calendarEl.style.margin = '0 auto 24px auto';
    var calendarEvents = (events || []).map(ev => ({
        id: ev.event_id,
        title: ev.title + (ev.zone_name && ev.zone_name !== 'Not Assigned' ? ' (' + ev.zone_name + ')' : ''),
        start: ev.start_date,
        end: ev.end_date,
        color: ev.is_recurring ? '#17a2b8' : '#007bff',
        extendedProps: {
            eventType: ev.event_type,
            zone: ev.zone_name,
            isRecurring: ev.is_recurring,
            startDate: ev.start_date,
            endDate: ev.end_date
        }
    }));
    zoneCalendarInstance = new FullCalendar.Calendar(calendarEl, {
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
            const start = formatDate(info.event.extendedProps.startDate);
            const end = formatDate(info.event.extendedProps.endDate);
            let tooltipHtml = `<div style='font-weight:bold;'>${info.event.title}</div>` +
                `<div>Type: <b>${info.event.extendedProps.eventType || '-'}</b></div>` +
                `<div>Zone: <b>${info.event.extendedProps.zone || '-'}</b></div>` +
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
    zoneCalendarInstance.render();
}
function loadZoneCalendarEvents() {
    fetch('get_events_paginated.php?page=1&pageSize=1000')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderZoneCalendar(data.events);
            } else {
                renderZoneCalendar([]);
            }
        })
        .catch(() => {
            renderZoneCalendar([]);
        });
}
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('calendar-view-tab')) {
        document.getElementById('calendar-view-tab').addEventListener('shown.bs.tab', function () {
            loadZoneCalendarEvents();
        });
    }
    // Optionally, load calendar if Calendar tab is default
    if (document.getElementById('calendar-view').classList.contains('show')) {
        loadZoneCalendarEvents();
    }
});
// --- END ZONE CALENDAR FINAL FIX ---
</script>
<style>
/* Tabs and table head: light mode (default) */
.nav-tabs .nav-link {
    color: #111 !important;
    border: 1px solid #dee2e6;
    margin-right: 4px;
    border-radius: 6px 6px 0 0;
    transition: all 0.2s ease;
}

.nav-tabs .nav-link:hover {
    border-color: #007bff;
}

.nav-tabs .nav-link.active {
    background: linear-gradient(90deg, #007bff 0%, #00d4ff 100%) !important;
    color: #fff !important;
    border: none;
}

.table thead th,
.table thead td,
.table thead {
    background: #0d6efd !important;
    color: #fff !important;
    font-weight: 500;
    border: none;
}

/* Dark mode support */
[data-bs-theme="dark"] .nav-tabs .nav-link {
    color: #fff !important;
    border-color: #495057;
}

[data-bs-theme="dark"] .nav-tabs .nav-link:hover {
    border-color: #0d6efd;
}

[data-bs-theme="dark"] .nav-tabs .nav-link.active {
    background: linear-gradient(90deg, #007bff 0%, #00d4ff 100%) !important;
    color: #fff !important;
    border: none;
}

[data-bs-theme="dark"] .table thead th,
[data-bs-theme="dark"] .table thead td,
[data-bs-theme="dark"] .table thead {
    background: #0d6efd !important;
    color: #fff !important;
}

/* Calendar styles */
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
    padding: 2px 4px;
    margin: 1px 2px;
    border: none;
    transition: transform 0.15s ease;
}

.fc .fc-event:hover {
    transform: scale(1.02);
}

/* Dark mode calendar support */
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
</style>

<?php
// Assembly Events Index Page
// Copied and adapted from the old event system
session_start();
// Keep session alive for AJAX requests
if (isset($_GET['ping'])) {
    echo 'pong';
    exit;
}

$page_title = "Assembly Event Management";
include "../../../config/db.php";

if (!isset($_SESSION['member_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /Ekklessia-church-management/app/pages/login.php?redirect=$redirect_url");
    exit;
}

$logged_in_member_id = $_SESSION['member_id'];

try {
    $stmt = $pdo->query("SELECT event_type_id, name, is_recurring, default_frequency, level FROM event_types WHERE level = 'assembly' ORDER BY name ASC");
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $event_types = [];
    $_SESSION['error_message'] = "Error fetching event types: " . $e->getMessage();
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_events FROM events WHERE level = 'assembly'");
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
            <h5 class="mb-0">Assembly Events</h5>
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
                        <select id="assemblyFilter" class="form-select">
                            <option value="">All Assemblies</option>
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT assembly_id, name FROM assemblies WHERE status = 1 ORDER BY name ASC");
                                $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            } catch (PDOException $e) {
                                $assemblies = [];
                            }
                            foreach ($assemblies as $assembly): ?>
                                <option value="<?= $assembly['assembly_id'] ?>"><?= htmlspecialchars($assembly['name']) ?></option>
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
                    <h5 class="modal-title" id="addEventModalLabel">Add Assembly Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addEventForm">
                        <input type="hidden" name="level" value="2"> <!-- 2 for assembly level -->
                        <div class="mb-3">
                            <label for="assemblySelect" class="form-label">Assembly <span class="text-danger">*</span></label>
                            <select class="form-select" id="assemblySelect" name="assembly_id" required>
                                <option value="">Select Assembly</option>
                                <?php
                                // Fetch all assemblies for the dropdown
                                try {
                                    $stmt = $pdo->query("SELECT assembly_id, name FROM assemblies WHERE status = 1 ORDER BY name ASC");
                                    $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                } catch (PDOException $e) {
                                    $assemblies = [];
                                }
                                foreach ($assemblies as $assembly): ?>
                                    <option value="<?= $assembly['assembly_id'] ?>"><?= htmlspecialchars($assembly['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
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
    <!-- Hidden template for edit form (used by JS to restore modal content) -->
    <div id="edit-form-template" style="display:none;">
        <form id="editEventForm">
            <input type="hidden" id="editEventId" name="eventId">
            <div class="mb-3">
                <label for="editAssemblySelect" class="form-label">Assembly <span class="text-danger">*</span></label>
                <select class="form-select" id="editAssemblySelect" name="assembly_id" required>
                    <option value="">Select Assembly</option>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT assembly_id, name FROM assemblies WHERE status = 1 ORDER BY name ASC");
                        $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        $assemblies = [];
                    }
                    foreach ($assemblies as $assembly): ?>
                        <option value="<?= $assembly['assembly_id'] ?>"><?= htmlspecialchars($assembly['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="editEventName" class="form-label">Event Name <span class="text-danger">*</span></label>
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
            </div>
            <div class="mb-3">
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
                </button>            </div>
        </form>
    </div>    <?php include "../../../includes/footer.php"; ?>
</main>

<!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEventModalLabel">Edit Assembly Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form content will be inserted by JS -->
            </div>
        </div>
    </div>
</div>

<!-- Load the fixed version of the event handlers -->
<script src="event-edit-fixed.js?v=<?= time() ?>"></script>
<script src="event-handlers-fixed.js?v=<?= time() ?>"></script>
<script src="pagination.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Recurrence logic
    var recurringCheckbox = document.getElementById('isRecurring');
    var recurrenceOptions = document.getElementById('recurrenceOptions');
    if (recurringCheckbox && recurrenceOptions) {
        recurringCheckbox.addEventListener('change', function () {
            recurrenceOptions.style.display = this.checked ? '' : 'none';
        });
    }

    // Add Event form AJAX
    var addEventForm = document.getElementById('addEventForm');
    if (addEventForm) {
        addEventForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(addEventForm);
            fetch('add_event_process.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Show success alert
                    var alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3';
                    alert.style.zIndex = 1050;
                    alert.innerHTML = '<strong>Success!</strong> ' + data.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    document.body.appendChild(alert);
                    // Close modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('addEventModal'));
                    if (modal) modal.hide();
                    // Reset form
                    addEventForm.reset();
                    if (recurrenceOptions) recurrenceOptions.style.display = 'none';
                    // Reload events after adding
                    if (typeof loadPaginatedTabEvents === 'function') loadPaginatedTabEvents('upcoming', 1);
                } else {
                    alert(data.message || 'Error adding event.');
                }
            })
            .catch(() => {
                alert('Error adding event.');
            });
        });
    }

    // Assembly Calendar View (FullCalendar)
    function formatDate(dateStr) {
        try {
            const date = new Date(dateStr);
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            let datePart = date.toLocaleDateString(undefined, options);
            let timePart = date.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', hour12: true });
            return `${datePart} . ${timePart.toLowerCase()}`;
        } catch (e) {
            return dateStr;
        }
    }
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
        calendarEl.innerHTML = '';
        calendarEl.style.background = '#fff';
        calendarEl.style.borderRadius = '18px';
        calendarEl.style.boxShadow = '0 4px 24px 0 rgba(0,0,0,0.10), 0 1.5px 4px 0 rgba(0,0,0,0.08)';
        calendarEl.style.padding = '18px 8px 8px 8px';
        calendarEl.style.margin = '0 auto 24px auto';
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
    // Show calendar when tab is activated
    if (document.getElementById('calendar-view-tab')) {
        document.getElementById('calendar-view-tab').addEventListener('shown.bs.tab', function () {
            loadCalendarEvents();
        });
    }
});
</script>
<style>
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
        background: #2a2e38;    }
}
</style>

<!-- Date Format Debug Tool -->
<div id="debug-date-tool" style="position: fixed; bottom: 10px; right: 10px; z-index: 9999; background: #f8f9fa; border: 1px solid #ddd; padding: 10px; border-radius: 5px; display: none;">
    <h5>Date Format Debug</h5>
    <div class="mb-2">
        <label>Test date string:</label>
        <input type="text" id="test-date-input" class="form-control" value="<?= date('Y-m-d H:i:s') ?>">
    </div>
    <button id="test-format-btn" class="btn btn-sm btn-primary">Test Format</button>
    <div id="format-result" class="mt-2 p-2 border"></div>
    <div class="mt-2">
        <button id="debug-log-events" class="btn btn-sm btn-info">Log Current Events</button>
    </div>
</div>

<script>
// Show debug tool with Ctrl+Shift+D
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.shiftKey && e.key === 'D') {
        const debugTool = document.getElementById('debug-date-tool');
        debugTool.style.display = debugTool.style.display === 'none' ? 'block' : 'none';
    }
});

// Test button functionality
document.getElementById('test-format-btn')?.addEventListener('click', function() {
    const dateStr = document.getElementById('test-date-input').value;
    try {
        const formattedDate = window.formatDateWords ? window.formatDateWords(dateStr) : 'formatDateWords function not available';
        document.getElementById('format-result').innerHTML = `
            <strong>Original:</strong> ${dateStr}<br>
            <strong>Formatted:</strong> ${formattedDate}
        `;
        console.log('Test format result:', formattedDate);
    } catch (e) {
        document.getElementById('format-result').innerHTML = `
            <div class="text-danger">Error: ${e.message}</div>
        `;
        console.error('Format error:', e);
    }
});

// Debug events button
document.getElementById('debug-log-events')?.addEventListener('click', function() {
    // Find the current active tab
    const activeTab = document.querySelector('.tab-pane.active');
    if (!activeTab) return;
    
    const tabId = activeTab.id;
    const tableId = tabId.replace('-events', 'EventsTable').replace('-view', 'View');
    const table = document.getElementById(tableId);
    
    console.log('Debug: Active tab =', tabId);
    console.log('Debug: Table ID =', tableId);
    console.log('Debug: Table exists =', !!table);
    
    if (table) {
        console.log('Debug: Table rows count =', table.querySelectorAll('tbody tr').length);
        console.log('Debug: Table HTML =', table.querySelector('tbody').innerHTML);
    }
    
    // Reload the current tab to see if it fixes the issue
    if (window.loadPaginatedTabEvents && window.currentTab) {
        window.loadPaginatedTabEvents(window.currentTab, window.currentPage[window.currentTab] || 1);
        console.log('Debug: Reloaded tab', window.currentTab);
    }
});

// Test the formatDateWords function directly
function testDateFormatting() {
    try {
        const testDates = [
            '2025-05-28 18:30:00',
            '05/28/2025 18:30:00',
            new Date().toISOString()
        ];
        
        console.log('=== Date Format Testing ===');
        testDates.forEach(date => {
            try {
                console.log(`Original: ${date}`);
                if (window.formatDateWords) {
                    console.log(`Formatted: ${window.formatDateWords(date)}`);
                } else {
                    console.warn('formatDateWords function not available yet');
                }
                console.log('-----------------');
            } catch (e) {
                console.error(`Error formatting ${date}:`, e);
            }
        });
    } catch (e) {
        console.error('Test function error:', e);
    }
}

// Run test after page loads
setTimeout(testDateFormatting, 2000);
</script>
</body>
</html>

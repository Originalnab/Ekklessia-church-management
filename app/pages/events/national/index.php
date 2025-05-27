<?php
// National Events Index Page
// Copied and adapted from the old event system
session_start();
$page_title = "National Event Management";
include "../../../config/db.php";

if (!isset($_SESSION['member_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /Ekklessia-church-management/app/pages/login.php?redirect=$redirect_url");
    exit;
}

$logged_in_member_id = $_SESSION['member_id'];

try {
    $stmt = $pdo->query("SELECT event_type_id, name, is_recurring, default_frequency, level FROM event_types WHERE level = 'national' ORDER BY name ASC");
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $event_types = [];
    $_SESSION['error_message'] = "Error fetching event types: " . $e->getMessage();
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_events FROM events WHERE level = 'national'");
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
    <!-- Events Table and Tabs -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">National Events</h5>
            <div class="btn-group float-end" role="group" aria-label="Event Actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <i class="bi bi-plus-circle"></i> Add Event
                </button>
            </div>
        </div>
        <div class="card-body">
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
                    <h5 class="modal-title" id="addEventModalLabel">Add National Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addEventForm">
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
                                <option value="yearly">Yearly</option>
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
        </div>    </div>
    <!-- End Add Event Modal -->

    <!-- Edit Event Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEventModalLabel">Edit National Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editEventForm">
                        <input type="hidden" id="editEventId" name="eventId">
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
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Event
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>    </div>    <!-- End Edit Event Modal -->

    <!-- View Event Modal -->
    <div class="modal fade" id="viewEventModal" tabindex="-1" aria-labelledby="viewEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="viewEventModalLabel">
                        <i class="bi bi-eye-fill"></i> Event Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="viewEventContent">
                        <div class="text-center">
                            <div class="spinner-border text-info" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading event details...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary" id="editFromViewBtn" style="display: none;">
                        <i class="bi bi-pencil"></i> Edit Event
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End View Event Modal -->

    <!-- Delete Event Confirmation Modal -->
    <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteEventModalLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="bi bi-trash-fill text-danger" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Delete Event</h4>
                        <p class="mb-3">Are you sure you want to delete this event?</p>
                        <div class="alert alert-warning">
                            <strong>Event:</strong> <span id="deleteEventName">Loading...</span><br>
                            <small class="text-muted">This action cannot be undone.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash-fill"></i> Delete Event
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete Event Confirmation Modal -->    <link rel="stylesheet" href="styles.css">
    <style>
/* Additional styles can be added here */
</style>

    <script src="event-handlers.js?v=<?= time() ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Add calendar styles dynamically
        const calendarStyle = document.createElement('style');
        calendarStyle.textContent = `
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
                color: #1a1a1a;
            }

            .fc .fc-button-primary {
                background-color: #007bff;
                border-color: #0056b3;
            }

            .fc .fc-button-primary:hover {
                background-color: #0056b3;
                border-color: #004085;
            }

            .fc .fc-daygrid-day-number {
                font-weight: 500;
                color: #333;
            }

            .fc .fc-daygrid-day-top {
                padding: 8px;
            }

            .fc .fc-daygrid-day.fc-day-today {
                background-color: rgba(0, 123, 255, 0.1);
            }

            .fc .fc-event {
                border-radius: 4px;
                padding: 2px 4px;
                margin: 1px 0;
                font-size: 0.85em;
                cursor: pointer;
                transition: transform 0.1s ease;
            }

            .fc .fc-event:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .fc .fc-event.fc-event-recurring {
                background-color: #17a2b8;
                border-color: #138496;
            }

            /* Dark mode support */
            @media (prefers-color-scheme: dark) {
                #calendar {
                    background: #1e1e1e;
                }
                
                .fc .fc-toolbar-title {
                    color: #ffffff;
                }
                
                .fc .fc-daygrid-day-number {
                    color: #e0e0e0;
                }
                
                .fc th {
                    color: #ffffff;
                    background-color: #333;
                }
                
                .fc td {
                    border-color: #404040;
                }
                
                .fc .fc-daygrid-day.fc-day-today {
                    background-color: rgba(0, 123, 255, 0.2);
                }
                
                .fc .fc-button-primary {
                    background-color: #2b2b2b;
                    border-color: #404040;
                }
            }
        `;
        document.head.appendChild(calendarStyle);

        var recurringCheckbox = document.getElementById('isRecurring');
        var recurrenceOptions = document.getElementById('recurrenceOptions');
        if (recurringCheckbox && recurrenceOptions) {
            recurringCheckbox.addEventListener('change', function () {
                recurrenceOptions.style.display = this.checked ? '' : 'none';
            });
        }
        // Handle add event form submit
        var addEventForm = document.getElementById('addEventForm');
        if (addEventForm) {
            addEventForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(addEventForm);
                // If not recurring, clear recurrenceFrequency
                if (!document.getElementById('isRecurring').checked) {
                    formData.set('recurrenceFrequency', '');
                }
                fetch('add_event_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Show success, close modal, reload events
                        var alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3';
                        alert.style.zIndex = 1050;
                        alert.innerHTML = '<strong>Success!</strong> ' + data.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                        document.body.appendChild(alert);
                        var modal = bootstrap.Modal.getInstance(document.getElementById('addEventModal'));
                        if (modal) modal.hide();
                        addEventForm.reset();
                        if (recurrenceOptions) recurrenceOptions.style.display = 'none';
                        // Optionally reload events table here
                        if (typeof loadPaginatedTabEvents === 'function') loadPaginatedTabEvents('upcoming', 1);
                    } else {
                        alert(data.message || 'Error adding event.');
                    }
                })
                .catch(() => {
                    alert('Error adding event.');                });
            });
        }
        
        // Handle edit event modal
        var editRecurringCheckbox = document.getElementById('editIsRecurring');
        var editRecurrenceOptions = document.getElementById('editRecurrenceOptions');
        if (editRecurringCheckbox && editRecurrenceOptions) {
            editRecurringCheckbox.addEventListener('change', function () {
                editRecurrenceOptions.style.display = this.checked ? '' : 'none';
            });
        }

        // Handle edit event form submit
        var editEventForm = document.getElementById('editEventForm');
        if (editEventForm) {
            editEventForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(editEventForm);
                
                // If not recurring, clear recurrenceFrequency
                if (!document.getElementById('editIsRecurring').checked) {
                    formData.set('recurrenceFrequency', '');
                }
                
                fetch('update_event_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Show success, close modal, reload events
                        var alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3';
                        alert.style.zIndex = 1050;
                        alert.innerHTML = '<strong>Success!</strong> ' + data.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                        document.body.appendChild(alert);
                        
                        var modal = bootstrap.Modal.getInstance(document.getElementById('editEventModal'));
                        if (modal) modal.hide();
                        
                        // Reload current tab
                        if (typeof loadPaginatedTabEvents === 'function') {
                            var activeTab = document.querySelector('.nav-link.active');
                            if (activeTab) {
                                var tabType = activeTab.id.replace('-events-tab', '');
                                loadPaginatedTabEvents(tabType, 1);
                            }
                        }
                        
                        // Reload calendar if it's the active tab
                        if (document.getElementById('calendar-view').classList.contains('show', 'active')) {
                            loadNationalCalendarEvents();
                        }
                    } else {
                        alert(data.message || 'Error updating event.');
                    }
                })
                .catch(() => {
                    alert('Error updating event.');
                });
            });        }

        // Helper function to get ordinal suffix for numbers
        function getOrdinalSuffix(day) {
            if (day > 3 && day < 21) return 'th';
            switch (day % 10) {
                case 1: return 'st';
                case 2: return 'nd';
                case 3: return 'rd';
                default: return 'th';
            }
        }

        // Helper function to format date as "3rd January, 2024 . 10:25 am"
        function formatDate(dateStr) {
            try {
                if (!dateStr) return 'Not set';
                
                const date = new Date(dateStr);
                if (isNaN(date.getTime())) {
                    console.error("Invalid date string:", dateStr);
                    return dateStr;
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

                return `${day}${ordinal} ${month}, ${year} . ${timePart}`;
            } catch (e) {
                console.error("Error formatting date:", e, dateStr);
                return dateStr;
            }
        }

        // Helper function to format datetime for input field
        function formatDateTimeLocal(date) {
            try {
                if (!date || isNaN(date.getTime())) return '';
                
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                
                return `${year}-${month}-${day}T${hours}:${minutes}`;
            } catch (e) {
                console.error("Error formatting datetime for input:", e);
                return '';
            }
        }

        // Global function to load event data for viewing
        window.loadEventForView = function(eventId) {
            // Show loading state
            document.getElementById('viewEventContent').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading event details...</p>
                </div>
            `;
            
            // Show the modal
            var viewModal = new bootstrap.Modal(document.getElementById('viewEventModal'));
            viewModal.show();
            
            // Fetch event data
            fetch('view_event.php?event_id=' + eventId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.event) {
                        renderEventView(data.event);
                        
                        // Show edit button and set its onclick handler
                        const editBtn = document.getElementById('editFromViewBtn');
                        editBtn.style.display = 'inline-block';
                        editBtn.onclick = function() {
                            viewModal.hide();
                            setTimeout(() => {
                                window.loadEventForEdit(eventId);
                            }, 300);
                        };
                    } else {
                        document.getElementById('viewEventContent').innerHTML = `
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> ${data.message || 'Error loading event details.'}
                            </div>
                        `;
                        document.getElementById('editFromViewBtn').style.display = 'none';
                    }
                })
                .catch(() => {
                    document.getElementById('viewEventContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> Error loading event details.
                        </div>
                    `;
                    document.getElementById('editFromViewBtn').style.display = 'none';
                });
        };

        // Function to render event details in the view modal
        function renderEventView(event) {
            const recurringBadge = event.is_recurring == 1 
                ? `<span class="badge bg-info ms-2"><i class="bi bi-arrow-repeat"></i> Recurring (${event.frequency || 'Unknown'})</span>` 
                : '';
            
            const locationSection = event.location 
                ? `<div class="row mb-3">
                     <div class="col-sm-3"><strong><i class="bi bi-geo-alt"></i> Location:</strong></div>
                     <div class="col-sm-9">${event.location}</div>
                   </div>` 
                : '';
            
            const descriptionSection = event.description 
                ? `<div class="row mb-3">
                     <div class="col-sm-3"><strong><i class="bi bi-card-text"></i> Description:</strong></div>
                     <div class="col-sm-9">${event.description}</div>
                   </div>` 
                : '';

            document.getElementById('viewEventContent').innerHTML = `
                <div class="card border-0">
                    <div class="card-body">
                        <h4 class="card-title text-primary mb-3">
                            <i class="bi bi-calendar-event"></i> ${event.title || 'Untitled Event'}
                            ${recurringBadge}
                        </h4>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong><i class="bi bi-tag"></i> Event Type:</strong></div>
                            <div class="col-sm-9">
                                <span class="badge bg-primary">${event.event_type_name || 'Unknown'}</span>
                                ${event.event_type_description ? `<br><small class="text-muted">${event.event_type_description}</small>` : ''}
                            </div>
                        </div>
                          <div class="row mb-3">
                            <div class="col-sm-3"><strong><i class="bi bi-calendar-check"></i> Start:</strong></div>
                            <div class="col-sm-9">${formatDate(event.start_date)}</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong><i class="bi bi-calendar-x"></i> End:</strong></div>
                            <div class="col-sm-9">${formatDate(event.end_date)}</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong><i class="bi bi-clock"></i> Duration:</strong></div>
                            <div class="col-sm-9">${event.duration}</div>
                        </div>
                        
                        ${locationSection}
                        ${descriptionSection}
                        
                        <hr class="my-4">
                        
                        <div class="row mb-2">
                            <div class="col-sm-3"><strong><i class="bi bi-person-plus"></i> Created By:</strong></div>
                            <div class="col-sm-9">${event.creator_name}</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-sm-3"><strong><i class="bi bi-calendar-plus"></i> Created:</strong></div>
                            <div class="col-sm-9">${event.formatted_created_at || 'Unknown'}</div>
                        </div>
                        
                        ${event.formatted_updated_at ? `
                        <div class="row mb-2">
                            <div class="col-sm-3"><strong><i class="bi bi-person-check"></i> Last Updated By:</strong></div>
                            <div class="col-sm-9">${event.updater_name}</div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-sm-3"><strong><i class="bi bi-calendar-check"></i> Last Updated:</strong></div>
                            <div class="col-sm-9">${event.formatted_updated_at}</div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        // Global function to load event data for editing
        window.loadEventForEdit = function(eventId) {
            fetch('get_events_paginated.php?event_id=' + eventId + '&pageSize=1')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.events && data.events.length > 0) {
                        const event = data.events[0];
                        
                        // Populate the edit form
                        document.getElementById('editEventId').value = event.event_id;
                        document.getElementById('editEventName').value = event.title || '';
                        document.getElementById('editEventType').value = event.event_type_id || '';
                        
                        // Convert the datetime format for input fields
                        if (event.start_date) {
                            const startDate = new Date(event.start_date);
                            document.getElementById('editStartDate').value = formatDateTimeLocal(startDate);
                        }
                        if (event.end_date) {
                            const endDate = new Date(event.end_date);
                            document.getElementById('editEndDate').value = formatDateTimeLocal(endDate);
                        }
                        
                        // Set recurring options
                        const isRecurring = event.is_recurring == 1;
                        document.getElementById('editIsRecurring').checked = isRecurring;
                        document.getElementById('editRecurrenceOptions').style.display = isRecurring ? '' : 'none';
                        
                        if (isRecurring && event.frequency) {
                            document.getElementById('editRecurrenceFrequency').value = event.frequency;
                        }
                        
                        // Show the modal
                        var editModal = new bootstrap.Modal(document.getElementById('editEventModal'));
                        editModal.show();
                    } else {
                        alert('Error loading event data.');
                    }
                })
                .catch(() => {
                    alert('Error loading event data.');
                });
        };

        // Global function to show delete confirmation modal
        window.showDeleteConfirmation = function(eventId, eventName) {
            document.getElementById('deleteEventName').textContent = eventName || 'Unknown Event';
            
            // Store the event ID for deletion
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.onclick = function() {
                performDeleteEvent(eventId);
            };
            
            // Show the modal
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
            deleteModal.show();
        };

        // Function to actually delete the event
        function performDeleteEvent(eventId) {
            fetch('delete_event_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ eventId: eventId })
            })
            .then(res => res.json())
            .then((data) => {
                // Hide the delete modal first
                var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteEventModal'));
                if (deleteModal) deleteModal.hide();
                
                if (data.success) {
                    // Show success message
                    var alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3';
                    alert.style.zIndex = 1050;
                    alert.innerHTML = '<strong>Success!</strong> ' + data.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    document.body.appendChild(alert);
                    
                    // Reload current tab
                    if (typeof loadPaginatedTabEvents === 'function') {
                        var activeTab = document.querySelector('.nav-link.active');
                        if (activeTab) {
                            var tabType = activeTab.id.replace('-events-tab', '');
                            loadPaginatedTabEvents(tabType, 1);
                        }
                    }
                    
                    // Reload calendar if it's the active tab
                    if (document.getElementById('calendar-view').classList.contains('show', 'active')) {
                        loadNationalCalendarEvents();
                    }
                } else {
                    alert(data.message || 'Error deleting event.');
                }
            })
            .catch(() => {
                // Hide the delete modal
                var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteEventModal'));
                if (deleteModal) deleteModal.hide();
                alert('Error deleting event.');
            });
        }
        // Ensure correct tab loads data on tab switch
        if (document.getElementById('upcoming-events-tab')) {
            document.getElementById('upcoming-events-tab').addEventListener('shown.bs.tab', function () {
                if (typeof loadPaginatedTabEvents === 'function') loadPaginatedTabEvents('upcoming', 1);
            });
        }
        if (document.getElementById('past-events-tab')) {
            document.getElementById('past-events-tab').addEventListener('shown.bs.tab', function () {
                if (typeof loadPaginatedTabEvents === 'function') loadPaginatedTabEvents('past', 1);
            });
        }
        if (document.getElementById('all-events-tab')) {
            document.getElementById('all-events-tab').addEventListener('shown.bs.tab', function () {
                if (typeof loadPaginatedTabEvents === 'function') loadPaginatedTabEvents('all', 1);
            });
        }        // --- NATIONAL CALENDAR FIX ---
        let nationalCalendarInstance = null;
        
        // Format date for tooltips
        function formatDate(dateStr) {
            try {
                if (!dateStr) return 'Not set';
                
                const date = new Date(dateStr);
                if (isNaN(date.getTime())) {
                    console.error("Invalid date string:", dateStr);
                    return dateStr;
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

                return `${day}${ordinal} ${month}, ${year} . ${timePart}`;
            } catch (e) {
                console.error("Error formatting date:", e, dateStr);
                return dateStr;
            }
        }
        
        function renderNationalCalendar(events) {
            var calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;
            // Destroy previous calendar instance if it exists
            if (nationalCalendarInstance) {
                nationalCalendarInstance.destroy();
                nationalCalendarInstance = null;
            }
            calendarEl.innerHTML = '';
            calendarEl.style.background = '#fff';
            calendarEl.style.borderRadius = '18px';
            calendarEl.style.boxShadow = '0 4px 24px 0 rgba(0,0,0,0.10), 0 1.5px 4px 0 rgba(0,0,0,0.08)';
            calendarEl.style.padding = '18px 8px 8px 8px';
            calendarEl.style.margin = '0 auto 24px auto';

            // Map events with improved handling of dates and data
            var calendarEvents = (events || []).map(ev => {
                // Ensure we have valid dates
                const startDate = new Date(ev.start_date);
                let endDate = ev.end_date ? new Date(ev.end_date) : new Date(startDate);
                
                // If end date is before start date, set it to start date
                if (endDate < startDate) {
                    endDate = startDate;
                }

                return {
                    id: ev.event_id,
                    title: ev.title || ev.event_name || 'Untitled Event',
                    start: startDate.toISOString(),
                    end: endDate.toISOString(),
                    allDay: startDate.getHours() === 0 && startDate.getMinutes() === 0 && 
                           endDate.getHours() === 0 && endDate.getMinutes() === 0,
                    color: ev.is_recurring ? '#17a2b8' : '#007bff',
                    borderColor: ev.is_recurring ? '#17a2b8' : '#007bff',
                    textColor: '#ffffff',
                    extendedProps: {
                        eventType: ev.event_type || ev.event_type_name || 'General',
                        isRecurring: Boolean(ev.is_recurring),
                        startDate: ev.start_date,
                        endDate: ev.end_date,
                        description: ev.description || ''
                    }
                };
            });

            nationalCalendarInstance = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                aspectRatio: 1.5,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                events: calendarEvents,
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                },
                displayEventTime: true,
                displayEventEnd: true,
                eventDisplay: 'block',
                eventClick: function(info) {
                    // When an event is clicked, show the view modal
                    if (typeof window.loadEventForView === 'function') {
                        window.loadEventForView(info.event.id);
                    }
                },                eventDidMount: function(info) {
                    // Format dates using our consistent date format
                    const start = formatDate(info.event.extendedProps.startDate);
                    const end = formatDate(info.event.extendedProps.endDate);
                    let tooltipHtml = `<div style='font-weight:bold;'>${info.event.title}</div>` +
                        `<div>Type: <b>${info.event.extendedProps.eventType || '-'}</b></div>` +
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
            nationalCalendarInstance.render();
        }
        function loadNationalCalendarEvents() {
            // Get the filter values
            const searchValue = document.getElementById('eventSearchInput')?.value || '';
            const typeValue = document.getElementById('eventTypeFilter')?.value || '';
            const startDateValue = document.getElementById('startDateFilter')?.value || '';
            const endDateValue = document.getElementById('endDateFilter')?.value || '';

            // Construct query params
            const params = new URLSearchParams({
                page: 1,
                pageSize: 1000,
                search: searchValue,
                type: typeValue,
                startDate: startDateValue,
                endDate: endDateValue
            });

            fetch('get_events_paginated.php?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clean up event data before rendering
                        const cleanedEvents = data.events.map(event => ({
                            ...event,
                            title: event.title?.trim() || event.event_name?.trim() || 'Untitled Event',
                            start_date: event.start_date || event.startDate,
                            end_date: event.end_date || event.endDate
                        }));
                        renderNationalCalendar(cleanedEvents);
                    } else {
                        console.error('Failed to load events:', data.message);
                        renderNationalCalendar([]);
                    }
                })
                .catch((error) => {
                    console.error('Error loading calendar events:', error);
                    renderNationalCalendar([]);
                });
        }
        if (document.getElementById('calendar-view-tab')) {
            document.getElementById('calendar-view-tab').addEventListener('shown.bs.tab', function () {
                loadNationalCalendarEvents();
            });
        }
        // Optionally, load calendar if Calendar tab is default
        if (document.getElementById('calendar-view').classList.contains('show')) {
            loadNationalCalendarEvents();
        }
        // --- END NATIONAL CALENDAR FIX ---
    });
    </script>
</main>
<?php include "../../../includes/footer.php"; ?>
</body>
</html>

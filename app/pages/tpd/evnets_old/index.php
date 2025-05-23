<?php
session_start();
$page_title = "Event Management";
include "../../../config/db.php";

// Check if user is logged in using member_id
if (!isset($_SESSION['member_id'])) {
    // Redirect to login page with a redirect parameter to return to this page
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /Ekklessia-church-management/app/pages/login.php?redirect=$redirect_url");
    exit;
}

// Get the logged-in member's ID
$logged_in_member_id = $_SESSION['member_id'];

// Fetch event types for the Add/Edit Event modals
try {
    $stmt = $pdo->query("SELECT event_type_id, name, is_recurring, default_frequency, level FROM event_types ORDER BY name ASC");
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $event_types = [];
    $_SESSION['error_message'] = "Error fetching event types: " . $e->getMessage();
}

// Fetch total events count for dashboard
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_events FROM events");
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
    <!-- Success/Error Alerts -->
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

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Event Management</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addEventModal">
                        <i class="bi bi-plus-circle"></i> Add New Event
                    </button>
                    <button class="btn btn-danger" id="bulkDeleteBtn" style="display: none;">
                        <i class="bi bi-trash"></i> Delete Selected
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="filterTitle" class="form-label"><i class="bi bi-search me-2"></i>Event Title</label>
                    <input type="text" class="form-control" id="filterTitle" placeholder="Search by title">
                </div>
                <div class="col-md-3">
                    <label for="filterType" class="form-label"><i class="bi bi-calendar-event me-2"></i>Event Type</label>
                    <select class="form-select" id="filterType">
                        <option value="">All Types</option>
                        <?php foreach ($event_types as $type): ?>
                            <option value="<?= htmlspecialchars($type['name']) ?>"><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filterLevel" class="form-label"><i class="bi bi-globe me-2"></i>Level</label>
                    <select class="form-select" id="filterLevel">
                        <option value="">All Levels</option>
                        <option value="household">Household</option>
                        <option value="assembly">Assembly</option>
                        <option value="zone">Zone</option>
                        <option value="national">National</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filterStart" class="form-label"><i class="bi bi-calendar-date me-2"></i>Start From</label>
                    <input type="date" class="form-control" id="filterStart">
                </div>
                <div class="col-md-2">
                    <label for="filterEnd" class="form-label"><i class="bi bi-calendar-date me-2"></i>End To</label>
                    <input type="date" class="form-control" id="filterEnd">
                </div>
            </div>

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-4" id="eventViewTabs">
                <li class="nav-item">
                    <a class="nav-link active" id="table-tab" data-bs-toggle="tab" href="#table-view">Table View</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="calendar-tab" data-bs-toggle="tab" href="#calendar-view">Calendar View</a>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="unassigned-tab" data-bs-toggle="tab" data-bs-target="#unassigned" type="button" role="tab" aria-controls="unassigned" aria-selected="false">
                        <i class="bi bi-person-x me-2"></i>Unassigned Shepherds
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Table View -->
                <div class="tab-pane fade show active" id="table-view">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="eventsTable">
                            <thead class="table-light">
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Start Date/Time</th>
                                    <th>End Date/Time</th>
                                    <th>Levels</th>
                                    <th>Location</th>
                                    <th>Recurring</th>
                                    <th>Frequency</th>
                                    <th>Recurrence Day</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Events will be populated dynamically -->
                            </tbody>
                        </table>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center" id="paginationControls"></ul>
                        </nav>
                    </div>
                </div>

                <!-- Calendar View -->
                <div class="tab-pane fade" id="calendar-view">
                    <div id="calendar"></div>
                </div>

                <div class="tab-pane fade" id="unassigned" role="tabpanel" aria-labelledby="unassigned-tab">
                    <div class="table-responsive mt-3">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="unassignedShepherdsTable">
                                <!-- Data will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addEventModalLabel">Add New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addEventForm">
                        <div class="mb-3">
                            <label for="eventTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="eventTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="eventType" class="form-label">Event Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="eventType" name="event_type_id" required>
                                <option value="">-- Select Event Type --</option>
                                <?php foreach ($event_types as $type): ?>
                                    <option value="<?= $type['event_type_id'] ?>" data-is-recurring="<?= $type['is_recurring'] ?>" data-level="<?= $type['level'] ?>" data-frequency="<?= $type['default_frequency'] ?>">
                                        <?= htmlspecialchars($type['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="eventDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="eventDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Level <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input level-checkbox" type="checkbox" name="levels[]" value="household" id="levelHousehold">
                                    <label class="form-check-label" for="levelHousehold">Household</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input level-checkbox" type="checkbox" name="levels[]" value="assembly" id="levelAssembly">
                                    <label class="form-check-label" for="levelAssembly">Assembly</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input level-checkbox" type="checkbox" name="levels[]" value="zone" id="levelZone">
                                    <label class="form-check-label" for="levelZone">Zone</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input level-checkbox" type="checkbox" name="levels[]" value="national" id="levelNational">
                                    <label class="form-check-label" for="levelNational">National</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3" id="locationSelect" style="display: none;">
                            <label class="form-label">Locations <span class="text-danger">*</span></label>
                            <div id="locationCheckboxes" class="d-flex flex-wrap gap-3">
                                <!-- Checkboxes for households, assemblies, zones will be populated dynamically -->
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="eventLocation" class="form-label">Venue</label>
                            <input type="text" class="form-control" id="eventLocation" name="location">
                        </div>
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="startDate" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="endDate" name="end_date" required>
                        </div>
                        <div id="recurringFields" style="display: none;">
                            <div class="mb-3">
                                <label for="eventFrequency" class="form-label">Frequency</label>
                                <select class="form-select" id="eventFrequency" name="frequency">
                                    <option value="">-- Select Frequency --</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                            <div class="mb-3" id="recurrenceDayField" style="display: none;">
                                <label for="recurrenceDay" class="form-label">Recurrence Day</label>
                                <select class="form-select" id="recurrenceDay" name="recurrence_day">
                                    <option value="">-- Select Day --</option>
                                    <option value="Sunday">Sunday</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Event</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <div id="eventToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
            <div class="toast-header">
                <strong class="me-auto">Event Details</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <p><strong>Title:</strong> <span id="toastEventTitle"></span></p>
                <p><strong>Type:</strong> <span id="toastEventType"></span></p>
                <p><strong>Start:</strong> <span id="toastEventStart"></span></p>
                <p><strong>End:</strong> <span id="toastEventEnd"></span></p>
                <p><strong>Level:</strong> <span id="toastEventLevel"></span></p>
                <p><strong>Location:</strong> <span id="toastEventLocation"></span></p>
                <p><strong>Description:</strong> <span id="toastEventDescription"></span></p>
                <p><strong>Recurring:</strong> <span id="toastEventRecurring"></span></p>
                <p><strong>Frequency:</strong> <span id="toastEventFrequency"></span></p>
                <p><strong>Recurrence Day:</strong> <span id="toastEventRecurrenceDay"></span></p>
            </div>
        </div>
    </div>
</main>

<?php include "../../../includes/footer.php"; ?>

<!-- Bootstrap CSS (already included in header.php, but ensure it's present) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css" rel="stylesheet">

<style>
    .nav-card { background-color: #ffffff; border: none; box-shadow: 0 -2px 6px -2px rgba(0, 0, 0, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 10px; padding: 20px; margin-bottom: 20px; margin-top: -30px; position: relative; top: -10px; max-width: 1200px; margin: -30px auto 20px; }
    .nav-card .nav-link-btn { display: flex; flex-direction: column; align-items: center; text-align: center; text-decoration: none; color: #333; padding: 15px; border-radius: 8px; transition: background-color 0.3s, transform 0.2s; }
    .nav-link-btn:hover { background-color: #f1f3f5; transform: scale(1.05); }
    .nav-link-btn i { font-size: 1.5rem; margin-bottom: 8px; }
    .nav-link-btn span { font-size: 0.9rem; font-weight: 500; }
    [data-bs-theme="dark"] .nav-card { background-color: var(--card-bg-dark); }
    [data-bs-theme="dark"] .nav-link-btn { color: #e0e0e0; }
    [data-bs-theme="dark"] .nav-link-btn:hover { background-color: rgba(255, 255, 255, 0.1); }
    .btn-gradient-blue { background: linear-gradient(45deg, #007bff, #00d4ff); color: white; border: none; padding: 5px 10px; border-radius: 5px; transition: transform 0.3s; min-width: 150px; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .btn-gradient-blue.clickable:hover { transform: scale(1.05); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); cursor: pointer; }
    .btn-gradient-blue:hover { transform: scale(1.05); }
    .card { border: 1px solid #e0e0e0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: box-shadow 0.3s ease; }
    @media (prefers-color-scheme: dark) { .card { border: 1px solid #4a90e2 !important; box-shadow: 0 4px 12px -2px rgba(74, 144, 226, 0.5), 0 0 20px rgba(74, 144, 226, 0.3) !important; } }
    
    /* Enhanced Tab Styling */
    #eventViewTabs .nav-link {
        color: #6c757d;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 5px 5px 0 0;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        position: relative;
    }
    
    #eventViewTabs .nav-link:hover {
        color: #007bff;
        background-color: rgba(0, 123, 255, 0.1);
    }
    
    #eventViewTabs .nav-link.active {
        color: #007bff;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
        border-bottom: 3px solid #007bff;
        box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.05);
    }
    
    [data-bs-theme="dark"] #eventViewTabs .nav-link.active {
        color: #4a90e2;
        background-color: #2c2c2c;
        border-color: #444 #444 #2c2c2c;
        border-bottom: 3px solid #4a90e2;
    }
    
    /* Fixed Calendar Styling */
    #calendar-view { 
        padding: 15px; 
        min-height: 600px; 
    }
    #calendar { 
        height: 600px !important;
        max-width: 1200px; /* Added max-width to control the calendar width */
        margin: 0 auto; 
    }
    .fc { 
        height: 100%;
        font-family: 'Arial', sans-serif; 
    }
    .fc .fc-view-harness {
        height: 500px !important;
    }
    .fc-daygrid-day { 
        border: 1px solid #e0e0e0; 
        background-color: #fff; 
        transition: background-color 0.3s; 
    }
    .fc-daygrid-day:hover { background-color: #f8f9fa; }
    .fc-daygrid-day-number { color: #333; font-weight: 500; }
    .fc-day-today { background-color: #e3f2fd !important; }
    
    /* Enhanced Calendar Event Styling */
    .fc-event { 
        border-radius: 5px; 
        padding: 3px 6px !important;
        font-size: 0.95em !important; 
        cursor: pointer; 
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15); 
        transition: transform 0.2s, box-shadow 0.2s; 
        margin: 1px 0 !important;
        overflow: visible !important;
        white-space: normal !important;
        line-height: 1.3 !important;
    }
    
    .fc-event:hover { 
        transform: scale(1.02); 
        z-index: 10 !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .fc-event-title { 
        font-weight: 600; 
        color: #fff; 
        overflow: visible !important;
        white-space: normal !important;
        display: block !important;
    }
    
    /* Gradient backgrounds for events */
    .event-recurring {
        background: linear-gradient(45deg, #28a745, #6fcf97) !important;
        border-color: #28a745 !important;
    }
    
    .event-normal {
        background: linear-gradient(45deg, #007bff, #00d4ff) !important;
        border-color: #007bff !important;
    }
    
    .event-national {
        background: linear-gradient(45deg, #dc3545, #ff6b6b) !important;
        border-color: #dc3545 !important;
    }
    
    .event-zone {
        background: linear-gradient(45deg, #fd7e14, #ffc107) !important;
        border-color: #fd7e14 !important;
    }
    
    .event-assembly {
        background: linear-gradient(45deg, #17a2b8, #4fc3f7) !important;
        border-color: #17a2b8 !important;
    }
    
    .event-household {
        background: linear-gradient(45deg, #6f42c1, #ba68c8) !important;
        border-color: #6f42c1 !important;
    }
    
    /* Multiple events on same day */
    .fc-daygrid-event-harness {
        margin-top: 1px !important;
        margin-bottom: 1px !important;
    }
    
    .fc-daygrid-day-events {
        padding: 2px 0 !important;
    }
    
    .fc-daygrid-more-link {
        background: linear-gradient(45deg, #6c757d, #adb5bd);
        color: white !important;
        padding: 2px 5px;
        border-radius: 5px;
        font-weight: 600;
        margin-top: 3px !important;
        display: inline-block;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .fc-popover {
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2) !important;
        border: none !important;
        border-radius: 8px !important;
    }
    
    .fc-popover-header {
        background: linear-gradient(45deg, #007bff, #00d4ff) !important;
        color: white !important;
        border-top-left-radius: 8px !important;
        border-top-right-radius: 8px !important;
        padding: 10px !important;
    }
    
    .fc-popover-body {
        padding: 10px !important;
    }
    
    /* Responsive calendar adjustments */
    @media (max-width: 768px) {
        #calendar {
            height: 500px !important;
        }
        .fc .fc-view-harness {
            height: 400px !important;
        }
        .fc-header-toolbar {
            flex-direction: column;
            gap: 10px;
        }
    }
    
    #addEventModal .modal-dialog { max-width: 700px; }
    #addEventModal .modal-content { border-radius: 10px; width: 700px; height: 700px; overflow-y: auto; }
    #addEventModal .modal-body { padding: 20px; }
    select[onmousedown="event.preventDefault();"] { background-color: #e9ecef; cursor: not-allowed; }
    
    [data-bs-theme="dark"] .fc-daygrid-day { background-color: #2c2c2c; border-color: #444; }
    [data-bs-theme="dark"] .fc-daygrid-day-number { color: #e0e0e0; }
    [data-bs-theme="dark"] .fc-day-today { background-color: #1e88e5 !important; }
    [data-bs-theme="dark"] .fc-event-title { color: #e0e0e0; }
    .toast { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); min-width: 300px; max-width: 400px; }
    .toast-header { background: linear-gradient(45deg, #007bff, #00d4ff); color: white; border-top-left-radius: 10px; border-top-right-radius: 10px; }
    .toast-body { background: linear-gradient(45deg, #f8f9fa, #e9ecef); color: #333; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; padding: 15px; }
    .toast-body p { margin: 5px 0; font-size: 0.95em; }
    .toast-body strong { color: #007bff; }
    [data-bs-theme="dark"] .toast-header { background: linear-gradient(45deg, #0056b3, #0096c7); }
    [data-bs-theme="dark"] .toast-body { background: linear-gradient(45deg, #343a40, #495057); color: #e0e0e0; }
    [data-bs-theme="dark"] .toast-body strong { color: #4a90e2; }
</style>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>

<script>
// Pass the logged-in member ID to JavaScript
const loggedInMemberId = <?= json_encode($logged_in_member_id) ?>;

document.addEventListener('DOMContentLoaded', function () {
    let currentPage = 1;
    const recordsPerPage = 10;
    let calendarInstance = null;
    let cachedEvents = [];

    // Show alert function
    function showAlert(type, message) {
        const alertContainer = document.body;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1060;">
                <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        alertContainer.appendChild(wrapper);
        setTimeout(() => {
            const alert = wrapper.querySelector('.alert');
            if (alert) bootstrap.Alert.getInstance(alert)?.close();
        }, 5000);
    }

    // Fetch paginated events
    function fetchPaginatedEvents(page) {
        const filterTitle = document.getElementById('filterTitle').value.toLowerCase();
        const filterType = document.getElementById('filterType').value;
        const filterLevel = document.getElementById('filterLevel').value;
        const filterStart = document.getElementById('filterStart').value;
        const filterEnd = document.getElementById('filterEnd').value;

        const params = new URLSearchParams({
            page: page,
            title: filterTitle,
            event_type_name: filterType,
            level: filterLevel,
            start: filterStart,
            end: filterEnd
        });

        return fetch(`fetch_paginated_events.php?${params.toString()}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text().then(text => {
                    if (!text) {
                        throw new Error('Empty response received from server');
                    }
                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    cachedEvents = data.events; // Cache events for calendar use
                    renderTable(data.events);
                    generatePaginationControls(page, data.pagination.total_pages);
                    updateDashboardCounts(data.pagination.total_events, data.events);
                    
                    // Only render calendar if we're on calendar tab
                    const calendarTab = document.getElementById('calendar-tab');
                    if (calendarTab.classList.contains('active')) {
                        renderCalendar(data.events);
                    }
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching events:', error);
                showAlert('danger', 'Failed to load events: ' + error.message);
            });
    }

    // Update dashboard counts
    function updateDashboardCounts(totalEvents, events) {
        const upcomingEvents = events.filter(e => {
            const startDate = new Date(e.start_date);
            return startDate > new Date();
        }).length;
        const recurringEvents = events.filter(e => e.is_recurring).length;
        const thisMonthEvents = events.filter(e => {
            const eventDate = new Date(e.start_date);
            const now = new Date();
            return eventDate.getFullYear() === now.getFullYear() && eventDate.getMonth() === now.getMonth();
        }).length;

        document.getElementById('totalEventsCount').textContent = totalEvents;
        document.getElementById('upcomingEventsCount').textContent = upcomingEvents;
        document.getElementById('recurringEventsCount').textContent = recurringEvents;
        document.getElementById('thisMonthEventsCount').textContent = thisMonthEvents;
    }

    // Render events table
    function renderTable(events) {
        const tbody = document.querySelector('#eventsTable tbody');
        tbody.innerHTML = '';
        events.forEach((event, index) => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', event.event_id);
            row.setAttribute('data-title', event.title);
            row.setAttribute('data-event-type', event.event_type_name || '');
            row.setAttribute('data-description', event.description || '');
            row.setAttribute('data-start-date', event.start_date || '');
            row.setAttribute('data-end-date', event.end_date || '');
            row.setAttribute('data-location', event.location || '');
            row.setAttribute('data-level', event.level || '');
            row.setAttribute('data-is-recurring', event.is_recurring ? 'Yes' : 'No');
            row.setAttribute('data-frequency', event.frequency || '');
            row.setAttribute('data-recurrence-day', event.recurrence_day || '');

            row.innerHTML = `
                <td><input type="checkbox" class="row-checkbox" data-id="${event.event_id}"></td>
                <td>${((currentPage - 1) * recordsPerPage) + index + 1}</td>
                <td>
                    <button class="btn btn-gradient-blue text-nowrap clickable" style="min-width: 150px;" data-event-id="${event.event_id}">
                        ${event.title}
                    </button>
                </td>
                <td>${event.event_type_name || 'N/A'}</td>
                <td>${event.start_date ? new Date(event.start_date).toLocaleString() : 'N/A'}</td>
                <td>${event.end_date ? new Date(event.end_date).toLocaleString() : 'N/A'}</td>
                <td>${event.level || 'N/A'}</td>
                <td>${event.location || 'N/A'}</td>
                <td>${event.is_recurring ? 'Yes' : 'No'}</td>
                <td>${event.frequency || 'N/A'}</td>
                <td>${event.recurrence_day || 'N/A'}</td>
                <td>${event.created_by_name || 'Unknown'}</td>
                <td>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary load-edit-modal" data-event-id="${event.event_id}" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-event-btn" data-event-id="${event.event_id}" data-bs-toggle="modal" data-bs-target="#deleteEventModal-${event.event_id}" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);

            // Create delete confirmation modal for each event
            const deleteModal = document.createElement('div');
            deleteModal.className = 'modal fade';
            deleteModal.id = `deleteEventModal-${event.event_id}`;
            deleteModal.setAttribute('tabindex', '-1');
            deleteModal.setAttribute('aria-labelledby', `deleteEventModalLabel-${event.event_id}`);
            deleteModal.setAttribute('aria-hidden', 'true');
            deleteModal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="deleteEventModalLabel-${event.event_id}">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete the event <strong>${event.title}</strong>?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                            <button type="button" class="btn btn-danger confirm-delete-btn" data-event-id="${event.event_id}">Yes</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(deleteModal);
        });

        attachEventListeners();
    }

    // Generate pagination controls
    function generatePaginationControls(currentPage, totalPages) {
        const paginationContainer = document.getElementById('paginationControls');
        paginationContainer.innerHTML = '';
        const maxPagesToShow = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous"><span aria-hidden="true">«</span></a>`;
        paginationContainer.appendChild(prevLi);

        for (let i = startPage; i <= endPage; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            paginationContainer.appendChild(pageLi);
        }

        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next"><span aria-hidden="true">»</span></a>`;
        paginationContainer.appendChild(nextLi);

        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page >= 1 && page <= totalPages) {
                    currentPage = page;
                    fetchPaginatedEvents(currentPage);
                }
            });
        });
    }

    // Render FullCalendar
    function renderCalendar(events) {
        const calendarEl = document.getElementById('calendar');
        
        // If calendar already exists, destroy it first
        if (calendarInstance) {
            calendarInstance.destroy();
        }
        
        calendarInstance = new FullCalendar.Calendar(calendarEl, {
            height: 600, // Set explicit height
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            dayMaxEventRows: true, // Enable "more" link when too many events
            views: {
                dayGrid: {
                    dayMaxEventRows: 4 // Adjust to show more events before the "more" link
                }
            },
            eventClassNames: function(arg) {
                // Apply appropriate gradient class based on event properties
                const event = arg.event;
                let classes = [];
                
                // Add class based on recurring status
                if (event.extendedProps.is_recurring) {
                    classes.push('event-recurring');
                } else {
                    // Add class based on level if not recurring
                    if (event.extendedProps.level === 'national') {
                        classes.push('event-national');
                    } else if (event.extendedProps.level === 'zone') {
                        classes.push('event-zone');
                    } else if (event.extendedProps.level === 'assembly') {
                        classes.push('event-assembly');
                    } else if (event.extendedProps.level === 'household') {
                        classes.push('event-household');
                    } else {
                        classes.push('event-normal');
                    }
                }
                
                return classes;
            },
            events: events.map(event => ({
                id: event.event_id,
                title: event.title,
                start: event.start_date,
                end: event.end_date,
                extendedProps: {
                    description: event.description,
                    event_type: event.event_type_name,
                    location: event.location,
                    level: event.level,
                    is_recurring: event.is_recurring,
                    frequency: event.frequency,
                    recurrence_day: event.recurrence_day
                }
            })),
            eventDidMount: function(info) {
                // Add tooltip to events for better visibility
                const content = `
                    <div>
                        <strong>${info.event.title}</strong><br>
                        ${info.event.extendedProps.event_type || ''}<br>
                        ${info.event.start ? info.event.start.toLocaleDateString() : ''} 
                        ${info.event.extendedProps.is_recurring ? '(Recurring)' : ''}
                    </div>
                `;
                
                // Apply the tooltip using title attribute for simplicity
                info.el.setAttribute('title', info.event.title + 
                    (info.event.extendedProps.event_type ? ' - ' + info.event.extendedProps.event_type : '') +
                    (info.event.extendedProps.is_recurring ? ' (Recurring)' : ''));
            },
            eventClick: function(info) {
                const event = info.event;
                document.getElementById('toastEventTitle').textContent = event.title;
                document.getElementById('toastEventType').textContent = event.extendedProps.event_type || 'N/A';
                document.getElementById('toastEventStart').textContent = event.start ? event.start.toLocaleString() : 'N/A';
                document.getElementById('toastEventEnd').textContent = event.end ? event.end.toLocaleString() : 'N/A';
                document.getElementById('toastEventLevel').textContent = event.extendedProps.level || 'N/A';
                document.getElementById('toastEventLocation').textContent = event.extendedProps.location || 'N/A';
                document.getElementById('toastEventDescription').textContent = event.extendedProps.description || 'N/A';
                document.getElementById('toastEventRecurring').textContent = event.extendedProps.is_recurring ? 'Yes' : 'No';
                document.getElementById('toastEventFrequency').textContent = event.extendedProps.frequency || 'N/A';
                document.getElementById('toastEventRecurrenceDay').textContent = event.extendedProps.recurrence_day || 'N/A';

                const toast = new bootstrap.Toast(document.getElementById('eventToast'));
                toast.show();
            }
        });
        calendarInstance.render();
    }

    // Listen for tab changes to ensure calendar renders properly
    document.getElementById('calendar-tab').addEventListener('shown.bs.tab', function (e) {
        setTimeout(() => {
            renderCalendar(cachedEvents);
        }, 100);
    });

    // Attach event listeners
    function attachEventListeners() {
        // Load edit modal
        document.querySelectorAll('.load-edit-modal').forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-event-id');
                fetch(`edit_event.php?event_id=${eventId}&currentPage=${currentPage}`)
                    .then(response => response.text())
                    .then(html => {
                        document.body.insertAdjacentHTML('beforeend', html);
                        const modal = new bootstrap.Modal(document.getElementById(`editEventModal-${eventId}`));
                        modal.show();
                    })
                    .catch(error => showAlert('danger', 'Error loading edit modal: ' + error.message));
            });
        });

        // Delete event
        document.querySelectorAll('.confirm-delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-event-id');
                fetch('delete_event.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `event_id=${eventId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        const modal = bootstrap.Modal.getInstance(document.getElementById(`deleteEventModal-${eventId}`));
                        modal.hide();
                        fetchPaginatedEvents(currentPage);
                    } else {
                        showAlert('danger', 'Error: ' + data.message);
                    }
                })
                .catch(error => showAlert('danger', 'Error deleting event: ' + error.message));
            });
        });

        // Bulk delete checkbox handling
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', toggleBulkDeleteButton);
        });

        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleBulkDeleteButton();
        });

        // Event details on click
        document.querySelectorAll('.clickable').forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-event-id');
                const row = document.querySelector(`#eventsTable tr[data-id="${eventId}"]`);
                document.getElementById('toastEventTitle').textContent = row.dataset.title;
                document.getElementById('toastEventType').textContent = row.dataset.eventType || 'N/A';
                document.getElementById('toastEventStart').textContent = row.dataset.startDate ? new Date(row.dataset.startDate).toLocaleString() : 'N/A';
                document.getElementById('toastEventEnd').textContent = row.dataset.endDate ? new Date(row.dataset.endDate).toLocaleString() : 'N/A';
                document.getElementById('toastEventLevel').textContent = row.dataset.level || 'N/A';
                document.getElementById('toastEventLocation').textContent = row.dataset.location || 'N/A';
                document.getElementById('toastEventDescription').textContent = row.dataset.description || 'N/A';
                document.getElementById('toastEventRecurring').textContent = row.dataset.isRecurring;
                document.getElementById('toastEventFrequency').textContent = row.dataset.frequency || 'N/A';
                document.getElementById('toastEventRecurrenceDay').textContent = row.dataset.recurrenceDay || 'N/A';

                const toast = new bootstrap.Toast(document.getElementById('eventToast'));
                toast.show();
            });
        });
    }

    // Toggle bulk delete button visibility
    function toggleBulkDeleteButton() {
        const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        bulkDeleteBtn.style.display = selectedCheckboxes.length > 0 ? 'block' : 'none';
    }

    // Bulk delete events
    document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        const eventIds = Array.from(selectedCheckboxes).map(cb => cb.dataset.id);

        if (eventIds.length === 0) return;

        if (confirm(`Are you sure you want to delete ${eventIds.length} event(s)?`)) {
            fetch('bulk_delete_events.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ event_ids: eventIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    fetchPaginatedEvents(currentPage);
                } else {
                    showAlert('danger', 'Error: ' + data.message);
                }
            })
            .catch(error => showAlert('danger', 'Error deleting events: ' + error.message));
        }
    });

    // Apply filters
    function applyFilters() {
        currentPage = 1;
        fetchPaginatedEvents(currentPage);
    }

    document.getElementById('filterTitle').addEventListener('input', applyFilters);
    document.getElementById('filterType').addEventListener('change', applyFilters);
    document.getElementById('filterLevel').addEventListener('change', applyFilters);
    document.getElementById('filterStart').addEventListener('change', applyFilters);
    document.getElementById('filterEnd').addEventListener('change', applyFilters);

    // Handle Add Event Form Submission
    const addEventForm = document.getElementById('addEventForm');
    addEventForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            showAlert('danger', 'Please fill out all required fields.');
            this.reportValidity();
            return;
        }

        const selectedLevels = Array.from(document.querySelectorAll('.level-checkbox:checked')).map(cb => cb.value);
        if (selectedLevels.length === 0) {
            showAlert('danger', 'Please select at least one level.');
            return;
        }

        const nonNationalLevels = selectedLevels.filter(level => level !== 'national');
        if (nonNationalLevels.length > 0) {
            const selectedLocations = Array.from(document.querySelectorAll('#locationCheckboxes .location-checkbox:checked')).map(cb => cb.value);
            if (selectedLocations.length === 0) {
                showAlert('danger', 'Please select at least one location.');
                return;
            }
        }

        const startDate = new Date(document.getElementById('startDate').value);
        const endDate = new Date(document.getElementById('endDate').value);
        if (endDate <= startDate) {
            showAlert('danger', 'End date must be after start date.');
            return;
        }

        const eventTypeSelect = document.getElementById('eventType');
        const selectedOption = eventTypeSelect.options[eventTypeSelect.selectedIndex];
        const isRecurring = selectedOption.getAttribute('data-is-recurring') === '1';
        if (isRecurring) {
            const frequency = document.getElementById('eventFrequency').value;
            if (!frequency) {
                showAlert('danger', 'Frequency is required for recurring events.');
                return;
            }
            if ((frequency === 'weekly' || frequency === 'monthly') && !document.getElementById('recurrenceDay').value) {
                showAlert('danger', 'Recurrence day is required.');
                return;
            }
        }

        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';

        const formData = new FormData(this);
        formData.append('created_by', loggedInMemberId);

        try {
            const response = await fetch('add_event.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showAlert('success', data.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('addEventModal'));
                modal.hide();
                fetchPaginatedEvents(currentPage);
                this.reset();
                document.getElementById('recurringFields').style.display = 'none';
                document.getElementById('locationSelect').style.display = 'none';
                document.getElementById('locationCheckboxes').innerHTML = '';
            } else {
                showAlert('danger', data.message);
            }
        } catch (error) {
            showAlert('danger', 'Error adding event: ' + error.message);
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    });

    // Dynamic Form Fields for Add Event Modal
    const levelCheckboxes = document.querySelectorAll('.level-checkbox');
    const eventTypeSelect = document.getElementById('eventType');
    const frequencySelect = document.getElementById('eventFrequency');
    const recurrenceDayField = document.getElementById('recurrenceDayField');
    const recurrenceDaySelect = document.getElementById('recurrenceDay');
    const locationSelect = document.getElementById('locationSelect');
    const locationCheckboxes = document.getElementById('locationCheckboxes');

    function updateLocationCheckboxes() {
        const selectedLevels = Array.from(levelCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
        locationCheckboxes.innerHTML = '';

        if (selectedLevels.length === 0 || selectedLevels.includes('national')) {
            locationSelect.style.display = 'none';
            return;
        }

        locationSelect.style.display = 'block';
        const fetchPromises = [];
        if (selectedLevels.includes('household')) {
            fetchPromises.push(fetch('fetch_households.php').then(res => res.json()));
        } else {
            fetchPromises.push(Promise.resolve([]));
        }
        if (selectedLevels.includes('assembly')) {
            fetchPromises.push(fetch('fetch_assemblies.php').then(res => res.json()));
        } else {
            fetchPromises.push(Promise.resolve([]));
        }
        if (selectedLevels.includes('zone')) {
            fetchPromises.push(fetch('fetch_zones.php').then(res => res.json()));
        } else {
            fetchPromises.push(Promise.resolve([]));
        }

        Promise.all(fetchPromises)
            .then(([households, assemblies, zones]) => {
                households.forEach(h => {
                    const div = document.createElement('div');
                    div.className = 'form-check';
                    div.innerHTML = `
                        <input class="form-check-input location-checkbox" type="checkbox" name="household_ids[]" value="${h.household_id}" id="household-${h.household_id}">
                        <label class="form-check-label" for="household-${h.household_id}">${h.name}</label>
                    `;
                    locationCheckboxes.appendChild(div);
                });
                assemblies.forEach(a => {
                    const div = document.createElement('div');
                    div.className = 'form-check';
                    div.innerHTML = `
                        <input class="form-check-input location-checkbox" type="checkbox" name="assembly_ids[]" value="${a.assembly_id}" id="assembly-${a.assembly_id}">
                        <label class="form-check-label" for="assembly-${a.assembly_id}">${a.name}</label>
                    `;
                    locationCheckboxes.appendChild(div);
                });
                zones.forEach(z => {
                    const div = document.createElement('div');
                    div.className = 'form-check';
                    div.innerHTML = `
                        <input class="form-check-input location-checkbox" type="checkbox" name="zone_ids[]" value="${z.zone_id}" id="zone-${z.zone_id}">
                        <label class="form-check-label" for="zone-${z.zone_id}">${z.name}</label>
                    `;
                    locationCheckboxes.appendChild(div);
                });
            })
            .catch(error => showAlert('danger', 'Error fetching locations: ' + error.message));
    }

    eventTypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const isRecurring = selectedOption.getAttribute('data-is-recurring') === '1';
        const defaultLevel = selectedOption.getAttribute('data-level');
        const defaultFrequency = selectedOption.getAttribute('data-frequency');

        levelCheckboxes.forEach(checkbox => {
            checkbox.checked = (checkbox.value === defaultLevel);
        });

        updateLocationCheckboxes();

        const recurringFields = document.getElementById('recurringFields');
        recurringFields.style.display = isRecurring ? 'block' : 'none';
        frequencySelect.value = defaultFrequency || '';
        recurrenceDayField.style.display = (defaultFrequency === 'weekly' || defaultFrequency === 'monthly') ? 'block' : 'none';
        recurrenceDaySelect.required = isRecurring && (defaultFrequency === 'weekly' || defaultFrequency === 'monthly');
    });

    frequencySelect.addEventListener('change', function() {
        const frequency = this.value;
        recurrenceDayField.style.display = (frequency === 'weekly' || frequency === 'monthly') ? 'block' : 'none';
        recurrenceDaySelect.required = (frequency === 'weekly' || frequency === 'monthly');
    });

    levelCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const selectedLevels = Array.from(levelCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            if (this.value === 'national' && this.checked) {
                levelCheckboxes.forEach(cb => {
                    if (cb.value !== 'national') cb.checked = false, cb.disabled = true;
                });
                locationSelect.style.display = 'none';
                locationCheckboxes.innerHTML = '';
            } else {
                levelCheckboxes.forEach(cb => cb.disabled = false);
                const nationalCheckbox = document.getElementById('levelNational');
                if (nationalCheckbox.checked) {
                    nationalCheckbox.checked = false;
                }
                updateLocationCheckboxes();
            }
        });
    });

    // Refresh events on eventUpdated
    window.addEventListener('eventUpdated', function(e) {
        showAlert('success', e.detail.message);
        fetchPaginatedEvents(e.detail.currentPage);
    });

    // Initial fetch
    fetchPaginatedEvents(currentPage);
});

// Add this to your existing JavaScript
function loadUnassignedShepherds() {
    $.ajax({
        url: 'get_unassigned_shepherds.php',
        method: 'GET',
        success: function(response) {
            const shepherds = JSON.parse(response);
            const tbody = $('#unassignedShepherdsTable');
            tbody.empty();
            
            shepherds.forEach(shepherd => {
                tbody.append(`
                    <tr>
                        <td>${shepherd.first_name} ${shepherd.last_name}</td>
                        <td>${shepherd.phone}</td>
                        <td><span class="badge bg-warning">Unassigned</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary assign-shepherd" data-id="${shepherd.id}">
                                <i class="bi bi-person-plus"></i> Assign
                            </button>
                        </td>
                    </tr>
                `);
            });
        },
        error: function(xhr, status, error) {
            console.error('Error loading unassigned shepherds:', error);
        }
    });
}

// Load unassigned shepherds when tab is shown
$('#unassigned-tab').on('shown.bs.tab', function (e) {
    loadUnassignedShepherds();
});

// Handle shepherd assignment
$(document).on('click', '.assign-shepherd', function() {
    const shepherdId = $(this).data('id');
    // Add your assignment logic here
    // You might want to open a modal or redirect to an assignment page
});
</script>
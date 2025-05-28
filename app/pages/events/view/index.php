<?php
session_start();
require_once '../../../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header('Location: /app/pages/auth/login.php');
    exit;
}

// Include header
$pageTitle = "Event View";
include '../../../includes/header.php';
?>

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
                <!-- ...navigation links if needed... -->
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
    </div>    <!-- Events Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Events</h5>
            <div class="d-flex align-items-center gap-3">
                <!-- Theme toggle switch -->
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="themeToggle">
                    <label class="form-check-label" for="themeToggle">
                        <i class="bi bi-moon-stars"></i>
                    </label>
                </div>
                <!-- Debug toggle button -->
                <?php if (isset($_GET['debug']) && $_GET['debug'] === 'true'): ?>
                    <a href="?debug=false" class="btn btn-sm btn-outline-secondary">Disable Debug</a>
                <?php else: ?>
                    <a href="?debug=true" class="btn btn-sm btn-outline-primary">Enable Debug</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="filters-container mb-4">
                <form id="eventFilters" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <label for="startDate" class="form-label mb-0">Start Date</label>
                        <input type="date" id="startDate" name="startDate" class="form-control" placeholder="Start Date">
                    </div>
                    <div class="col-md-4">
                        <label for="endDate" class="form-label mb-0">End Date</label>
                        <input type="date" id="endDate" name="endDate" class="form-control" placeholder="End Date">
                    </div>
                    <div class="col-md-4">
                        <label for="eventLevel" class="form-label mb-0">Event Level</label>
                        <select id="eventLevel" name="eventLevel" class="form-select">
                            <option value="">All Levels</option>
                            <option value="0">National</option>
                            <option value="2">Zone</option>
                            <option value="3">Assembly</option>
                            <option value="1">Household</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="eventType" class="form-label mb-0">Event Type</label>
                        <select id="eventType" name="eventType" class="form-select">
                            <option value="">All Types</option>
                            <!-- Options will be dynamically populated based on level -->
                        </select>
                    </div>
                </form>
            </div>
            <!-- Calendar View -->
            <div class="calendar-container">
                <?php include 'partials/calendar.php'; ?>
            </div>
        </div>
    </div>

    <!-- Debug Panel (conditionally included) -->
    <?php include 'partials/debug_panel.php'; ?>
</main>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css' rel='stylesheet' />
<!-- Custom Calendar Styles -->
<link href="css/calendar.css" rel="stylesheet" />
<!-- FullCalendar Bundle (includes all needed JS) -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<!-- Custom JS -->
<script src="js/event-viewer.js" defer></script>
<!-- Theme Toggle JS -->
<script src="js/theme-toggle.js" defer></script>
<?php include '../../../includes/footer.php'; ?>
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
        <!-- ...existing dashboard cards... -->
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
            <ul class="nav nav-tabs mb-4" id="eventViewTabs">
                <li class="nav-item">
                    <a class="nav-link active" id="table-tab" data-bs-toggle="tab" href="#table-view">Table View</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="calendar-tab" data-bs-toggle="tab" href="#calendar-view">Calendar View</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="table-view">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="eventsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Event Name</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Recurring</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamic content will be loaded here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="calendar-view">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Add/Edit Modals would go here -->
</main>
<?php include "../../../includes/footer.php"; ?>
</body>
</html>

<?php
// Calendar View for Household Events
session_start();
$page_title = "Household Events Calendar";
include "../../../config/db.php";

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /Ekklessia-church-management/app/pages/login.php?redirect=$redirect_url");
    exit;
}

$base_url = '/Ekklessia-church-management/app/pages';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "../../../includes/header.php"; ?>
    <!-- FullCalendar CSS from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />
    <!-- Bootstrap CSS from CDN (if not already included) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="d-flex flex-column min-vh-100">
<main class="container flex-grow-1 py-2">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Household Events Calendar</h5>
        </div>
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
    <!-- FullCalendar JS from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <!-- Bootstrap JS from CDN (if not already included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="calendar-view.js"></script>
</main>
<?php include "../../../includes/footer.php"; ?>
</body>
</html>

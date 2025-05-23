<?php
session_start();
$page_title = "Home for Saints";
include "../../../config/db.php";

// Use a default member_id for testing purposes since there's no login
$member_id = 1; // You can change this to any valid member_id in your database for testing

// Fetch member details
try {
    $stmt = $pdo->prepare("
        SELECT m.first_name, m.last_name, m.contact, m.email, m.joined_date, m.status, 
               h.name AS household_name, a.name AS assembly_name
        FROM members m
        LEFT JOIN member_household mh ON m.member_id = mh.member_id
        LEFT JOIN households h ON mh.household_id = h.household_id
        LEFT JOIN assemblies a ON h.assembly_id = a.assembly_id
        WHERE m.member_id = ?
    ");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching member data: " . $e->getMessage();
    $member = [];
}

// Fetch total households and members for mini-dashboard
$total_households = $pdo->query("SELECT COUNT(*) FROM households WHERE status = 1")->fetchColumn();
$total_members = $pdo->query("SELECT COUNT(*) FROM members WHERE status IN ('Committed saint', 'Active saint')")->fetchColumn();
$attendance_rate = 75; // Static for demo; could be dynamic with attendance data

$base_url = '/Ekklessia-church-management/app/pages';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
unset($_SESSION['success_message']);
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
<body class="d-flex flex-column min-vh-100">
<?php include "../../../includes/sidebar.php"; ?>

<main class="container flex-grow-1 py-2">
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Success!</strong> <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Error!</strong> <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Navigation Card -->
    <div class="card nav-card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/saints/profile.php" class="nav-link-btn">
                        <i class="bi bi-person-fill text-primary"></i><span>My Profile</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/saints/household.php" class="nav-link-btn">
                        <i class="bi bi-house-fill text-warning"></i><span>My Household</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/saints/events.php" class="nav-link-btn">
                        <i class="bi bi-calendar-event text-success"></i><span>Events</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mini Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle p-3 me-3">
                            <i class="bi bi-house fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $total_households ?></h3>
                            <small class="text-muted">Total Households</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning text-white rounded-circle p-3 me-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $total_members ?></h3>
                            <small class="text-muted">Active Saints</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info text-white rounded-circle p-3 me-3">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $attendance_rate ?>%</h3>
                            <small class="text-muted">Attendance Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Details Table -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Welcome, <?= htmlspecialchars($member['first_name'] ?? '') ?> <?= htmlspecialchars($member['last_name'] ?? '') ?></h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Household</th>
                            <th>Assembly</th>
                            <th>Joined Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($member['contact'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($member['email'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($member['household_name'] ?? 'Not Assigned') ?></td>
                            <td><?= htmlspecialchars($member['assembly_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($member['joined_date'] ? (new DateTime($member['joined_date']))->format('jS F, Y') : 'N/A') ?></td>
                            <td><?= htmlspecialchars($member['status'] ?? 'N/A') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<style>
    .nav-card { background-color: #ffffff; border: none; box-shadow: 0 -2px 6px -2px rgba(0, 0, 0, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 10px; padding: 20px; margin-bottom: 20px; }
    .nav-card .nav-link-btn { display: flex; flex-direction: column; align-items: center; text-align: center; text-decoration: none; color: #333; padding: 15px; border-radius: 8px; transition: background-color 0.3s, transform 0.2s; }
    .nav-link-btn:hover { background-color: #f1f3f5; transform: scale(1.05); }
    .nav-link-btn i { font-size: 1.5rem; margin-bottom: 8px; }
    .nav-link-btn span { font-size: 0.9rem; font-weight: 500; }
    [data-bs-theme="dark"] .nav-card { background-color: var(--card-bg-dark); }
    [data-bs-theme="dark"] .nav-link-btn { color: #e0e0e0; }
    [data-bs-theme="dark"] .nav-link-btn:hover { background-color: rgba(255, 255, 255, 0.1); }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>
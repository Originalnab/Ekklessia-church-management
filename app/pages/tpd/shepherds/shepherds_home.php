<?php
session_start();
$page_title = "Home for Shepherds";
include "../../../config/db.php";

// Assuming the logged-in shepherd's ID is stored in the session
$shepherd_id = $_SESSION['member_id'] ?? 16; // Default to 16 (Genevieve Amponsah) for demo

// Fetch shepherd's assigned households
try {
    $stmt = $pdo->prepare("
        SELECT h.household_id, h.name AS household_name, h.date_started, h.status, a.name AS assembly_name,
               (SELECT COUNT(*) FROM member_household mh WHERE mh.household_id = h.household_id) AS total_members
        FROM households h
        JOIN household_shepherdhead_assignments hsa ON h.household_id = hsa.household_id
        LEFT JOIN assemblies a ON h.assembly_id = a.assembly_id
        WHERE hsa.shepherd_member_id = ?
        ORDER BY h.name ASC
    ");
    $stmt->execute([$shepherd_id]);
    $households = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching households: " . $e->getMessage();
    $households = [];
}

// Mini-dashboard data
$total_households = count($households);
$total_members = array_sum(array_column($households, 'total_members'));
$growth_rate = 10; // Static for demo; could be calculated based on historical data

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
                    <a href="<?= $base_url ?>/shepherds/profile.php" class="nav-link-btn">
                        <i class="bi bi-person-fill text-primary"></i><span>My Profile</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/shepherds/households.php" class="nav-link-btn">
                        <i class="bi bi-house-fill text-warning"></i><span>My Households</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/shepherds/members.php" class="nav-link-btn">
                        <i class="bi bi-people-fill text-success"></i><span>Members</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/shepherds/reports.php" class="nav-link-btn">
                        <i class="bi bi-bar-chart-fill text-info"></i><span>Reports</span>
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
                            <small class="text-muted">Assigned Households</small>
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
                            <small class="text-muted">Total Members</small>
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
                            <i class="bi bi-graph-up fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $growth_rate ?>%</h3>
                            <small class="text-muted">Growth Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Households Table -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">My Assigned Households</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Household Name</th>
                            <th>Assembly</th>
                            <th>Total Members</th>
                            <th>Date Started</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($households)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No households assigned.</td></tr>
                        <?php else: ?>
                            <?php foreach ($households as $index => $household): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($household['household_name']) ?></td>
                                    <td><?= htmlspecialchars($household['assembly_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($household['total_members']) ?></td>
                                    <td><?= htmlspecialchars($household['date_started'] ? (new DateTime($household['date_started']))->format('jS F, Y') : 'N/A') ?></td>
                                    <td><?= $household['status'] ? 'Active' : 'Inactive' ?></td>
                                    <td>
                                        <a href="<?= $base_url ?>/shepherds/household_details.php?id=<?= $household['household_id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
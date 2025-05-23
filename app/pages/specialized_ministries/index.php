<?php
session_start();
$page_title = "Specialized Ministries";
include "../../../app/config/config.php"; // Corrected to app/config
include "../auth/auth_check.php"; // Already working

// Fetch total ministries count
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_ministries FROM specialized_ministries");
    $total_ministries = $stmt->fetch(PDO::FETCH_ASSOC)['total_ministries'];
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching total ministries: " . $e->getMessage();
    $total_ministries = 0;
}

// Fetch scopes for dropdown
try {
    $scopesStmt = $pdo->query("SELECT scope_id, scope_name FROM scopes ORDER BY scope_name ASC");
    $scopes = $scopesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching scopes: " . $e->getMessage();
    $scopes = [];
}

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../includes/header.php"; // app/includes ?>
<body class="d-flex flex-column min-vh-100">
<main class="container flex-grow-1 py-2">
    <!-- Alerts -->
    <?php include "../../includes/alerts.php"; // app/includes ?>

    <!-- Navigation Card -->
    <?php include "../../includes/nav_card.php"; // app/includes ?>

    <!-- Mini Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm bg-gradient-primary text-white">
                <div class="card-body text-center">
                    <i class="bi bi-heart-fill fs-2"></i>
                    <h6 class="card-title">Total Ministries</h6>
                    <h3 class="card-text"><?= $total_ministries ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Ministries Section -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Specialized Ministries</h4>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addMinistryModal">
                    <i class="bi bi-plus-circle"></i> Add New Ministry
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="filterMinistryName" class="form-label"><i class="bi bi-search me-2"></i>Ministry Name</label>
                    <input type="text" class="form-control" id="filterMinistryName" placeholder="Search by ministry name">
                </div>
            </div>

            <!-- Ministries Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="ministriesTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Ministry Name</th>
                            <th>Scope</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div id="pagination" class="d-flex justify-content-end"></div>
            </div>

            <!-- Add/Edit Ministry Modal -->
            <div class="modal fade" id="addMinistryModal" tabindex="-1" aria-labelledby="addMinistryModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="addMinistryModalLabel">Add New Ministry</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Alert Container -->
                            <div id="ministryFormAlerts" class="mb-3"></div>
                            <form id="ministryForm">
                                <input type="hidden" id="ministryId" name="ministry_id">
                                <div class="mb-3">
                                    <label for="ministryName" class="form-label">Ministry Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ministryName" name="ministry_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="scopeId" class="form-label">Scope <span class="text-danger">*</span></label>
                                    <select class="form-select" id="scopeId" name="scope_id" required>
                                        <option value="">Select Scope</option>
                                        <?php foreach ($scopes as $scope): ?>
                                        <option value="<?= $scope['scope_id'] ?>"><?= htmlspecialchars($scope['scope_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Ministry</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include "../../includes/footer.php"; // app/includes ?>
<script src="/Ekklessia-church-management/assets/js/specialized_ministries.js"></script>
</body>
</html>
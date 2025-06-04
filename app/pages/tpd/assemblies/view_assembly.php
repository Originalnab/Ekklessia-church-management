<?php
session_start();
$page_title = "View Assembly Details";
include "../../../config/db.php";

// Check if assembly_id is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Assembly ID not provided.";
    header("Location: index.php");
    exit;
}

$assembly_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT a.*, z.name AS zone_name
        FROM assemblies a
        LEFT JOIN zones z ON a.zone_id = z.zone_id
        WHERE a.assembly_id = ?
    ");
    $stmt->execute([$assembly_id]);
    $assembly = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assembly) {
        $_SESSION['error_message'] = "Assembly not found.";
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching assembly: " . $e->getMessage();
    header("Location: index.php");
    exit;
}

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
<body class="d-flex flex-column min-vh-100">
<main class="container flex-grow-1 py-4">
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
    <div class="card nav-card mb-4">
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/members/index.php" class="nav-link-btn">
                        <i class="bi bi-people-fill text-primary"></i>
                        <span>Members</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="dropdown dropdown-hover">
                        <a href="#" class="nav-link-btn dropdown-toggle" id="assembliesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-building-fill text-success"></i>
                            <span>Assemblies</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="assembliesDropdown">
                            <li><a class="dropdown-item" href="<?= $base_url ?>/tpd/assemblies/index.php">Assemblies</a></li>
                            <li><a class="dropdown-item" href="<?= $base_url ?>/tpd/assemblies/assembly_management.php">Assembly Management</a></li>
                        </ul>
                    </div>
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
                    <a href="<?= $base_url ?>/roles/index.php" class="nav-link-btn">
                        <i class="bi bi-person-gear text-secondary"></i>
                        <span>Roles</span>
                    </a>
                </div>
            </div>
        </div>
    </div>    <!-- Assembly Details Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Assembly Details</h4>
            <div>
                <button class="btn btn-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#editAssemblyModal">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <a href="index.php" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Assemblies
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <div class="assembly-icon mb-3">
                        <i class="bi bi-building fs-1"></i>
                    </div>
                    <h5><?= htmlspecialchars($assembly['name']) ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($assembly['zone_name'] ?? 'No Zone Assigned') ?></p>
                    <span class="badge <?= $assembly['status'] ? 'bg-success' : 'bg-danger' ?> mt-2">
                        <?= $assembly['status'] ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Region:</strong></label>
                            <p><?= htmlspecialchars($assembly['region'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>City/Town:</strong></label>
                            <p><?= htmlspecialchars($assembly['city_town'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Digital Address:</strong></label>
                            <p><?= htmlspecialchars($assembly['digital_address'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Nearest Landmark:</strong></label>
                            <p><?= htmlspecialchars($assembly['nearest_landmark'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Date Started:</strong></label>
                            <p><?= htmlspecialchars($assembly['date_started'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Status:</strong></label>
                            <p><span class="badge <?= $assembly['status'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $assembly['status'] ? 'Active' : 'Inactive' ?>
                            </span></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Created At:</strong></label>
                            <p><?= htmlspecialchars($assembly['created_at'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Created By:</strong></label>
                            <p><?= htmlspecialchars($assembly['created_by'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .nav-card {
        background-color: #ffffff;
        border: none;
        box-shadow: 0 -2px 6px -2px rgba(0, 0, 0, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .assembly-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.2);
        transition: all 0.3s ease;
    }
    .assembly-icon:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 16px rgba(33, 150, 243, 0.3);
    }
    .assembly-icon i {
        font-size: 2.5rem;
        color: #1976d2;
    }
    .badge {
        padding: 0.5em 1em;
        font-size: 0.875rem;
        font-weight: 500;
    }
    /* Dark mode styles */
    [data-bs-theme="dark"] .assembly-icon {
        background: linear-gradient(135deg, #1a237e, #0d47a1);
    }
    [data-bs-theme="dark"] .assembly-icon i {
        color: #90caf9;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

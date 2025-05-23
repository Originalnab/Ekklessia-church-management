<?php
session_start();
$page_title = "Assemblies Info";
include "../../../config/db.php";

// Fetch assemblies with zone_id and created_by
try {
    $stmt = $pdo->query("SELECT a.assembly_id, a.name, a.region, a.city_town, a.digital_address, a.nearest_landmark, a.date_started, a.status, z.zone_id, z.name AS zone_name, a.created_by, a.created_at
                        FROM assemblies a
                        LEFT JOIN zones z ON a.zone_id = z.zone_id
                        ORDER BY a.created_at DESC");
    $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals for mini-dashboard
    $total_assemblies = count($assemblies);
    $active_assemblies = $total_assemblies; // Assuming all are active for now
    $avg_members_per_assembly = $total_assemblies ? $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn() / $total_assemblies : 0;
    $avg_members_per_assembly = round($avg_members_per_assembly);
    $total_progress = $total_assemblies ? array_sum(array_map(fn($a) => 82, $assemblies)) / $total_assemblies : 0; // Static for now
    $total_progress = round($total_progress);
} catch (PDOException $e) {
    echo "Error fetching assemblies: " . $e->getMessage();
    exit;
}

// Base URL for navigation links
$base_url = '/Ekklessia-church-management/app/pages';

// Fetch zones for the dropdown in the edit modal
$zones = $pdo->query("SELECT zone_id, name FROM zones")->fetchAll(PDO::FETCH_ASSOC);

// List of 16 regions in Ghana
$regions = [
    'Ahafo',
    'Ashanti',
    'Bono',
    'Bono East',
    'Central',
    'Eastern',
    'Greater Accra',
    'North East',
    'Northern',
    'Oti',
    'Savannah',
    'Upper East',
    'Upper West',
    'Volta',
    'Western',
    'Western North'
];

// Check for success message in session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
unset($_SESSION['success_message']); // Clear the message after displaying

// Check for error message in session
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
<body class="d-flex flex-column min-vh-100">

<main class="container flex-grow-1 py-2">
    <!-- Display Success Alert -->
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Success!</strong> <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Display Error Alert -->
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Error!</strong> <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

  
<!-- Navigation Card -->
<div class="card nav-card" style="margin-top: 50px; position: relative; top: -10px;">
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
</div>

<style>
    .dropdown-hover:hover .dropdown-menu {
        display: block;
    }
    .dropdown-hover .dropdown-menu {
        display: none;
        margin-top: 0; /* Adjust if needed to align with the button */
    }
    .nav-card .nav-link-btn.dropdown-toggle {
        cursor: pointer;
    }
</style>

    <!-- Mini Dashboard Section -->
    <div class="row g-3 mb-4">
        <!-- Total Assemblies Card -->
        <div class="col-md-3">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle p-3 me-3">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $total_assemblies ?></h3>
                            <small class="text-muted">Total Assemblies</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Assemblies Card -->
        <div class="col-md-3">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success text-white rounded-circle p-3 me-3">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $active_assemblies ?></h3>
                            <small class="text-muted">Active Assemblies</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Members Card -->
        <div class="col-md-3">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning text-white rounded-circle p-3 me-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $avg_members_per_assembly ?></h3>
                            <small class="text-muted">Avg Members</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Target Progress Card -->
        <div class="col-md-3">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info text-white rounded-circle p-3 me-3">
                            <i class="bi bi-bullseye fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $total_progress ?>%</h3>
                            <small class="text-muted">Target Progress</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Assemblies Info</h4>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addAssemblyModal">
                    <i class="bi bi-plus-circle"></i> Add New Assembly
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filter Section -->
            <div class="row g-3 mb-4">
                <!-- Search Filter -->
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text filter-icon">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search assemblies...">
                    </div>
                </div>

                <!-- Zone Filter -->
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text filter-icon">
                            <i class="bi bi-globe"></i>
                        </span>
                        <select class="form-select">
                            <option>All Zones</option>
                            <option>Northern Zone</option>
                            <option>Central Zone</option>
                            <option>Southern Zone</option>
                        </select>
                    </div>
                </div>

                <!-- Region Filter -->
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text filter-icon">
                            <i class="bi bi-map"></i>
                        </span>
                        <select class="form-select">
                            <option>All Regions</option>
                            <option>Ahafo</option>
                            <option>Ashanti</option>
                            <option>Bono</option>
                            <option>Bono East</option>
                            <option>Central</option>
                            <option>Eastern</option>
                            <option>Greater Accra</option>
                            <option>North East</option>
                            <option>Northern</option>
                            <option>Oti</option>
                            <option>Savannah</option>
                            <option>Upper East</option>
                            <option>Upper West</option>
                            <option>Volta</option>
                            <option>Western</option>
                            <option>Western North</option>
                        </select>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text filter-icon">
                            <i class="bi bi-funnel"></i>
                        </span>
                        <select class="form-select">
                            <option>All Statuses</option>
                            <option>Active</option>
                            <option>Inactive</option>
                            <option>Completed</option>
                        </select>
                    </div>
                </div>

                <!-- Clear Button -->
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear
                    </button>
                </div>
            </div>

            <!-- Assemblies Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Assembly</th>
                            <th>Region</th>
                            <th>City/Town</th>
                            <th>Digital Address</th>
                            <th>Nearest Landmark</th>
                            <th>Date Started</th>
                            <th>Status</th>
                            <th>Zone</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assemblies as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['region']) ?></td>
                                <td><?= htmlspecialchars($row['city_town']) ?></td>
                                <td><?= htmlspecialchars($row['digital_address'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['nearest_landmark'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['date_started'] ?? 'N/A') ?></td>
                                <td><?= $row['status'] ? 'Yes' : 'No' ?></td>
                                <td><?= htmlspecialchars($row['zone_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['created_at']) ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editAssemblyModal-<?= $row['assembly_id'] ?>" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAssemblyModal-<?= $row['assembly_id'] ?>" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Embedded Edit Modal -->
                            <div class="modal fade" id="editAssemblyModal-<?= $row['assembly_id'] ?>" tabindex="-1" aria-labelledby="editAssemblyModalLabel-<?= $row['assembly_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="editAssemblyModalLabel-<?= $row['assembly_id'] ?>">Edit Assembly: <?= htmlspecialchars($row['name']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="edit_assembly_process.php" method="POST">
                                                <input type="hidden" name="assembly_id" value="<?= $row['assembly_id'] ?>">
                                                <div class="row g-3 mt-3">
                                                    <div class="col-md-6">
                                                        <label for="editAssemblyName-<?= $row['assembly_id'] ?>" class="form-label">Assembly Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="editAssemblyName-<?= $row['assembly_id'] ?>" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editRegion-<?= $row['assembly_id'] ?>" class="form-label">Region <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="editRegion-<?= $row['assembly_id'] ?>" name="region" required>
                                                            <option value="">Select Region</option>
                                                            <?php foreach ($regions as $region): ?>
                                                                <option value="<?= htmlspecialchars($region) ?>" <?= $row['region'] === $region ? 'selected' : '' ?>><?= htmlspecialchars($region) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editCityTown-<?= $row['assembly_id'] ?>" class="form-label">City/Town <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="editCityTown-<?= $row['assembly_id'] ?>" name="city_town" value="<?= htmlspecialchars($row['city_town']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editDigitalAddress-<?= $row['assembly_id'] ?>" class="form-label">Digital Address</label>
                                                        <input type="text" class="form-control" id="editDigitalAddress-<?= $row['assembly_id'] ?>" name="digital_address" value="<?= htmlspecialchars($row['digital_address'] ?? '') ?>" placeholder="e.g., GH-123-456">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editNearestLandmark-<?= $row['assembly_id'] ?>" class="form-label">Nearest Landmark</label>
                                                        <input type="text" class="form-control" id="editNearestLandmark-<?= $row['assembly_id'] ?>" name="nearest_landmark" value="<?= htmlspecialchars($row['nearest_landmark'] ?? '') ?>" placeholder="e.g., Central Market">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editDateStarted-<?= $row['assembly_id'] ?>" class="form-label">Date Started</label>
                                                        <input type="date" class="form-control" id="editDateStarted-<?= $row['assembly_id'] ?>" name="date_started" value="<?= htmlspecialchars($row['date_started'] ?? '') ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editStatus-<?= $row['assembly_id'] ?>" class="form-label">Status</label>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="editStatus-<?= $row['assembly_id'] ?>" name="status" value="1" <?= $row['status'] ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="editStatus-<?= $row['assembly_id'] ?>"><?= $row['status'] ? 'Yes' : 'No' ?></label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editZoneId-<?= $row['assembly_id'] ?>" class="form-label">Zone <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="editZoneId-<?= $row['assembly_id'] ?>" name="zone_id" required>
                                                            <option value="">Select Zone</option>
                                                            <?php foreach ($zones as $zone): ?>
                                                                <option value="<?= $zone['zone_id'] ?>" <?= isset($row['zone_id']) && $row['zone_id'] == $zone['zone_id'] ? 'selected' : '' ?>><?= htmlspecialchars($zone['name']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editCreatedBy-<?= $row['assembly_id'] ?>" class="form-label">Created By <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="editCreatedBy-<?= $row['assembly_id'] ?>" name="created_by" value="<?= htmlspecialchars($row['created_by'] ?? 'Admin') ?>" required readonly>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Assembly</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Embedded Delete Confirmation Modal -->
                            <div class="modal fade" id="deleteAssemblyModal-<?= $row['assembly_id'] ?>" tabindex="-1" aria-labelledby="deleteAssemblyModalLabel-<?= $row['assembly_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title" id="deleteAssemblyModalLabel-<?= $row['assembly_id'] ?>">Confirm Deletion</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete the record for <strong><?= htmlspecialchars($row['name']) ?></strong>?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                            <form action="delete_assembly_process.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="assembly_id" value="<?= $row['assembly_id'] ?>">
                                                <button type="submit" class="btn btn-danger">Yes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Include the add_assemblies.php modal -->
    <?php include "add_assemblies.php"; ?>
</main>

<style>
    /* Navigation Card Styling */
    .nav-card {
        background-color: #ffffff;
        border: none;
        box-shadow: 0 -2px 6px -2px rgba(0, 0, 0, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1); /* Top and bottom shadows */
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .nav-card .nav-link-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        text-decoration: none;
        color: #333;
        padding: 15px;
        border-radius: 8px;
        transition: background-color 0.3s, transform 0.2s;
    }
    .nav-link-btn:hover {
        background-color: #f1f3f5;
        transform: scale(1.05);
    }
    .nav-link-btn i {
        font-size: 1.5rem;
        margin-bottom: 8px;
    }
    .nav-link-btn span {
        font-size: 0.9rem;
        font-weight: 500;
    }
    [data-bs-theme="dark"] .nav-card {
        background-color: var(--card-bg-dark);
    }
    [data-bs-theme="dark"] .nav-link-btn {
        color: #e0e0e0;
    }
    [data-bs-theme="dark"] .nav-link-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
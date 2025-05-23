<?php
session_start(); // Start the session
$page_title = "Zones Management";
include "../../../includes/header.php";
include "../../../config/db.php";

// Get messages from session
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;

// Clear session messages after displaying
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}

// Fetch zones
try {
    $stmt = $pdo->query("SELECT zone_id, name, description, created_at, created_by, updated_by FROM zones ORDER BY created_at DESC");
    $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals for mini-dashboard (example metrics)
    $total_zones = count($zones);
    $active_zones = $total_zones; // Assuming all zones are active for now
    $avg_assemblies_per_zone = $total_zones ? $pdo->query("SELECT COUNT(*) FROM assemblies")->fetchColumn() / $total_zones : 0;
    $avg_assemblies_per_zone = round($avg_assemblies_per_zone);
    $total_progress = $total_zones ? array_sum(array_map(fn($z) => 80, $zones)) / $total_zones : 0; // Static for now
    $total_progress = round($total_progress);
} catch (PDOException $e) {
    echo "Error fetching zones: " . $e->getMessage();
    exit;
}

// Base URL for navigation links
$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zones Management - Ekklessia Church Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/Ekklessia-church-management/Public/css/style.css" rel="stylesheet">
    <style>
        .card {
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        .modal .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
        }
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 400px;
        }
        .badge {
            cursor: pointer;
        }
        .modal-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 5px;
        }
        .modal-table th,
        .modal-table td {
            padding: 8px 12px;
            text-align: left;
            background-color: #f8f9fa;
            border: none;
        }
        .modal-table th {
            font-weight: 600;
            color: #333;
        }
        .modal-table td {
            color: #555;
        }
        .modal-table tr {
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<main class="container flex-grow-1 py-2"> <!-- Reduced from py-4 to py-2 -->

    <!-- Bootstrap Alerts -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Navigation Card -->
    <div class="card nav-card">
        <div class="card-body">
            <!-- <h5 class="card-title mb-4">Navigation</h5> -->
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
                    <a href="<?= $base_url ?>/roles/index.php" class="nav-link-btn">
                        <i class="bi bi-person-gear text-secondary"></i>
                        <span>Roles</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mini Dashboard Section -->
    <div class="row g-3 mb-4">
        <!-- Total Zones Card -->
        <div class="col-md-3">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle p-3 me-3">
                            <i class="bi bi-globe-americas fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $total_zones; ?></h3>
                            <small class="text-muted">Total Zones</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Zones Card -->
        <div class="col-md-3">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success text-white rounded-circle p-3 me-3">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $active_zones; ?></h3>
                            <small class="text-muted">Active Zones</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Assemblies Card -->
        <div class="col-md-3">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning text-white rounded-circle p-3 me-3">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $avg_assemblies_per_zone; ?></h3>
                            <small class="text-muted">Avg Assemblies</small>
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
                            <h3 class="mb-0"><?php echo $total_progress; ?>%</h3>
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
                <h4 class="mb-0"><?php echo $page_title; ?></h4>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addZoneModal">
                    <i class="bi bi-plus-circle"></i> Add New Zone
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
                        <input type="text" class="form-control" placeholder="Search zones...">
                    </div>
                </div>

                <!-- Clear Button -->
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear
                    </button>
                </div>
            </div>

            <!-- Zones Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Zone</th>
                            <th>Description</th>
                            <th>Date Created</th>
                            <th>Created By</th>
                            <th>Updated By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($zones as $zone): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-info view-zone" data-zone-id="<?php echo $zone['zone_id']; ?>" data-bs-toggle="modal" data-bs-target="#viewZoneModal">
                                        <?php echo htmlspecialchars($zone['name']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($zone['description']); ?></td>
                                <td><?php echo htmlspecialchars($zone['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($zone['created_by']); ?></td>
                                <td><?php echo htmlspecialchars($zone['updated_by'] ?? 'N/A'); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editZoneModal-<?php echo $zone['zone_id']; ?>" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteZoneModal-<?php echo $zone['zone_id']; ?>" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal for Each Zone -->
                            <div class="modal fade" id="editZoneModal-<?php echo $zone['zone_id']; ?>" tabindex="-1" aria-labelledby="editZoneModalLabel-<?php echo $zone['zone_id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editZoneModalLabel-<?php echo $zone['zone_id']; ?>">Edit Zone: <?php echo htmlspecialchars($zone['name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="edit_zone.php">
                                            <input type="hidden" name="zone_id" value="<?php echo $zone['zone_id']; ?>">
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="name-<?php echo $zone['zone_id']; ?>" class="form-label">Zone Name</label>
                                                    <input type="text" class="form-control" id="name-<?php echo $zone['zone_id']; ?>" name="name" value="<?php echo htmlspecialchars($zone['name']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="description-<?php echo $zone['zone_id']; ?>" class="form-label">Description (Optional)</label>
                                                    <textarea class="form-control" id="description-<?php echo $zone['zone_id']; ?>" name="description"><?php echo htmlspecialchars($zone['description'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="created_by-<?php echo $zone['zone_id']; ?>" class="form-label">Created By</label>
                                                    <input type="text" class="form-control" id="created_by-<?php echo $zone['zone_id']; ?>" name="created_by" value="<?php echo htmlspecialchars($zone['created_by']); ?>" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="updated_by-<?php echo $zone['zone_id']; ?>" class="form-label">Updated By</label>
                                                    <input type="text" class="form-control" id="updated_by-<?php echo $zone['zone_id']; ?>" name="updated_by" value="<?php echo htmlspecialchars($zone['updated_by'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Confirmation Modal for Each Zone -->
                            <div class="modal fade" id="deleteZoneModal-<?php echo $zone['zone_id']; ?>" tabindex="-1" aria-labelledby="deleteZoneModalLabel-<?php echo $zone['zone_id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteZoneModalLabel-<?php echo $zone['zone_id']; ?>">Confirm Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete the zone "<?php echo htmlspecialchars($zone['name']); ?>"? This action cannot be undone.
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                            <form method="POST" action="delete_zone.php">
                                                <input type="hidden" name="zone_id" value="<?php echo $zone['zone_id']; ?>">
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

    <!-- Single View Modal -->
    <div class="modal fade" id="viewZoneModal" tabindex="-1" aria-labelledby="viewZoneModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewZoneModalLabel">View Zone</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewZoneModalBody">
                    <!-- Zone details will be loaded here as a table -->
                    <table class="table modal-table">
                        <tbody id="viewZoneTableBody">
                            <!-- Table rows will be populated dynamically -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Adding New Zone -->
    <div class="modal fade" id="addZoneModal" tabindex="-1" aria-labelledby="addZoneModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addZoneModalLabel">Add New Zone</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="add_zone.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Zone Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="created_by" class="form-label">Created By</label>
                            <input type="text" class="form-control" id="created_by" name="created_by" value="Admin" required>
                        </div>
                        <div class="mb-3">
                            <label for="updated_by" class="form-label">Updated By (Optional)</label>
                            <input type="text" class="form-control" id="updated_by" name="updated_by">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Zone</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include "../../../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Handle View Modal population with AJAX
        document.querySelectorAll('.view-zone').forEach(badge => {
            badge.addEventListener('click', function () {
                const zoneId = this.getAttribute('data-zone-id');
                fetch(`view_zone.php?zone_id=${zoneId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const zone = data.data;
                            document.getElementById('viewZoneModalLabel').textContent = `View Zone: ${zone.name}`;
                            const tableBody = document.getElementById('viewZoneTableBody');
                            tableBody.innerHTML = `
                                <tr>
                                    <th>Zone Name</th>
                                    <td>${zone.name}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>${zone.description}</td>
                                </tr>
                                <tr>
                                    <th>Date Created</th>
                                    <td>${zone.created_at}</td>
                                </tr>
                                <tr>
                                    <th>Created By</th>
                                    <td>${zone.created_by}</td>
                                </tr>
                                <tr>
                                    <th>Updated By</th>
                                    <td>${zone.updated_by}</td>
                                </tr>
                            `;
                        } else {
                            document.getElementById('viewZoneModalBody').innerHTML = `<p class="text-danger">${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        document.getElementById('viewZoneModalBody').innerHTML = `<p class="text-danger">Error loading zone details: ${error.message}</p>`;
                    });
            });
        });
    </script>
</body>
</html>
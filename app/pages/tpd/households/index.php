<?php
session_start();
$page_title = "Households Management";
include "../../../config/db.php";

// Fetch households with assembly names
try {
    $stmt = $pdo->query("SELECT h.household_id, h.name, h.assembly_id, h.address, h.digital_address, h.status, h.nearest_landmark, h.date_started, h.created_at, h.updated_at, h.created_by, h.updated_by, a.name AS assembly_name
                        FROM households h
                        LEFT JOIN assemblies a ON h.assembly_id = a.assembly_id
                        ORDER BY h.created_at DESC");
    $households = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals for mini-dashboard
    $total_households = count($households);
    $avg_members_per_household = $total_households ? $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn() / $total_households : 0;
    $avg_members_per_household = round($avg_members_per_household);
    $total_progress = $total_households ? array_sum(array_map(fn($h) => 82, $households)) / $total_households : 0; // Static for now
    $total_progress = round($total_progress);
} catch (PDOException $e) {
    echo "Error fetching households: " . $e->getMessage();
    exit;
}

// Base URL for navigation links
$base_url = '/Ekklessia-church-management/app/pages';

// Fetch assemblies for the dropdown in the edit modal
$assemblies = $pdo->query("SELECT assembly_id, name FROM assemblies")->fetchAll(PDO::FETCH_ASSOC);

// Define Bootstrap 5 badge colors
$badge_colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];

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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Households Management - Ekklessia Church Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/Ekklessia-church-management/Public/css/style.css" rel="stylesheet">
    <style>
        .card {
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .nav-link-btn {
            text-decoration: none;
            color: inherit;
        }
        .nav-link-btn i {
            font-size: 2rem;
        }
        .nav-link-btn span {
            font-size: 0.9rem;
        }
        [data-bs-theme="dark"] .nav-link-btn {
            color: #e0e0e0;
        }
        [data-bs-theme="dark"] .nav-link-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        /* Style for the toggle switch in the table */
        .form-switch label {
            margin-left: 0.5rem;
            vertical-align: middle;
        }
        /* Style for the age button */
        .age-btn {
            font-size: 0.9rem;
            padding: 0.25rem 0.5rem;
            width: 200px;
            white-space: normal;
            overflow: visible;
            text-overflow: unset;
            display: inline-block;
            text-align: center;
        }
        /* Adjust button group styling */
        .btn-group .dropdown-toggle {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .btn-group .dropdown-menu {
            min-width: 10rem;
        }
        /* Ensure dropdown is positioned correctly */
        .dropdown-menu {
            position: absolute;
            z-index: 1000;
        }
    </style>
</head>
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
    <div class="card nav-card">
        <div class="card-body">
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
                    <div class="dropdown">
                        <a class="nav-link-btn dropdown-toggle" href="#" role="button" id="householdsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-house-fill text-warning"></i>
                            <span>Households</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="householdsDropdown">
                            <li>
                                <a class="dropdown-item" href="<?= $base_url ?>/tpd/households/index.php">
                                    <i class="bi bi-house-fill me-2"></i>View Households
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= $base_url ?>/tpd/households/assign_leader.php">
                                    <i class="bi bi-person-check-fill me-2"></i>Assign Leader
                                </a>
                            </li>
                        </ul>
                    </div>
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
        <!-- Total Households Card -->
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

        <!-- Average Members Card -->
        <div class="col-md-4">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning text-white rounded-circle p-3 me-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $avg_members_per_household ?></h3>
                            <small class="text-muted">Avg Members</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Target Progress Card -->
        <div class="col-md-4">
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
                <h4 class="mb-0">Households Management</h4>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addHouseholdModal">
                    <i class="bi bi-plus-circle"></i> Add New Household
                </button>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Filter Section -->
            <div class="row g-3 mb-4">
                <!-- Search Filter -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text filter-icon">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search households...">
                    </div>
                </div>

                <!-- Assembly Filter -->
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text filter-icon">
                            <i class="bi bi-building"></i>
                        </span>
                        <select class="form-select">
                            <option>All Assemblies</option>
                            <?php foreach ($assemblies as $assembly): ?>
                                <option value="<?= $assembly['assembly_id'] ?>"><?= htmlspecialchars($assembly['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text filter-icon">
                            <i class="bi bi-toggle-on"></i>
                        </span>
                        <select class="form-select">
                            <option>All Statuses</option>
                            <option>Active</option>
                            <option>Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Nearest Landmark Filter -->
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text filter-icon">
                            <i class="bi bi-geo-alt"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Nearest Landmark...">
                    </div>
                </div>

                <!-- Created By Filter -->
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text filter-icon">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Created By...">
                    </div>
                </div>

                <!-- Clear Button -->
                <div class="col-md-1">
                    <button class="btn btn-outline-primary w-100">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear
                    </button>
                </div>
            </div>

            <!-- Households Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>#</th>
                            <th>Household Name</th>
                            <th>Assembly</th>
                            <th>Address</th>
                            <th>Digital Address</th>
                            <th>Status</th>
                            <th>Nearest Landmark</th>
                            <th>Date Started</th>
                            <th>Age</th>
                            <th>Created By</th>
                            <th style="display: none;">Updated By</th>
                            <th style="display: none;">Updated At</th>
                            <th style="display: none;">Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($households as $index => $row): ?>
                            <tr>
                                <td><input type="checkbox" class="row-checkbox" data-id="<?= $row['household_id'] ?>"></td>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $badge_colors[$row['assembly_id'] % count($badge_colors)] ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                        <?= htmlspecialchars($row['assembly_name'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['address']) ?></td>
                                <td><?= htmlspecialchars($row['digital_address']) ?></td>
                                <td>
                                    <div class="form-switch">
                                        <input class="form-check-input household-status-toggle" type="checkbox" role="switch" id="status-<?= $row['household_id'] ?>" data-household-id="<?= $row['household_id'] ?>" <?= $row['status'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="status-<?= $row['household_id'] ?>" id="status-label-<?= $row['household_id'] ?>"><?= $row['status'] ? 'Active' : 'Inactive' ?></label>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['nearest_landmark'] ?? 'N/A') ?></td>
                                <td class="date-started" data-date="<?= htmlspecialchars($row['date_started'] ?? '') ?>">
                                    <?= htmlspecialchars($row['date_started'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <button class="btn btn-info text-white age-btn" style="background: linear-gradient(45deg, #17a2b8, #006d77); border: none;" id="age-<?= $row['household_id'] ?>" data-date-started="<?= htmlspecialchars($row['date_started'] ?? '') ?>">
                                        Loading...
                                    </button>
                                </td>
                                <td><?= htmlspecialchars($row['created_by']) ?></td>
                                <td style="display: none;"><?= htmlspecialchars($row['updated_by'] ?? 'N/A') ?></td>
                                <td style="display: none;"><?= htmlspecialchars($row['updated_at'] ?? 'N/A') ?></td>
                                <td style="display: none;"><?= htmlspecialchars($row['created_at']) ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editHouseholdModal-<?= $row['household_id'] ?>" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteHouseholdModal-<?= $row['household_id'] ?>" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <!-- Assign Button Group with 3 Options -->
                                        <div class="btn-group dropdown" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="assignDropdown-<?= $row['household_id'] ?>">
                                                <i class="bi bi-person-plus"></i> Assign
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Assign to Member</a></li>
                                                <li><a class="dropdown-item" href="#">Assign to Role</a></li>
                                                <li><a class="dropdown-item" href="#">Assign to Group</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <!-- Embedded Edit Modal -->
                            <div class="modal fade" id="editHouseholdModal-<?= $row['household_id'] ?>" tabindex="-1" aria-labelledby="editHouseholdModalLabel-<?= $row['household_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="editHouseholdModalLabel-<?= $row['household_id'] ?>">Edit Household: <?= htmlspecialchars($row['name']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="edit_household_process.php" method="POST">
                                                <input type="hidden" name="household_id" value="<?= $row['household_id'] ?>">
                                                <div class="row g-3 mt-3">
                                                    <div class="col-md-6">
                                                        <label for="editHouseholdName-<?= $row['household_id'] ?>" class="form-label">Household Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="editHouseholdName-<?= $row['household_id'] ?>" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editAssemblyId-<?= $row['household_id'] ?>" class="form-label">Assembly <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="editAssemblyId-<?= $row['household_id'] ?>" name="assembly_id" required>
                                                            <option value="">Select Assembly</option>
                                                            <?php foreach ($assemblies as $assembly): ?>
                                                                <option value="<?= $assembly['assembly_id'] ?>" <?= $row['assembly_id'] == $assembly['assembly_id'] ? 'selected' : '' ?>><?= htmlspecialchars($assembly['name']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editAddress-<?= $row['household_id'] ?>" class="form-label">Address <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="editAddress-<?= $row['household_id'] ?>" name="address" value="<?= htmlspecialchars($row['address']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editDigitalAddress-<?= $row['household_id'] ?>" class="form-label">Digital Address <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="editDigitalAddress-<?= $row['household_id'] ?>" name="digital_address" value="<?= htmlspecialchars($row['digital_address']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editStatus-<?= $row['household_id'] ?>" class="form-label">Status</label>
                                                        <div class="form-switch">
                                                            <input class="form-check-input" type="checkbox" role="switch" id="editStatus-<?= $row['household_id'] ?>" name="status" value="1" <?= $row['status'] ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="editStatus-<?= $row['household_id'] ?>" id="editStatusLabel-<?= $row['household_id'] ?>"><?= $row['status'] ? 'Active' : 'Inactive' ?></label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editNearestLandmark-<?= $row['household_id'] ?>" class="form-label">Nearest Landmark</label>
                                                        <input type="text" class="form-control" id="editNearestLandmark-<?= $row['household_id'] ?>" name="nearest_landmark" value="<?= htmlspecialchars($row['nearest_landmark'] ?? '') ?>" placeholder="e.g., Central Market">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editDateStarted-<?= $row['household_id'] ?>" class="form-label">Date Started</label>
                                                        <input type="date" class="form-control" id="editDateStarted-<?= $row['household_id'] ?>" name="date_started" value="<?= htmlspecialchars($row['date_started'] ?? '') ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editCreatedBy-<?= $row['household_id'] ?>" class="form-label">Created By <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="editCreatedBy-<?= $row['household_id'] ?>" name="created_by" value="<?= htmlspecialchars($row['created_by']) ?>" required readonly>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="editUpdatedBy-<?= $row['household_id'] ?>" class="form-label">Updated By</label>
                                                        <input type="text" class="form-control" id="editUpdatedBy-<?= $row['household_id'] ?>" name="updated_by" value="Admin" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Household</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Embedded Delete Confirmation Modal -->
                            <div class="modal fade" id="deleteHouseholdModal-<?= $row['household_id'] ?>" tabindex="-1" aria-labelledby="deleteHouseholdModalLabel-<?= $row['household_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title" id="deleteHouseholdModalLabel-<?= $row['household_id'] ?>">Confirm Deletion</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete the record for <strong><?= htmlspecialchars($row['name']) ?></strong>?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                            <form action="delete_household_process.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="household_id" value="<?= $row['household_id'] ?>">
                                                <button type="submit" class="btn btn-danger">Yes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Include the add_households.php modal -->
    <?php include "add_households.php"; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script>
    // Helper function to get the ordinal suffix (e.g., "st", "nd", "rd", "th")
    function getOrdinalSuffix(day) {
        if (day > 3 && day < 21) return 'th';
        switch (day % 10) {
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
            default: return 'th';
        }
    }

    // Function to format the date as "3rd January, 2024"
    function formatDateStarted(dateStr) {
        if (!dateStr || dateStr === 'N/A') return 'N/A';

        const date = new Date(dateStr);
        const day = date.getDate();
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        const month = monthNames[date.getMonth()];
        const year = date.getFullYear();
        const ordinalSuffix = getOrdinalSuffix(day);

        return `${day}${ordinalSuffix} ${month}, ${year}`;
    }

    // Calculate age for each household based on date_started
    function calculateAge(dateStarted) {
        if (!dateStarted || dateStarted === 'N/A') return 'N/A';

        const startedDate = new Date(dateStarted);
        const currentDate = new Date();

        startedDate.setHours(0, 0, 0, 0);
        currentDate.setHours(0, 0, 0, 0);

        const isToday = startedDate.getTime() === currentDate.getTime();
        if (isToday) return 'Today';

        const yesterday = new Date(currentDate);
        yesterday.setDate(currentDate.getDate() - 1);
        const isYesterday = startedDate.getTime() === yesterday.getTime();
        if (isYesterday) return 'Yesterday';

        const diffMs = currentDate - startedDate;
        const diffDaysTotal = Math.floor(diffMs / (1000 * 60 * 60 * 24));

        if (diffDaysTotal < 7) {
            return `${diffDaysTotal} day${diffDaysTotal !== 1 ? 's' : ''}`;
        } else if (diffDaysTotal < 30) {
            const weeks = Math.floor(diffDaysTotal / 7);
            const remainingDays = diffDaysTotal % 7;
            let result = `${weeks} week${weeks !== 1 ? 's' : ''}`;
            if (remainingDays > 0) {
                result += `, ${remainingDays} day${remainingDays !== 1 ? 's' : ''}`;
            }
            return result;
        }

        let years = currentDate.getFullYear() - startedDate.getFullYear();
        let months = currentDate.getMonth() - startedDate.getMonth();
        let days = currentDate.getDate() - startedDate.getDate();

        if (days < 0) {
            months--;
            const lastMonthDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), 0);
            days += lastMonthDate.getDate();
        }
        if (months < 0) {
            years--;
            months += 12;
        }

        let ageParts = [];
        if (years > 0) ageParts.push(`${years} year${years !== 1 ? 's' : ''}`);
        if (months > 0) ageParts.push(`${months} month${months !== 1 ? 's' : ''}`);
        if (days > 0) ageParts.push(`${days} day${days !== 1 ? 's' : ''}`);

        return ageParts.length > 0 ? ageParts.join(', ') : 'Less than a day';
    }

    // Initialize age calculation and date formatting on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Format Date Started column
        document.querySelectorAll('.date-started').forEach(function(cell) {
            const rawDate = cell.getAttribute('data-date');
            cell.textContent = formatDateStarted(rawDate);
        });

        // Calculate and display Age column
        document.querySelectorAll('tr').forEach(function(row) {
            const ageButton = row.querySelector('.age-btn');
            if (ageButton) {
                const dateStarted = ageButton.getAttribute('data-date-started');
                const ageText = calculateAge(dateStarted);
                ageButton.textContent = ageText;

                if (ageText.length > 15) {
                    ageButton.style.fontSize = '0.8rem';
                    ageButton.style.padding = '0.2rem 0.4rem';
                }
            }
        });

        // Select/deselect all checkboxes
        document.getElementById('select-all').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.row-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });

        // Dynamically update the status label for each edit modal
        document.querySelectorAll('input[name="status"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const label = document.getElementById('editStatusLabel-' + this.id.split('-')[1]);
                label.textContent = this.checked ? 'Active' : 'Inactive';
            });
        });

        // Handle toggle switch changes in the table with AJAX
        document.querySelectorAll('.household-status-toggle').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const householdId = this.getAttribute('data-household-id');
                const newStatus = this.checked ? 1 : 0;
                const label = document.getElementById('status-label-' + householdId);

                label.textContent = this.checked ? 'Active' : 'Inactive';

                fetch('toggle_household_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'household_id=' + encodeURIComponent(householdId) + '&status=' + encodeURIComponent(newStatus)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', 'Status updated successfully');
                    } else {
                        this.checked = !this.checked;
                        label.textContent = this.checked ? 'Active' : 'Inactive';
                        showAlert('danger', 'Error: ' + data.message);
                    }
                })
                .catch(error => {
                    this.checked = !this.checked;
                    label.textContent = this.checked ? 'Active' : 'Inactive';
                    showAlert('danger', 'Network error: ' + error.message);
                });
            });
        });

        // Initialize and manage dropdown behavior
        document.querySelectorAll('.dropdown-toggle').forEach(button => {
            const dropdown = new bootstrap.Dropdown(button);
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const dropdownMenu = this.nextElementSibling;
                if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                    console.log('Dropdown clicked for button:', this.id);
                    dropdown.show();
                } else {
                    console.error('Dropdown menu not found or invalid structure!');
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                const dropdownToggle = menu.previousElementSibling;
                if (dropdownToggle && !dropdownToggle.contains(e.target) && !menu.contains(e.target)) {
                    const dropdown = bootstrap.Dropdown.getInstance(dropdownToggle);
                    if (dropdown) {
                        dropdown.hide();
                    }
                }
            });
        });

        // Function to show Bootstrap alerts
        function showAlert(type, message) {
            const alertContainer = document.createElement('div');
            alertContainer.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
            alertContainer.style.zIndex = '1050';
            alertContainer.role = 'alert';
            alertContainer.innerHTML = `
                <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(alertContainer);

            setTimeout(() => {
                alertContainer.classList.remove('show');
                setTimeout(() => alertContainer.remove(), 150);
            }, 3000);
        }
    });
</script>
</body>
</html>
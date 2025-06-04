<?php
session_start();
$page_title = "Members Management";
include "../../../config/db.php";

// Fetch members with assembly names and shepherd names (initial load for first page)
try {
    $stmt = $pdo->query("SELECT 
            m.member_id, 
            m.first_name, 
            m.last_name, 
            m.date_of_birth, 
            m.gender, 
            m.marital_status, 
            m.contact, 
            m.email, 
            m.address, 
            m.digital_address, 
            m.occupation, 
            m.employer, 
            m.work_phone, 
            m.highest_education_level, 
            m.institution, 
            m.year_graduated, 
            m.status, 
            m.joined_date, 
            m.assemblies_id, 
            m.local_function_id, 
            m.username, 
            m.password, 
            m.created_at, 
            m.updated_at, 
            m.created_by, 
            m.updated_by, 
            m.profile_photo, 
            m.referral_id, 
            m.group_name,
            a.name as assembly_name,
            a.assembly_id,
            sh.first_name as shepherd_first_name,
            sh.last_name as shepherd_last_name,
            CONCAT(sh.first_name, ' ', sh.last_name) as shepherd_name,
            sh.member_id as shepherd_id,
            h.household_id,
            h.name as household_name,
            ref.first_name as referral_first_name,
            ref.last_name as referral_last_name
        FROM members m
        LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
        LEFT JOIN member_household mh ON m.member_id = mh.member_id
        LEFT JOIN households h ON mh.household_id = h.household_id
        LEFT JOIN members sh ON mh.shepherd_id = sh.member_id
        LEFT JOIN members ref ON m.referral_id = ref.member_id
        ORDER BY m.member_id DESC");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    $members = [];
}

// Fetch new members (joined in the last 30 days)
try {
    $stmt = $pdo->query("SELECT * FROM members WHERE joined_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
    $newMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching new members: " . $e->getMessage();
    $newMembers = [];
}

// Fetch total assemblies count directly from the assemblies table
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_assemblies FROM assemblies");
    $total_assemblies = $stmt->fetch(PDO::FETCH_ASSOC)['total_assemblies'];
} catch (PDOException $e) {
    echo "Error fetching total assemblies: " . $e->getMessage();
    exit;
}

// Fetch unique assemblies for the filter dropdown and color mapping
try {
    $stmt = $pdo->query("SELECT assembly_id, name AS assembly_name FROM assemblies ORDER BY name ASC");
    $all_assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $assemblies = array_column($all_assemblies, 'assembly_name');
    $assemblies = array_filter($assemblies); // Remove any null or empty values
    sort($assemblies); // Sort alphabetically

    // Create assembly map for color mapping
    $assembly_map = [];
    foreach ($all_assemblies as $assembly) {
        $assembly_map[$assembly['assembly_id']] = $assembly['assembly_name'];
    }
    $assembly_map['N/A'] = 'N/A'; // Add N/A for members without an assembly
    $assembly_map_json = json_encode($assembly_map);
} catch (PDOException $e) {
    echo "Error fetching assemblies: " . $e->getMessage();
    exit;
}

// Fetch households for filter dropdown
try {
    $stmt = $pdo->query("SELECT household_id, name AS household_name FROM households ORDER BY name ASC");
    $all_households = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $households = array_column($all_households, 'household_name');
    $households = array_filter($households);
    sort($households);
} catch (PDOException $e) {
    echo "Error fetching households: " . $e->getMessage();
    exit;
}

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Management - Ekklessia Church Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/Ekklessia-church-management/Public/css/style.css" rel="stylesheet">
    <style>
        .card {
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }
        .profile-photo {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #007bff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .profile-photo.clickable:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }
        .profile-photo-large {
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }
        .btn-gradient-blue {
            background: linear-gradient(45deg, #007bff, #00d4ff);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: transform 0.3s;
            min-width: 150px;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .btn-gradient-blue.clickable:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }
        .btn-gradient-blue:hover {
            transform: scale(1.05);
        }
        .assembly-badge {
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 12px;
            display: inline-block;
            min-width: 150px;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .btn-group .dropdown-toggle {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .btn-group .dropdown-menu {
            min-width: 10rem;
            z-index: 1050; /* Ensure dropdown appears above other elements */
        }
        .dropdown-menu {
            position: absolute;
            z-index: 1050;
        }
        .dropdown-menu.show {
            display: block !important; /* Force visibility */
        }
        .footer {
            border-top: 1px solid #e9ecef;
        }
        
        /* Modal specific styles */
        .modal-content {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .modal-dialog {
            transition: none !important; /* Prevent unwanted transitions */
        }

        .btn-close {
            transition: opacity 0.2s ease;
        }

        .btn-close:hover {
            opacity: 0.75;
        }

        /* Fix for delete button hover */
        .delete-member-btn, .confirm-delete-btn {
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .delete-member-btn:hover, .confirm-delete-btn:hover {
            transform: none; /* Remove transform on hover */
        }

        /* Remove any transform effects from modal buttons */
        .modal .btn {
            transform: none !important;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .assignment-step {
            transition: opacity 0.3s ease-in-out;
        }
        .assignment-step.d-none {
            display: none !important;
        }
        #householdMembers {
            max-height: 200px;
            overflow-y: auto;
        }
        #householdMembers .list-group-item {
            border-left: none;
            border-right: none;
            border-radius: 0;
            padding: 0.75rem 1rem;
        }
        #householdMembers .list-group-item:first-child {
            border-top: none;
        }
        #householdMembers .list-group-item:last-child {
            border-bottom: none;
        }
        .household-preview {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
        }
        .role-selection .form-check {
            padding-left: 2rem;
        }
        .role-selection .form-check-input {
            margin-left: -2rem;
        }

        /* Pagination styles */
        .pagination-modern {
            border-radius: 8px !important;
            margin: 0 4px;
            background: linear-gradient(90deg, #007bff 0%, #00d4ff 100%);
            color: #fff !important;
            border: none !important;
            box-shadow: 0 2px 8px rgba(0,123,255,0.10);
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            min-width: 38px;
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            font-size: 1rem;
        }
        .pagination-modern:hover {
            background: linear-gradient(90deg, #00d4ff 0%, #007bff 100%);
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(0,123,255,0.18);
        }
        .page-item.active .pagination-modern {
            background: linear-gradient(90deg, #28a745 0%, #6fcf97 100%) !important;
            color: #fff !important;
            font-weight: bold;
            box-shadow: 0 4px 16px rgba(40,167,69,0.18);
            border: 2px solid #28a745 !important;
        }
        .page-item.disabled .pagination-modern {
            background: #e9ecef !important;
            color: #adb5bd !important;
            cursor: not-allowed;
            border: none !important;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<main class="container flex-grow-1 py-2">
    <!-- Success/Error Alerts -->
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

    <!-- Mini Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #007bff, #00d4ff); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-2"></i>
                    <h6 class="card-title text-white">Total Members</h6>
                    <h3 class="card-text" id="totalMembersCount"><?= count($members) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #28a745, #6fcf97); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-building fs-2"></i>
                    <h6 class="card-title text-white">Total Assemblies</h6>
                    <h3 class="card-text"><?= $total_assemblies ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #17a2b8, #4fc3f7); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-person-check fs-2"></i>
                    <h6 class="card-title text-white">Active Members</h6>
                    <h3 class="card-text" id="activeMembersCount"><?= count(array_filter($members, fn($m) => $m['status'] === 'Active saint')) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #dc3545, #ff6b6b); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-clock fs-2"></i>
                    <h6 class="card-title text-white">New Members (Last 30 Days)</h6>
                    <h3 class="card-text" id="newMembersCount">
                        <?php
                        $newMembers = array_filter($members, function($m) {
                            $joinedDate = new DateTime($m['joined_date']);
                            $now = new DateTime();
                            $interval = $now->diff($joinedDate);
                            return $interval->days <= 30;
                        });
                        echo count($newMembers);
                        ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Members Management</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                        <i class="bi bi-upload"></i> Bulk Upload
                    </button>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                        <i class="bi bi-plus-circle"></i> Add New Member
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <!-- Filter Container -->
            <div class="filter-container">
                <!-- First Row - Main Filters -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label for="filterName" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="filterName" placeholder="Search by name">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="filterAssembly" class="form-label">Assembly</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white"><i class="bi bi-building"></i></span>
                            <select class="form-select" id="filterAssembly">
                                <option value="">All Assemblies</option>
                                <?php foreach ($assemblies as $assembly): ?>
                                    <option value="<?= htmlspecialchars($assembly) ?>"><?= htmlspecialchars($assembly) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="filterHousehold" class="form-label">Household</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white"><i class="bi bi-house"></i></span>
                            <select class="form-select" id="filterHousehold">
                                <option value="">All Households</option>
                                <?php foreach ($all_households as $household): ?>
                                    <option value="<?= htmlspecialchars($household['household_id']) ?>"><?= htmlspecialchars($household['household_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="filterShepherd" class="form-label">Shepherd</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white"><i class="bi bi-person-check"></i></span>
                            <select class="form-select" id="filterShepherd">
                                <option value="">All Shepherds</option>
                                <?php
                                $stmt = $pdo->query("
                                    SELECT DISTINCT m.member_id, m.first_name, m.last_name 
                                    FROM members m 
                                    WHERE m.local_function_id = 11
                                    ORDER BY m.first_name, m.last_name
                                ");
                                $shepherds = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($shepherds as $shepherd): ?>
                                    <option value="<?= htmlspecialchars($shepherd['member_id']) ?>">
                                        <?= htmlspecialchars($shepherd['first_name'] . ' ' . $shepherd['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Second Row - Additional Filters -->
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white"><i class="bi bi-person-check"></i></span>
                            <select class="form-select" id="filterStatus">
                                <option value="">All Statuses</option>
                                <option value="Committed saint">Committed Saint</option>
                                <option value="Active saint">Active Saint</option>
                                <option value="Worker">Worker</option>
                                <option value="New saint">New Saint</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="filterLocalRole" class="form-label">Local Role</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white"><i class="bi bi-person-badge"></i></span>
                            <select class="form-select" id="filterLocalRole">
                                <option value="">All Roles</option>
                                <?php
                                $stmt = $pdo->query("
                                    SELECT DISTINCT function_id, function_name 
                                    FROM church_functions 
                                    WHERE function_type = 'local'
                                    ORDER BY function_name
                                ");
                                $local_roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($local_roles as $role): ?>
                                    <option value="<?= htmlspecialchars($role['function_id']) ?>">
                                        <?= htmlspecialchars($role['function_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="filterJoinedStart" class="form-label">Joined From</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white"><i class="bi bi-calendar-date"></i></span>
                            <input type="date" class="form-control" id="filterJoinedStart">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="filterJoinedEnd" class="form-label">Joined To</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white"><i class="bi bi-calendar-date"></i></span>
                            <input type="date" class="form-control" id="filterJoinedEnd">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="table-responsive mt-4">
                <table class="table table-striped table-hover align-middle" id="membersTable">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Full Name</th>
                            <th>Contact</th>
                            <th>Assembly</th>
                            <th>Household</th>
                            <th>Shepherd</th>
                            <th>Local Role</th>
                            <th>Status</th>
                            <th>Admitted Date</th>
                            <th>Referral</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="membersTableBody">
                        <!-- Body will be populated by JavaScript -->
                    </tbody>
                </table>

                <!-- Pagination Placeholder -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="paginationControls"></ul>
                </nav>
            </div>
        </div>
    </div>    <!-- Assign Household Modal -->
    <div class="modal fade" id="assignHouseholdModal" tabindex="-1" aria-labelledby="assignHouseholdModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="assignHouseholdModalLabel">
                        <span id="singleAssignmentTitle">Assign Household (<span id="selectedMemberName"></span>)</span>
                        <span id="bulkAssignmentTitle" class="d-none">Bulk Assign Household (<span id="selectedMembersCount">0</span> members)</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <!-- Left Panel - Member Details -->
                        <div class="col-md-4 border-end">
                            <div class="p-4">
                                <div id="memberDetailsSection">
                                    <div class="text-center mb-4">
                                        <div class="avatar-circle mx-auto mb-3">
                                            <i class="bi bi-person-circle fs-1"></i>
                                        </div>
                                        <h5 class="member-name mb-2"></h5>
                                        <div class="member-assembly badge bg-primary mb-2"></div>
                                        <div class="member-status badge bg-success"></div>
                                    </div>
                                    <div class="member-details mt-4">
                                        <div class="detail-item mb-2">
                                            <i class="bi bi-telephone me-2"></i>
                                            <span class="member-phone"></span>
                                        </div>
                                        <div class="detail-item mb-2">
                                            <i class="bi bi-envelope me-2"></i>
                                            <span class="member-email"></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="bi bi-geo-alt me-2"></i>
                                            <span class="member-address"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Right Panel - Assignment Area -->
                        <div class="col-md-8">
                            <div class="p-4">
                                <!-- Step 1: Household Selection -->
                                <div id="step1">
                                    <form id="assignHouseholdForm">
                                        <input type="hidden" id="assignMemberId" name="member_id">
                                        <input type="hidden" id="assignAssembliesId" name="assemblies_id">
                                        <div class="mb-4">
                                            <label for="assemblySelect" class="form-label">Assembly</label>
                                            <input type="text" class="form-control bg-light" id="assemblySelect" readonly>
                                        </div>
                                        <div class="mb-4">
                                            <label for="householdSelect" class="form-label">Select Household <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select class="form-select" id="householdSelect" name="household_id" required>
                                                    <option value="">-- Select Household --</option>
                                                </select>
                                                <button type="button" class="btn btn-outline-primary" id="filterHouseholds">
                                                    <i class="bi bi-funnel"></i>
                                                </button>
                                            </div>
                                            <div id="searchHouseholdBox" class="mt-2 d-none">
                                                <input type="text" class="form-control" placeholder="Search households...">
                                            </div>
                                        </div>
                                        <!-- Member Role Selection -->
                                        <div class="mb-4">
                                            <label class="form-label d-block">Member Role</label>
                                            <div class="btn-group" role="group">
                                                <input type="radio" class="btn-check" name="memberRole" id="roleRegular" value="regular" checked>
                                                <label class="btn btn-outline-secondary" for="roleRegular">Regular Member</label>

                                                <input type="radio" class="btn-check" name="memberRole" id="roleShepherd" value="shepherd">
                                                <label class="btn btn-outline-secondary" for="roleShepherd">Shepherd</label>

                                                <input type="radio" class="btn-check" name="memberRole" id="roleAssistant" value="assistant">
                                                <label class="btn btn-outline-secondary" for="roleAssistant">Assistant</label>
                                            </div>
                                            <div id="roleWarning" class="alert alert-warning mt-2 d-none">
                                                This household already has a shepherd assigned. Adding another shepherd will replace the current one.
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-info" id="nextToPreview" disabled>Next <i class="bi bi-chevron-right"></i></button>
                                        </div>
                                    </form>
                                </div>
                                <!-- Step 2: Preview and Confirmation -->
                                <div id="step2" class="d-none">
                                    <div class="household-preview mb-4">
                                        <h5 class="border-bottom pb-2">Household Preview</h5>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p class="mb-2"><strong>Name:</strong> <span id="previewHouseholdName"></span></p>
                                                <p class="mb-2"><strong>Assembly:</strong> <span id="previewAssemblyName"></span></p>
                                                <p class="mb-2"><strong>Address:</strong> <span id="previewAddress"></span></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="mb-2"><strong>Members:</strong> <span id="previewMemberCount"></span></p>
                                                <p class="mb-2"><strong>Shepherd:</strong> <span id="previewShepherd"></span></p>
                                                <p class="mb-2"><strong>Assistants:</strong> <span id="previewAssistants"></span></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="current-members mb-4">
                                        <h5 class="border-bottom pb-2">Current Members</h5>
                                        <div class="list-group" id="householdMembers">
                                            <!-- Members will be populated dynamically -->
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-secondary" id
                                        <button type="submit" form="assignHouseholdForm" class="btn btn-primary">Confirm Assignment</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "bulk_upload_modal.php"; ?>
    <?php include "add_member.php"; ?>
    <?php include "edit_members.php"; ?>
    <?php include "view_member.php"; ?>

    <!-- Centralized Delete Confirmation Modal -->
    <div class="modal fade" id="deleteMemberModal" tabindex="-1" aria-labelledby="deleteMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteMemberModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete the record for <strong id="deleteMemberName"></strong>?</p>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include "../../../includes/footer.php"; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Global variables and functions
let currentPage = 1;
const recordsPerPage = 10;
let lastDeletedRow = null;
let currentMemberId = null;
let currentMemberName = null;

const deleteModal = document.getElementById('deleteMemberModal');

// Define renderTable at global scope
function renderTable(members) {
    const tbody = document.getElementById('membersTableBody');
    tbody.innerHTML = '';
    members.forEach((member, index) => {
        const row = document.createElement('tr');
        row.setAttribute('data-id', member.member_id);
        row.setAttribute('data-first-name', member.first_name);
        row.setAttribute('data-last-name', member.last_name);
        row.setAttribute('data-dob', member.date_of_birth);
        row.setAttribute('data-gender', member.gender);
        row.setAttribute('data-marital-status', member.marital_status);
        row.setAttribute('data-contact', member.contact);
        row.setAttribute('data-email', member.email || 'N/A');
        row.setAttribute('data-address', member.address || 'N/A');
        row.setAttribute('data-digital-address', member.digital_address || 'N/A');
        row.setAttribute('data-occupation', member.occupation || 'N/A');
        row.setAttribute('data-employer', member.employer || 'N/A');
        row.setAttribute('data-work-phone', member.work_phone || 'N/A');
        row.setAttribute('data-highest-education-level', member.highest_education_level || 'N/A');
        row.setAttribute('data-institution', member.institution || 'N/A');
        row.setAttribute('data-year-graduated', member.year_graduated || 'N/A');
        row.setAttribute('data-assembly', member.assembly_name || 'N/A');
        row.setAttribute('data-household', member.household_name || 'Not Assigned');
        row.setAttribute('data-status', member.status);
        row.setAttribute('data-joined-date', member.joined_date);
        row.setAttribute('data-username', member.username);
        row.setAttribute('data-password', member.password);
        row.setAttribute('data-created-at', member.created_at);
        row.setAttribute('data-updated-at', member.updated_at || 'N/A');
        row.setAttribute('data-created-by', member.created_by);
        row.setAttribute('data-updated-by', member.updated_by || 'N/A');
        row.setAttribute('data-assemblies-id', member.assemblies_id);
        row.setAttribute('data-referral-id', member.referral_id || 'N/A');
        row.setAttribute('data-referral-name', (member.referral_first_name || '') + ' ' + (member.referral_last_name || ''));
        row.setAttribute('data-group-name', member.group_name || 'N/A');
        row.setAttribute('data-shepherd-name', member.shepherd_name || 'Not Assigned');
        row.setAttribute('data-household-id', member.household_id && member.household_id != 0 ? member.household_id : '');
        row.setAttribute('data-shepherd-id', member.shepherd_id && member.shepherd_id != 0 ? member.shepherd_id : '');

        row.innerHTML = `
            <td><input type="checkbox" class="row-checkbox" data-id="${member.member_id}"></td>
            <td>${((currentPage - 1) * recordsPerPage) + index + 1}</td>
            <td>
                ${member.profile_photo ? 
                    `<img src="/Ekklessia-church-management/app/resources/assets/images/${member.profile_photo}" alt="Profile Photo" class="profile-photo clickable" data-member-id="${member.member_id}">` : 
                    `<img src="/Ekklessia-church-management/app/resources/assets/images/default.jpg" alt="Default Photo" class="profile-photo clickable" data-member-id="${member.member_id}">`}
            </td>
            <td>
                <button class="btn btn-gradient-blue text-nowrap clickable" style="min-width: 150px;" data-member-id="${member.member_id}">
                    ${member.first_name} ${member.last_name}
                </button>
            </td>
            <td>${member.contact}</td>
            <td>
                <span class="badge assembly-badge" 
                      data-assemblies-id="${member.assemblies_id || 'N/A'}" 
                      data-assembly-name="${member.assembly_name || 'N/A'}">
                    ${member.assembly_name || 'N/A'}
                </span>
            </td>
            <td>${member.household_name || 'Not Assigned'}</td>
            <td>${member.shepherd_name || 'Not Assigned'}</td>
            <td>
                <span class="badge" style="background: linear-gradient(45deg, #1a237e, #3949ab); color: white;">
                    ${member.function_name || 'N/A'}
                </span>
            </td>
            <td>${member.status}</td>
            <td>${member.joined_date}</td>
            <td>${member.referral_id ? member.referral_first_name + ' ' + member.referral_last_name : 'N/A'}</td>
            <td>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary load-edit-modal" data-member-id="${member.member_id}" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-member-btn" data-member-id="${member.member_id}" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="assignDropdown-${member.member_id}">
                            <i class="bi bi-person-plus"></i> Assign
                        </button>
                        <ul class="dropdown-menu" id="assignDropdownMenu-${member.member_id}">
                            <li><a class="dropdown-item assign-household-item" href="#" data-action="assign-household" data-member-id="${member.member_id}">Assign Household</a></li>
                            <li><a class="dropdown-item edit-household-item" href="#" data-action="edit-household" data-member-id="${member.member_id}">Edit Household</a></li>
                        </ul>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });

    applyAssemblyBadges();
    attachEventListeners();
}

// Define showAlert at global scope
function showAlert(type, message) {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertContainer.style.zIndex = '1050';
    alertContainer.role = 'alert';
    alertContainer.innerHTML = `
        <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alertContainer);
    setTimeout(() => {
        alertContainer.classList.add('fade');
        setTimeout(() => alertContainer.remove(), 150);
    }, 5000);
}

// Define updateDashboardCounts at global scope
function updateDashboardCounts(totalMembers, members) {
    const activeMembers = members.filter(m => m.status === 'Active saint').length;
    const newMembers = members.filter(m => {
        const joinedDate = new Date(m.joined_date);
        const now = new Date();
        return (now - joinedDate) / (1000 * 60 * 60 * 24) <= 30;
    }).length;

    document.getElementById('totalMembersCount').textContent = totalMembers;
    document.getElementById('activeMembersCount').textContent = activeMembers;
    document.getElementById('newMembersCount').textContent = newMembers;
}

// Define fetchPaginatedMembers at global scope
function fetchPaginatedMembers(page) {
    const filterName = document.getElementById('filterName')?.value.toLowerCase() || '';
    const filterAssembly = document.getElementById('filterAssembly')?.value || '';
    const filterHousehold = document.getElementById('filterHousehold')?.value || '';
    const filterShepherd = document.getElementById('filterShepherd')?.value || '';
    const filterStatus = document.getElementById('filterStatus')?.value || '';
    const filterLocalRole = document.getElementById('filterLocalRole')?.value || '';
    const filterJoinedStart = document.getElementById('filterJoinedStart')?.value || '';
    const filterJoinedEnd = document.getElementById('filterJoinedEnd')?.value || '';

    const params = new URLSearchParams({
        page: page,
        name: filterName,
        assembly: filterAssembly,
        household: filterHousehold,
        shepherd: filterShepherd,
        status: filterStatus,
        local_role: filterLocalRole,
        joined_start: filterJoinedStart,
        joined_end: filterJoinedEnd
    });

    return fetch(`fetch_paginated_members.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.members);
                generatePaginationControls(page, data.pagination.total_pages);
                updateDashboardCounts(data.pagination.total_members, data.members);
                return data; // Return data for chaining
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching members:', error);
            showAlert('danger', 'Failed to load members: ' + error.message);
            throw error; // Re-throw for error handling
        });
}

// Fix: Add a global loadEditModal function for dynamic modal loading
function loadEditModal(memberId) {
    const modal = document.getElementById(`editMemberModal-${memberId}`);
    if (!modal) {
        fetch(`edit_members.php?member_id=${memberId}`)
            .then(response => response.text())
            .then(html => {
                document.body.insertAdjacentHTML('beforeend', html);
                const newModal = new bootstrap.Modal(document.getElementById(`editMemberModal-${memberId}`));
                newModal.show();
            })
            .catch(error => console.error('Error loading modal:', error));
    } else {
        new bootstrap.Modal(modal).show();
    }
}

// Assign unique gradient colors to assembly badges
const gradientColors = [
    'linear-gradient(45deg, #007bff, #00d4ff)', // Blue
    'linear-gradient(45deg, #28a745, #6fcf97)', // Green
    'linear-gradient(45deg, #ffc107, #ffca28)', // Yellow
    'linear-gradient(45deg, #17a2b8, #4fc3f7)', // Cyan
    'linear-gradient(45deg, #dc3545, #ff6b6b)', // Red
    'linear-gradient(45deg, #6c757d, #b0b5b9)', // Gray
    'linear-gradient(45deg, #ff6f61, #ff9f84)', // Coral
    'linear-gradient(45deg, #9c27b0, #ce93d8)', // Purple
    'linear-gradient(45deg, #ff9800, #ffb74d)', // Orange
    'linear-gradient(45deg, #e91e63, #f06292)', // Pink
    'linear-gradient(45deg, #4caf50, #81c784)', // Green 2
    'linear-gradient(45deg, #3f51b5, #7986cb)' // Indigo
];

// Build a map from assembly id to color
const assemblyMap = typeof window.assemblyMap !== 'undefined' ? window.assemblyMap : <?php echo $assembly_map_json; ?>;
const assemblyColorMap = {};
let colorIndex = 0;
for (const assemblyId in assemblyMap) {
    if (assemblyId === 'N/A' || assemblyMap[assemblyId] === 'N/A') {
        assemblyColorMap[assemblyId] = 'linear-gradient(45deg, #6c757d, #b0b5b9)';
    } else {
        assemblyColorMap[assemblyId] = gradientColors[colorIndex % gradientColors.length];
        colorIndex++;
    }
}

function applyAssemblyBadges() {
    document.querySelectorAll('.assembly-badge').forEach(badge => {
        const assemblyId = badge.getAttribute('data-assemblies-id');
        badge.style.background = assemblyColorMap[assemblyId] || 'linear-gradient(45deg, #6c757d, #b0b5b9)';
        badge.style.color = (assemblyId === 'N/A' || !assemblyId) ? 'black' : 'white';
    });
}

function attachEventListeners() {
    // Handle clickable elements
    document.querySelectorAll('.clickable').forEach(element => {
        element.addEventListener('click', function() {
            const memberId = this.getAttribute('data-member-id');
            if (memberId) {
                const row = document.querySelector(`tr[data-id="${memberId}"]`);
                if (row) {
                    // Handle view member details
                    const viewModal = new bootstrap.Modal(document.getElementById('viewMemberModal'));
                    // Update modal content with row data
                    populateViewModal(row);
                    viewModal.show();
                }
            }
        });
    });

    // Re-initialize dropdowns
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(dropdownToggle => {
        new bootstrap.Dropdown(dropdownToggle);
    });
}

function populateViewModal(row) {
    const attrs = [
        'first-name', 'last-name', 'dob', 'gender', 'marital-status', 
        'contact', 'email', 'address', 'digital-address', 'occupation',
        'employer', 'work-phone', 'highest-education-level', 'institution', // Changed 'education' to 'highest-education-level'
        'year-graduated', 'assembly', 'household', 'status', 
        'joined-date', 'referral-name', 'group-name', 'shepherd-name',
        'username', 'password', 'created-at', 'updated-at', 
        'created-by', 'updated-by'
    ];
    
    attrs.forEach(attr => {
        const element = document.getElementById(`viewMember${attr.split('-').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)).join('')}`);
        if (element) {
            element.textContent = row.getAttribute(`data-${attr}`) || 'N/A';
        }
    });
    
    // Handle profile photo
    const photoElement = document.getElementById('viewMemberPhoto');
    if (photoElement) {
        photoElement.src = row.querySelector('.profile-photo').src;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Global modal cleanup function
    function cleanupModals() {
        const existingBackdrops = document.querySelectorAll('.modal-backdrop');
        existingBackdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }

    // Clean up on page load
    cleanupModals();

    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JavaScript is not loaded.');
        return;
    }

    // Initialize dropdowns properly
    function initializeDropdowns() {
        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(dropdownToggle => {
            new bootstrap.Dropdown(dropdownToggle);
        });
    }    // Call after loading members and after any dynamic updates
    initializeDropdowns();
    // Ensure assembly badges are styled on initial page load
    applyAssemblyBadges();

    // Ensure assembly badges are styled on initial page load
    applyAssemblyBadges();

    // Properly handle dropdown clicks
    document.body.addEventListener('click', function(e) {
        const dropdownToggle = e.target.closest('[data-bs-toggle="dropdown"]');
        if (dropdownToggle) {
            e.preventDefault();
            e.stopPropagation();
            const dropdown = bootstrap.Dropdown.getOrCreateInstance(dropdownToggle);
            dropdown.toggle();
        }
    });    // Handle assign/edit actions
    document.body.addEventListener('click', function(e) {
        const action = e.target.closest('.dropdown-item');
        if (!action) return;

        e.preventDefault();
        const memberId = action.dataset.memberId;
        const actionType = action.dataset.action;
        
        if (actionType === 'assign-household' || actionType === 'edit-household') {
            const memberRow = document.querySelector(`tr[data-id="${memberId}"]`);
            if (!memberRow) {
                showAlert('danger', 'Member information not found');
                return;
            }

            // Get member data from row attributes
            const memberName = `${memberRow.getAttribute('data-first-name')} ${memberRow.getAttribute('data-last-name')}`;
            const assemblyId = memberRow.getAttribute('data-assemblies-id');
            const householdId = memberRow.getAttribute('data-household-id');
            const shepherdId = memberRow.getAttribute('data-shepherd-id');

            if (actionType === 'assign-household') {
                // Set member info for assign modal
                document.getElementById('selectedMemberName').textContent = memberName;
                document.getElementById('assignMemberId').value = memberId;
                document.getElementById('assignAssembliesId').value = assemblyId;
                document.getElementById('assemblySelect').value = memberRow.getAttribute('data-assembly');

                // Show the assign modal
                const modal = new bootstrap.Modal(document.getElementById('assignHouseholdModal'));
                modal.show();

                // After modal is shown, fetch the data
                fetch(`fetch_households_by_assembly.php?assembly_id=${encodeURIComponent(assemblyId)}`)
                    .then(response => response.json())
                    .then(households => {
                        const householdSelect = document.getElementById('householdSelect');
                        householdSelect.innerHTML = '<option value="">-- Select Household --</option>';
                        households.forEach(household => {
                            const selected = household.household_id == householdId ? ' selected' : '';
                            householdSelect.innerHTML += `<option value="${household.household_id}"${selected}>${household.name}</option>`;
                        });
                    })
                    .catch(error => showAlert('danger', 'Failed to load households'));

                fetch(`fetch_shepherds_by_assembly.php?assembly_id=${encodeURIComponent(assemblyId)}`)
                    .then(response => response.json())
                    .then(members => {
                        const shepherdSelect = document.getElementById('shepherdSelect');
                        shepherdSelect.innerHTML = '<option value="">-- Select Shepherd --</option>';
                        members.forEach(member => {
                            const selected = member.member_id == shepherdId ? ' selected' : '';
                            shepherdSelect.innerHTML += `<option value="${member.member_id}"${selected}>${member.first_name} ${member.last_name}</option>`;
                        });
                    })
                    .catch(error => showAlert('danger', 'Failed to load shepherds'));
            } else if (actionType === 'edit-household') {
                document.getElementById('editSelectedMemberName').textContent = memberName;
                document.getElementById('editMemberId').value = memberId;
                document.getElementById('editAssembliesId').value = assemblyId;
                document.getElementById('editAssemblySelect').value = memberRow.getAttribute('data-assembly');

                // Show the edit modal
                const modal = new bootstrap.Modal(document.getElementById('editHouseholdModal'));
                modal.show();

                // Fetch households and shepherds for edit
                fetchHouseholdsAndShepherdsForEdit(assemblyId, householdId, shepherdId, memberId);
            }
        }
    });

    // Select-all checkbox functionality
    document.getElementById('select-all').addEventListener('change', function () {
        document.querySelectorAll('.row-checkbox').forEach(checkbox => checkbox.checked = this.checked);
    });

    // Load edit modal dynamically
    document.querySelectorAll('.load-edit-modal').forEach(button => {
        button.addEventListener('click', function () {
            const memberId = this.getAttribute('data-member-id');
            loadEditModal(memberId);
        });
    });

    // Pagination and filtering setup

    function fetchPaginatedMembers(page) {
        const filterName = document.getElementById('filterName').value.toLowerCase();
        const filterAssembly = document.getElementById('filterAssembly').value;
        const filterHousehold = document.getElementById('filterHousehold').value;
        const filterShepherd = document.getElementById('filterShepherd').value;
        const filterStatus = document.getElementById('filterStatus').value;
        const filterLocalRole = document.getElementById('filterLocalRole').value;
        const filterJoinedStart = document.getElementById('filterJoinedStart').value;
        const filterJoinedEnd = document.getElementById('filterJoinedEnd').value;

        const params = new URLSearchParams({
            page: page,
            name: filterName,
            assembly: filterAssembly,
            household: filterHousehold,
            shepherd: filterShepherd,
            status: filterStatus,
            local_role: filterLocalRole,
            joined_start: filterJoinedStart,
            joined_end: filterJoinedEnd
        })
        console.log(`Fetching members with params: ${params.toString()}`);
        return fetch(`fetch_paginated_members.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderTable(data.members);
                    generatePaginationControls(page, data.pagination.total_pages);
                    updateDashboardCounts(data.pagination.total_members, data.members);
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching members:', error);
                showAlert('danger', 'Failed to load members: ' + error.message);
            });
    }

    function updateDashboardCounts(totalMembers, members) {
        const activeMembers = members.filter(m => m.status === 'Active saint').length;
        const newMembers = members.filter(m => {
            const joinedDate = new Date(m.joined_date);
            const now = new Date();
            return (now - joinedDate) / (1000 * 60 * 60 * 24) <= 30;
        }).length;

        document.getElementById('totalMembersCount').textContent = totalMembers;
        document.getElementById('activeMembersCount').textContent = activeMembers;
        document.getElementById('newMembersCount').textContent = newMembers;
    }

    function renderTable(members) {
        const tbody = document.getElementById('membersTableBody');
        tbody.innerHTML = '';
        members.forEach((member, index) => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', member.member_id);
            row.setAttribute('data-first-name', member.first_name);
            row.setAttribute('data-last-name', member.last_name);
            row.setAttribute('data-dob', member.date_of_birth);
            row.setAttribute('data-gender', member.gender);
            row.setAttribute('data-marital-status', member.marital_status);
            row.setAttribute('data-contact', member.contact);
            row.setAttribute('data-email', member.email || 'N/A');
            row.setAttribute('data-address', member.address || 'N/A');
            row.setAttribute('data-digital-address', member.digital_address || 'N/A');
            row.setAttribute('data-occupation', member.occupation || 'N/A');
            row.setAttribute('data-employer', member.employer || 'N/A');
            row.setAttribute('data-work-phone', member.work_phone || 'N/A');
            row.setAttribute('data-highest-education-level', member.highest_education_level || 'N/A');
            row.setAttribute('data-institution', member.institution || 'N/A');
            row.setAttribute('data-year-graduated', member.year_graduated || 'N/A');
            row.setAttribute('data-assembly', member.assembly_name || 'N/A');
            row.setAttribute('data-household', member.household_name || 'Not Assigned');
            row.setAttribute('data-status', member.status);
            row.setAttribute('data-joined-date', member.joined_date);
            row.setAttribute('data-username', member.username);
            row.setAttribute('data-password', member.password);
            row.setAttribute('data-created-at', member.created_at);
            row.setAttribute('data-updated-at', member.updated_at || 'N/A');
            row.setAttribute('data-created-by', member.created_by);
            row.setAttribute('data-updated-by', member.updated_by || 'N/A');
            row.setAttribute('data-assemblies-id', member.assemblies_id);
            row.setAttribute('data-referral-id', member.referral_id || 'N/A');
            row.setAttribute('data-referral-name', (member.referral_first_name || '') + ' ' + (member.referral_last_name || ''));
            row.setAttribute('data-group-name', member.group_name || 'N/A');
            row.setAttribute('data-shepherd-name', member.shepherd_name || 'Not Assigned');
            row.setAttribute('data-household-id', member.household_id && member.household_id != 0 ? member.household_id : '');
            row.setAttribute('data-shepherd-id', member.shepherd_id && member.shepherd_id != 0 ? member.shepherd_id : '');

            row.innerHTML = `
                <td><input type="checkbox" class="row-checkbox" data-id="${member.member_id}"></td>
                <td>${((currentPage - 1) * recordsPerPage) + index + 1}</td>
                <td>
                    ${member.profile_photo ? 
                        `<img src="/Ekklessia-church-management/app/resources/assets/images/${member.profile_photo}" alt="Profile Photo" class="profile-photo clickable" data-member-id="${member.member_id}">` : 
                        `<img src="/Ekklessia-church-management/app/resources/assets/images/default.jpg" alt="Default Photo" class="profile-photo clickable" data-member-id="${member.member_id}">`}
                </td>
                <td>
                    <button class="btn btn-gradient-blue text-nowrap clickable" style="min-width: 150px;" data-member-id="${member.member_id}">
                        ${member.first_name} ${member.last_name}
                    </button>
                </td>
                <td>${member.contact}</td>
                <td>
                    <span class="badge assembly-badge" 
                          data-assemblies-id="${member.assemblies_id || 'N/A'}" 
                          data-assembly-name="${member.assembly_name || 'N/A'}">
                    ${member.assembly_name || 'N/A'}
                    </span>
                </td>
                <td>${member.household_name || 'Not Assigned'}</td>
                <td>${member.shepherd_name || 'Not Assigned'}</td>
                <td>
                    <span class="badge" style="background: linear-gradient(45deg, #1a237e, #3949ab); color: white;">
                        ${member.function_name || 'N/A'}
                    </span>
                </td>
                <td>${member.status}</td>
                <td>${member.joined_date}</td>
                <td>${member.referral_id ? member.referral_first_name + ' ' + member.referral_last_name : 'N/A'}</td>
                <td>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary load-edit-modal" data-member-id="${member.member_id}" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-member-btn" data-member-id="${member.member_id}" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="assignDropdown-${member.member_id}">
                                <i class="bi bi-person-plus"></i> Assign
                            </button>
                            <ul class="dropdown-menu" id="assignDropdownMenu-${member.member_id}">
                                <li><a class="dropdown-item assign-household-item" href="#" data-action="assign-household" data-member-id="${member.member_id}">Assign Household</a></li>
                                <li><a class="dropdown-item edit-household-item" href="#" data-action="edit-household" data-member-id="${member.member_id}">Edit Household</a></li>
                            </ul>
                        </div>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });

        applyAssemblyBadges();
        attachEventListeners();
    }

    function attachViewMemberListeners() {
        document.querySelectorAll('.clickable').forEach(element => {
            element.addEventListener('click', function() {
                const memberId = this.getAttribute('data-member-id');
                const row = document.querySelector(`#membersTable tr[data-id=\"${memberId}\"]`);
                if (row) {
                    // Log member data
                    const memberData = {};
                    for (const attr of row.attributes) {
                        if (attr.name.startsWith('data-')) {
                            memberData[attr.name.substring(5)] = attr.value;
                        }
                    }
                    memberData['member_id_clicked'] = memberId; // Add the clicked memberId for clarity

                    fetch('log_to_file.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            level: 'INFO',
                            message: 'View Card Clicked: Member Data',
                            context: memberData
                        })
                    }).catch(error => console.error('Error logging to file:', error));

                    document.getElementById('viewMemberName').textContent = `${row.getAttribute('data-first-name')} ${row.getAttribute('data-last-name')}`;
                    document.getElementById('viewMemberFirstName').textContent = row.getAttribute('data-first-name');
                    document.getElementById('viewMemberLastName').textContent = row.getAttribute('data-last-name');
                    document.getElementById('viewMemberDOB').textContent = row.getAttribute('data-dob');
                    document.getElementById('viewMemberGender').textContent = row.getAttribute('data-gender');
                    document.getElementById('viewMemberMaritalStatus').textContent = row.getAttribute('data-marital-status');
                    document.getElementById('viewMemberContact').textContent = row.getAttribute('data-contact');
                    document.getElementById('viewMemberEmail').textContent = row.getAttribute('data-email');
                    document.getElementById('viewMemberAddress').textContent = row.getAttribute('data-address');
                    document.getElementById('viewMemberDigitalAddress').textContent = row.getAttribute('data-digital-address');
                    document.getElementById('viewMemberOccupation').textContent = row.getAttribute('data-occupation');
                    document.getElementById('viewMemberEmployer').textContent = row.getAttribute('data-employer');
                    document.getElementById('viewMemberWorkPhone').textContent = row.getAttribute('data-work-phone');
                    document.getElementById('viewMemberEducation').textContent = row.getAttribute('data-highest-education-level');
                    document.getElementById('viewMemberInstitution').textContent = row.getAttribute('data-institution');
                    document.getElementById('viewMemberYearGraduated').textContent = row.getAttribute('data-year-graduated');
                    document.getElementById('viewMemberAssembly').textContent = row.getAttribute('data-assembly');
                    document.getElementById('viewMemberHousehold').textContent = row.getAttribute('data-household');
                    document.getElementById('viewMemberStatus').textContent = row.getAttribute('data-status');
                    document.getElementById('viewMemberJoinedDate').textContent = row.getAttribute('data-joined-date');
                    document.getElementById('viewMemberUsername').textContent = row.getAttribute('data-username');
                    document.getElementById('viewMemberPassword').textContent = row.getAttribute('data-password');
                    document.getElementById('viewMemberCreatedAt').textContent = row.getAttribute('data-created-at');
                    document.getElementById('viewMemberUpdatedAt').textContent = row.getAttribute('data-updated-at');
                    document.getElementById('viewMemberCreatedBy').textContent = row.getAttribute('data-created-by');
                    document.getElementById('viewMemberUpdatedBy').textContent = row.getAttribute('data-updated-by');
                    document.getElementById('viewMemberPhoto').src = row.querySelector('.profile-photo').src;
                    document.getElementById('viewMemberReferral').textContent = row.getAttribute('data-referral-name');
                    document.getElementById('viewMemberGroupName').textContent = row.getAttribute('data-group-name'); // Added for group name
                    document.getElementById('viewMemberShepherd').textContent = row.getAttribute('data-shepherd-name');

                    viewMemberModal.show();
                }
            });
        });
    }

    function generatePaginationControls(currentPage, totalPages) {
        const paginationContainer = document.getElementById('paginationControls');
        paginationContainer.innerHTML = '';
        const maxPagesToShow = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item${currentPage === 1 ? ' disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link pagination-modern" href="#" data-page="${currentPage - 1}" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
        paginationContainer.appendChild(prevLi);

        for (let i = startPage; i <= endPage; i++) {
            const li = document.createElement('li');
            li.className = `page-item${i === currentPage ? ' active' : ''}`;
            li.innerHTML = `<a class="page-link pagination-modern" href="#" data-page="${i}">${i}</a>`;
            paginationContainer.appendChild(li);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item${currentPage === totalPages ? ' disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link pagination-modern" href="#" data-page="${currentPage + 1}" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
        paginationContainer.appendChild(nextLi);

        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page >= 1 && page <= totalPages && page !== currentPage) {
                    currentPage = page;
                    fetchPaginatedMembers(currentPage);
                }
            });
        });
    }

    // Add modern pagination styles
    const style = document.createElement('style');
    style.innerHTML = `
    .pagination-modern {
        border-radius: 8px !important;
        margin: 0 4px;
        background: linear-gradient(90deg, #007bff 0%, #00d4ff 100%);
        color: #fff !important;
        border: none !important;
        box-shadow: 0 2px 8px rgba(0,123,255,0.10);
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        min-width: 38px;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        font-size: 1rem;
    }
    .pagination-modern:hover {
        background: linear-gradient(90deg, #00d4ff 0%, #007bff 100%);
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(0,123,255,0.18);
    }
    .page-item.active .pagination-modern {
        background: linear-gradient(90deg, #28a745 0%, #6fcf97 100%) !important;
        color: #fff !important;
        font-weight: bold;
        box-shadow: 0 4px 16px rgba(40,167,69,0.18);
        border: 2px solid #28a745 !important;
    }
    .page-item.disabled .pagination-modern {
        background: #e9ecef !important;
        color: #adb5bd !important;
        cursor: not-allowed;
        border: none !important;
    }
    `;
    document.head.appendChild(style);

    // Load the first page of members on initial load
    fetchPaginatedMembers(1);
});
</script>
</body>
</html>
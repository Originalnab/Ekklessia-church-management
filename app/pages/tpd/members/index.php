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
                    <tbody>
                        <?php foreach ($members as $index => $member): ?>
                            <tr data-id="<?= $member['member_id'] ?>" 
                                data-first-name="<?= htmlspecialchars($member['first_name']) ?>" 
                                data-last-name="<?= htmlspecialchars($member['last_name']) ?>" 
                                data-dob="<?= htmlspecialchars($member['date_of_birth']) ?>" 
                                data-gender="<?= htmlspecialchars($member['gender']) ?>" 
                                data-marital-status="<?= htmlspecialchars($member['marital_status']) ?>" 
                                data-contact="<?= htmlspecialchars($member['contact']) ?>" 
                                data-email="<?= htmlspecialchars($member['email'] ?? 'N/A') ?>" 
                                data-address="<?= htmlspecialchars($member['address'] ?? 'N/A') ?>" 
                                data-digital-address="<?= htmlspecialchars($member['digital_address']) ?>" 
                                data-occupation="<?= htmlspecialchars($member['occupation'] ?? 'N/A') ?>" 
                                data-employer="<?= htmlspecialchars($member['employer'] ?? 'N/A') ?>" 
                                data-work-phone="<?= htmlspecialchars($member['work_phone'] ?? 'N/A') ?>" 
                                data-highest-education-level="<?= htmlspecialchars($member['highest_education_level'] ?? 'N/A') ?>" 
                                data-institution="<?= htmlspecialchars($member['institution'] ?? 'N/A') ?>" 
                                data-year-graduated="<?= htmlspecialchars($member['year_graduated'] ?? 'N/A') ?>" 
                                data-assembly="<?= htmlspecialchars($member['assembly_name'] ?? 'N/A') ?>" 
                                data-household="<?= htmlspecialchars($member['household_name'] ?? 'Not Assigned') ?>" 
                                data-status="<?= htmlspecialchars($member['status']) ?>" 
                                data-joined-date="<?= htmlspecialchars($member['joined_date']) ?>" 
                                data-referral-id="<?= htmlspecialchars($member['referral_id'] ?? 'N/A') ?>" 
                                data-referral-name="<?= htmlspecialchars(($member['referral_first_name'] ?? '') . ' ' . ($member['referral_last_name'] ?? '')) ?>" 
                                data-shepherd-name="<?= htmlspecialchars($member['shepherd_name'] ?? 'Not Assigned') ?>" 
                                data-household-id="<?= htmlspecialchars(!empty($member['household_id']) && $member['household_id'] != 0 ? $member['household_id'] : '') ?>" 
                                data-shepherd-id="<?= htmlspecialchars(!empty($member['shepherd_id']) && $member['shepherd_id'] != 0 ? $member['shepherd_id'] : '') ?>">
                                <td><input type="checkbox" class="row-checkbox" data-id="<?= $member['member_id'] ?>"></td>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <?php if ($member['profile_photo']): ?>
                                        <img src="/Ekklessia-church-management/app/resources/assets/images/<?= htmlspecialchars($member['profile_photo']) ?>" alt="Profile Photo" class="profile-photo clickable" data-member-id="<?= $member['member_id'] ?>">
                                    <?php else: ?>
                                        <img src="/Ekklessia-church-management/app/resources/assets/images/default.jpg" alt="Default Photo" class="profile-photo clickable" data-member-id="<?= $member['member_id'] ?>">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-gradient-blue text-nowrap clickable" style="min-width: 150px;" data-member-id="<?= $member['member_id'] ?>">
                                        <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                    </button>
                                </td>
                                <td><?= htmlspecialchars($member['contact']) ?></td>
                                <td>
                                    <span class="badge assembly-badge" 
                                          data-assemblies-id="<?= htmlspecialchars($member['assemblies_id'] ?? 'N/A') ?>" 
                                          data-assembly-name="<?= htmlspecialchars($member['assembly_name'] ?? 'N/A') ?>">
                                        <?= htmlspecialchars($member['assembly_name'] ?? 'N/A') ?>
                                </span>
                                </td>
                                <td><?= htmlspecialchars($member['household_name'] ?? 'Not Assigned') ?></td>
                                <td><?= htmlspecialchars($member['shepherd_name'] ?? 'Not Assigned') ?></td>                            <td>
                                <span class="badge bg-info">
                                    <?= htmlspecialchars($member['function_name'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($member['status']) ?></td>
                            <td><?= htmlspecialchars($member['joined_date']) ?></td>
                            <td>
                                <?php if ($member['referral_id']): ?>
                                        <?= htmlspecialchars($member['referral_first_name'] . ' ' . $member['referral_last_name']) ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary load-edit-modal" data-member-id="<?= $member['member_id'] ?>" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-member-btn" data-member-id="<?= $member['member_id'] ?>" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="assignDropdown-<?= $member['member_id'] ?>">
                                                <i class="bi bi-person-plus"></i> Assign
                                            </button>
                                            <ul class="dropdown-menu" id="assignDropdownMenu-<?= $member['member_id'] ?>">
                                                <li><a class="dropdown-item assign-household-item" href="#" data-action="assign-household" data-member-id="<?= $member['member_id'] ?>">Assign Household</a></li>
                                                <li><a class="dropdown-item edit-household-item" href="#" data-action="edit-household" data-member-id="<?= $member['member_id'] ?>">Edit Household</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination Placeholder -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="paginationControls"></ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Assign Household Modal -->
    <div class="modal fade" id="assignHouseholdModal" tabindex="-1" aria-labelledby="assignHouseholdModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="assignHouseholdModalLabel">Household and Shepherd (<span id="selectedMemberName"></span>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="assignHouseholdForm">
                        <input type="hidden" id="assignMemberId" name="member_id">
                        <input type="hidden" name="assemblies_id" id="assignAssembliesId">
                        <div class="mb-3">
                            <label for="assemblySelect" class="form-label">Assembly <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="assemblySelect" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="householdSelect" class="form-label">Household <span class="text-danger">*</span></label>
                            <select class="form-select" id="householdSelect" name="household_id" required>
                                <option value="">-- Select Household --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="shepherdSelect" class="form-label">Shepherd <span class="text-danger">*</span></label>
                            <select class="form-select" id="shepherdSelect" name="shepherd_id" required>
                                <option value="">-- Select Shepherd --</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Assign</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Household Modal -->
    <div class="modal fade" id="editHouseholdModal" tabindex="-1" aria-labelledby="editHouseholdModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editHouseholdModalLabel">Edit Household and Shepherd (<span id="editSelectedMemberName"></span>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editHouseholdForm">
                        <input type="hidden" id="editMemberId" name="member_id">
                        <input type="hidden" name="assemblies_id" id="editAssembliesId">
                        <div class="mb-3">
                            <label for="editAssemblySelect" class="form-label">Assembly <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="editAssemblySelect" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editHouseholdSelect" class="form-label">Household <span class="text-danger">*</span></label>
                            <select class="form-select" id="editHouseholdSelect" name="household_id" required>
                                <option value="">-- Select Household --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editShepherdSelect" class="form-label">Shepherd <span class="text-danger">*</span></label>
                            <select class="form-select" id="editShepherdSelect" name="shepherd_id" required>
                                <option value="">-- Select Shepherd --</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Upload Modal -->
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
    const tbody = document.querySelector('#membersTable tbody');
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
        row.setAttribute('data-education', member.highest_education_level || 'N/A');
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
                <span class="badge assembly-badge" data-assemblies-id="${member.assemblies_id || 'N/A'}" data-assembly-name="${member.assembly_name || 'N/A'}">
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

// Add these before DOMContentLoaded
function applyAssemblyBadges() {
    const gradientColors = [
        'linear-gradient(45deg, #007bff, #00d4ff)',
        'linear-gradient(45deg, #28a745, #6fcf97)',
        'linear-gradient(45deg, #ffc107, #ffca28)',
        'linear-gradient(45deg, #17a2b8, #4fc3f7)',
        'linear-gradient(45deg, #dc3545, #ff6b6b)',
        'linear-gradient(45deg, #6c757d, #b0b5b9)',
        'linear-gradient(45deg, #ff6f61, #ff9f84)',
        'linear-gradient(45deg, #9c27b0, #ce93d8)',
        'linear-gradient(45deg, #ff9800, #ffb74d)',
        'linear-gradient(45deg, #e91e63, #f06292)',
        'linear-gradient(45deg, #4caf50, #81c784)',
        'linear-gradient(45deg, #3f51b5, #7986cb)'
    ];

    document.querySelectorAll('.assembly-badge').forEach((badge, index) => {
        const assemblyId = badge.getAttribute('data-assemblies-id');
        badge.style.background = assemblyId === 'N/A' ? 
            'linear-gradient(45deg, #6c757d, #b0b5b9)' : 
            gradientColors[index % gradientColors.length];
        badge.style.color = 'white';
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

    // Re-initialize edit modal buttons
    document.querySelectorAll('.load-edit-modal').forEach(button => {
        button.addEventListener('click', function() {
            const memberId = this.getAttribute('data-member-id');
            loadEditModal(memberId);
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
        'employer', 'work-phone', 'education', 'institution', 
        'year-graduated', 'assembly', 'household', 'status', 
        'joined-date', 'referral-name', 'shepherd-name'
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
    }

    // Call after loading members and after any dynamic updates
    initializeDropdowns();

    // Properly handle dropdown clicks
    document.body.addEventListener('click', function(e) {
        const dropdownToggle = e.target.closest('[data-bs-toggle="dropdown"]');
        if (dropdownToggle) {
            e.preventDefault();
            e.stopPropagation();
            const dropdown = bootstrap.Dropdown.getOrCreateInstance(dropdownToggle);
            dropdown.toggle();
        }
    });

    // Handle assign/edit actions
    document.body.addEventListener('click', function(e) {
        const action = e.target.closest('.dropdown-item');
        if (action) {
            e.preventDefault();
            const memberId = action.dataset.memberId;
            const actionType = action.dataset.action;
            handleAssignAction(memberId, actionType);
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
        });
    });

    // Assign unique gradient colors to assembly badges
    const gradientColors = [
        'linear-gradient(45deg, #007bff, #00d4ff)',
        'linear-gradient(45deg, #28a745, #6fcf97)',
        'linear-gradient(45deg, #ffc107, #ffca28)',
        'linear-gradient(45deg, #17a2b8, #4fc3f7)',
        'linear-gradient(45deg, #dc3545, #ff6b6b)',
        'linear-gradient(45deg, #6c757d, #b0b5b9)',
        'linear-gradient(45deg, #ff6f61, #ff9f84)',
        'linear-gradient(45deg, #9c27b0, #ce93d8)',
        'linear-gradient(45deg, #ff9800, #ffb74d)',
        'linear-gradient(45deg, #e91e63, #f06292)',
        'linear-gradient(45deg, #4caf50, #81c784)',
        'linear-gradient(45deg, #3f51b5, #7986cb)'
    ];

    const assemblyMap = <?php echo $assembly_map_json; ?>;
    const assemblyColorMap = {};
    let colorIndex = 0;
    for (const assemblyId in assemblyMap) {
        assemblyColorMap[assemblyId] = assemblyId === 'N/A' || assemblyMap[assemblyId] === 'N/A' 
            ? 'linear-gradient(45deg, #6c757d, #b0b5b9)' 
            : gradientColors[colorIndex++ % gradientColors.length];
    }

    function applyAssemblyBadges() {
        document.querySelectorAll('.assembly-badge').forEach(badge => {
            const assemblyId = badge.getAttribute('data-assemblies-id');
            badge.style.background = assemblyColorMap[assemblyId] || 'linear-gradient(45deg, #6c757d, #b0b5b9)';
            badge.style.color = 'white';
        });
    }

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
        });

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
        const tbody = document.querySelector('#membersTable tbody');
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
            row.setAttribute('data-education', member.highest_education_level || 'N/A');
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
                    <span class="badge assembly-badge" data-assemblies-id="${member.assemblies_id || 'N/A'}" data-assembly-name="${member.assembly_name || 'N/A'}">
                        ${member.assembly_name || 'N/A'}
                    </span>
                </td>
                <td>${member.household_name || 'Not Assigned'}</td>
                <td>${member.shepherd_name || 'Not Assigned'}</td>                <td>                    <span class="badge" style="background: linear-gradient(45deg, #1a237e, #3949ab); color: white;">
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

    function attachEventListeners() {
        // Edit modal buttons
        document.querySelectorAll('.load-edit-modal').forEach(button => {
            button.addEventListener('click', function() {
                const memberId = this.getAttribute('data-member-id');
                const modal = document.getElementById(`editMemberModal-${memberId}`);
                if (!modal) {
                    fetch(`edit_members.php?member_id=${memberId}`)
                        .then(response => response.text())
                        .then(html => {
                            document.body.insertAdjacentHTML('beforeend', html);
                            new bootstrap.Modal(document.getElementById(`editMemberModal-${memberId}`)).show();
                        })
                        .catch(error => console.error('Error loading modal:', error));
                } else {
                    new bootstrap.Modal(modal).show();
                }
            });
        });

        attachViewMemberListeners();

        // Use event delegation for dropdown items
        document.getElementById('membersTable').addEventListener('click', function(e) {
            const target = e.target;
            if (target.classList.contains('assign-household-item') || target.classList.contains('edit-household-item')) {
                e.preventDefault();
                const action = target.getAttribute('data-action');
                const memberId = target.getAttribute('data-member-id');
                const row = document.querySelector(`#membersTable tr[data-id="${memberId}"]`);
                if (!row) return;

                console.log(`Dropdown item clicked: ${action} for member ${memberId}`);

                const assemblyId = row.getAttribute('data-assemblies-id');
                const assemblyName = row.getAttribute('data-assembly');
                const memberName = `${row.getAttribute('data-first-name')} ${row.getAttribute('data-last-name')}`;
                const householdId = row.getAttribute('data-household-id');
                const shepherdId = row.getAttribute('data-shepherd-id');

                if (action === 'assign-household') {
                    document.getElementById('assignMemberId').value = memberId;
                    document.getElementById('assignAssembliesId').value = assemblyId;
                    document.getElementById('assemblySelect').value = assemblyName;
                    document.getElementById('selectedMemberName').textContent = memberName;
                    fetchHouseholdsAndShepherds(assemblyId, memberId);
                    assignHouseholdModal.show();
                    console.log('Assign Household modal should be shown');
                } else if (action === 'edit-household') {
                    document.getElementById('editMemberId').value = memberId;
                    document.getElementById('editAssembliesId').value = assemblyId;
                    document.getElementById('editAssemblySelect').value = assemblyName;
                    document.getElementById('editSelectedMemberName').textContent = memberName;
                    fetchHouseholdsAndShepherdsForEdit(assemblyId, householdId, shepherdId, memberId);
                    editHouseholdModal.show();
                    console.log('Edit Household modal should be shown');
                }
            }
        });
    }

    function attachViewMemberListeners() {
        document.querySelectorAll('.clickable').forEach(element => {
            element.addEventListener('click', function() {
                const memberId = this.getAttribute('data-member-id');
                const row = document.querySelector(`#membersTable tr[data-id="${memberId}"]`);
                if (row) {
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
                    document.getElementById('viewMemberEducation').textContent = row.getAttribute('data-education');
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

        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous"><span aria-hidden="true">«</span></a>`;
        paginationContainer.appendChild(prevLi);

        for (let i = startPage; i <= endPage; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            paginationContainer.appendChild(pageLi);
        }

        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next"><span aria-hidden="true">»</span></a>`;
        paginationContainer.appendChild(nextLi);

        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page >= 1 && page <= totalPages) {
                    currentPage = page;
                    fetchPaginatedMembers(currentPage);
                }
            });
        });
    }

    function deleteMember(memberId) {
        fetch('delete_member_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `member_id=${encodeURIComponent(memberId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById(`deleteMemberModal-${memberId}`));
                modal.hide();
                fetchPaginatedMembers(currentPage);
            } else {
                showAlert('danger', 'Error: ' + data.message);
            }
        })
        .catch(error => showAlert('danger', 'Network error: ' + error.message));
    }

    const viewMemberModal = new bootstrap.Modal(document.getElementById('viewMemberModal'));
    const assignHouseholdModal = new bootstrap.Modal(document.getElementById('assignHouseholdModal'));
    const editHouseholdModal = new bootstrap.Modal(document.getElementById('editHouseholdModal'));
    const bulkUploadModal = new bootstrap.Modal(document.getElementById('bulkUploadModal'));

    // Initial load
    fetchPaginatedMembers(currentPage);

    const filterName = document.getElementById('filterName');
    const filterAssembly = document.getElementById('filterAssembly');
    const filterHousehold = document.getElementById('filterHousehold');
    const filterShepherd = document.getElementById('filterShepherd');
    const filterStatus = document.getElementById('filterStatus');
    const filterLocalRole = document.getElementById('filterLocalRole');
    const filterJoinedStart = document.getElementById('filterJoinedStart');
    const filterJoinedEnd = document.getElementById('filterJoinedEnd');

    function applyFilters() {
        currentPage = 1; // Reset to first page on filter change
        fetchPaginatedMembers(currentPage);
    }

    filterName.addEventListener('input', applyFilters);
    filterAssembly.addEventListener('change', applyFilters);
    filterHousehold.addEventListener('change', applyFilters);
    filterShepherd.addEventListener('change', applyFilters);
    filterStatus.addEventListener('change', applyFilters);
    filterLocalRole.addEventListener('change', applyFilters);
    filterJoinedStart.addEventListener('change', applyFilters);
    filterJoinedEnd.addEventListener('change', applyFilters);

    function fetchHouseholdsAndShepherds(assemblyId, memberId) {
        if (!assemblyId || assemblyId === 'N/A') {
            showAlert('danger', 'No valid assembly assigned to this member.');
            return;
        }

        fetch(`fetch_households_by_assembly.php?assembly_id=${encodeURIComponent(assemblyId)}`)
            .then(response => response.json())
            .then(households => {
                const householdSelect = document.getElementById('householdSelect');
                householdSelect.innerHTML = '<option value="">-- Select Household --</option>';
                households.forEach(household => {
                    householdSelect.innerHTML += `<option value="${household.household_id}">${household.name}</option>`;
                });
            })
            .catch(error => showAlert('danger', 'Failed to load households.'));

        fetch(`fetch_shepherds_by_assembly.php?assembly_id=${encodeURIComponent(assemblyId)}`)
            .then(response => response.json())
            .then(members => {
                const shepherdSelect = document.getElementById('shepherdSelect');
                shepherdSelect.innerHTML = '<option value="">-- Select Shepherd --</option>';
                members.forEach(member => {
                    shepherdSelect.innerHTML += `<option value="${member.member_id}">${member.first_name} ${member.last_name}</option>`;
                });
            })
            .catch(error => showAlert('danger', 'Failed to load shepherds.'));
    }

    function fetchHouseholdsAndShepherdsForEdit(assemblyId, currentHouseholdId, currentShepherdId, memberId) {
        if (!assemblyId || assemblyId === 'N/A') {
            showAlert('danger', 'No valid assembly assigned to this member.');
            return;
        }

        fetch(`fetch_households_by_assembly.php?assembly_id=${encodeURIComponent(assemblyId)}`)
            .then(response => response.json())
            .then(households => {
                const householdSelect = document.getElementById('editHouseholdSelect');
                householdSelect.innerHTML = '<option value="">-- Select Household --</option>';
                households.forEach(household => {
                    const selected = household.household_id == currentHouseholdId ? ' selected' : '';
                    householdSelect.innerHTML += `<option value="${household.household_id}"${selected}>${household.name}</option>`;
                });
            })
            .catch(error => showAlert('danger', 'Failed to load households.'));

        fetch(`fetch_shepherds_by_assembly.php?assembly_id=${encodeURIComponent(assemblyId)}`)
            .then(response => response.json())
            .then(members => {
                const shepherdSelect = document.getElementById('editShepherdSelect');
                shepherdSelect.innerHTML = '<option value="">-- Select Shepherd --</option>';
                members.forEach(member => {
                    const selected = member.member_id == currentShepherdId ? ' selected' : '';
                    shepherdSelect.innerHTML += `<option value="${member.member_id}"${selected}>${member.first_name} ${member.last_name}</option>`;
                });
            })
            .catch(error => showAlert('danger', 'Failed to load shepherds.'));
    }

    document.getElementById('assignHouseholdForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'assign');
        fetch('assign_household_process.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    assignHouseholdModal.hide();
                    fetchPaginatedMembers(currentPage);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => showAlert('danger', 'An error occurred while assigning the household.'));
    });

    document.getElementById('editHouseholdForm').addEventListener('submit', function(e) {
        e.preventDefault();
       
        const formData = new FormData(this);
        formData.append('action', 'edit');
        fetch('assign_household_process.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    editHouseholdModal.hide();
                    fetchPaginatedMembers(currentPage);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => showAlert('danger', 'An error occurred while updating the household.'));
    });

    document.getElementById('bulkUploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('preview_bulk_upload.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    uploadStep.style.display = 'none';
                    previewStep.style.display = 'block';
                    previewContent.innerHTML = data.html;

                    document.getElementById('backToUpload').addEventListener('click', function() {
                        uploadStep.style.display = 'block';
                        previewStep.style.display = 'none';
                        previewContent.innerHTML = '';
                        document.getElementById('bulkUploadForm').reset();
                    });

                    document.getElementById('bulkUploadPreviewForm').addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        fetch('process_bulk_upload.php', { method: 'POST', body: formData })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    showAlert('success', data.message);
                                    bulkUploadModal.hide();
                                    fetchPaginatedMembers(currentPage);
                                    uploadStep.style.display = 'block';
                                    previewStep.style.display = 'none';
                                    previewContent.innerHTML = '';
                                    document.getElementById('bulkUploadForm').reset();
                                } else {
                                    showAlert('danger', data.message);
                                }
                            })
                            .catch(error => showAlert('danger', 'An error occurred while importing members.'));
                    });
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => showAlert('danger', 'An error occurred while uploading the CSV file.'));
    });

    bulkUploadModal._element.addEventListener('hidden.bs.modal', function () {
        uploadStep.style.display = 'block';
        previewStep.style.display = 'none';
        previewContent.innerHTML = '';
        document.getElementById('bulkUploadForm').reset();
    });

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

    console.log('Members Data:', <?php echo json_encode($members); ?>);
});

// Move the delete button click handler outside DOMContentLoaded
document.addEventListener('click', function(e) {
    const deleteBtn = e.target.closest('.delete-member-btn');
    if (!deleteBtn) return;

    e.preventDefault();
    currentMemberId = deleteBtn.getAttribute('data-member-id');
    const row = deleteBtn.closest('tr');
    currentMemberName = `${row.getAttribute('data-first-name')} ${row.getAttribute('data-last-name')}`;
    
    document.getElementById('deleteMemberName').textContent = currentMemberName;
    const bsDeleteModal = new bootstrap.Modal(deleteModal);
    bsDeleteModal.show();
});

// Move delete confirmation handler outside DOMContentLoaded
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!currentMemberId) return;

    // Show loading state
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';

    // Store reference to the row being deleted and add visual feedback
    lastDeletedRow = document.querySelector(`tr[data-id="${currentMemberId}"]`);
    if (lastDeletedRow) {
        lastDeletedRow.style.backgroundColor = '#ffe6e6';
        lastDeletedRow.style.transition = 'opacity 0.5s ease, background-color 0.3s ease';
        lastDeletedRow.style.opacity = '0.5';
    }

    fetch('delete_member_process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `member_id=${encodeURIComponent(currentMemberId)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Successfully deleted ${currentMemberName}`);
            bootstrap.Modal.getInstance(deleteModal).hide();
            
            // First verify the current page data
            return fetch(`fetch_paginated_members.php?page=${currentPage}`)
                .then(response => response.json());
        } else {
            throw new Error(data.message || 'Failed to delete member');
        }
    })
    .then(data => {
        if (data.success) {
            // If current page has no records and we're not on page 1, go to previous page
            if (data.members.length === 0 && currentPage > 1) {
                currentPage--;
            }
            
            // Refresh the table with updated data
            return fetchPaginatedMembers(currentPage)
                .then(() => {
                    // Update dashboard counts
                    updateDashboardCounts(
                        data.pagination.total_members, 
                        data.members
                    );
                });
        }
    })
    .catch(error => {
        showAlert('danger', error.message);
        // Restore the row's appearance if deletion failed
        if (lastDeletedRow) {
            lastDeletedRow.style.backgroundColor = '';
            lastDeletedRow.style.opacity = '1';
        }
    })
    .finally(() => {
        // Reset button state and variables
        this.disabled = false;
        this.textContent = 'Delete';
        currentMemberId = null;
        currentMemberName = null;
        lastDeletedRow = null;
    });
});
</script>
</body>
</html>
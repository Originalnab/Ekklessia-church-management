<?php
session_start();
$page_title = "Role Assignment";
include "../../../config/db.php";
include "../../../functions/role_management.php";
include "../../../functions/member_functions.php";
include "../../../functions/assemblies_functions.php";

// Check if user is logged in using member_id
if (!isset($_SESSION['member_id'])) {
    // Redirect to login page with a redirect parameter to return to this page
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /Ekklessia-church-management/app/pages/auth/login.php?redirect=$redirect_url");
    exit;
}

// Get the logged-in member's ID
$logged_in_member_id = $_SESSION['member_id'];

// Fetch roles for filters and forms
$roles = getAllRoles();

// Fetch total members count for dashboard
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_members FROM members");
    $total_members = $stmt->fetch(PDO::FETCH_ASSOC)['total_members'];
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching total members: " . $e->getMessage();
    $total_members = 0;
}

// Fetch total assigned roles count
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_assignments FROM member_role");
    $total_assignments = $stmt->fetch(PDO::FETCH_ASSOC)['total_assignments'];
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching role assignments: " . $e->getMessage();
    $total_assignments = 0;
}

// Fetch total roles count
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_roles FROM roles");
    $total_roles = $stmt->fetch(PDO::FETCH_ASSOC)['total_roles'];
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching roles count: " . $e->getMessage();
    $total_roles = 0;
}

$base_url = '/Ekklessia-church-management/app';
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
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
    <div class="card nav-card" style="margin-top: -30px; position: relative; top: -10px;">
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/pages/tpd/members/index.php" class="nav-link-btn">
                        <i class="bi bi-people-fill text-primary"></i>
                        <span>Members</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/pages/roles/index.php" class="nav-link-btn">
                        <i class="bi bi-person-badge-fill text-success"></i>
                        <span>Roles</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/pages/roles/assignment/index.php" class="nav-link-btn active">
                        <i class="bi bi-person-check-fill text-danger"></i>
                        <span>Role Assignment</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/pages/roles/permissions/index.php" class="nav-link-btn">
                        <i class="bi bi-shield-lock-fill text-info"></i>
                        <span>Permissions</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/pages/tpd/dashboard/index.php" class="nav-link-btn">
                        <i class="bi bi-speedometer2 text-warning"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mini Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm role-assignment-card">
                <div class="card-body d-flex align-items-center">
                    <div class="role-card-icon bg-primary-gradient rounded-circle p-3 me-3">
                        <i class="bi bi-people-fill fs-2 text-white"></i>
                    </div>
                    <div>
                        <h6 class="card-title text-muted mb-1">Total Members</h6>
                        <h3 class="card-text mb-0"><?= $total_members ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm role-assignment-card">
                <div class="card-body d-flex align-items-center">
                    <div class="role-card-icon bg-success-gradient rounded-circle p-3 me-3">
                        <i class="bi bi-person-badge-fill fs-2 text-white"></i>
                    </div>
                    <div>
                        <h6 class="card-title text-muted mb-1">Total Roles</h6>
                        <h3 class="card-text mb-0"><?= $total_roles ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm role-assignment-card">
                <div class="card-body d-flex align-items-center">
                    <div class="role-card-icon bg-danger-gradient rounded-circle p-3 me-3">
                        <i class="bi bi-person-check-fill fs-2 text-white"></i>
                    </div>
                    <div>
                        <h6 class="card-title text-muted mb-1">Role Assignments</h6>
                        <h3 class="card-text mb-0"><?= $total_assignments ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Role Assignment</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-light" id="bulkAssignBtn">
                        <i class="bi bi-person-check"></i> Bulk Assign Role
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="assemblyFilter" class="form-label"><i class="bi bi-building me-2"></i>Assembly</label>
                    <select class="form-select" id="assemblyFilter">
                        <option value="0">All Assemblies</option>
                        <?php
                        // Replace getAllAssemblies() with direct database query
                        $stmt = $pdo->query("SELECT assembly_id, name AS assembly_name FROM assemblies ORDER BY name ASC");
                        $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($assemblies as $assembly) {
                            echo "<option value='{$assembly['assembly_id']}'>{$assembly['assembly_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="roleFilter" class="form-label"><i class="bi bi-person-badge me-2"></i>Role</label>
                    <select class="form-select" id="roleFilter">
                        <option value="0">All Roles</option>
                        <?php
                        foreach ($roles as $role) {
                            echo "<option value='{$role['role_id']}'>{$role['role_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="searchInput" class="form-label"><i class="bi bi-search me-2"></i>Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by name, email or phone">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" id="searchBtn">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>
            
            <!-- Bulk Selection Controls -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="bulk-controls" style="display: none;">
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><span id="selectedCount">0</span> members selected</strong>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary" id="clearSelectionBtn">
                                        <i class="bi bi-x-circle"></i> Clear Selection
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Members Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="membersTable">
                    <thead class="role-assignment-thead">
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
                                </div>
                            </th>
                            <th>#</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Assembly</th>
                            <th>Current Roles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="membersTableBody">
                        <!-- Members will be loaded here via AJAX -->
                        <tr>
                            <td colspan="7" class="text-center">Loading members...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <nav aria-label="Members pagination" class="mt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span id="paginationInfo">Showing 0 of 0 members</span>
                    </div>
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Pagination will be generated via JavaScript -->
                    </ul>
                </div>
            </nav>
        </div>
    </div>

    <!-- Single Member Role Assignment Modal -->
    <div class="modal fade" id="assignRoleModal" tabindex="-1" aria-labelledby="assignRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="assignRoleModalLabel">Assign Roles to <span id="memberName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <p><strong>Instructions:</strong></p>
                                <ul>
                                    <li>Select one or more roles to assign to this member.</li>
                                    <li>You can designate one role as the primary role.</li>
                                    <li>The primary role will be used for dashboard access and permissions.</li>
                                    <li>If no primary role is selected, the first role will be used as default.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="currentRoles" class="form-label"><i class="bi bi-person-lines-fill me-2"></i>Current Roles</label>
                        <div id="currentRoles" class="p-2 border rounded mb-3">
                            <!-- Current roles will be loaded here -->
                            <div class="text-muted">Loading current roles...</div>
                        </div>
                        
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="removeAllRolesBtn">
                                <i class="bi bi-trash"></i> Remove All Roles
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="roleSelect" class="form-label"><i class="bi bi-plus-circle me-2"></i>Add Role</label>
                        <select class="form-select mb-2" id="roleSelect">
                            <option value="">-- Select a role to assign --</option>
                            <?php
                            foreach ($roles as $role) {
                                echo "<option value='{$role['role_id']}'>{$role['role_name']}</option>";
                            }
                            ?>
                        </select>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="makeRolePrimary">
                            <label class="form-check-label" for="makeRolePrimary">
                                Make this the primary role
                            </label>
                        </div>
                        
                        <button type="button" class="btn btn-primary" id="addRoleBtn" disabled>
                            <i class="bi bi-plus-circle"></i> Add Role
                        </button>
                    </div>
                    
                    <input type="hidden" id="currentMemberId" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Role Assignment Modal -->
    <div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-labelledby="bulkAssignModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="bulkAssignModalLabel">Bulk Assign Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <p><strong>You are about to assign roles to <span id="bulkSelectedCount">0</span> members.</strong></p>
                        <p>Select one or more roles you want to assign to all selected members.</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-people-fill me-2"></i>Select Roles</label>
                        <div class="role-checkboxes border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <?php
                            foreach ($roles as $role) {
                                echo "<div class='form-check mb-2'>
                                    <input class='form-check-input bulk-role-checkbox' type='checkbox' 
                                        name='bulkRoleIds[]' value='{$role['role_id']}' id='bulkRole_{$role['role_id']}'>
                                    <label class='form-check-label' for='bulkRole_{$role['role_id']}'>
                                        {$role['role_name']}
                                    </label>
                                </div>";
                            }
                            ?>
                        </div>
                        <small class="text-muted mt-2 d-block">Select at least one role to proceed.</small>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="bulkMakeRolePrimary">
                        <label class="form-check-label" for="bulkMakeRolePrimary">
                            Make the first selected role primary for all members
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmBulkAssignBtn" disabled>
                        <i class="bi bi-person-check"></i> Assign Roles
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <div id="memberToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
            <div class="toast-header">
                <strong class="me-auto">Member Details</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <p><strong>Name:</strong> <span id="toastMemberName"></span></p>
                <p><strong>Email:</strong> <span id="toastMemberEmail"></span></p>
                <p><strong>Phone:</strong> <span id="toastMemberPhone"></span></p>
                <p><strong>Assembly:</strong> <span id="toastMemberAssembly"></span></p>
                <p><strong>Current Roles:</strong> <span id="toastMemberRoles"></span></p>
            </div>
        </div>
    </div>
    
    <!-- Member Details Modal -->
    <div class="modal fade" id="memberDetailsModal" tabindex="-1" aria-labelledby="memberDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(45deg, #007bff, #00d4ff); color: white;">
                    <h5 class="modal-title" id="memberDetailsModalLabel">Member Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column flex-md-row align-items-center mb-4">
                        <div class="role-profile-image-container me-md-4 mb-3 mb-md-0">
                            <img id="modalMemberImage" src="" class="img-fluid rounded-circle role-profile-image" alt="Member Profile">
                        </div>
                        <div class="text-center text-md-start">
                            <h4 id="modalMemberName" class="mb-2"></h4>
                            <div id="modalQuickRoles" class="mt-2"></div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-3 role-assignment-card">
                                <div class="card-header role-card-header-gradient d-flex align-items-center">
                                    <i class="bi bi-person-fill me-2"></i> Personal Information
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Email:</strong></p>
                                            <p id="modalMemberEmail" class="text-muted"></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Phone:</strong></p>
                                            <p id="modalMemberPhone" class="text-muted"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-3 role-assignment-card">
                                <div class="card-header role-card-header-gradient d-flex align-items-center">
                                    <i class="bi bi-building me-2"></i> Church Information
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Assembly:</strong></p>
                                            <p id="modalMemberAssembly" class="text-muted"></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Member ID:</strong></p>
                                            <p id="modalMemberId" class="text-muted"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card role-assignment-card">
                                <div class="card-header role-card-header-gradient d-flex align-items-center">
                                    <i class="bi bi-person-badge me-2"></i> Roles
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle mb-0">
                                            <thead class="role-table-head">
                                                <tr>
                                                    <th>Role Name</th>
                                                    <th>Hierarchy Level</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="modalMemberRolesTable">
                                                <!-- Roles will be populated here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="modalManageRolesBtn">
                        <i class="bi bi-person-gear"></i> Manage Roles
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include "../../../includes/footer.php"; ?>

<!-- Bootstrap CSS (already included in header.php, but ensure it's present) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
    /* Page-specific styles that won't affect other pages */
    .role-assignment-card { 
        border: 1px solid #e0e0e0; 
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
        transition: box-shadow 0.3s ease;
        border-radius: 10px;
        overflow: hidden;
    }
    .role-assignment-card:hover { 
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
    }
    
    /* Gradient backgrounds for card icons */
    .bg-primary-gradient {
        background: linear-gradient(45deg, #007bff, #00d4ff);
    }
    .bg-success-gradient {
        background: linear-gradient(45deg, #28a745, #6fcf97);
    }
    .bg-danger-gradient {
        background: linear-gradient(45deg, #dc3545, #ff6b6b);
    }
    .role-card-icon {
        min-width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Table header with gradient */
    .role-assignment-thead {
        background: linear-gradient(45deg, #007bff, #00d4ff);
        color: white;
    }
    .role-assignment-thead th {
        font-weight: 500;
        padding: 12px 8px;
        border-bottom: none;
    }
    
    /* Page-specific navigation styles */
    .nav-card { 
        background-color: #ffffff; 
        border: none; 
        box-shadow: 0 -2px 6px -2px rgba(0, 0, 0, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1); 
        border-radius: 10px; 
        padding: 20px; 
        margin-bottom: 20px; 
        margin-top: -30px; 
        position: relative; 
        top: -10px; 
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
    .nav-link-btn.active { 
        background-color: #e9f2ff; 
        border-left: 3px solid #007bff; 
    }
    .nav-link-btn i { 
        font-size: 1.5rem; 
        margin-bottom: 8px; 
    }
    .nav-link-btn span { 
        font-size: 0.9rem; 
        font-weight: 500; 
    }
    
    /* Blue gradient button - page specific */
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
    
    /* Page-specific table styles */
    .table-responsive { 
        overflow-x: auto; 
    }
    #membersTable { 
        min-width: 800px; 
    }
    
    /* Card headers with gradient */
    .role-card-header-gradient {
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        color: #333;
        font-weight: 600;
        border-bottom: 1px solid #dee2e6;
    }
    
    /* Role table headers with gradient */
    .role-table-head {
        background: linear-gradient(45deg, #007bff, #00d4ff);
        color: white;
    }
    .role-table-head th {
        font-weight: 500;
        padding: 10px;
        border-bottom: none;
    }
    
    /* Dark mode adjustments for this page only */
    [data-bs-theme="dark"] .role-assignment-card { 
        border: 1px solid #4a90e2 !important; 
        box-shadow: 0 4px 12px -2px rgba(74, 144, 226, 0.5), 0 0 20px rgba(74, 144, 226, 0.3) !important; 
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
    [data-bs-theme="dark"] .role-assignment-thead {
        background: linear-gradient(45deg, #0056b3, #0096c7);
    }
    [data-bs-theme="dark"] .role-card-header-gradient {
        background: linear-gradient(45deg, #2b3035, #343a40);
        color: #e0e0e0;
        border-bottom: 1px solid #495057;
    }
    [data-bs-theme="dark"] .role-table-head {
        background: linear-gradient(45deg, #0056b3, #0096c7);
    }
    
    /* Toast styling - page specific */
    .toast { 
        border: none; 
        border-radius: 10px; 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); 
        min-width: 300px; 
        max-width: 400px; 
    }
    .toast-header { 
        background: linear-gradient(45deg, #007bff, #00d4ff); 
        color: white; 
        border-top-left-radius: 10px; 
        border-top-right-radius: 10px; 
    }
    .toast-body { 
        background: linear-gradient(45deg, #f8f9fa, #e9ecef); 
        color: #333; 
        border-bottom-left-radius: 10px; 
        border-bottom-right-radius: 10px; 
        padding: 15px; 
    }
    .toast-body p { 
        margin: 5px 0; 
        font-size: 0.95em; 
    }
    .toast-body strong { 
        color: #007bff; 
    }
    [data-bs-theme="dark"] .toast-header { 
        background: linear-gradient(45deg, #0056b3, #0096c7); 
    }
    [data-bs-theme="dark"] .toast-body { 
        background: linear-gradient(45deg, #343a40, #495057); 
        color: #e0e0e0; 
    }
    [data-bs-theme="dark"] .toast-body strong { 
        color: #4a90e2; 
    }
    
    /* Highlight selected row */
    #membersTable tbody tr.selected { 
        background-color: #e3f2fd !important; 
    }
    [data-bs-theme="dark"] #membersTable tbody tr.selected { 
        background-color: #1e3a5f !important; 
    }
    
    /* Role badges */
    .role-badge {
        display: inline-block;
        padding: 0.25em 0.6em;
        font-size: 0.75em;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 20px;
        margin-right: 3px;
        margin-bottom: 3px;
        background: linear-gradient(45deg, #007bff, #00d4ff);
        color: white;
        transition: all 0.2s;
    }
    .role-badge.primary {
        background: linear-gradient(45deg, #dc3545, #ff6b6b);
    }
    .role-badge:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    /* Page-specific profile image with gradient border */
    .role-assignment-profile-img-container {
        position: relative;
        display: inline-block;
        padding: 3px;
        border-radius: 50%;
        background: linear-gradient(45deg, #007bff, #00d4ff, #6a11cb, #2575fc);
        background-size: 200% 200%;
        animation: role-assignment-gradientBorder 3s ease infinite;
    }
    
    .role-assignment-profile-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid white;
    }
    
    @keyframes role-assignment-gradientBorder {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    /* Modal profile image - page specific */
    .role-profile-image-container {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        padding: 4px;
        background: linear-gradient(45deg, #007bff, #00d4ff, #6a11cb, #2575fc);
        background-size: 200% 200%;
        animation: role-assignment-gradientBorder 3s ease infinite;
    }
    
    .role-profile-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 3px solid white;
        border-radius: 50%;
    }
    
    /* Modal styling - page specific */
    .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }
    
    .card-header {
        font-weight: 500;
    }
    
    .modal-body .card {
        border-radius: 10px;
        overflow: hidden;
    }
    
    /* Role status badges */
    .role-status-badge {
        padding: 0.25em 0.6em;
        font-size: 0.75em;
        font-weight: 500;
        border-radius: 20px;
    }
    .badge-primary-role {
        background: linear-gradient(45deg, #dc3545, #ff6b6b);
        color: white;
    }
    .badge-active {
        background: linear-gradient(45deg, #28a745, #6fcf97);
        color: white;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables for pagination and selection
    let currentPage = 1;
    let totalPages = 1;
    let selectedMembers = new Set();
    let allMembers = [];
    
    // Function to show alerts
    function showAlert(type, message) {
        const alertContainer = document.body;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1060;">
                <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        alertContainer.appendChild(wrapper);
        setTimeout(() => {
            const alert = wrapper.querySelector('.alert');
            if (alert) bootstrap.Alert.getInstance(alert)?.close();
        }, 5000);
    }
    
    // Function to load members with pagination
    function loadMembers(page = 1) {
        const searchTerm = document.getElementById('searchInput').value;
        const assemblyId = document.getElementById('assemblyFilter').value;
        const roleId = document.getElementById('roleFilter').value;
        const limit = 10; // Items per page
        
        // Show loading message
        document.getElementById('membersTableBody').innerHTML = '<tr><td colspan="7" class="text-center">Loading members...</td></tr>';
        
        // Fetch members from the server
        fetch(`${window.location.origin}/Ekklessia-church-management/app/pages/roles/assignment/fetch_members.php?page=${page}&limit=${limit}&search=${encodeURIComponent(searchTerm)}&assembly_id=${assemblyId}&role_id=${roleId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store pagination info
                    currentPage = data.pagination.current_page;
                    totalPages = data.pagination.total_pages;
                    allMembers = data.members;
                    
                    // Update table with members
                    updateMembersTable(data.members);
                    
                    // Update pagination
                    updatePagination(data.pagination);
                    
                    // Update pagination info text
                    const start = (currentPage - 1) * limit + 1;
                    const end = Math.min(currentPage * limit, data.pagination.total_members);
                    document.getElementById('paginationInfo').textContent = 
                        `Showing ${start}-${end} of ${data.pagination.total_members} members`;
                } else {
                    document.getElementById('membersTableBody').innerHTML = 
                        `<tr><td colspan="7" class="text-center text-danger">Error: ${data.message}</td></tr>`;
                    document.getElementById('paginationInfo').textContent = 'No members to display';
                }
            })
            .catch(error => {
                console.error('Error fetching members:', error);
                document.getElementById('membersTableBody').innerHTML = 
                    '<tr><td colspan="7" class="text-center text-danger">Failed to load members. Please try again.</td></tr>';
                document.getElementById('paginationInfo').textContent = 'Error loading members';
            });
    }
    
    // Function to update the members table
    function updateMembersTable(members) {
        const tableBody = document.getElementById('membersTableBody');
        
        if (members.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No members found</td></tr>';
            return;
        }
        
        let html = '';
        
        members.forEach((member, index) => {
            const isSelected = selectedMembers.has(parseInt(member.member_id));            const profilePic = member.profile_photo ? 
                `/Ekklessia-church-management/app/resources/assets/images/${member.profile_photo}` : 
                '/Ekklessia-church-management/app/resources/assets/images/default-profile.png';
            
            // Format roles as badges
            let rolesBadges = '';
            if (member.roles) {
                const roles = member.roles.split(', ');
                const primaryRole = member.primary_role || '';
                
                roles.forEach(role => {
                    const isPrimary = role === primaryRole;
                    rolesBadges += `<span class="role-badge${isPrimary ? ' primary' : ''}">${role}${isPrimary ? ' (Primary)' : ''}</span>`;
                });
            } else {
                rolesBadges = '<em class="text-muted">No roles assigned</em>';
            }
                
            html += `
                <tr${isSelected ? ' class="selected"' : ''} data-member-id="${member.member_id}" data-member-name="${member.first_name} ${member.last_name}" 
                    data-member-email="${member.email || ''}" data-member-phone="${member.contact || ''}" 
                    data-member-assembly="${member.assembly_name || 'Not Assigned'}" data-member-roles="${member.roles || ''}">
                    <td>
                        <div class="form-check">
                            <input class="form-check-input member-checkbox" type="checkbox" 
                                value="${member.member_id}" ${isSelected ? 'checked' : ''}>
                        </div>
                    </td>
                    <td>${(currentPage - 1) * 10 + index + 1}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="role-assignment-profile-img-container me-2">
                                <img src="${profilePic}" class="role-assignment-profile-img" alt="${member.first_name} ${member.last_name}" onerror="this.src='/Ekklessia-church-management/Public/assets/images/default-profile.png';">
                            </div>
                            <div class="member-name-btn btn-gradient-blue clickable" data-bs-toggle="modal" data-bs-target="#memberDetailsModal" 
                                data-member-id="${member.member_id}" data-member-name="${member.first_name} ${member.last_name}"
                                data-member-email="${member.email || 'Not provided'}" data-member-phone="${member.contact || 'Not provided'}"
                                data-member-assembly="${member.assembly_name || 'Not Assigned'}" data-member-roles="${member.roles || ''}"
                                data-member-photo="${profilePic}">
                                ${member.first_name} ${member.last_name}
                            </div>
                        </div>
                    </td>
                    <td>
                        ${member.contact ? `<div><i class="bi bi-telephone"></i> ${member.contact}</div>` : ''}
                        ${member.email ? `<div><i class="bi bi-envelope"></i> ${member.email}</div>` : ''}
                    </td>
                    <td>${member.assembly_name || '<em class="text-muted">Not Assigned</em>'}</td>
                    <td>${rolesBadges}</td>
                    <td>
                        <button class="btn btn-sm btn-primary assign-role-btn" data-member-id="${member.member_id}" 
                            data-member-name="${member.first_name} ${member.last_name}">
                            <i class="bi bi-person-gear"></i> Manage Roles
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tableBody.innerHTML = html;
        
        // Add event listeners to checkboxes
        document.querySelectorAll('.member-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', handleCheckboxChange);
        });
        
        // Add event listeners to assign role buttons
        document.querySelectorAll('.assign-role-btn').forEach(button => {
            button.addEventListener('click', handleAssignRoleClick);
        });
        
        // Add event listeners to member name buttons
        document.querySelectorAll('.member-name-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const dataset = e.target.dataset;
                openMemberDetailsModal(dataset);
            });
        });
        
        // Update bulk controls visibility
        updateBulkControls();
    }
    
    // Function to open member details modal
    function openMemberDetailsModal(memberData) {
        // Set modal content
        document.getElementById('modalMemberName').textContent = memberData.memberName || 'Unknown';
        document.getElementById('modalMemberEmail').textContent = memberData.memberEmail || 'Not provided';
        document.getElementById('modalMemberPhone').textContent = memberData.memberPhone || 'Not provided';
        document.getElementById('modalMemberAssembly').textContent = memberData.memberAssembly || 'Not assigned';
        document.getElementById('modalMemberId').textContent = memberData.memberId || 'Unknown';
        
        // Set member image
        const modalMemberImage = document.getElementById('modalMemberImage');
        modalMemberImage.src = memberData.memberPhoto || '/Ekklessia-church-management/Public/assets/images/default-profile.png';
        modalMemberImage.onerror = function() {
            this.src = '/Ekklessia-church-management/Public/assets/images/default-profile.png';
        };
        
        // Format quick role badges for the top section
        const modalQuickRoles = document.getElementById('modalQuickRoles');
        if (memberData.memberRoles) {
            const roles = memberData.memberRoles.split(', ');
            let quickBadges = '';
            
            roles.forEach(role => {
                // Check if this is the primary role
                const isPrimary = memberData.primaryRole && role === memberData.primaryRole;
                quickBadges += `
                    <span class="role-badge mb-2 me-2${isPrimary ? ' primary' : ''}">
                        ${role}${isPrimary ? ' (Primary)' : ''}
                    </span>
                `;
            });
            
            modalQuickRoles.innerHTML = quickBadges || '<em class="text-muted">No roles assigned</em>';
        } else {
            modalQuickRoles.innerHTML = '<em class="text-muted">No roles assigned</em>';
        }
        
        // Format roles as table rows
        const modalMemberRolesTable = document.getElementById('modalMemberRolesTable');
        if (memberData.memberRoles) {
            const roles = memberData.memberRoles.split(', ');
            let tableRows = '';
            
            // We would normally fetch hierarchies from server, but for demo we'll use placeholders
            const hierarchies = {
                'EXCO': 'National',
                'Zone Director': 'Zone',
                'Presiding Elder': 'Assembly',
                'Shepherd': 'Household'
            };
            
            roles.forEach(role => {
                const isPrimary = memberData.primaryRole && role === memberData.primaryRole;
                const hierarchy = hierarchies[role] || 'Assembly'; // Default to Assembly if not found
                
                tableRows += `
                    <tr>
                        <td>${role}</td>
                        <td>${hierarchy}</td>
                        <td>
                            ${isPrimary ? 
                                '<span class="badge role-status-badge badge-primary-role">Primary</span>' : 
                                '<span class="badge role-status-badge badge-active">Active</span>'}
                        </td>
                    </tr>
                `;
            });
            
            modalMemberRolesTable.innerHTML = tableRows || '<tr><td colspan="3" class="text-center"><em>No roles assigned</em></td></tr>';
        } else {
            modalMemberRolesTable.innerHTML = '<tr><td colspan="3" class="text-center"><em>No roles assigned</em></td></tr>';
        }
        
        // Set up the manage roles button
        const modalManageRolesBtn = document.getElementById('modalManageRolesBtn');
        modalManageRolesBtn.dataset.memberId = memberData.memberId;
        modalManageRolesBtn.dataset.memberName = memberData.memberName;
        
        modalManageRolesBtn.onclick = function() {
            // Close the details modal
            bootstrap.Modal.getInstance(document.getElementById('memberDetailsModal')).hide();
            
            // Open the assign role modal
            document.getElementById('memberName').textContent = memberData.memberName;
            document.getElementById('currentMemberId').value = memberData.memberId;
            loadMemberRoles(memberData.memberId);
            
            const assignRoleModal = new bootstrap.Modal(document.getElementById('assignRoleModal'));
            assignRoleModal.show();
        };
    }
    
    // Show member details in a toast
    function showMemberToast(memberData) {
        document.getElementById('toastMemberName').textContent = memberData.memberName || 'N/A';
        document.getElementById('toastMemberEmail').textContent = memberData.memberEmail || 'N/A';
        document.getElementById('toastMemberPhone').textContent = memberData.memberPhone || 'N/A';
        document.getElementById('toastMemberAssembly').textContent = memberData.memberAssembly || 'N/A';
        document.getElementById('toastMemberRoles').textContent = memberData.memberRoles || 'None';
        
        const toast = new bootstrap.Toast(document.getElementById('memberToast'));
        toast.show();
    }
    
    // Function to update pagination
    function updatePagination(pagination) {
        const paginationElement = document.getElementById('pagination');
        let html = '';
        
        // Previous button
        html += `
            <li class="page-item ${pagination.current_page <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${pagination.current_page - 1}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        `;
        
        // Page numbers
        const maxPages = 5;
        const startPage = Math.max(1, pagination.current_page - Math.floor(maxPages / 2));
        const endPage = Math.min(pagination.total_pages, startPage + maxPages - 1);
        
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        // Next button
        html += `
            <li class="page-item ${pagination.current_page >= pagination.total_pages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${pagination.current_page + 1}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        `;
        
        paginationElement.innerHTML = html;
        
        // Add event listeners to pagination links
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (!isNaN(page)) {
                    loadMembers(page);
                }
            });
        });
    }
    
    // Handle checkbox change for member selection
    function handleCheckboxChange(e) {
        const memberId = parseInt(e.target.value);
        const row = e.target.closest('tr');
        
        if (e.target.checked) {
            selectedMembers.add(memberId);
            row.classList.add('selected');
        } else {
            selectedMembers.delete(memberId);
            row.classList.remove('selected');
        }
        
        updateBulkControls();
    }
    
    // Update bulk controls visibility and counts
    function updateBulkControls() {
        const selectedCount = selectedMembers.size;
        const bulkControls = document.querySelector('.bulk-controls');
        const bulkAssignBtn = document.getElementById('bulkAssignBtn');
        
        // Update selected count
        document.getElementById('selectedCount').textContent = selectedCount;
        document.getElementById('bulkSelectedCount').textContent = selectedCount;
        
        // Show/hide bulk controls based on selection
        if (selectedCount > 0) {
            bulkControls.style.display = 'block';
        } else {
            bulkControls.style.display = 'none';
        }
        
        // Always keep bulk assign button enabled
        bulkAssignBtn.disabled = false;
        
        // Update "select all" checkbox
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const checkboxes = document.querySelectorAll('.member-checkbox');
        
        if (checkboxes.length > 0) {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const someChecked = Array.from(checkboxes).some(cb => cb.checked);
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
    }
    
    // Handle assign role button click
    function handleAssignRoleClick(e) {
        const memberId = e.target.closest('.assign-role-btn').dataset.memberId;
        const memberName = e.target.closest('.assign-role-btn').dataset.memberName;
        
        // Set modal title and member ID
        document.getElementById('memberName').textContent = memberName;
        document.getElementById('currentMemberId').value = memberId;
        
        // Load current roles for the member
        loadMemberRoles(memberId);
        
        // Show the modal
        const assignRoleModal = new bootstrap.Modal(document.getElementById('assignRoleModal'));
        assignRoleModal.show();
    }
    
    // Function to load member's current roles
    function loadMemberRoles(memberId) {
        const currentRolesDiv = document.getElementById('currentRoles');
        currentRolesDiv.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        
        fetch(`${window.location.origin}/Ekklessia-church-management/app/pages/roles/assignment/get_member_roles.php?member_id=${memberId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.roles.length === 0) {
                        currentRolesDiv.innerHTML = '<div class="text-muted text-center py-2">No roles assigned</div>';
                    } else {
                        let html = '<div class="list-group">';
                        
                        data.roles.forEach(role => {
                            html += `
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold">${role.role_name}</span>
                                        ${role.is_primary ? '<span class="badge bg-primary ms-2">Primary</span>' : ''}
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger remove-role-btn" 
                                        data-role-id="${role.role_id}" data-role-name="${role.role_name}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                        currentRolesDiv.innerHTML = html;
                        
                        // Add event listeners to remove role buttons
                        document.querySelectorAll('.remove-role-btn').forEach(button => {
                            button.addEventListener('click', handleRemoveRole);
                        });
                    }
                } else {
                    currentRolesDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading member roles:', error);
                currentRolesDiv.innerHTML = '<div class="alert alert-danger">Failed to load roles. Please try again.</div>';
            });
    }
    
    // Handle remove role button click
    function handleRemoveRole(e) {
        const roleId = e.target.closest('.remove-role-btn').dataset.roleId;
        const roleName = e.target.closest('.remove-role-btn').dataset.roleName;
        const memberId = document.getElementById('currentMemberId').value;
        
        if (!confirm(`Are you sure you want to remove the role "${roleName}" from this member?`)) {
            return;
        }
        
        // Send request to remove role
        fetch(`${window.location.origin}/Ekklessia-church-management/app/pages/roles/assignment/save_role_assignment.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                member_id: memberId,
                role_id: roleId,
                action: 'remove'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', `Role "${roleName}" has been removed`);
                // Reload member roles
                loadMemberRoles(memberId);
                
                // Reload members table to update current roles display
                loadMembers(currentPage);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error removing role:', error);
            showAlert('danger', 'Failed to remove role. Please try again.');
        });
    }
    
    // Handle add role button click
    document.getElementById('addRoleBtn').addEventListener('click', function() {
        const roleSelect = document.getElementById('roleSelect');
        const roleId = roleSelect.value;
        const roleName = roleSelect.options[roleSelect.selectedIndex].text;
        const makeRolePrimary = document.getElementById('makeRolePrimary').checked;
        const memberId = document.getElementById('currentMemberId').value;
        
        if (!roleId) {
            showAlert('danger', 'Please select a role to add');
            return;
        }
        
        // Add loading state
        const button = this;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
        
        // Send request to add role
        fetch(`${window.location.origin}/Ekklessia-church-management/app/pages/roles/assignment/save_role_assignment.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                member_id: memberId,
                role_id: roleId,
                action: 'add',
                make_primary: makeRolePrimary
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reset form
                document.getElementById('roleSelect').value = '';
                document.getElementById('makeRolePrimary').checked = false;
                document.getElementById('addRoleBtn').disabled = true;
                
                showAlert('success', `Role "${roleName}" has been added${makeRolePrimary ? ' as primary' : ''}`);
                
                // Reload member roles
                loadMemberRoles(memberId);
                
                // Reload members table to update current roles display
                loadMembers(currentPage);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error adding role:', error);
            showAlert('danger', 'Failed to add role. Please try again.');
        })
        .finally(() => {
            // Restore button state
            button.disabled = false;
            button.innerHTML = originalText;
        });
    });
    
    // Enable/disable add role button based on selection
    document.getElementById('roleSelect').addEventListener('change', function() {
        document.getElementById('addRoleBtn').disabled = !this.value;
    });
    
    // Handle remove all roles button click
    document.getElementById('removeAllRolesBtn').addEventListener('click', function() {
        const memberId = document.getElementById('currentMemberId').value;
        const memberName = document.getElementById('memberName').textContent;
        
        if (!confirm(`Are you sure you want to remove ALL roles from ${memberName}?`)) {
            return;
        }
        
        // Send request to remove all roles
        fetch(`${window.location.origin}/Ekklessia-church-management/app/pages/roles/assignment/remove_all_roles.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                member_id: memberId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', `All roles have been removed from ${memberName}`);
                
                // Reload member roles
                loadMemberRoles(memberId);
                
                // Reload members table to update current roles display
                loadMembers(currentPage);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error removing all roles:', error);
            showAlert('danger', 'Failed to remove all roles. Please try again.');
        });
    });
    
    // Handle bulk assign button click
    document.getElementById('bulkAssignBtn').addEventListener('click', function() {
        // Update selected count in modal
        document.getElementById('bulkSelectedCount').textContent = selectedMembers.size;
        
        // If no members selected, show alert and return
        if(selectedMembers.size === 0) {
            showAlert('warning', 'Please select at least one member to assign roles to');
            return;
        }
        
        // Reset form - uncheck all role checkboxes
        document.querySelectorAll('.bulk-role-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Reset primary role checkbox
        document.getElementById('bulkMakeRolePrimary').checked = false;
        
        // Disable confirm button until a role is selected
        document.getElementById('confirmBulkAssignBtn').disabled = true;
        
        // Show the modal
        const bulkAssignModal = new bootstrap.Modal(document.getElementById('bulkAssignModal'));
        bulkAssignModal.show();
    });
    
    // Enable/disable confirm bulk assign button based on checkbox selections
    document.querySelectorAll('.bulk-role-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const anyChecked = document.querySelectorAll('.bulk-role-checkbox:checked').length > 0;
            document.getElementById('confirmBulkAssignBtn').disabled = !anyChecked;
        });
    });
    
    // Handle confirm bulk assign button click
    document.getElementById('confirmBulkAssignBtn').addEventListener('click', function() {
        // Get all selected roles
        const selectedRoles = Array.from(document.querySelectorAll('.bulk-role-checkbox:checked')).map(checkbox => {
            return {
                id: checkbox.value,
                name: checkbox.nextElementSibling.textContent.trim()
            };
        });
        
        if (selectedRoles.length === 0) {
            showAlert('danger', 'Please select at least one role to assign');
            return;
        }
        
        // Get all selected role IDs
        const roleIds = selectedRoles.map(role => parseInt(role.id));
        
        // Check if we should make the first role primary
        const makePrimary = document.getElementById('bulkMakeRolePrimary').checked;
        
        // Get selected member IDs
        const memberIds = Array.from(selectedMembers);
        
        // Add loading state
        const button = this;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Assigning...';
        
        // Send request to bulk assign roles
        fetch(`${window.location.origin}/Ekklessia-church-management/app/pages/roles/assignment/bulk_assign_roles.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                member_ids: memberIds,
                role_ids: roleIds,
                make_primary: makePrimary
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the modal
                bootstrap.Modal.getInstance(document.getElementById('bulkAssignModal')).hide();
                
                // Show success message
                showAlert('success', data.message);
                
                // Clear selection
                selectedMembers.clear();
                updateBulkControls();
                
                // Reload members table to update roles
                loadMembers(currentPage);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error bulk assigning roles:', error);
            showAlert('danger', 'Failed to assign roles. Please try again.');
        })
        .finally(() => {
            // Restore button state
            button.disabled = false;
            button.innerHTML = originalText;
        });
    });
    
    // Handle select all checkbox
    document.getElementById('selectAllCheckbox').addEventListener('change', function(e) {
        const isChecked = e.target.checked;
        const checkboxes = document.querySelectorAll('.member-checkbox');
        const rows = document.querySelectorAll('#membersTableBody tr');
        
        checkboxes.forEach((checkbox, index) => {
            checkbox.checked = isChecked;
            const memberId = parseInt(checkbox.value);
            
            if (isChecked) {
                selectedMembers.add(memberId);
                rows[index].classList.add('selected');
            } else {
                selectedMembers.delete(memberId);
                rows[index].classList.remove('selected');
            }
        });
        
        updateBulkControls();
    });
    
    // Handle clear selection button
    document.getElementById('clearSelectionBtn').addEventListener('click', function() {
        selectedMembers.clear();
        document.querySelectorAll('.member-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.querySelectorAll('#membersTableBody tr.selected').forEach(row => {
            row.classList.remove('selected');
        });
        updateBulkControls();
    });
    
    // Handle search button click
    document.getElementById('searchBtn').addEventListener('click', function() {
        currentPage = 1;
        loadMembers(currentPage);
    });
    
    // Handle search on enter key press
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            currentPage = 1;
            loadMembers(currentPage);
        }
    });
    
    // Handle assembly filter change
    document.getElementById('assemblyFilter').addEventListener('change', function() {
        currentPage = 1;
        loadMembers(currentPage);
    });
    
    // Handle role filter change
    document.getElementById('roleFilter').addEventListener('change', function() {
        currentPage = 1;
        loadMembers(currentPage);
    });
    
    // Initial load of members
    loadMembers();
});

// Add this to your JavaScript section
function removeRole(memberId, roleId, roleName) {
    if (!confirm(`Are you sure you want to remove the ${roleName} role?`)) {
        return;
    }
    
    fetch(`${window.location.origin}/Ekklessia-church-management/app/pages/roles/assignment/remove_role.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            member_id: memberId,
            role_id: roleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            // Refresh the roles display
            loadMemberRoles(memberId);
            
            // If this was a primary role, we may need to refresh the member's info
            if (data.was_primary) {
                refreshMemberDisplay(memberId);
            }
        } else {
            toastr.error(data.message || 'Failed to remove role');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Failed to remove role. Please try again.');
    });
}

// Add the role removal button handler
document.addEventListener('click', function(e) {
    if (e.target.matches('.remove-role-btn') || e.target.closest('.remove-role-btn')) {
        const btn = e.target.matches('.remove-role-btn') ? e.target : e.target.closest('.remove-role-btn');
        e.preventDefault();
        const memberId = btn.dataset.memberId;
        const roleId = btn.dataset.roleId;
        const roleName = btn.dataset.roleName;
        removeRole(memberId, roleId, roleName);
    }
});

// Update your role item template to include data attributes
function createRoleItem(role, memberId) {
    return `
        <div class="role-item d-flex justify-content-between align-items-center mb-2">
            <div>
                <span class="role-name">${role.role_name}</span>
                ${role.is_primary ? '<span class="badge bg-primary ms-2">Primary</span>' : ''}
            </div>
            <button class="btn btn-sm btn-danger remove-role-btn" 
                    data-member-id="${memberId}"
                    data-role-id="${role.role_id}"
                    data-role-name="${role.role_name}">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
}

// Add this helper function to refresh member display
function refreshMemberDisplay(memberId) {
    // Refresh the member's row in the table
    fetch(`${window.location.origin}/Ekklessia-church-management/app/pages/roles/assignment/get_member_info.php?member_id=${memberId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the member's row with new information
                const memberRow = document.querySelector(`tr[data-member-id="${memberId}"]`);
                if (memberRow) {
                    updateMemberRow(memberRow, data.member);
                }
            }
        })
        .catch(error => console.error('Error refreshing member display:', error));
}
</script>
</body>
</html>
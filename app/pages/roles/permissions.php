<?php
session_start();
$page_title = "Permission Management";
include "../../config/config.php";
include "../auth/auth_check.php";

// Fetch total permissions count for dashboard
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_permissions FROM permissions");
    $total_permissions = $stmt->fetch(PDO::FETCH_ASSOC)['total_permissions'];
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching permissions: " . $e->getMessage();
    $total_permissions = 0;
}

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../includes/header.php"; ?>
<body class="d-flex flex-column min-vh-100">
<main class="container flex-grow-1 py-2">
    <!-- Success/Error Alerts -->
    <?php include "../../includes/alerts.php"; ?>

    <!-- Navigation Card -->
    <?php include "../../includes/nav_card.php"; ?>

    <!-- Mini Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm bg-gradient-primary text-white">
                <div class="card-body text-center">
                    <i class="bi bi-shield-lock fs-2"></i>
                    <h6 class="card-title">Total Permissions</h6>
                    <h3 class="card-text"><?= $total_permissions ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Permission Management Section -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Permission Management</h4>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#permissionModal">
                    <i class="bi bi-plus-circle"></i> Add New Permission
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="filterPermissionName" class="form-label">
                        <i class="bi bi-search me-2"></i>Permission Name
                    </label>
                    <input type="text" class="form-control" id="filterPermissionName" placeholder="Search permissions">
                </div>
                <div class="col-md-3">
                    <label for="filterUsage" class="form-label">
                        <i class="bi bi-filter me-2"></i>Usage
                    </label>
                    <select class="form-select" id="filterUsage">
                        <option value="">All</option>
                        <option value="assigned">In Use</option>
                        <option value="unassigned">Not In Use</option>
                    </select>
                </div>
            </div>
            <!-- Permission Groups -->
            <div class="accordion" id="permissionAccordion">
                <?php
                require_once "../../functions/permission_groups.php";
                $groups = getPermissionGroups();
                
                foreach ($groups as $groupKey => $group): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading_<?= $groupKey ?>">
                            <button class="accordion-button <?= $groupKey === 'scope_management' ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse_<?= $groupKey ?>" aria-expanded="<?= $groupKey === 'scope_management' ? 'true' : 'false' ?>" 
                                    aria-controls="collapse_<?= $groupKey ?>">
                                <?= htmlspecialchars($group['title']) ?>
                            </button>
                        </h2>
                        <div id="collapse_<?= $groupKey ?>" class="accordion-collapse collapse <?= $groupKey === 'scope_management' ? 'show' : '' ?>" 
                             aria-labelledby="heading_<?= $groupKey ?>" data-bs-parent="#permissionAccordion">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle permission-table" 
                                           data-group="<?= $groupKey ?>">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Permission Name</th>
                                                <th>Description</th>
                                                <th>Used By Roles</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            try {
                                                // Get all permissions with their roles for this group
                                                $stmt = $pdo->prepare("
                                                    SELECT 
                                                        p.permission_id,
                                                        p.permission_name,
                                                        p.description,
                                                        p.created_at,
                                                        GROUP_CONCAT(DISTINCT r.role_name ORDER BY r.role_name) as roles
                                                    FROM permissions p
                                                    LEFT JOIN role_church_function_permissions rp ON p.permission_id = rp.permission_id
                                                    LEFT JOIN roles r ON rp.role_id = r.role_id
                                                    WHERE p.permission_name IN ('" . implode("','", $group['permissions']) . "')
                                                    GROUP BY p.permission_id
                                                    ORDER BY p.permission_name
                                                ");
                                                $stmt->execute();
                                                $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                if (empty($permissions)) {
                                                    echo "<tr><td colspan='6' class='text-center'>No permissions found in this group. Click 'Add New Permission' to create one.</td></tr>";
                                                } else {
                                                    $rowIndex = 1;
                                                    foreach ($permissions as $permission) {
                                                        echo "<tr>";
                                                        echo "<td>" . $rowIndex++ . "</td>";
                                                        echo "<td>" . htmlspecialchars($permission['permission_name']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($permission['description'] ?? 'No description') . "</td>";
                                                        echo "<td><small class='text-" . ($permission['roles'] ? 'success' : 'muted') . "'>" 
                                                             . htmlspecialchars($permission['roles'] ?? 'Not assigned') . "</small></td>";
                                                        echo "<td>" . date('Y-m-d', strtotime($permission['created_at'])) . "</td>";
                                                        echo "<td>";
                                                        echo "<div class='btn-group btn-group-sm' role='group'>";
                                                        echo "<button class='btn btn-primary edit-permission-btn' 
                                                                data-permission-id='" . $permission['permission_id'] . "'
                                                                data-permission-name='" . htmlspecialchars($permission['permission_name']) . "'
                                                                data-permission-desc='" . htmlspecialchars($permission['description'] ?? '') . "'
                                                                data-permission-group='" . htmlspecialchars($groupKey) . "'
                                                                data-bs-toggle='modal' 
                                                                data-bs-target='#permissionModal'>
                                                                <i class='bi bi-pencil'></i>
                                                              </button>";
                                                        echo "<button class='btn btn-sm btn-danger delete-permission-btn' 
                                                                data-permission-id='" . $permission['permission_id'] . "'>
                                                                <i class='bi bi-trash'></i>
                                                              </button>";
                                                        echo "</div>";
                                                        echo "</td>";
                                                        echo "</tr>";
                                                    }
                                                }
                                            } catch (PDOException $e) {
                                                echo "<tr><td colspan='6' class='text-danger'>Error fetching permissions: " . 
                                                     htmlspecialchars($e->getMessage()) . "</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Add/Edit Permission Modal -->
            <div class="modal fade" id="permissionModal" tabindex="-1" aria-labelledby="permissionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="permissionModalLabel">Add/Edit Permission</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="permissionForm" action="manage_permissions_process.php" method="POST">
                                <input type="hidden" id="permissionId" name="permission_id">
                                <div class="mb-3">
                                    <label for="permissionGroup" class="form-label">Permission Group <span class="text-danger">*</span></label>
                                    <select class="form-select" id="permissionGroup" name="group" required>
                                        <option value="">Select Group</option>
                                        <?php
                                        foreach ($groups as $groupKey => $group): ?>
                                            <option value="<?= htmlspecialchars($groupKey) ?>"><?= htmlspecialchars($group['title']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="permissionName" class="form-label">Permission Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="permissionName" name="permission_name" required>
                                    <small class="form-text text-muted">Use lowercase letters and underscores, e.g., 'manage_members'</small>
                                </div>
                                <div class="mb-3">
                                    <label for="permissionDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="permissionDescription" name="description" rows="3"></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Permission</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include "../../includes/footer.php"; ?>
<script src="../../assets/js/permissions.js"></script>
</body>
</html>
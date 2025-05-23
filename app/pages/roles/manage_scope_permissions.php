<?php
/**
 * Manage Scope Permissions Page
 * 
 * This page allows administrators to manually assign scope permissions to roles
 */

require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../functions/permission_constants.php';
require_once '../../functions/role_functions.php';
require_once '../auth/auth_check.php';

// Ensure user has permission to manage roles
if (!memberHasPermission($_SESSION['member_id'], 'manage_roles')) {
    $_SESSION['error'] = "You do not have permission to manage scope permissions.";
    header("Location: index.php");
    exit;
}

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_scope_permissions'])) {
    $roleId = isset($_POST['role_id']) ? intval($_POST['role_id']) : 0;
    $scopeId = isset($_POST['scope_id']) ? intval($_POST['scope_id']) : 0;
    $selectedPermissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    
    if ($roleId && $scopeId && !empty($selectedPermissions)) {
        try {
            global $pdo;
            
            // Begin transaction
            $pdo->beginTransaction();
            
            // First remove existing scope permissions for this role and scope
            $stmt = $pdo->prepare("DELETE FROM scope_permissions WHERE role_id = ? AND scope_id = ?");
            $stmt->execute([$roleId, $scopeId]);
            
            // Insert new permissions
            $insertStmt = $pdo->prepare("
                INSERT INTO scope_permissions (role_id, scope_id, permission_id, created_by)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($selectedPermissions as $permissionId) {
                $insertStmt->execute([
                    $roleId, 
                    $scopeId, 
                    $permissionId, 
                    $_SESSION['username']
                ]);
            }
            
            $pdo->commit();
            $successMessage = "Scope permissions successfully assigned!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $errorMessage = "Error: " . $e->getMessage();
            error_log("Error assigning scope permissions: " . $e->getMessage());
        }
    } else {
        $errorMessage = "Please select a role, scope, and at least one permission.";
    }
}

// Get all roles
$stmt = $pdo->prepare("SELECT role_id, role_name FROM roles ORDER BY role_name");
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all scopes
$stmt = $pdo->prepare("SELECT scope_id, scope_name FROM scopes ORDER BY scope_name");
$stmt->execute();
$scopes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all permissions
$stmt = $pdo->prepare("SELECT permission_id, permission_name, description FROM permissions ORDER BY permission_name");
$stmt->execute();
$permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Page title
$pageTitle = "Manage Scope Permissions";

// Include header
include_once '../../includes/header.php';

// Define function to check if permission is already assigned
function isPermissionAssigned($roleId, $scopeId, $permissionId, $pdo) {
    if (!$roleId || !$scopeId) return false;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM scope_permissions 
        WHERE role_id = ? AND scope_id = ? AND permission_id = ?
    ");
    $stmt->execute([$roleId, $scopeId, $permissionId]);
    return $stmt->fetchColumn() > 0;
}

// Get selected role and scope for AJAX loading
$selectedRoleId = isset($_GET['role_id']) ? intval($_GET['role_id']) : 0;
$selectedScopeId = isset($_GET['scope_id']) ? intval($_GET['scope_id']) : 0;
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../Public/index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="index.php">Roles Management</a></li>
        <li class="breadcrumb-item active"><?= $pageTitle ?></li>
    </ol>
    
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?= $successMessage ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?= $errorMessage ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-key me-1"></i>
            Assign Scope Permissions
        </div>
        <div class="card-body">
            <form id="scopePermissionForm" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="role_id" class="form-label">Select Role</label>
                        <select class="form-select" id="role_id" name="role_id" required>
                            <option value="">-- Select Role --</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['role_id'] ?>" <?= $selectedRoleId == $role['role_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['role_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="scope_id" class="form-label">Select Scope</label>
                        <select class="form-select" id="scope_id" name="scope_id" required>
                            <option value="">-- Select Scope --</option>
                            <?php foreach ($scopes as $scope): ?>
                                <option value="<?= $scope['scope_id'] ?>" <?= $selectedScopeId == $scope['scope_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($scope['scope_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header">Select Permissions</div>
                    <div class="card-body">
                        <div id="permissionsContainer" class="row">
                            <?php if ($selectedRoleId && $selectedScopeId): ?>
                                <?php foreach ($permissions as $permission): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                name="permissions[]" 
                                                value="<?= $permission['permission_id'] ?>" 
                                                id="permission_<?= $permission['permission_id'] ?>"
                                                <?= isPermissionAssigned($selectedRoleId, $selectedScopeId, $permission['permission_id'], $pdo) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="permission_<?= $permission['permission_id'] ?>">
                                                <?= htmlspecialchars($permission['permission_name']) ?>
                                                <small class="d-block text-muted"><?= htmlspecialchars($permission['description']) ?></small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center">
                                    <p class="text-muted">Please select a role and scope to view and assign permissions.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <button type="submit" name="assign_scope_permissions" class="btn btn-primary">
                        Save Scope Permissions
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle role and scope selection changes
    const roleSelect = document.getElementById('role_id');
    const scopeSelect = document.getElementById('scope_id');
    
    function updatePermissionsView() {
        const roleId = roleSelect.value;
        const scopeId = scopeSelect.value;
        
        if (roleId && scopeId) {
            window.location.href = `manage_scope_permissions.php?role_id=${roleId}&scope_id=${scopeId}`;
        }
    }
    
    roleSelect.addEventListener('change', updatePermissionsView);
    scopeSelect.addEventListener('change', updatePermissionsView);
});
</script>

<?php include_once '../../includes/footer.php'; ?>
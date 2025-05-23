<?php
session_start();
include "../../config/config.php";
include "../../functions/role_management.php";
include "../auth/auth_check.php";

$page_title = "Role Management";

// Get all roles
$roles = getAllRoles();
// Get all permissions
$permissions = getAllPermissions();

?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../includes/header.php"; ?>
<body class="d-flex flex-column min-vh-100">
    <?php include "../../includes/alerts.php"; ?>
    
    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?= $page_title ?></h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="bi bi-plus-circle me-2"></i>Add New Role
            </button>
        </div>
        
        <!-- Roles Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Role Name</th>
                                <th>Hierarchy Level</th>
                                <th>Description</th>
                                <th>Permissions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td><?= htmlspecialchars($role['role_name']) ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= htmlspecialchars($role['hierarchy_level']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($role['description'] ?? '') ?></td>
                                    <td>
                                        <?php
                                        $rolePermissions = getRolePermissions($role['role_id']);
                                        foreach ($rolePermissions as $perm): ?>
                                            <span class="badge bg-secondary me-1">
                                                <?= htmlspecialchars($perm['permission_name']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary edit-role" 
                                                    data-role-id="<?= $role['role_id'] ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editRoleModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-role" 
                                                    data-role-id="<?= $role['role_id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Role Modal -->
        <div class="modal fade" id="addRoleModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addRoleForm" action="save_role.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Role Name</label>
                                <input type="text" class="form-control" name="role_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hierarchy Level</label>
                                <select class="form-select" name="hierarchy_level" required>
                                    <option value="">Select Level</option>
                                    <option value="National">National</option>
                                    <option value="Zone">Zone</option>
                                    <option value="Assembly">Assembly</option>
                                    <option value="Household">Household</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Permissions</label>
                                <div class="row g-3">
                                    <?php foreach ($permissions as $permission): ?>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="permissions[]" 
                                                       value="<?= $permission['permission_id'] ?>"
                                                       id="perm_<?= $permission['permission_id'] ?>">
                                                <label class="form-check-label" 
                                                       for="perm_<?= $permission['permission_id'] ?>">
                                                    <?= htmlspecialchars($permission['permission_name']) ?>
                                                    <?php if ($permission['description']): ?>
                                                        <small class="text-muted d-block">
                                                            <?= htmlspecialchars($permission['description']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Role</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Role Modal -->
        <div class="modal fade" id="editRoleModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editRoleForm" action="save_role.php" method="POST">
                            <input type="hidden" name="role_id" value="">
                            <div class="mb-3">
                                <label class="form-label">Role Name</label>
                                <input type="text" class="form-control" name="role_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hierarchy Level</label>
                                <select class="form-select" name="hierarchy_level" required>
                                    <option value="">Select Level</option>
                                    <option value="National">National</option>
                                    <option value="Zone">Zone</option>
                                    <option value="Assembly">Assembly</option>
                                    <option value="Household">Household</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Permissions</label>
                                <div class="row g-3">
                                    <?php foreach ($permissions as $permission): ?>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="permissions[]" 
                                                       value="<?= $permission['permission_id'] ?>"
                                                       id="edit_perm_<?= $permission['permission_id'] ?>">
                                                <label class="form-check-label" 
                                                       for="edit_perm_<?= $permission['permission_id'] ?>">
                                                    <?= htmlspecialchars($permission['permission_name']) ?>
                                                    <?php if ($permission['description']): ?>
                                                        <small class="text-muted d-block">
                                                            <?= htmlspecialchars($permission['description']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update Role</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include "../../includes/footer.php"; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle role deletion
        document.querySelectorAll('.delete-role').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this role?')) {
                    const roleId = this.dataset.roleId;
                    fetch('delete_role.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `role_id=${roleId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Error deleting role');
                        }
                    });
                }
            });
        });

        // Handle role editing
        document.querySelectorAll('.edit-role').forEach(button => {
            button.addEventListener('click', function() {
                const roleId = this.dataset.roleId;
                fetch(`get_role.php?role_id=${roleId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Populate edit form with role data
                            const form = document.querySelector('#editRoleForm');
                            form.elements['role_id'].value = data.role.role_id;
                            form.elements['role_name'].value = data.role.role_name;
                            form.elements['hierarchy_level'].value = data.role.hierarchy_level;
                            form.elements['description'].value = data.role.description || '';
                            
                            // Reset and set permissions
                            form.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
                                checkbox.checked = data.permissions.includes(parseInt(checkbox.value));
                            });
                        }
                    });
            });
        });
    });
    </script>
</body>
</html>
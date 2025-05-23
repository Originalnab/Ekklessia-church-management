<?php
session_start();
$page_title = "Role Management";
include "../../config/config.php";
include "../auth/auth_check.php";
include "../../functions/role_management.php";
include "../../functions/permission_groups.php";

// Removed permission check to allow anyone to access this page

// Set highest hierarchy level to allow managing all levels
$memberHierarchy = 'National';

// Fetch all available scopes for the form
try {
    $scopeStmt = $pdo->prepare("SELECT scope_id, scope_name, description FROM scopes ORDER BY scope_id");
    $scopeStmt->execute();
    $scopes = $scopeStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching scopes: " . $e->getMessage());
    $_SESSION['error_message'] = "Error fetching scopes: " . $e->getMessage();
    $scopes = [];
}

// Fetch total roles count for dashboard
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_roles FROM roles");
    $stmt->execute();
    $total_roles = $stmt->fetch(PDO::FETCH_ASSOC)['total_roles'];
} catch (PDOException $e) {
    error_log("Error fetching total roles: " . $e->getMessage());
    $_SESSION['error_message'] = "Error fetching total roles: " . $e->getMessage();
    $total_roles = 0;
}

// Get role statistics
try {
    $statsQuery = "
        SELECT 
            (SELECT COUNT(DISTINCT member_id) FROM member_role) as members_with_roles,
            (SELECT COUNT(*) FROM members) as total_members
    ";
    $statsStmt = $pdo->query($statsQuery);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    $membersWithRoles = (int)$stats['members_with_roles'];
    $totalMembers = (int)$stats['total_members'];
    $membersWithoutRoles = $totalMembers - $membersWithRoles;
} catch (PDOException $e) {
    error_log("Error fetching role statistics: " . $e->getMessage());
    $membersWithRoles = 0;
    $totalMembers = 0;
    $membersWithoutRoles = 0;
}

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../includes/header.php"; ?>

<!-- Content Area -->
<div class="container py-4">
    <!-- Success/Error Alerts -->
    <?php include "../../includes/alerts.php"; ?>

    <!-- Navigation Card -->
    <?php include "../../includes/nav_card.php"; ?>

    <!-- Mini Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm bg-gradient-primary text-white">
                <div class="card-body text-center">
                    <i class="bi bi-shield-fill fs-2"></i>
                    <h6 class="card-title">Total Roles</h6>
                    <h3 class="card-text"><?= $total_roles ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Management Section -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Role Management</h4>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                    <i class="bi bi-plus-circle"></i> Add New Role
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="filterRoleName" class="form-label"><i class="bi bi-search me-2"></i>Role Name</label>
                    <input type="text" class="form-control" id="filterRoleName" placeholder="Search by role name">
                </div>
            </div>

            <!-- Roles Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="rolesTable">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAllRoles">
                                </div>
                            </th>
                            <th>#</th>
                            <th>Role Name</th>
                            <th>Scopes</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="rolesTableBody">
                        <!-- Roles will be loaded here dynamically -->
                    </tbody>
                </table>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted" id="tableInfo">
                        Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span id="totalEntries">0</span> entries
                    </div>
                    <nav aria-label="Role navigation">
                        <ul class="pagination" id="rolePagination">
                            <!-- Pagination will be generated here -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Role</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addRoleForm">
                    <div class="mb-3">
                        <label class="form-label required">Role Name</label>
                        <input type="text" class="form-control" name="role_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Scope <small class="text-muted">(Select all that apply)</small></label>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach ($scopes as $scope): ?>
                            <div class="form-check">
                                <input class="form-check-input scope-checkbox" type="checkbox" 
                                    name="scope_ids[]" 
                                    value="<?= $scope['scope_id'] ?>" 
                                    id="scope_<?= $scope['scope_id'] ?>">
                                <label class="form-check-label" for="scope_<?= $scope['scope_id'] ?>">
                                    <?= ucfirst(htmlspecialchars($scope['scope_name'])) ?>
                                    <small class="d-block text-muted"><?= htmlspecialchars($scope['description']) ?></small>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="invalid-feedback">Please select at least one scope.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="accordion" id="permissionsAccordion">
                            <?php
                            $permissionGroups = getPermissionGroups();
                            foreach ($permissionGroups as $groupKey => $group):
                                $permissions = getPermissionsByGroup($groupKey);
                            ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse_<?= $groupKey ?>"
                                                aria-expanded="false" 
                                                aria-controls="collapse_<?= $groupKey ?>">
                                            <?= htmlspecialchars($group['title']) ?>
                                        </button>
                                    </h2>
                                    <div id="collapse_<?= $groupKey ?>" class="accordion-collapse collapse" 
                                         data-bs-parent="#permissionsAccordion">
                                        <div class="accordion-body">
                                            <div class="row g-3">
                                                <?php foreach ($permissions as $permission): ?>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                name="permissions[]" 
                                                                value="<?= $permission ?>" 
                                                                id="perm_<?= $permission ?>">
                                                            <label class="form-check-label" for="perm_<?= $permission ?>">
                                                                <?= htmlspecialchars($permission) ?>
                                                                <small class="d-block text-muted">
                                                                    <?= htmlspecialchars(getPermissionDescription($permission)) ?>
                                                                </small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveRole()">Save Role</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Role</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editRoleForm">
                    <input type="hidden" name="role_id">
                    <div class="mb-3">
                        <label class="form-label required">Role Name</label>
                        <input type="text" class="form-control" name="role_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Scope <small class="text-muted">(Select all that apply)</small></label>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach ($scopes as $scope): ?>
                            <div class="form-check">
                                <input class="form-check-input scope-checkbox" type="checkbox" 
                                    name="scope_ids[]" 
                                    value="<?= $scope['scope_id'] ?>" 
                                    id="edit_scope_<?= $scope['scope_id'] ?>">
                                <label class="form-check-label" for="edit_scope_<?= $scope['scope_id'] ?>">
                                    <?= ucfirst(htmlspecialchars($scope['scope_name'])) ?>
                                    <small class="d-block text-muted"><?= htmlspecialchars($scope['description']) ?></small>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="invalid-feedback">Please select at least one scope.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="accordion" id="editPermissionsAccordion">
                            <?php
                            foreach ($permissionGroups as $groupKey => $group):
                                $permissions = getPermissionsByGroup($groupKey);
                            ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#edit_collapse_<?= $groupKey ?>"
                                                aria-expanded="false" 
                                                aria-controls="edit_collapse_<?= $groupKey ?>">
                                            <?= htmlspecialchars($group['title']) ?>
                                        </button>
                                    </h2>
                                    <div id="edit_collapse_<?= $groupKey ?>" class="accordion-collapse collapse" 
                                         data-bs-parent="#editPermissionsAccordion">
                                        <div class="accordion-body">
                                            <div class="row g-3">
                                                <?php foreach ($permissions as $permission): ?>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                name="permissions[]" 
                                                                value="<?= $permission ?>" 
                                                                id="edit_perm_<?= $permission ?>">
                                                            <label class="form-check-label" for="edit_perm_<?= $permission ?>">
                                                                <?= htmlspecialchars($permission) ?>
                                                                <small class="d-block text-muted">
                                                                    <?= htmlspecialchars(getPermissionDescription($permission)) ?>
                                                                </small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateRole()">Update Role</button>
            </div>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>

<script>
let currentPage = 1;
const itemsPerPage = 10;

function loadRoles(page = 1) {
    const filterName = document.getElementById('filterRoleName').value;
    
    fetch(`fetch_paginated_roles.php?page=${page}&name=${filterName}`)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('rolesTableBody');
            tableBody.innerHTML = '';
            
            data.roles.forEach((role, index) => {
                const tr = document.createElement('tr');
                
                // Generate scope badges HTML
                let scopeBadges = '';
                if (role.scopes && role.scopes.length > 0) {
                    scopeBadges = role.scopes.map(scope => {
                        const badgeClass = getScopeBadgeClass(scope.scope_name);
                        return `<span class="badge bg-${badgeClass} me-1" title="${scope.description}">${capitalizeFirstLetter(scope.scope_name)}</span>`;
                    }).join(' ');
                } else {
                    // Fallback to hierarchy level if no scopes are defined
                    scopeBadges = `<span class="badge bg-${getBadgeClass(role.hierarchy_level)}">${role.hierarchy_level}</span>`;
                }
                
                tr.innerHTML = `
                    <td>
                        <div class="form-check">
                            <input class="form-check-input role-checkbox" type="checkbox" value="${role.role_id}" id="role_${role.role_id}">
                        </div>
                    </td>
                    <td>${(page - 1) * itemsPerPage + index + 1}</td>
                    <td>${role.role_name}</td>
                    <td>${scopeBadges}</td>
                    <td>
                        ${role.permissions ? role.permissions.split(',').map(p =>
                            `<span class="badge bg-secondary me-1">${p.trim()}</span>`).join('') : ''}
                    </td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary edit-role" 
                                    data-role-id="${role.role_id}"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editRoleModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-role" 
                                    data-role-id="${role.role_id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tableBody.appendChild(tr);
            });

            // Update pagination and stats
            updatePagination(data.total_pages, page);
            currentPage = page;
            
            // Update table info
            const showingStart = data.roles.length > 0 ? ((page - 1) * itemsPerPage) + 1 : 0;
            const showingEnd = showingStart + data.roles.length - 1;
            document.getElementById('showingStart').textContent = showingStart;
            document.getElementById('showingEnd').textContent = showingEnd > 0 ? showingEnd : 0;
            document.getElementById('totalEntries').textContent = data.total_roles || 0;
        })
        .catch(error => {
            console.error('Error loading roles:', error);
            showAlert('Error loading roles. Please try again.', 'danger');
        });
}

// Helper function to get badge class for scope
function getScopeBadgeClass(scopeName) {
    const classes = {
        'household': 'info',
        'assembly': 'success',
        'zone': 'warning',
        'national': 'danger'
    };
    return classes[scopeName.toLowerCase()] || 'secondary';
}

// Helper function to capitalize first letter
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function updatePagination(totalPages, currentPage) {
    const pagination = document.getElementById('rolePagination');
    pagination.innerHTML = '';
    
    // Previous button
    pagination.innerHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadRoles(${currentPage - 1})">&laquo;</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        pagination.innerHTML += `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadRoles(${i})">${i}</a>
            </li>
        `;
    }
    
    // Next button
    pagination.innerHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadRoles(${currentPage + 1})">&raquo;</a>
        </li>
    `;
}

// Initialize roles table
document.addEventListener('DOMContentLoaded', () => {
    loadRoles();
    
    // Add filter listener
    document.getElementById('filterRoleName').addEventListener('input', debounce(() => {
        loadRoles(1);
    }, 300));
});

// Debounce function to limit API calls while typing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add hierarchy level check to role operations
function canManageRole(roleHierarchy) {
    const memberHierarchy = '<?php echo $memberHierarchy; ?>';
    const hierarchyLevels = ['National', 'Zone', 'Assembly', 'Household'];
    const memberLevel = hierarchyLevels.indexOf(memberHierarchy);
    const roleLevel = hierarchyLevels.indexOf(roleHierarchy);
    
    return roleLevel >= memberLevel;
}

function getBadgeClass(hierarchyLevel) {
    const classes = {
        'National': 'danger',
        'Zone': 'warning',
        'Assembly': 'success',
        'Household': 'info'
    };
    return classes[hierarchyLevel] || 'secondary';
}

function updateStats(stats) {
    if (stats) {
        document.getElementById('totalRoles').textContent = stats.total_roles || 0;
        document.getElementById('membersWithRoles').textContent = stats.members_with_roles || 0;
        document.getElementById('membersWithoutRoles').textContent = stats.members_without_roles || 0;
    }
}

function saveRole() {
    const form = document.getElementById('addRoleForm');
    const formData = new FormData(form);
    
    // Validate at least one scope is selected
    const selectedScopes = form.querySelectorAll('input[name="scope_ids[]"]:checked');
    if (selectedScopes.length === 0) {
        showAlert('Please select at least one scope', 'danger');
        return;
    }
    
    // Show loading state
    const submitButton = document.querySelector('#addRoleModal .btn-primary');
    const originalText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
    
    fetch('save_role.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message || 'Role saved successfully!', 'success');
            
            // Properly close the modal using Bootstrap's API
            const modalElement = document.getElementById('addRoleModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide();
            
            // Reset form properly
            form.reset();
            
            // Reload roles table to show the new role
            loadRoles(1);
        } else {
            throw new Error(data.message || 'Failed to save role');
        }
    })
    .catch(error => {
        console.error('Error saving role:', error);
        showAlert(error.message, 'danger');
    })
    .finally(() => {
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}

// Handle edit role button clicks
document.addEventListener('click', function(e) {
    if (e.target.closest('.edit-role')) {
        const button = e.target.closest('.edit-role');
        const roleId = button.dataset.roleId;
        
        // Show loading state in the modal
        const modalBody = document.querySelector('#editRoleModal .modal-body');
        const origForm = modalBody.innerHTML;
        modalBody.innerHTML = `<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading role data...</p></div>`;
        
        // Fetch role data
        fetch(`get_role.php?role_id=${roleId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Restore the original form structure
                    modalBody.innerHTML = origForm;
                    
                    // Get the form element after restoring it
                    const form = document.getElementById('editRoleForm');
                    
                    // Set form values
                    form.querySelector('input[name="role_id"]').value = roleId;
                    form.querySelector('input[name="role_name"]').value = data.role.role_name;
                    form.querySelector('textarea[name="description"]').value = data.role.description || '';
                    
                    // Reset all checkboxes first
                    form.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    
                    console.log("Loading scopes:", data.scopes);
                    // Set scopes
                    if (data.scopes && Array.isArray(data.scopes)) {
                        data.scopes.forEach(scopeId => {
                            const scopeCheckbox = form.querySelector(`input[name="scope_ids[]"][value="${scopeId}"]`);
                            if (scopeCheckbox) {
                                scopeCheckbox.checked = true;
                            } else {
                                console.warn(`Scope checkbox not found for scope ID: ${scopeId}`);
                            }
                        });
                    } else if (data.role.scope_id) {
                        // Fallback to the old single scope_id if role_scopes not implemented yet
                        const scopeCheckbox = form.querySelector(`input[name="scope_ids[]"][value="${data.role.scope_id}"]`);
                        if (scopeCheckbox) {
                            scopeCheckbox.checked = true;
                        }
                    }
                    
                    // Set permissions using permission names
                    if (data.permission_names && Array.isArray(data.permission_names)) {
                        data.permission_names.forEach(permName => {
                            const permCheckbox = form.querySelector(`input[name="permissions[]"][value="${permName}"]`);
                            if (permCheckbox) {
                                permCheckbox.checked = true;
                                // Open the accordion group
                                const accordionCollapse = permCheckbox.closest('.accordion-collapse');
                                if (accordionCollapse) {
                                    accordionCollapse.classList.add('show');
                                }
                            }
                        });
                    } else if (data.permissions && Array.isArray(data.permissions)) {
                        // Fallback to permission IDs
                        data.permissions.forEach(permId => {
                            // Try to find checkboxes that might be storing IDs as values
                            const permCheckboxes = form.querySelectorAll('input[name="permissions[]"]');
                            permCheckboxes.forEach(checkbox => {
                                if (checkbox.dataset.permissionId === String(permId)) {
                                    checkbox.checked = true;
                                }
                            });
                        });
                    }
                    
                    // Add visual indication for selected checkboxes
                    form.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
                        const label = checkbox.closest('label') || checkbox.nextElementSibling;
                        if (label) {
                            label.style.fontWeight = 'bold';
                        }
                    });
                } else {
                    showAlert('Failed to load role data: ' + data.message, 'danger');
                    // Restore the form but clear values
                    modalBody.innerHTML = origForm;
                }
            })
            .catch(error => {
                console.error('Error loading role:', error);
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading role data. Please try again.</div>`;
                // After a short delay, restore the form
                setTimeout(() => {
                    modalBody.innerHTML = origForm;
                }, 3000);
            });
    }
});

// Reset form when opening modal for new role
document.querySelector('[data-bs-target="#addRoleModal"]').addEventListener('click', function(e) {
    if (!e.target.closest('.edit-role')) {
        const form = document.getElementById('addRoleForm');
        form.reset();
        // Remove any existing role ID input
        const roleIdInput = form.querySelector('input[name="role_id"]');
        if (roleIdInput) roleIdInput.remove();
        
        // Reset modal title
        const modalTitle = document.querySelector('#addRoleModal .modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Add New Role';
        }

        // Uncheck all permissions
        const permissionInputs = form.querySelectorAll('input[name="permissions[]"]');
        permissionInputs.forEach(input => {
            input.checked = false;
        });
    }
});

// Add this function to handle role updates
function updateRole() {
    const form = document.getElementById('editRoleForm');
    const formData = new FormData(form);
    
    // Validate at least one scope is selected
    const selectedScopes = form.querySelectorAll('input[name="scope_ids[]"]:checked');
    if (selectedScopes.length === 0) {
        showAlert('Please select at least one scope', 'danger');
        return;
    }

    // Validate at least one permission is selected
    const selectedPermissions = form.querySelectorAll('input[name="permissions[]"]:checked');
    if (selectedPermissions.length === 0) {
        showAlert('Please select at least one permission', 'danger');
        return;
    }

    // Show loading state
    const submitButton = document.querySelector('#editRoleModal .btn-primary');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
    
    // Log what's being submitted
    console.log('Selected permissions:', Array.from(selectedPermissions).map(p => p.value));
    
    fetch('save_role.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert('Role updated successfully!', 'success');
            
            // Properly close the modal using Bootstrap's API
            const modalElement = document.getElementById('editRoleModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide();
            
            // Fix the modal backdrop issue
            const modalBackdrop = document.querySelector('.modal-backdrop');
            if (modalBackdrop) {
                modalBackdrop.remove();
            }
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            
            // Reload roles table to show the updated role
            loadRoles(currentPage);
        } else {
            throw new Error(data.message || 'Failed to update role');
        }
    })
    .catch(error => {
        console.error('Error updating role:', error);
        showAlert(error.message, 'danger');
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}

// Add form submit handler for edit form
document.getElementById('editRoleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    updateRole();
});

// Function to display alerts to the user
function showAlert(message, type = 'success') {
    const alertPlaceholder = document.querySelector('.container');
    if (!alertPlaceholder) return;
    
    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show mb-4" role="alert">
            <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert the alert at the top of the container, after any existing navigation
    const navCard = alertPlaceholder.querySelector('.nav-card');
    if (navCard) {
        navCard.insertAdjacentElement('afterend', wrapper.firstElementChild);
    } else {
        alertPlaceholder.prepend(wrapper.firstElementChild);
    }
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = wrapper.firstElementChild;
        if (alert && typeof bootstrap !== 'undefined') {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

// Add debugging message
console.log("Document loaded with index.php JavaScript");

// This function will load role data into the edit form when the edit button is clicked
function loadRoleForEditing(roleId) {
    console.log("Loading role data for ID:", roleId);
    
    // Show loading spinner
    const modalBody = document.querySelector('#editRoleModal .modal-body');
    modalBody.innerHTML = `<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading role data...</p></div>`;
    
    // Fetch role data
    fetch(`get_role.php?role_id=${roleId}`)
        .then(response => response.json())
        .then(data => {
            console.log("Received role data:", data);
            if (data.success) {
                // Restore the form HTML
                modalBody.innerHTML = `
                <form id="editRoleForm">
                    <input type="hidden" name="role_id" value="${roleId}">
                    <div class="mb-3">
                        <label class="form-label required">Role Name</label>
                        <input type="text" class="form-control" name="role_name" required value="${data.role.role_name}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Scope <small class="text-muted">(Select all that apply)</small></label>
                        <div class="d-flex flex-wrap gap-3" id="edit-scopes-container">
                            <?php foreach ($scopes as $scope): ?>
                            <div class="form-check">
                                <input class="form-check-input scope-checkbox" type="checkbox" 
                                    name="scope_ids[]" 
                                    value="<?= $scope['scope_id'] ?>" 
                                    id="edit_scope_<?= $scope['scope_id'] ?>">
                                <label class="form-check-label" for="edit_scope_<?= $scope['scope_id'] ?>">
                                    <?= ucfirst(htmlspecialchars($scope['scope_name'])) ?>
                                    <small class="d-block text-muted"><?= htmlspecialchars($scope['description']) ?></small>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="invalid-feedback">Please select at least one scope.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3">${data.role.description || ''}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="accordion" id="editPermissionsAccordion">
                            <?php
                            foreach ($permissionGroups as $groupKey => $group):
                                $permissions = getPermissionsByGroup($groupKey);
                            ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#edit_collapse_<?= $groupKey ?>"
                                                aria-expanded="false" 
                                                aria-controls="edit_collapse_<?= $groupKey ?>">
                                            <?= htmlspecialchars($group['title']) ?>
                                        </button>
                                    </h2>
                                    <div id="edit_collapse_<?= $groupKey ?>" class="accordion-collapse collapse" 
                                         data-bs-parent="#editPermissionsAccordion">
                                        <div class="accordion-body">
                                            <div class="row g-3">
                                                <?php foreach ($permissions as $permission): ?>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                name="permissions[]" 
                                                                value="<?= $permission ?>" 
                                                                id="edit_perm_<?= $permission ?>">
                                                            <label class="form-check-label" for="edit_perm_<?= $permission ?>">
                                                                <?= htmlspecialchars($permission) ?>
                                                                <small class="d-block text-muted">
                                                                    <?= htmlspecialchars(getPermissionDescription($permission)) ?>
                                                                </small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
                `;
                
                // Now check the appropriate scope checkboxes
                if (data.scopes && Array.isArray(data.scopes)) {
                    data.scopes.forEach(scopeId => {
                        const checkbox = document.querySelector(`#edit_scope_${scopeId}`);
                        if (checkbox) {
                            checkbox.checked = true;
                        } else {
                            console.warn(`Scope checkbox not found for ID: ${scopeId}`);
                        }
                    });
                }
                
                // Check the appropriate permission checkboxes
                if (data.permission_names && Array.isArray(data.permission_names)) {
                    data.permission_names.forEach(permName => {
                        const permCheckbox = document.querySelector(`#edit_perm_${permName}`);
                        if (permCheckbox) {
                            permCheckbox.checked = true;
                            
                            // Open the accordion group containing this permission
                            const accordionCollapse = permCheckbox.closest('.accordion-collapse');
                            if (accordionCollapse) {
                                accordionCollapse.classList.add('show');
                            }
                        } else {
                            console.warn(`Permission checkbox not found for: ${permName}`);
                        }
                    });
                }
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading role data: ${data.message || 'Unknown error'}</div>`;
            }
        })
        .catch(error => {
            console.error("Error fetching role:", error);
            modalBody.innerHTML = `<div class="alert alert-danger">Error loading role data. Please try again.</div>`;
        });
}

// Remove all existing click handlers for edit-role buttons and set up the new one
document.addEventListener('DOMContentLoaded', function() {
    // Set up a single delegate event handler for all edit buttons
    document.body.addEventListener('click', function(event) {
        const editButton = event.target.closest('.edit-role');
        if (editButton) {
            event.preventDefault();
            const roleId = editButton.getAttribute('data-role-id');
            if (roleId) {
                // Show the modal first
                const modal = new bootstrap.Modal(document.getElementById('editRoleModal'));
                modal.show();
                
                // Then load the data
                setTimeout(() => loadRoleForEditing(roleId), 300);
            }
        }
    });
});

// Add this function to ensure all modals clean up properly when closed
document.addEventListener('DOMContentLoaded', function() {
    // Handle all modal hide events across the application
    document.body.addEventListener('hide.bs.modal', function(event) {
        // Wait a short time and make sure backdrop and body classes are removed
        setTimeout(() => {
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }, 150);
    });
});

// Replace the button click handler with a more robust handler
document.addEventListener('DOMContentLoaded', function() {
    // Add click handler for the Update Role button
    const updateRoleButton = document.querySelector('#editRoleModal .btn-primary');
    if (updateRoleButton) {
        updateRoleButton.addEventListener('click', function() {
            updateRole();
        });
    }
});

// Also add a click handler for the modal close button to properly clean up the modal
document.addEventListener('DOMContentLoaded', function() {
    const modalCloseButtons = document.querySelectorAll('.modal .btn-close, .modal .btn-secondary');
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Ensure modal backdrop is removed
            setTimeout(() => {
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }, 150);
        });
    });
});
</script>
</body>
</html>
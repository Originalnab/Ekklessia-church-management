<?php
session_start();
require_once "../../config/config.php";
require_once "../../functions/role_management.php";
require_once "../../functions/role_debug_logger.php";
require_once "../../functions/permission_constants.php";  // Include permission constants

header('Content-Type: application/json');

try {
    // Log the incoming request
    logRoleAction('FORM_SUBMISSION', 'Role form submitted', [
        'POST_DATA' => $_POST,
        'USER_ID' => $_SESSION['member_id'] ?? 'unknown'
    ]);

    // Validate required fields
    if (!isset($_POST['role_name']) || !isset($_POST['scope_ids'])) {
        throw new Exception("Required fields are missing");
    }

    $roleId = isset($_POST['role_id']) ? (int)$_POST['role_id'] : null;
    $roleName = trim($_POST['role_name']);
    $scopeIds = $_POST['scope_ids'];
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

    // Validate permissions array
    if (!is_array($permissions)) {
        throw new Exception("Invalid permissions format");
    }

    // Use the centralized permission mapping system
    $mappedPermissions = mapAllFormPermissions($permissions);
    
    // Log the permission mapping
    logRoleAction('PERMISSION_MAPPING', 'Mapped permission names', [
        'original' => $permissions,
        'mapped' => $mappedPermissions
    ]);
    
    // Use the mapped permissions going forward
    $permissions = $mappedPermissions;

    // Validate scope IDs
    if (empty($scopeIds) || !is_array($scopeIds)) {
        throw new Exception("At least one scope must be selected");
    }
    
    // For backward compatibility, determine hierarchy level based on the highest scope
    // The lower the scope_id, the higher the authority (1=Household, 2=Assembly, 3=Zone, 4=National)
    $scopeToHierarchy = [
        1 => 'Household',
        2 => 'Assembly',
        3 => 'Zone',
        4 => 'National'
    ];
    
    // Get the highest scope (in our case, the lowest numeric value has highest authority)
    $highestScopeId = min(array_map('intval', $scopeIds));
    $hierarchyLevel = isset($scopeToHierarchy[$highestScopeId]) ? 
                      $scopeToHierarchy[$highestScopeId] : 
                      'Household'; // Default to Household if unknown
    
    // Log processed data
    logRoleAction('DATA_PROCESSING', 'Processed form data', [
        'role_id' => $roleId,
        'role_name' => $roleName,
        'scope_ids' => $scopeIds,
        'highest_scope_id' => $highestScopeId,
        'hierarchy_level' => $hierarchyLevel, // Just for logging
        'description' => $description,
        'permissions_count' => count($permissions)
    ]);

    // Validate role name
    if (empty($roleName)) {
        throw new Exception("Role name cannot be empty");
    }

    // Check if we're dealing with permission IDs or names
    $arePermissionIds = false;
    if (!empty($permissions)) {
        $firstPermission = reset($permissions);
        $arePermissionIds = is_numeric($firstPermission);
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        if ($roleId) {
            // Update existing role
            $stmt = $pdo->prepare("
                UPDATE roles 
                SET role_name = ?, 
                    hierarchy_level = ?, /* Keep for backward compatibility */
                    description = ?,
                    updated_at = NOW(),
                    updated_by = ?
                WHERE role_id = ?
            ");
            $stmt->execute([$roleName, $hierarchyLevel, $description, $_SESSION['member_id'] ?? 'system', $roleId]);
        } else {
            // Create new role
            $stmt = $pdo->prepare("
                INSERT INTO roles (role_name, hierarchy_level, description, created_at, updated_at, created_by)
                VALUES (?, ?, ?, NOW(), NOW(), ?)
            ");
            $stmt->execute([$roleName, $hierarchyLevel, $description, $_SESSION['member_id'] ?? 'system']);
            $roleId = $pdo->lastInsertId();
        }

        // Handle permissions
        if (!empty($permissions)) {
            // Get existing permissions first
            $stmt = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
            $stmt->execute([$roleId]);
            $existingPermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if ($arePermissionIds) {
                // If we have permission IDs, use them directly
                $permissionIds = array_map('intval', $permissions);
            } else {
                // If we have permission names, look up their IDs
                $placeholders = str_repeat('?,', count($permissions) - 1) . '?';
                $stmt = $pdo->prepare("
                    SELECT permission_id 
                    FROM permissions 
                    WHERE permission_name IN ($placeholders)
                ");
                $stmt->execute($permissions);
                $permissionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            if (empty($permissionIds)) {
                throw new Exception("No valid permissions found");
            }

            // Find permissions to add and remove
            $permissionsToAdd = array_diff($permissionIds, $existingPermissions);
            $permissionsToRemove = array_diff($existingPermissions, $permissionIds);

            // Remove only the permissions that need to be removed
            if (!empty($permissionsToRemove)) {
                $placeholders = str_repeat('?,', count($permissionsToRemove) - 1) . '?';
                $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ? AND permission_id IN ($placeholders)");
                array_unshift($permissionsToRemove, $roleId);
                $stmt->execute($permissionsToRemove);
            }

            // Add only new permissions
            if (!empty($permissionsToAdd)) {
                $stmt = $pdo->prepare("
                    INSERT INTO role_permissions (role_id, permission_id, is_active, created_at, created_by) 
                    VALUES (?, ?, 1, NOW(), ?)
                ");

                foreach ($permissionsToAdd as $permId) {
                    $stmt->execute([$roleId, $permId, $_SESSION['member_id'] ?? 'system']);
                }
            }

            logRoleAction('PERMISSIONS', 'Updated permissions for role', [
                'role_id' => $roleId,
                'added' => count($permissionsToAdd),
                'removed' => count($permissionsToRemove)
            ]);
        }

        // Handle scopes
        // Delete existing scopes
        $stmt = $pdo->prepare("DELETE FROM role_scopes WHERE role_id = ?");
        $stmt->execute([$roleId]);

        // Insert new scopes
        foreach ($scopeIds as $scopeId) {
            $stmt = $pdo->prepare("INSERT INTO role_scopes (role_id, scope_id) VALUES (?, ?)");
            $stmt->execute([$roleId, (int)$scopeId]);
        }

        $pdo->commit();

        logRoleAction('SUCCESS', 'Role operation completed successfully', [
            'role_id' => $roleId,
            'operation' => $roleId ? 'update' : 'create'
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => $roleId ? 'Role updated successfully' : 'Role created successfully',
            'role_id' => $roleId
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    logRoleAction('ERROR', 'Error in role operation', null, $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
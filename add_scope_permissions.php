<?php
/**
 * Add Scope Permissions
 * 
 * This script adds required scope-related permissions to the shepherd role
 * to ensure they can properly manage scopes in the system.
 */

// Include database connection and required functions
require_once 'app/config/db.php';
require_once 'app/functions/role_management.php';

// Define the Shepherd role ID (typically 4)
$shepherdRoleId = 4;

// Define the scope-related permissions we want to assign
$scopePermissions = [
    'view_scopes',
    'add_scope',
    'edit_scope',
    'delete_scope',
    'manage_scopes'
];

// Function to get permission IDs by name
function getPermissionIdsByName($permissionNames) {
    global $pdo;
    
    $placeholders = implode(',', array_fill(0, count($permissionNames), '?'));
    
    $stmt = $pdo->prepare("
        SELECT permission_id, permission_name 
        FROM permissions 
        WHERE permission_name IN ($placeholders)
    ");
    
    $stmt->execute($permissionNames);
    
    $permissions = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $permissions[$row['permission_name']] = $row['permission_id'];
    }
    
    return $permissions;
}

// Execute the permission assignments
try {
    // Start a transaction
    $pdo->beginTransaction();
    
    // Get permission IDs for the scope permissions
    $permissionMap = getPermissionIdsByName($scopePermissions);
    
    // Check if we found all the permissions we need
    $missingPermissions = array_diff($scopePermissions, array_keys($permissionMap));
    
    if (!empty($missingPermissions)) {
        throw new Exception("The following permissions are missing: " . implode(', ', $missingPermissions));
    }
    
    // Get permission IDs as a simple array
    $permissionIds = array_values($permissionMap);
    
    // Assign the permissions to the Shepherd role
    $result = assignPermissionsToRole($shepherdRoleId, $permissionIds, 'System');
    
    if (!$result) {
        throw new Exception("Failed to assign permissions to Shepherd role");
    }
    
    $pdo->commit();
    
    echo "SUCCESS: Scope permissions have been successfully assigned to the Shepherd role.";
    
} catch (Exception $e) {
    // Roll back the transaction if something failed
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo "ERROR: " . $e->getMessage();
    error_log("Error in add_scope_permissions.php: " . $e->getMessage());
}
?>
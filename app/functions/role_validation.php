<?php
/**
 * Validate role changes before saving
 */
function validateRoleChanges($roleId, $roleName, $hierarchyLevel) {
    global $pdo;
    $errors = [];
    
    // Check required fields
    if (empty($roleName)) {
        $errors[] = "Role name is required";
    }
    
    if (empty($hierarchyLevel)) {
        $errors[] = "Hierarchy level is required";
    }
    
    // Validate hierarchy level
    $validLevels = ['National', 'Zone', 'Assembly', 'Household'];
    if (!in_array($hierarchyLevel, $validLevels)) {
        $errors[] = "Invalid hierarchy level";
    }
    
    // Check for duplicate role name
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM roles 
        WHERE role_name = ? AND role_id != ?
    ");
    $stmt->execute([$roleName, $roleId]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "A role with this name already exists";
    }
    
    return $errors;
}

/**
 * Validate role deletion
 */
function validateRoleDeletion($roleId) {
    global $pdo;
    $errors = [];
    
    // Check if any members are assigned to this role
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM member_role 
        WHERE role_id = ?
    ");
    $stmt->execute([$roleId]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Cannot delete role: There are members assigned to this role";
    }
    
    return $errors;
}

/**
 * Validate role permissions assignment
 */
function validateRolePermissions($roleId, $permissionIds) {
    global $pdo;
    $errors = [];
    
    if (empty($permissionIds)) {
        return $errors; // Empty permissions is allowed
    }
    
    // Verify all permissions exist
    $placeholders = str_repeat('?,', count($permissionIds) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM permissions 
        WHERE permission_id IN ($placeholders)
    ");
    $stmt->execute($permissionIds);
    
    if ($stmt->fetchColumn() != count($permissionIds)) {
        $errors[] = "One or more invalid permissions selected";
    }
    
    return $errors;
}

/**
 * Validate role assignment to member
 */
function validateRoleAssignment($memberId, $roleId, $isPrimary = false) {
    global $pdo;
    $errors = [];
    
    // Verify member exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE member_id = ?");
    $stmt->execute([$memberId]);
    if ($stmt->fetchColumn() == 0) {
        $errors[] = "Invalid member selected";
    }
    
    // Verify role exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE role_id = ?");
    $stmt->execute([$roleId]);
    if ($stmt->fetchColumn() == 0) {
        $errors[] = "Invalid role selected";
    }
    
    // If this is a primary role assignment, check if member already has a primary role
    if ($isPrimary) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM member_role 
            WHERE member_id = ? AND is_primary = 1 AND role_id != ?
        ");
        $stmt->execute([$memberId, $roleId]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Member already has a primary role";
        }
    }
    
    return $errors;
}

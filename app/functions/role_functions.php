<?php
require_once "permission_constants.php";

function getRoleWithCache($roleId) {
    $cacheKey = "role_" . $roleId;
    $cachedRole = apcu_fetch($cacheKey);
    
    if ($cachedRole === false) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE role_id = ?");
        $stmt->execute([$roleId]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role) {
            apcu_store($cacheKey, $role, 3600); // Cache for 1 hour
            return $role;
        }
        return null;
    }
    
    return $cachedRole;
}

function getAllPermissions($roleId) {
    $role = getRoleWithCache($roleId);
    if (!$role) return [];
    
    $permissions = getDirectPermissions($roleId);
    
    // If has parent role, merge permissions
    if ($role['parent_role_id']) {
        $parentPermissions = getAllPermissions($role['parent_role_id']);
        $permissions = array_merge($permissions, $parentPermissions);
    }
    
    return array_unique($permissions);
}

function validateRoleHierarchy($roleId, $newParentId) {
    if (!$newParentId) return true;
    
    // Prevent circular dependencies
    $currentParent = $newParentId;
    $maxDepth = 10; // Prevent infinite loops
    $depth = 0;
    
    while ($currentParent && $depth < $maxDepth) {
        if ($currentParent == $roleId) return false;
        
        $parentRole = getRoleWithCache($currentParent);
        if (!$parentRole) break;
        
        $currentParent = $parentRole['parent_role_id'];
        $depth++;
    }
    
    return true;
}

function validateRoleScope($roleId, $scopeId, $parentRoleId) {
    global $pdo;
    
    if (!$parentRoleId) return true;
    
    $stmt = $pdo->prepare("SELECT scope_id FROM roles WHERE role_id = ?");
    $stmt->execute([$parentRoleId]);
    $parentRole = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$parentRole) return true;
    
    // Ensure child scope is not higher than parent scope
    return $scopeId <= $parentRole['scope_id'];
}

/**
 * Get all roles
 * 
 * @param PDO $pdo Database connection
 * @return array All roles
 */
function getAllRoles($pdo) {
    $stmt = $pdo->prepare("
        SELECT r.*, 
               COUNT(DISTINCT rp.permission_id) as permission_count,
               COUNT(DISTINCT mr.member_id) as member_count
        FROM roles r
        LEFT JOIN role_permissions rp ON r.role_id = rp.role_id
        LEFT JOIN member_role mr ON r.role_id = mr.role_id
        GROUP BY r.role_id
        ORDER BY r.role_name
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get a role by ID
 * 
 * @param PDO $pdo Database connection
 * @param int $roleId Role ID
 * @return array Role data
 */
function getRoleById($pdo, $roleId) {
    $stmt = $pdo->prepare("
        SELECT * FROM roles WHERE role_id = ?
    ");
    $stmt->execute([$roleId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get all permissions for a role
 * 
 * @param PDO $pdo Database connection
 * @param int $roleId Role ID
 * @return array Permission data
 */
function getRolePermissions($pdo, $roleId) {
    $stmt = $pdo->prepare("
        SELECT p.* 
        FROM permissions p
        JOIN role_permissions rp ON p.permission_id = rp.permission_id
        WHERE rp.role_id = ?
    ");
    $stmt->execute([$roleId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all available permissions
 * 
 * @param PDO $pdo Database connection
 * @return array All permissions
 */
function getAllPermissions($pdo) {
    $stmt = $pdo->prepare("
        SELECT * FROM permissions ORDER BY permission_name
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all permissions grouped by category for UI display
 * 
 * @param PDO $pdo Database connection
 * @return array Permissions grouped by category
 */
function getGroupedPermissionsList($pdo) {
    $allPermissions = getAllPermissions($pdo);
    
    // Use the permission groups from permission_constants.php
    global $PERMISSION_GROUPS;
    
    $result = [];
    foreach ($PERMISSION_GROUPS as $groupName => $groupPermissions) {
        $permissionsInGroup = [];
        
        // Find permissions that belong to this group
        foreach ($allPermissions as $permission) {
            if (in_array($permission['permission_name'], $groupPermissions)) {
                // Add display name to permission
                $permission['display_name'] = getPermissionDisplayName($permission['permission_name']);
                $permissionsInGroup[] = $permission;
            }
        }
        
        if (!empty($permissionsInGroup)) {
            $result[$groupName] = $permissionsInGroup;
        }
    }
    
    // Add any uncategorized permissions to "Other" group
    $uncategorized = [];
    $allGroupedPermNames = [];
    
    foreach ($PERMISSION_GROUPS as $permissions) {
        $allGroupedPermNames = array_merge($allGroupedPermNames, $permissions);
    }
    
    foreach ($allPermissions as $permission) {
        if (!in_array($permission['permission_name'], $allGroupedPermNames)) {
            // Add display name to permission
            $permission['display_name'] = getPermissionDisplayName($permission['permission_name']);
            $uncategorized[] = $permission;
        }
    }
    
    if (!empty($uncategorized)) {
        $result['Other'] = $uncategorized;
    }
    
    return $result;
}

/**
 * Get all members assigned to a role
 * 
 * @param PDO $pdo Database connection
 * @param int $roleId Role ID
 * @return array Members with this role
 */
function getMembersWithRole($pdo, $roleId) {
    $stmt = $pdo->prepare("
        SELECT m.*, mr.assigned_date
        FROM members m
        JOIN member_role mr ON m.member_id = mr.member_id
        WHERE mr.role_id = ?
        ORDER BY m.last_name, m.first_name
    ");
    $stmt->execute([$roleId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Check if a member has a specific permission
 * 
 * @param PDO $pdo Database connection
 * @param int $memberId Member ID
 * @param string $permissionName Permission name (use constants from permission_constants.php)
 * @return bool True if member has the permission
 */
function memberHasPermission($pdo, $memberId, $permissionName) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM member_role mr
        JOIN role_permissions rp ON mr.role_id = rp.role_id
        JOIN permissions p ON rp.permission_id = p.permission_id
        WHERE mr.member_id = ? AND p.permission_name = ?
    ");
    $stmt->execute([$memberId, $permissionName]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] > 0;
}

/**
 * Get all permissions for a member across all their roles
 * 
 * @param PDO $pdo Database connection
 * @param int $memberId Member ID
 * @return array Permission names
 */
function getMemberPermissions($pdo, $memberId) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT p.permission_name
        FROM member_role mr
        JOIN role_permissions rp ON mr.role_id = rp.role_id
        JOIN permissions p ON rp.permission_id = p.permission_id
        WHERE mr.member_id = ?
    ");
    $stmt->execute([$memberId]);
    
    $permissions = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $permissions[] = $row['permission_name'];
    }
    
    return $permissions;
}

/**
 * Generate a database update query to create the permissions table 
 * based on the defined permissions in permission_constants.php
 * 
 * @return string SQL update query
 */
function generatePermissionsTableSql() {
    $permissions = getAllPermissionConstants();
    
    $sql = "-- Auto-generated permissions update SQL based on permission_constants.php\n";
    $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
    
    $sql .= "-- Create permissions table if it doesn't exist\n";
    $sql .= "CREATE TABLE IF NOT EXISTS `permissions` (
    `permission_id` int(11) NOT NULL AUTO_INCREMENT,
    `permission_name` varchar(50) NOT NULL,
    `description` varchar(255) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`permission_id`),
    UNIQUE KEY `permission_name` (`permission_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

    $sql .= "-- Insert permissions if they don't exist\n";
    global $PERMISSION_DISPLAY_NAMES;
    
    foreach ($permissions as $name => $value) {
        $displayName = $PERMISSION_DISPLAY_NAMES[$value] ?? $value;
        
        $sql .= "INSERT INTO `permissions` (`permission_name`, `description`) 
        SELECT '$value', '$displayName'
        FROM dual
        WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `permission_name` = '$value');\n";
    }
    
    return $sql;
}

/**
 * Synchronize the database permissions with the constants defined in permission_constants.php
 * 
 * @param PDO $pdo Database connection
 * @return array Results of the synchronization
 */
function syncPermissionsWithDatabase($pdo) {
    $constants = getAllPermissionConstants();
    $permissionNames = array_values($constants);
    
    // Check which permissions exist in the database
    $stmt = $pdo->prepare("SELECT permission_name FROM permissions");
    $stmt->execute();
    $existingPermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $results = [
        'added' => [],
        'missing_in_constants' => array_diff($existingPermissions, $permissionNames),
        'already_exist' => array_intersect($existingPermissions, $permissionNames)
    ];
    
    // Add permissions that don't exist in the database
    $toAdd = array_diff($permissionNames, $existingPermissions);
    global $PERMISSION_DISPLAY_NAMES;
    
    foreach ($toAdd as $permission) {
        $displayName = $PERMISSION_DISPLAY_NAMES[$permission] ?? $permission;
        
        $stmt = $pdo->prepare("
            INSERT INTO permissions (permission_name, description)
            VALUES (?, ?)
        ");
        $stmt->execute([$permission, $displayName]);
        
        $results['added'][] = $permission;
    }
    
    return $results;
}

/**
 * Get roles that have a specific permission
 * 
 * @param PDO $pdo Database connection
 * @param string $permissionName Permission name
 * @return array Roles with this permission
 */
function getRolesWithPermission($pdo, $permissionName) {
    $stmt = $pdo->prepare("
        SELECT r.*
        FROM roles r
        JOIN role_permissions rp ON r.role_id = rp.role_id
        JOIN permissions p ON rp.permission_id = p.permission_id
        WHERE p.permission_name = ?
        ORDER BY r.role_name
    ");
    $stmt->execute([$permissionName]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

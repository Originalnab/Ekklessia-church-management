<?php
/**
 * Role Management Helper Functions
 * 
 * This file contains helper functions for the new role management system
 * which doesn't rely on church_functions table.
 */

/**
 * Get all roles assigned to a member with their hierarchy levels
 * 
 * @param int $memberId The member ID
 * @return array Array of role information
 */
function getMemberRoles($memberId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            mr.member_role_id,
            mr.role_id,
            mr.is_primary,
            mr.assigned_at,
            mr.assigned_by,
            r.role_name,
            r.hierarchy_level,
            r.description as role_description
        FROM 
            member_role mr
        JOIN 
            roles r ON mr.role_id = r.role_id
        WHERE 
            mr.member_id = ?
        ORDER BY 
            FIELD(r.hierarchy_level, 'National', 'Zone', 'Assembly', 'Household'),
            mr.is_primary DESC,
            mr.assigned_at DESC
    ");
    
    $stmt->execute([$memberId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get a member's primary role
 * 
 * @param int $memberId The member ID
 * @return array|null The primary role info or null if none found
 */
function getMemberPrimaryRole($memberId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            mr.member_role_id,
            mr.role_id,
            r.role_name,
            r.description as role_description,
            s.scope_name,
            s.scope_id
        FROM 
            member_role mr
        JOIN 
            roles r ON mr.role_id = r.role_id
        JOIN 
            scopes s ON r.scope_id = s.scope_id
        WHERE 
            mr.member_id = ? 
            AND mr.is_primary = 1
        LIMIT 1
    ");
    
    $stmt->execute([$memberId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Log role assignment action
 */
function logRoleAssignment($memberId, $roleId, $action, $performedBy, $details = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO role_assignment_log 
            (member_id, role_id, action, performed_by, performed_at, action_details) 
            VALUES (?, ?, ?, ?, NOW(), ?)
        ");
        
        $stmt->execute([
            $memberId,
            $roleId,
            $action,
            $performedBy,
            $details ? json_encode($details) : null
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error logging role assignment: " . $e->getMessage());
        return false;
    }
}

/**
 * Assign a role to a member
 * 
 * @param int $memberId The member ID
 * @param int $roleId The role ID
 * @param bool $isPrimary Whether this is the member's primary role
 * @param string $assignedBy Who assigned this role
 * @return bool True if successful, false otherwise
 */
function assignRoleToMember($memberId, $roleId, $isPrimary = false, $assignedBy = null) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // If this is marked as primary, clear any existing primary roles
        if ($isPrimary) {
            $stmt = $pdo->prepare("UPDATE member_role SET is_primary = 0 WHERE member_id = ?");
            $stmt->execute([$memberId]);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO member_role (member_id, role_id, is_primary, assigned_by) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                is_primary = VALUES(is_primary),
                assigned_by = VALUES(assigned_by)
        ");
        
        $stmt->execute([
            $memberId,
            $roleId,
            $isPrimary ? 1 : 0,
            $assignedBy ?? $_SESSION['member_id'] ?? 'System'
        ]);
        
        // Log the assignment
        logRoleAssignment(
            $memberId, 
            $roleId, 
            $isPrimary ? 'MAKE_PRIMARY' : 'ASSIGN',
            $assignedBy ?? $_SESSION['member_id'] ?? 'System',
            ['operation' => $isPrimary ? 'add_as_primary' : 'add']
        );
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error assigning role: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove a role from a member
 * 
 * @param int $memberId The member ID
 * @param int $roleId The role ID
 * @return bool True if successful, false otherwise
 */
function removeRoleFromMember($memberId, $roleId) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Check if this was a primary role
        $stmt = $pdo->prepare("SELECT is_primary FROM member_role WHERE member_id = ? AND role_id = ?");
        $stmt->execute([$memberId, $roleId]);
        $wasPrimary = (bool)$stmt->fetchColumn();
        
        // Remove the role
        $stmt = $pdo->prepare("DELETE FROM member_role WHERE member_id = ? AND role_id = ?");
        $stmt->execute([$memberId, $roleId]);
        
        // Log the removal
        logRoleAssignment(
            $memberId, 
            $roleId, 
            $wasPrimary ? 'REMOVE_PRIMARY' : 'REMOVE',
            $_SESSION['member_id'] ?? 'System',
            ['operation' => 'remove', 'was_primary' => $wasPrimary]
        );
        
        // If this was primary, assign new primary if there are other roles
        if ($wasPrimary) {
            $stmt = $pdo->prepare("
                UPDATE member_role 
                SET is_primary = 1 
                WHERE member_id = ? 
                ORDER BY assigned_at ASC 
                LIMIT 1
            ");
            $stmt->execute([$memberId]);
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error removing role: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all permissions for a role
 * 
 * @param int $roleId The role ID
 * @return array Array of permission names
 */
function getRolePermissions($roleId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            p.permission_id,
            p.permission_name,
            p.description
        FROM 
            role_permissions rp
        JOIN 
            permissions p ON rp.permission_id = p.permission_id
        WHERE 
            rp.role_id = ? AND rp.is_active = 1
    ");
    
    $stmt->execute([$roleId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all permissions for a member (from all their roles)
 * 
 * @param int $memberId The member ID
 * @return array Array of permission names
 */
function getMemberPermissions($memberId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            p.permission_id,
            p.permission_name,
            p.description
        FROM 
            member_role mr
        JOIN 
            role_permissions rp ON mr.role_id = rp.role_id
        JOIN 
            permissions p ON rp.permission_id = p.permission_id
        WHERE 
            mr.member_id = ? AND rp.is_active = 1
    ");
    
    $stmt->execute([$memberId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Check if a member has a specific permission
 * 
 * @param int $memberId The member ID
 * @param string $permissionName The permission name to check
 * @return bool True if member has permission, false otherwise
 */
function memberHasPermission($memberId, $permissionName) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM member_role mr
        JOIN role_permissions rp ON mr.role_id = rp.role_id
        JOIN permissions p ON rp.permission_id = p.permission_id
        WHERE mr.member_id = ? AND p.permission_name = ? AND rp.is_active = 1
    ");
    
    $stmt->execute([$memberId, $permissionName]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Check if member has role management permissions
 */
function canManageRoles($memberId) {
    return memberHasPermission($memberId, 'manage_roles') || 
           memberHasPermission($memberId, 'add_role') || 
           memberHasPermission($memberId, 'edit_role') ||
           memberHasPermission($memberId, 'delete_role');
}

/**
 * Assign permissions to a role
 * 
 * @param int $roleId The role ID
 * @param array $permissionIds Array of permission IDs to assign
 * @param string $createdBy Who assigned these permissions
 * @return bool True if successful, false otherwise
 */
function assignPermissionsToRole($roleId, $permissionIds, $createdBy = 'System') {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // First try to use new role_permissions table
        $stmt = $pdo->prepare("
            SELECT EXISTS (
                SELECT 1 FROM information_schema.tables 
                WHERE table_schema = DATABASE() AND table_name = 'role_permissions'
            ) as table_exists
        ");
        $stmt->execute();
        $tableExists = $stmt->fetchColumn();
        
        if ($tableExists) {
            // Use new table structure
            // First clear existing permissions
            $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt->execute([$roleId]);
            
            // Then add new ones
            $stmt = $pdo->prepare("
                INSERT INTO role_permissions (role_id, permission_id, is_active, created_by) 
                VALUES (?, ?, 1, ?)
            ");
            
            foreach ($permissionIds as $permId) {
                $stmt->execute([$roleId, $permId, $createdBy]);
            }
        } else {
            // Fall back to old table structure
            // Get the first function_id associated with this role
            $stmt = $pdo->prepare("
                SELECT function_id FROM church_functions 
                WHERE role_id = ? LIMIT 1
            ");
            $stmt->execute([$roleId]);
            $functionId = $stmt->fetchColumn();
            
            if (!$functionId) {
                // Use a default function_id (you may need to adjust this)
                $functionId = 1;
            }
            
            // Clear existing permissions
            $stmt = $pdo->prepare("
                DELETE FROM role_church_function_permissions 
                WHERE role_id = ?
            ");
            $stmt->execute([$roleId]);
            
            // Add new ones
            $stmt = $pdo->prepare("
                INSERT INTO role_church_function_permissions (permission_id, role_id, function_id, is_active) 
                VALUES (?, ?, ?, 1)
            ");
            
            foreach ($permissionIds as $permId) {
                $stmt->execute([$permId, $roleId, $functionId]);
            }
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error assigning permissions: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all roles in the system
 * 
 * @return array Array of role information
 */
function getAllRoles() {
    global $pdo;
    
    // Modified query to remove the scope_id reference that's causing the error
    $stmt = $pdo->query("
        SELECT 
            r.role_id,
            r.role_name,
            r.description
        FROM 
            roles r
        ORDER BY 
            r.role_name
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all permissions in the system
 * 
 * @return array Array of permission information
 */
function getAllPermissions() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT 
            permission_id,
            permission_name,
            description
        FROM 
            permissions
        ORDER BY 
            permission_name
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Create a new role
 * 
 * @param string $roleName The name of the role
 * @param int $scopeId The scope ID for this role
 * @param string $description Description of the role
 * @param string $createdBy Who created this role
 * @return int|false The new role ID if successful, false otherwise
 */
function createRole($roleName, $scopeId, $description = '', $createdBy = 'System') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO roles (role_name, scope_id, description, created_by) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$roleName, $scopeId, $description, $createdBy]);
        
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Error creating role: " . $e->getMessage());
        return false;
    }
}

/**
 * Get the appropriate dashboard URL based on member's roles
 * 
 * @param int $memberId The member ID
 * @return string The dashboard URL
 */
function getMemberDashboardUrl($memberId) {
    $roles = getMemberRoles($memberId);
    
    if (empty($roles)) {
        return '/Ekklessia-church-management/app/pages/dashboard/default_home.php';
    }

    // Check roles in order of hierarchy
    foreach ($roles as $role) {
        switch ($role['hierarchy_level']) {
            case 'National':
                if ($role['role_name'] === 'EXCO') {
                    return '/Ekklessia-church-management/app/pages/dashboard/exco_home.php';
                }
                break;
                
            case 'Zone':
                if (memberHasPermission($memberId, 'manage_zone')) {
                    return '/Ekklessia-church-management/app/pages/dashboard/zone_director_home.php';
                }
                break;
                
            case 'Assembly':
                if (memberHasPermission($memberId, 'manage_assembly')) {
                    return '/Ekklessia-church-management/app/pages/dashboard/presiding_elder_home.php';
                }
                break;
                
            case 'Household':
                if (memberHasPermission($memberId, 'manage_household')) {
                    return '/Ekklessia-church-management/app/pages/dashboard/shepherd_home.php';
                }
                break;
        }
    }
    
    // If member has multiple roles but none match above, show multi-role dashboard
    if (count($roles) > 1) {
        return '/Ekklessia-church-management/app/pages/dashboard/multi_role_dashboard.php';
    }
    
    // Default dashboard
    return '/Ekklessia-church-management/app/pages/dashboard/member_home.php';
}

/**
 * Get the appropriate dashboard URL based on role ID and hierarchy level
 * 
 * @param int $roleId The role ID
 * @param string $hierarchyLevel The hierarchy level
 * @return string The dashboard URL
 */
function getDashboardByRole($roleId, $hierarchyLevel) {
    global $pdo;
    
    // Get role name
    $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE role_id = ?");
    $stmt->execute([$roleId]);
    $roleName = $stmt->fetchColumn();
    
    // Determine dashboard by hierarchy level and role name
    switch ($hierarchyLevel) {
        case 'National':
            if ($roleName === 'EXCO') {
                return '/Ekklessia-church-management/app/pages/dashboard/exco_home.php';
            }
            break;
            
        case 'Zone':
            return '/Ekklessia-church-management/app/pages/dashboard/zone_director_home.php';
            
        case 'Assembly':
            return '/Ekklessia-church-management/app/pages/dashboard/presiding_elder_home.php';
            
        case 'Household':
            return '/Ekklessia-church-management/app/pages/dashboard/shepherd_home.php';
    }
    
    // Default to multi-role dashboard if no specific dashboard found
    return '/Ekklessia-church-management/app/pages/dashboard/multi_role_dashboard.php';
}

/**
 * Get all roles with paginated results
 */
function getPaginatedRoles($page = 1, $perPage = 10, $filter = '') {
    global $pdo;
    
    $offset = ($page - 1) * $perPage;
    $whereClause = $filter ? "WHERE r.role_name LIKE :filter" : "";
    
    $query = "
        SELECT 
            r.role_id,
            r.role_name,
            r.hierarchy_level,
            r.description,
            GROUP_CONCAT(DISTINCT p.permission_name) as permissions,
            COUNT(DISTINCT mr.member_id) as assigned_members
        FROM roles r
        LEFT JOIN role_permissions rp ON r.role_id = rp.role_id
        LEFT JOIN permissions p ON rp.permission_id = p.permission_id
        LEFT JOIN member_role mr ON r.role_id = mr.role_id
        $whereClause
        GROUP BY r.role_id
        ORDER BY 
            FIELD(r.hierarchy_level, 'National', 'Zone', 'Assembly', 'Household'),
            r.role_name
        LIMIT :offset, :limit
    ";
    
    $stmt = $pdo->prepare($query);
    if ($filter) {
        $stmt->bindValue(':filter', "%$filter%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get total number of roles (for pagination)
 */
function getTotalRoles($filter = '') {
    global $pdo;
    
    $query = "SELECT COUNT(*) FROM roles";
    if ($filter) {
        $query .= " WHERE role_name LIKE :filter";
    }
    
    $stmt = $pdo->prepare($query);
    if ($filter) {
        $stmt->bindValue(':filter', "%$filter%", PDO::PARAM_STR);
    }
    $stmt->execute();
    
    return $stmt->fetchColumn();
}

/**
 * Get role membership statistics
 */
function getRoleStats() {
    global $pdo;
    
    $query = "
        SELECT 
            (SELECT COUNT(DISTINCT member_id) FROM member_role) as members_with_roles,
            (SELECT COUNT(*) FROM members) as total_members
    ";
    
    $stmt = $pdo->query($query);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'members_with_roles' => (int)$stats['members_with_roles'],
        'members_without_roles' => (int)$stats['total_members'] - (int)$stats['members_with_roles']
    ];
}

/**
 * Check if a member has a specific role
 */
function memberHasRole($memberId, $roleId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM member_role 
        WHERE member_id = ? AND role_id = ?
    ");
    
    $stmt->execute([$memberId, $roleId]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Get all roles with their permissions
 */
function getRolesWithPermissions() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT 
            r.role_id,
            r.role_name,
            r.hierarchy_level,
            r.description,
            GROUP_CONCAT(DISTINCT p.permission_name) as permissions
        FROM roles r
        LEFT JOIN role_permissions rp ON r.role_id = rp.role_id
        LEFT JOIN permissions p ON rp.permission_id = p.permission_id
        GROUP BY r.role_id
        ORDER BY r.role_name
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Set up default Shepherd role permissions
 * 
 * @param int $shepherdRoleId The Shepherd role ID (typically 4)
 * @return bool True if successful, false otherwise
 */
function setupShepherdRolePermissions($shepherdRoleId = 4) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Get required permission IDs for role management
        $stmt = $pdo->prepare("
            SELECT permission_id 
            FROM permissions 
            WHERE permission_name IN ('manage_roles', 'add_role', 'edit_role', 'delete_role', 'view_roles')
        ");
        $stmt->execute();
        $permissionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($permissionIds)) {
            throw new Exception('Required permissions not found');
        }
        
        // First, check if permissions are already assigned
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM role_permissions 
            WHERE role_id = ? AND permission_id IN (" . implode(',', $permissionIds) . ")
        ");
        $stmt->execute([$shepherdRoleId]);
        $existingCount = $stmt->fetchColumn();
        
        if ($existingCount < count($permissionIds)) {
            // Add missing permissions
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO role_permissions (role_id, permission_id, is_active, created_by) 
                VALUES (?, ?, 1, 'System')
            ");
            
            foreach ($permissionIds as $permissionId) {
                $stmt->execute([$shepherdRoleId, $permissionId]);
            }
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error setting up Shepherd permissions: " . $e->getMessage());
        return false;
    }
}
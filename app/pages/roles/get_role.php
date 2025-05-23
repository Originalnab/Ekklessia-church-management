<?php
session_start();
include "../../config/config.php";
include "../../functions/role_management.php";

header('Content-Type: application/json');

if (!isset($_GET['role_id'])) {
    echo json_encode(['success' => false, 'message' => 'Role ID is required']);
    exit;
}

try {
    $roleId = (int)$_GET['role_id'];
    
    // Get role details
    $stmt = $pdo->prepare("
        SELECT 
            r.role_id,
            r.role_name,
            r.hierarchy_level,
            r.description,
            r.created_at,
            r.created_by
        FROM roles r
        WHERE r.role_id = ?
    ");
    $stmt->execute([$roleId]);
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$role) {
        throw new Exception('Role not found');
    }
    
    // Check if role_scopes table exists
    $tableExists = false;
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'role_scopes'");
        $tableExists = $checkTable->rowCount() > 0;
    } catch (PDOException $e) {
        // Table doesn't exist
    }
    
    // Get role scopes
    $scopes = [];
    
    if ($tableExists) {
        // If role_scopes table exists, get all scopes for this role
        $scopeStmt = $pdo->prepare("
            SELECT scope_id FROM role_scopes WHERE role_id = ?
        ");
        $scopeStmt->execute([$roleId]);
        $scopes = $scopeStmt->fetchAll(PDO::FETCH_COLUMN);
    } 
    // The fallback to role.scope_id is removed as that column no longer exists
    
    // Get role permissions with names instead of just IDs
    $permStmt = $pdo->prepare("
        SELECT 
            p.permission_id, 
            p.permission_name
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.permission_id
        WHERE rp.role_id = ?
    ");
    $permStmt->execute([$roleId]);
    $permissions = $permStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response with both permission IDs and names
    echo json_encode([
        'success' => true,
        'role' => $role,
        'scopes' => $scopes,
        'permissions' => array_column($permissions, 'permission_id'),
        'permission_names' => array_column($permissions, 'permission_name')
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
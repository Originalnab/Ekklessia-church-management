<?php
session_start();
include "../../config/config.php";
include "../auth/auth_check.php";

header('Content-Type: application/json');

if (!isset($_GET['role_id'])) {
    echo json_encode(['success' => false, 'message' => 'Role ID is required']);
    exit;
}

try {
    $roleId = $_GET['role_id'];
    
    // Get role permissions including inherited ones from parent roles
    $stmt = $pdo->prepare("
        WITH RECURSIVE role_hierarchy AS (
            SELECT role_id, parent_role_id, 1 as level
            FROM roles
            WHERE role_id = :role_id
            UNION ALL
            SELECT r.role_id, r.parent_role_id, rh.level + 1
            FROM roles r
            INNER JOIN role_hierarchy rh ON r.role_id = rh.parent_role_id
        )
        SELECT DISTINCT p.permission_id, p.permission_name
        FROM role_hierarchy rh
        JOIN role_church_function_permissions rcfp ON rcfp.role_id = rh.role_id
        JOIN permissions p ON p.permission_id = rcfp.permission_id
        WHERE rcfp.is_active = 1
        ORDER BY p.permission_name
    ");
    
    $stmt->execute(['role_id' => $roleId]);
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'permissions' => $permissions
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
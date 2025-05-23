<?php
require_once __DIR__ . "/../app/config/config.php";

try {
    $pdo->beginTransaction();

    // Get the Shepherd role ID
    $stmt = $pdo->prepare("SELECT role_id FROM roles WHERE role_name LIKE '%Shepherd%'");
    $stmt->execute();
    $shepherdRoleId = $stmt->fetchColumn();

    if (!$shepherdRoleId) {
        throw new Exception("Shepherd role not found");
    }

    // Get the permission IDs
    $stmt = $pdo->prepare("
        SELECT permission_id 
        FROM permissions 
        WHERE permission_name IN ('manage_roles', 'add_role', 'edit_role', 'delete_role')
    ");
    $stmt->execute();
    $permissionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Add permissions to the Shepherd role
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO role_permissions (role_id, permission_id, is_active, created_by) 
        VALUES (?, ?, 1, 'System')
    ");

    foreach ($permissionIds as $permissionId) {
        $stmt->execute([$shepherdRoleId, $permissionId]);
    }

    $pdo->commit();
    echo "Successfully updated Shepherd role permissions";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
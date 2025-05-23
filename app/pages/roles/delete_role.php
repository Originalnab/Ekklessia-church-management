<?php
session_start();
include "../../config/config.php";
include "../../functions/role_management.php";

header('Content-Type: application/json');

if (!isset($_POST['role_id'])) {
    echo json_encode(['success' => false, 'message' => 'Role ID is required']);
    exit;
}

try {
    $roleId = (int)$_POST['role_id'];
    
    $pdo->beginTransaction();
    
    // First check if there are any members with this role
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM member_role WHERE role_id = ?");
    $stmt->execute([$roleId]);
    $memberCount = $stmt->fetchColumn();
    
    if ($memberCount > 0) {
        throw new Exception('Cannot delete role: There are members assigned to this role');
    }
    
    // Delete role permissions
    $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
    $stmt->execute([$roleId]);
    
    // Delete the role
    $stmt = $pdo->prepare("DELETE FROM roles WHERE role_id = ?");
    $stmt->execute([$roleId]);
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Role deleted successfully']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
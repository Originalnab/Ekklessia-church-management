<?php
session_start();
include "../../config/config.php";
include "../../functions/role_management.php";

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['member_id']) || !isset($input['roles'])) {
        throw new Exception('Member ID and roles are required');
    }
    
    $memberId = (int)$input['member_id'];
    $roles = $input['roles'];
    $assignedBy = $_SESSION['username'] ?? 'System';

    $pdo->beginTransaction();

    // Clear existing non-primary roles if specified
    if (!empty($input['clearExisting'])) {
        $stmt = $pdo->prepare("DELETE FROM member_role WHERE member_id = ? AND is_primary = 0");
        $stmt->execute([$memberId]);
    }

    // Process each role assignment
    foreach ($roles as $role) {
        $roleId = (int)$role['role_id'];
        $isPrimary = !empty($role['is_primary']);

        if ($isPrimary) {
            // Clear any existing primary roles first
            $stmt = $pdo->prepare("UPDATE member_role SET is_primary = 0 WHERE member_id = ?");
            $stmt->execute([$memberId]);
        }

        // Insert or update role assignment
        $stmt = $pdo->prepare("
            INSERT INTO member_role (member_id, role_id, is_primary, assigned_by) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                is_primary = VALUES(is_primary),
                assigned_by = VALUES(assigned_by),
                assigned_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$memberId, $roleId, $isPrimary ? 1 : 0, $assignedBy]);
    }

    // Get updated role assignments for response
    $updatedRoles = getMemberRoles($memberId);

    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Roles assigned successfully',
        'roles' => $updatedRoles
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
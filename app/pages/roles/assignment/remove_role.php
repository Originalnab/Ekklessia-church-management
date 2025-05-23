<?php
session_start();
include "../../../config/config.php";
include "../../../functions/role_management.php";
include "../../../functions/role_assignment_logger.php";

header('Content-Type: application/json');

if (!isset($_SESSION['member_id'])) {
    logRoleAssignmentError("Unauthorized access attempt", ['session' => $_SESSION]);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['member_id']) || !isset($data['role_id'])) {
    logRoleAssignmentError("Invalid request data", $data);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    $memberId = (int)$data['member_id'];
    $roleId = (int)$data['role_id'];
    
    // Get member and role info for logging
    $memberStmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) as member_name FROM members WHERE member_id = ?");
    $memberStmt->execute([$memberId]);
    $memberName = $memberStmt->fetchColumn();
    
    $roleStmt = $pdo->prepare("SELECT role_name FROM roles WHERE role_id = ?");
    $roleStmt->execute([$roleId]);
    $roleName = $roleStmt->fetchColumn();
    
    // Check if role is primary
    $checkStmt = $pdo->prepare("SELECT is_primary FROM member_role WHERE member_id = ? AND role_id = ?");
    $checkStmt->execute([$memberId, $roleId]);
    $isPrimary = (bool)$checkStmt->fetchColumn();
    
    // Log the attempt
    logRoleAssignmentOperation('REMOVE_ATTEMPT', [
        'member_id' => $memberId,
        'member_name' => $memberName,
        'role_id' => $roleId,
        'role_name' => $roleName,
        'is_primary' => $isPrimary,
        'requested_by' => $_SESSION['member_id']
    ]);
    
    // Remove the role
    $deleteStmt = $pdo->prepare("DELETE FROM member_role WHERE member_id = ? AND role_id = ?");
    $deleteStmt->execute([$memberId, $roleId]);
    
    // Log the successful removal in the database
    $logStmt = $pdo->prepare("
        INSERT INTO role_assignment_log 
        (member_id, role_id, action, performed_by, action_details) 
        VALUES (?, ?, ?, ?, CAST(? AS JSON))
    ");
    
    $actionDetails = json_encode([
        'role_name' => $roleName,
        'was_primary' => $isPrimary,
        'member_name' => $memberName,
        'action_type' => 'remove',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    $logStmt->execute([
        $memberId,
        $roleId,
        $isPrimary ? 'REMOVE_PRIMARY' : 'REMOVE',
        $_SESSION['member_id'],
        $actionDetails
    ]);
    
    // Also log to debug file
    logRoleAssignmentOperation('ROLE_REMOVED', [
        'member_id' => $memberId,
        'member_name' => $memberName,
        'role_id' => $roleId,
        'role_name' => $roleName,
        'was_primary' => $isPrimary,
        'removed_by' => $_SESSION['member_id']
    ]);
    
    // If removing primary role, assign new primary if other roles exist
    if ($isPrimary) {
        $nextRoleStmt = $pdo->prepare("
            SELECT mr.role_id, r.role_name 
            FROM member_role mr
            JOIN roles r ON mr.role_id = r.role_id
            WHERE mr.member_id = ? 
            ORDER BY mr.assigned_at ASC 
            LIMIT 1
        ");
        $nextRoleStmt->execute([$memberId]);
        
        if ($nextRole = $nextRoleStmt->fetch()) {
            $updateStmt = $pdo->prepare("
                UPDATE member_role 
                SET is_primary = 1 
                WHERE member_id = ? AND role_id = ?
            ");
            $updateStmt->execute([$memberId, $nextRole['role_id']]);
            
            // Log setting new primary role
            $newPrimaryLogStmt = $pdo->prepare("
                INSERT INTO role_assignment_log 
                (member_id, role_id, action, performed_by, action_details) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $newPrimaryDetails = json_encode([
                'role_name' => $nextRole['role_name'],
                'reason' => 'Auto-assigned as primary after previous primary role removal',
                'previous_primary_role' => $roleName,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            $newPrimaryLogStmt->execute([
                $memberId,
                $nextRole['role_id'],
                'MAKE_PRIMARY',
                $_SESSION['member_id'],
                $newPrimaryDetails
            ]);
            
            // Also log to debug file
            logRoleAssignmentOperation('NEW_PRIMARY_ROLE_SET', [
                'member_id' => $memberId,
                'member_name' => $memberName,
                'role_id' => $nextRole['role_id'],
                'role_name' => $nextRole['role_name'],
                'previous_primary_role' => $roleName,
                'set_by' => $_SESSION['member_id']
            ]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully removed {$roleName} role from {$memberName}",
        'was_primary' => $isPrimary
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log both to database and debug file
    $errorDetails = [
        'error_message' => $e->getMessage(),
        'member_id' => $memberId ?? null,
        'role_id' => $roleId ?? null,
        'stack_trace' => $e->getTraceAsString(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    try {
        // Try to log to database despite the error
        $errorLogStmt = $pdo->prepare("
            INSERT INTO role_assignment_log 
            (member_id, role_id, action, performed_by, action_details) 
            VALUES (?, ?, 'ERROR', ?, CAST(? AS JSON))
        ");
        
        $errorLogStmt->execute([
            $memberId ?? 0,
            $roleId ?? 0,
            $_SESSION['member_id'],
            json_encode($errorDetails)
        ]);
    } catch (Exception $logError) {
        // If database logging fails, at least log to file
        error_log("Failed to log error to database: " . $logError->getMessage());
    }
    
    // Always log to debug file
    logRoleAssignmentError($e->getMessage(), $errorDetails);
    
    echo json_encode([
        'success' => false,
        'message' => 'Error removing role: ' . $e->getMessage(),
        'action_details' => 'Check role_assignment_debug.log for more information'
    ]);
}
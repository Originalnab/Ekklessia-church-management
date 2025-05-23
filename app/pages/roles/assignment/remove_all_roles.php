<?php
session_start();
include "../../../config/config.php";
include "../../../functions/role_management.php";
include "../../../functions/role_assignment_logger.php";

// Check if user is logged in and has permission
if (!isset($_SESSION['member_id']) || !memberHasPermission($_SESSION['member_id'], 'assign_roles')) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get JSON data from request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Validate data
if (!$data || !isset($data['member_id'])) {
    logRoleAssignmentError("Invalid data provided for role removal", $data);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data provided'
    ]);
    exit;
}

$memberId = (int)$data['member_id'];

// Validate member ID
if ($memberId <= 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid member ID'
    ]);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get member name for messages
    $memberStmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) as member_name FROM members WHERE member_id = ?");
    $memberStmt->execute([$memberId]);
    $memberName = $memberStmt->fetchColumn();
    
    // Get current roles for logging
    $currentRolesStmt = $pdo->prepare("
        SELECT mr.role_id, r.role_name, mr.is_primary
        FROM member_role mr
        JOIN roles r ON mr.role_id = r.role_id
        WHERE mr.member_id = ?
    ");
    $currentRolesStmt->execute([$memberId]);
    $currentRoles = $currentRolesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($currentRoles) === 0) {
        $message = "No roles found for {$memberName} to remove";
        logRoleAssignmentAction(
            $memberId, 
            0, 
            'REMOVE',
            $_SESSION['member_id'],
            ['message' => $message]
        );
    } else {
        // Remove all roles
        $stmtDelete = $pdo->prepare("DELETE FROM member_role WHERE member_id = ?");
        $stmtDelete->execute([$memberId]);
        
        // Log each role removal
        foreach ($currentRoles as $role) {
            logRoleAssignmentAction(
                $memberId,
                $role['role_id'],
                $role['is_primary'] ? 'REMOVE_PRIMARY' : 'REMOVE',
                $_SESSION['member_id'],
                [
                    'role_name' => $role['role_name'],
                    'was_primary' => $role['is_primary']
                ]
            );
        }
        
        $message = "All roles removed from {$memberName}";
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'member_id' => $memberId
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    logRoleAssignmentError("Error removing roles", [
        'error' => $e->getMessage(),
        'member_id' => $memberId
    ]);
    
    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error removing roles: ' . $e->getMessage()
    ]);
}
?>
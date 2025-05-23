<?php
session_start();
include "../../../config/config.php";
include "../../../functions/role_management.php";

// Check if user is logged in - removed permission requirement for now
if (!isset($_SESSION['member_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to continue'
    ]);
    exit;
}

// Get JSON data from request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Validate data
if (!$data || !isset($data['member_id']) || !isset($data['role_id']) || !isset($data['action'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data provided'
    ]);
    exit;
}

$memberId = (int)$data['member_id'];
$roleId = (int)$data['role_id'];
$action = $data['action'];
$makePrimary = isset($data['make_primary']) ? (bool)$data['make_primary'] : false;

// Validate member ID
if ($memberId <= 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid member ID'
    ]);
    exit;
}

// Validate role ID
if ($roleId <= 0 && $action !== 'remove_all') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid role ID'
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
    
    // Get role name for messages
    if ($roleId > 0) {
        $roleStmt = $pdo->prepare("SELECT role_name FROM roles WHERE role_id = ?");
        $roleStmt->execute([$roleId]);
        $roleName = $roleStmt->fetchColumn();
    }
    
    // Handle the requested action
    switch ($action) {
        case 'add':
            // If making this role primary, first remove primary status from other roles
            if ($makePrimary) {
                $stmtUpdatePrimary = $pdo->prepare("
                    UPDATE member_role 
                    SET is_primary = 0
                    WHERE member_id = ?
                ");
                $stmtUpdatePrimary->execute([$memberId]);
            }
            
            // Check if this role is already assigned
            $checkStmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM member_role 
                WHERE member_id = ? AND role_id = ?
            ");
            $checkStmt->execute([$memberId, $roleId]);
            
            if ($checkStmt->fetchColumn() > 0) {
                // Role already exists, update it
                $stmtUpdate = $pdo->prepare("
                    UPDATE member_role 
                    SET is_primary = ?, 
                        assigned_by = ?,
                        assigned_at = NOW()
                    WHERE member_id = ? AND role_id = ?
                ");
                $stmtUpdate->execute([
                    $makePrimary ? 1 : 0, 
                    $_SESSION['member_id'],
                    $memberId,
                    $roleId
                ]);
                
                // Log the update
                $logStmt = $pdo->prepare("
                    INSERT INTO role_assignment_log 
                    (member_id, role_id, action, performed_by, action_details) 
                    VALUES (?, ?, ?, ?, ?)
                ");

                // Convert details to JSON object
                $actionDetails = json_encode([
                    'action_type' => $makePrimary ? 'update_primary' : 'update',
                    'role_name' => $roleName,
                    'is_primary' => $makePrimary,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);

                $logStmt->execute([
                    $memberId, 
                    $roleId, 
                    'ASSIGN',
                    $_SESSION['member_id'],
                    $actionDetails
                ]);
                
                $message = "Role updated for {$memberName}";
            } else {
                // Add the new role
                $stmtInsert = $pdo->prepare("
                    INSERT INTO member_role (member_id, role_id, is_primary, assigned_by, assigned_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmtInsert->execute([
                    $memberId,
                    $roleId,
                    $makePrimary ? 1 : 0,
                    $_SESSION['member_id']
                ]);
                
                // Log the assignment
                $logStmt = $pdo->prepare("
                    INSERT INTO role_assignment_log 
                    (member_id, role_id, action, performed_by, action_details) 
                    VALUES (?, ?, ?, ?, ?)
                ");

                // Convert details to JSON object
                $actionDetails = json_encode([
                    'action_type' => $makePrimary ? 'assign_primary' : 'assign',
                    'role_name' => $roleName,
                    'is_primary' => $makePrimary,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);

                $logStmt->execute([
                    $memberId, 
                    $roleId, 
                    'ASSIGN',
                    $_SESSION['member_id'],
                    $actionDetails
                ]);
                
                $message = "Role assigned to {$memberName}";
            }
            break;
            
        case 'remove':
            // Check if this was the primary role
            $checkPrimaryStmt = $pdo->prepare("
                SELECT is_primary 
                FROM member_role 
                WHERE member_id = ? AND role_id = ?
            ");
            $checkPrimaryStmt->execute([$memberId, $roleId]);
            $wasPrimary = (bool)$checkPrimaryStmt->fetchColumn();
            
            // Remove the role
            $stmtDelete = $pdo->prepare("DELETE FROM member_role WHERE member_id = ? AND role_id = ?");
            $stmtDelete->execute([$memberId, $roleId]);
            
            // Log the removal
            $logStmt = $pdo->prepare("
                INSERT INTO role_assignment_log 
                (member_id, role_id, action, performed_by, action_details) 
                VALUES (?, ?, ?, ?, ?)
            ");

            // Convert details to JSON object
            $actionDetails = json_encode([
                'action_type' => 'remove',
                'role_name' => $roleName,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            $logStmt->execute([
                $memberId, 
                $roleId, 
                'REMOVE',
                $_SESSION['member_id'],
                $actionDetails
            ]);
            
            // If this was the primary role, assign a new primary role if available
            if ($wasPrimary) {
                $nextRoleStmt = $pdo->prepare("
                    SELECT role_id 
                    FROM member_role 
                    WHERE member_id = ? 
                    ORDER BY assigned_at ASC
                    LIMIT 1
                ");
                $nextRoleStmt->execute([$memberId]);
                $nextRoleId = $nextRoleStmt->fetchColumn();
                
                if ($nextRoleId) {
                    $setNewPrimaryStmt = $pdo->prepare("
                        UPDATE member_role 
                        SET is_primary = 1
                        WHERE member_id = ? AND role_id = ?
                    ");
                    $setNewPrimaryStmt->execute([$memberId, $nextRoleId]);
                    
                    // Get the new primary role name
                    $newPrimaryRoleStmt = $pdo->prepare("SELECT role_name FROM roles WHERE role_id = ?");
                    $newPrimaryRoleStmt->execute([$nextRoleId]);
                    $newPrimaryRoleName = $newPrimaryRoleStmt->fetchColumn();
                    
                    // Log setting the new primary role
                    $logStmt = $pdo->prepare("
                        INSERT INTO role_assignment_log 
                        (member_id, role_id, action, performed_by, action_details) 
                        VALUES (?, ?, ?, ?, ?)
                    ");

                    // Convert details to JSON object
                    $actionDetails = json_encode([
                        'action_type' => 'set_new_primary',
                        'role_name' => $newPrimaryRoleName,
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);

                    $logStmt->execute([
                        $memberId, 
                        $nextRoleId, 
                        'UPDATE',
                        $_SESSION['member_id'],
                        $actionDetails
                    ]);
                }
            }
            
            $message = "Role removed from {$memberName}";
            break;
            
        default:
            throw new Exception("Invalid action");
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
    $pdo->rollBack();
    
    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error processing role assignment: ' . $e->getMessage()
    ]);
}
?>
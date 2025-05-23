<?php
session_start();
header('Content-Type: application/json');

include "../../../config/db.php";
include "../../../functions/role_management.php";
include "../../../functions/role_debug_logger.php";

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['member_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

// Extract data
$memberId = isset($data['member_id']) ? intval($data['member_id']) : 0;
$roleId = isset($data['role_id']) ? intval($data['role_id']) : 0;
$isPrimary = isset($data['is_primary']) ? (bool)$data['is_primary'] : false;
$action = isset($data['action']) ? $data['action'] : '';

if ($memberId <= 0 || $roleId <= 0 || empty($action)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Log who is making this change
    $adminId = $_SESSION['member_id'];
    logDebug("Admin $adminId is performing role $action for member $memberId with role $roleId");
    
    switch ($action) {
        case 'add':
            // Check if the role assignment already exists
            $checkQuery = "SELECT COUNT(*) FROM member_role WHERE member_id = ? AND role_id = ?";
            $checkStmt = $pdo->prepare($checkQuery);
            $checkStmt->execute([$memberId, $roleId]);
            
            if ($checkStmt->fetchColumn() > 0) {
                // Role already assigned, update it
                $updateQuery = "UPDATE member_role SET is_primary = ? WHERE member_id = ? AND role_id = ?";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->execute([$isPrimary, $memberId, $roleId]);
                
                // If setting as primary, update other roles to non-primary
                if ($isPrimary) {
                    $resetQuery = "UPDATE member_role SET is_primary = 0 WHERE member_id = ? AND role_id != ?";
                    $resetStmt = $pdo->prepare($resetQuery);
                    $resetStmt->execute([$memberId, $roleId]);
                }
                
                $message = "Role updated successfully";
            } else {
                // Add new role assignment
                $insertQuery = "INSERT INTO member_role (member_id, role_id, is_primary) VALUES (?, ?, ?)";
                $insertStmt = $pdo->prepare($insertQuery);
                $insertStmt->execute([$memberId, $roleId, $isPrimary]);
                
                // If setting as primary, update other roles to non-primary
                if ($isPrimary) {
                    $resetQuery = "UPDATE member_role SET is_primary = 0 WHERE member_id = ? AND role_id != ?";
                    $resetStmt = $pdo->prepare($resetQuery);
                    $resetStmt->execute([$memberId, $roleId]);
                }
                
                // Log to role assignment audit log
                $logQuery = "INSERT INTO role_assignment_log 
                            (member_id, role_id, assigned_by, assigned_at, action) 
                            VALUES (?, ?, ?, NOW(), 'assigned')";
                $logStmt = $pdo->prepare($logQuery);
                $logStmt->execute([$memberId, $roleId, $adminId]);
                
                $message = "Role assigned successfully";
            }
            break;
            
        case 'remove':
            // Remove role assignment
            $removeQuery = "DELETE FROM member_role WHERE member_id = ? AND role_id = ?";
            $removeStmt = $pdo->prepare($removeQuery);
            $removeStmt->execute([$memberId, $roleId]);
            
            // Log to role assignment audit log
            $logQuery = "INSERT INTO role_assignment_log 
                        (member_id, role_id, assigned_by, assigned_at, action) 
                        VALUES (?, ?, ?, NOW(), 'removed')";
            $logStmt = $pdo->prepare($logQuery);
            $logStmt->execute([$memberId, $roleId, $adminId]);
            
            // If this was the primary role, set another one as primary if available
            if ($isPrimary) {
                // Find another role to set as primary
                $findQuery = "SELECT role_id FROM member_role WHERE member_id = ? LIMIT 1";
                $findStmt = $pdo->prepare($findQuery);
                $findStmt->execute([$memberId]);
                
                if ($newPrimaryRoleId = $findStmt->fetchColumn()) {
                    $updateQuery = "UPDATE member_role SET is_primary = 1 WHERE member_id = ? AND role_id = ?";
                    $updateStmt = $pdo->prepare($updateQuery);
                    $updateStmt->execute([$memberId, $newPrimaryRoleId]);
                }
            }
            
            $message = "Role removed successfully";
            break;
            
        case 'set_primary':
            // Set this role as primary
            $updateQuery = "UPDATE member_role SET is_primary = 1 WHERE member_id = ? AND role_id = ?";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute([$memberId, $roleId]);
            
            // Set all other roles as non-primary
            $resetQuery = "UPDATE member_role SET is_primary = 0 WHERE member_id = ? AND role_id != ?";
            $resetStmt = $pdo->prepare($resetQuery);
            $resetStmt->execute([$memberId, $roleId]);
            
            $message = "Primary role updated successfully";
            break;
            
        default:
            throw new Exception("Invalid action specified");
    }
    
    $pdo->commit();
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    
    // Return error
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
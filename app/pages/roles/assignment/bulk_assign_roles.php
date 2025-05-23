<?php
session_start();
include "../../../config/config.php";
include "../../../functions/role_management.php";
include "../../../functions/role_assignment_logger.php"; // Update the include path

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to continue'
    ]);
    logRoleAssignmentError('Unauthorized access attempt - user not logged in', []);
    exit;
}

// Get JSON data from request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Validate data
if (!$data || !isset($data['member_ids']) || !isset($data['role_ids'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data provided'
    ]);
    logRoleAssignmentError('Invalid data provided for bulk role assignment', $data);
    exit;
}

$memberIds = $data['member_ids'];
$roleIds = $data['role_ids'];
$makePrimary = isset($data['make_primary']) ? (bool)$data['make_primary'] : false;

// Validate member IDs
if (!is_array($memberIds) || empty($memberIds)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No members selected'
    ]);
    logRoleAssignmentError('No members selected for bulk role assignment', []);
    exit;
}

// Validate role IDs
if (!is_array($roleIds) || empty($roleIds)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No roles selected'
    ]);
    logRoleAssignmentError('No roles selected for bulk role assignment', []);
    exit;
}

try {
    // Get role names for logging
    $roleNames = [];
    $rolesStmt = $pdo->prepare("SELECT role_id, role_name FROM roles WHERE role_id IN (" . implode(',', array_fill(0, count($roleIds), '?')) . ")");
    $rolesStmt->execute($roleIds);
    while ($role = $rolesStmt->fetch(PDO::FETCH_ASSOC)) {
        $roleNames[$role['role_id']] = $role['role_name'];
    }

    // Validate roles exist
    if (count($roleNames) === 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'No valid roles found'
        ]);
        logRoleAssignmentError('No valid roles found for bulk assignment', ['role_ids' => $roleIds]);
        exit;
    }

    // Start transaction
    $pdo->beginTransaction();

    $successCount = 0;
    $errorCount = 0;
    $skippedCount = 0;

    // Process each member
    foreach ($memberIds as $memberId) {
        $memberId = (int)$memberId;
        
        if ($memberId <= 0) {
            $errorCount++;
            logRoleAssignmentError("Invalid member ID skipped", ['member_id' => $memberId]);
            continue;
        }

        try {
            // If making first role primary, first remove primary status from all existing roles
            if ($makePrimary) {
                $stmtUpdatePrimary = $pdo->prepare("
                    UPDATE member_role 
                    SET is_primary = 0
                    WHERE member_id = ?
                ");
                $stmtUpdatePrimary->execute([$memberId]);
            }

            // Process each role for this member
            foreach ($roleIds as $index => $roleId) {
                $roleId = (int)$roleId;
                $roleName = $roleNames[$roleId] ?? 'Unknown Role';

                if ($roleId <= 0) {
                    logRoleAssignmentError("Invalid role ID skipped", ['role_id' => $roleId]);
                    continue;
                }

                // Set primary flag - only for the first role if makePrimary is true
                $isPrimary = ($makePrimary && $index === 0);

                // Check if this role is already assigned
                $checkStmt = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM member_role 
                    WHERE member_id = ? AND role_id = ?
                ");
                $checkStmt->execute([$memberId, $roleId]);

                if ($checkStmt->fetchColumn() > 0) {
                    // Role already exists, update it if making primary
                    if ($isPrimary) {
                        $stmtUpdate = $pdo->prepare("
                            UPDATE member_role 
                            SET is_primary = 1,
                                assigned_by = ?,
                                assigned_at = NOW()
                            WHERE member_id = ? AND role_id = ?
                        ");
                        $stmtUpdate->execute([
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

                        $actionDetails = json_encode([
                            'action_type' => 'update',
                            'role_name' => $roleName,
                            'is_primary' => true,
                            'timestamp' => date('Y-m-d H:i:s')
                        ]);

                        $logStmt->execute([
                            $memberId,
                            $roleId,
                            'UPDATE',
                            $_SESSION['member_id'],
                            $actionDetails
                        ]);

                        $successCount++;
                    } else {
                        // Skip if already assigned and not making primary
                        $skippedCount++;
                    }
                } else {
                    // Add the new role
                    $stmtInsert = $pdo->prepare("
                        INSERT INTO member_role (member_id, role_id, is_primary, assigned_by, assigned_at)
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                    $stmtInsert->execute([
                        $memberId,
                        $roleId,
                        $isPrimary ? 1 : 0,
                        $_SESSION['member_id']
                    ]);

                    // Log the assignment
                    $logStmt = $pdo->prepare("
                        INSERT INTO role_assignment_log 
                        (member_id, role_id, action, performed_by, action_details) 
                        VALUES (?, ?, ?, ?, ?)
                    ");

                    $actionDetails = json_encode([
                        'action_type' => 'bulk_assign',
                        'role_name' => $roleName,
                        'is_primary' => $isPrimary,
                        'timestamp' => date('Y-m-d H:i:s'),
                        'batch_info' => [
                            'total_roles' => count($roleIds),
                            'role_position' => $index + 1
                        ]
                    ]);

                    $logStmt->execute([
                        $memberId,
                        $roleId,
                        'ASSIGN',
                        $_SESSION['member_id'],
                        $actionDetails
                    ]);

                    $successCount++;
                }
            }
        } catch (Exception $memberEx) {
            $errorCount++;
            logRoleAssignmentError($memberEx->getMessage(), [
                'member_id' => $memberId,
                'error' => $memberEx->getMessage()
            ]);
        }
    }

    // Commit transaction
    $pdo->commit();

    // Get role names as string for message
    $roleNamesString = implode(', ', array_values($roleNames));

    // Prepare summary message
    $messageParts = [];
    if ($successCount > 0) {
        $messageParts[] = "Successfully assigned {$successCount} roles";
    }
    if ($skippedCount > 0) {
        $messageParts[] = "{$skippedCount} role assignments skipped (already exist)";
    }
    if ($errorCount > 0) {
        $messageParts[] = "{$errorCount} assignments failed";
    }

    $message = implode(". ", $messageParts) . ". Roles: {$roleNamesString}";

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'stats' => [
            'success' => $successCount,
            'skipped' => $skippedCount,
            'error' => $errorCount,
            'total' => count($memberIds) * count($roleIds)
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    logRoleAssignmentError($e->getMessage(), [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error processing bulk role assignment: ' . $e->getMessage()
    ]);
}
?>
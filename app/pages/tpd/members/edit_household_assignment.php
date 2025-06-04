<?php
session_start();
include "../../../config/db.php";
include "../../../functions/household_assignment_logger.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logHouseholdError('UPDATE', '', '', 'Invalid request method');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $pdo->beginTransaction();

    $memberId = filter_input(INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT);
    $householdId = filter_input(INPUT_POST, 'household_id', FILTER_SANITIZE_NUMBER_INT);
    $newRole = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

    if (!$memberId || !$householdId || !$newRole) {
        logHouseholdError('UPDATE', $memberId, $householdId, 'Missing required fields');
        throw new Exception('Missing required fields');
    }

    // Get current assignment
    $currentStmt = $pdo->prepare("
        SELECT mh.role, mh.household_id as current_household_id, 
               h.assembly_id as household_assembly_id,
               m.assemblies_id as member_assembly_id,
               m.first_name, m.last_name,
               h.name as household_name
        FROM member_household mh
        JOIN members m ON m.member_id = mh.member_id
        JOIN households h ON h.household_id = mh.household_id
        WHERE mh.member_id = ? AND mh.status = 1
    ");
    $currentStmt->execute([$memberId]);
    $current = $currentStmt->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        logHouseholdError('UPDATE', $memberId, $householdId, 'Member not found or has no active household assignment');
        throw new Exception('Member not found or has no active household assignment');
    }

    // Validate assembly match if changing household
    if ($householdId != $current['current_household_id']) {
        $newHouseholdStmt = $pdo->prepare("SELECT assembly_id, name FROM households WHERE household_id = ? AND status = 1");
        $newHouseholdStmt->execute([$householdId]);
        $newHousehold = $newHouseholdStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$newHousehold) {
            logHouseholdError('UPDATE', $memberId, $householdId, 'New household not found or inactive');
            throw new Exception('New household not found or inactive');
        }

        if ($current['member_assembly_id'] != $newHousehold['assembly_id']) {
            logHouseholdError('UPDATE', $memberId, $householdId, 'Assembly mismatch with new household');
            throw new Exception('Member and new household must belong to the same assembly');
        }
    }

    // Validate role limits for new household
    if ($newRole !== 'regular') {
        $validateStmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN role = 'leader' AND status = 1 THEN 1 ELSE 0 END) as leaders,
                SUM(CASE WHEN role = 'assistant' AND status = 1 THEN 1 ELSE 0 END) as assistants
            FROM member_household 
            WHERE household_id = ? AND member_id != ?
        ");
        $validateStmt->execute([$householdId, $memberId]);
        $counts = $validateStmt->fetch(PDO::FETCH_ASSOC);

        if ($newRole === 'leader' && $counts['leaders'] > 0) {
            logHouseholdError('UPDATE', $memberId, $householdId, 'Household already has a leader');
            throw new Exception('This household already has a leader assigned');
        }
        if ($newRole === 'assistant' && $counts['assistants'] >= 2) {
            logHouseholdError('UPDATE', $memberId, $householdId, 'Household already has maximum assistants');
            throw new Exception('This household already has the maximum number of assistants (2)');
        }
    }

    // Update the assignment
    $updateStmt = $pdo->prepare("
        UPDATE member_household 
        SET household_id = ?,
            role = ?,
            updated_at = NOW(),
            updated_by = ?
        WHERE member_id = ? AND status = 1
    ");

    $updatedBy = $_SESSION['username'] ?? 'system';
    $updateStmt->execute([
        $householdId,
        $newRole,
        $updatedBy,
        $memberId
    ]);

    // Log the successful update
    $logMessage = sprintf(
        "Updated assignment for %s %s: Changed role from %s to %s%s",
        $current['first_name'],
        $current['last_name'],
        $current['role'],
        $newRole,
        $householdId != $current['current_household_id'] 
            ? sprintf(" and moved to new household: %s", 
                $newHousehold['name'] ?? 'Unknown'
            )
            : ""
    );

    logHouseholdAssignment(
        'UPDATE',
        $memberId,
        $householdId,
        $newRole,
        $updatedBy,
        true,
        $logMessage
    );

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Household assignment updated successfully',
        'details' => $logMessage
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

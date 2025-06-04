<?php
session_start();
include "../../../config/db.php";
include "../../../functions/household_assignment_logger.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logHouseholdError('INVALID_REQUEST', '', '', 'Invalid request method');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Validate and sanitize input
    $memberId = filter_input(INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT);
    $assembliesId = filter_input(INPUT_POST, 'assemblies_id', FILTER_SANITIZE_NUMBER_INT);
    $householdId = filter_input(INPUT_POST, 'household_id', FILTER_SANITIZE_NUMBER_INT);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

    if (!$memberId || !$householdId || !$role) {
        throw new Exception('Missing required fields');
    }

    // Validate member exists and is active
    $memberStmt = $pdo->prepare("SELECT member_id, assemblies_id, first_name, last_name FROM members WHERE member_id = ? AND status IN ('Active saint', 'Worker', 'Committed saint')");
    $memberStmt->execute([$memberId]);
    $member = $memberStmt->fetch(PDO::FETCH_ASSOC);
    if (!$member) {
        logHouseholdError('VALIDATION', $memberId, $householdId, 'Member not found or not in active status');
        throw new Exception('Member not found or not in active status (Active saint, Worker, or Committed saint)');
    }

    // Get assembly and name of the household
    $householdStmt = $pdo->prepare("SELECT h.household_id, h.name, h.assembly_id FROM households h WHERE h.household_id = ? AND h.status = 1");
    $householdStmt->execute([$householdId]);
    $household = $householdStmt->fetch(PDO::FETCH_ASSOC);
    if (!$household) {
        logHouseholdError('VALIDATION', $memberId, $householdId, 'Household not found or inactive');
        throw new Exception('Household not found or inactive');
    }

    // Verify assembly match
    if ($member['assemblies_id'] != $household['assembly_id']) {
        logHouseholdError('VALIDATION', $memberId, $householdId, 'Assembly mismatch');
        throw new Exception('Member and household must belong to the same assembly');
    }

    // Check if member already has a household assignment
    $checkStmt = $pdo->prepare("SELECT household_id, role FROM member_household WHERE member_id = ? AND status = 1");
    $checkStmt->execute([$memberId]);
    if ($checkStmt->fetch()) {
        logHouseholdError('VALIDATION', $memberId, $householdId, 'Member already has an active household assignment');
        throw new Exception('Member already has an active household assignment');
    }

    // Validate role and check limits
    $validateStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN role = 'leader' AND status = 1 THEN 1 ELSE 0 END) as leaders,
            SUM(CASE WHEN role = 'assistant' AND status = 1 THEN 1 ELSE 0 END) as assistants
        FROM member_household 
        WHERE household_id = ?
    ");
    $validateStmt->execute([$householdId]);
    $counts = $validateStmt->fetch(PDO::FETCH_ASSOC);

    switch ($role) {
        case 'leader':
            if ($counts['leaders'] > 0) {
                logHouseholdError('VALIDATION', $memberId, $householdId, 'Household already has a leader');
                throw new Exception('This household already has a leader assigned');
            }
            break;
        case 'assistant':
            if ($counts['assistants'] >= 2) {
                logHouseholdError('VALIDATION', $memberId, $householdId, 'Maximum assistants limit reached');
                throw new Exception('This household already has the maximum number of assistants (2)');
            }
            break;
        case 'regular':
            break;
        default:
            logHouseholdError('VALIDATION', $memberId, $householdId, 'Invalid role selected');
            throw new Exception('Invalid role selected');
    }

    // All validations passed, perform the assignment
    $insertStmt = $pdo->prepare("
        INSERT INTO member_household (
            member_id,
            household_id,
            role,
            status,
            assigned_by,
            assigned_at,
            updated_at
        ) VALUES (?, ?, ?, 1, ?, NOW(), NOW())
    ");

    $assignedBy = $_SESSION['username'] ?? 'system';
    $insertStmt->execute([
        $memberId,
        $householdId,
        $role,
        $assignedBy
    ]);

    // Log the successful assignment
    $memberName = $member['first_name'] . ' ' . $member['last_name'];
    $successMessage = "Successfully assigned $memberName to household {$household['name']} as $role";
    logHouseholdAssignment('ASSIGN', $memberId, $householdId, $role, $assignedBy, true, $successMessage);

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => $successMessage
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    logHouseholdError('ERROR', $memberId ?? '', $householdId ?? '', $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

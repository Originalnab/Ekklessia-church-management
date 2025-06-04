<?php
header('Content-Type: application/json');
include "../../../config/db.php";

$household_id = filter_input(INPUT_GET, 'household_id', FILTER_SANITIZE_NUMBER_INT);
$role = filter_input(INPUT_GET, 'role', FILTER_SANITIZE_STRING);

try {
    if (!$household_id || !$role) {
        throw new Exception('Missing required parameters');
    }

    // Check if household exists and is active
    $householdStmt = $pdo->prepare("SELECT status FROM households WHERE household_id = ?");
    $householdStmt->execute([$household_id]);
    
    if (!$householdStmt->fetchColumn()) {
        throw new Exception('Household not found or inactive');
    }

    // Count current members by role
    $roleStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN role = 'leader' AND status = 1 THEN 1 ELSE 0 END) as leaders,
            SUM(CASE WHEN role = 'assistant' AND status = 1 THEN 1 ELSE 0 END) as assistants
        FROM member_household 
        WHERE household_id = ? AND status = 1
    ");
    $roleStmt->execute([$household_id]);
    $counts = $roleStmt->fetch(PDO::FETCH_ASSOC);

    $allowed = true;
    $message = '';

    switch ($role) {
        case 'leader':
            if ($counts['leaders'] > 0) {
                $allowed = false;
                $message = 'This household already has a leader assigned';
            }
            break;
            
        case 'assistant':
            if ($counts['assistants'] >= 2) {
                $allowed = false;
                $message = 'This household already has the maximum number of assistants (2)';
            }
            break;

        case 'regular':
            // No special validation for regular members
            break;
            
        default:
            throw new Exception('Invalid role selected');
    }

    echo json_encode([
        'success' => true,
        'allowed' => $allowed,
        'message' => $message
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'allowed' => false,
        'message' => $e->getMessage()
    ]);
}

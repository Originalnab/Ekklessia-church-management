<?php
include "../../../config/db.php";

// Validate and sanitize input
$householdId = filter_input(INPUT_GET, 'household_id', FILTER_SANITIZE_NUMBER_INT);

if (!$householdId) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid household ID provided'
    ]);
    exit;
}

try {
    // First check if this household exists and is active
    $checkStmt = $pdo->prepare("SELECT household_id FROM households WHERE household_id = ? AND status = 1");
    $checkStmt->execute([$householdId]);
    if (!$checkStmt->fetch()) {
        throw new Exception('Household not found or inactive');
    }

    // Get household members with their roles
    $stmt = $pdo->prepare("
        SELECT 
            m.member_id,
            CONCAT(m.first_name, ' ', m.last_name) as name,
            m.contact,
            mh.role,
            mh.status
        FROM members m
        INNER JOIN member_household mh ON m.member_id = mh.member_id
        WHERE mh.household_id = ? AND mh.status = 1
        ORDER BY 
            CASE mh.role 
                WHEN 'leader' THEN 1 
                WHEN 'assistant' THEN 2 
                ELSE 3 
            END,
            m.first_name, m.last_name
    ");
    
    $stmt->execute([$householdId]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get role counts for validation
    $roleStmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN role = 'leader' THEN 1 ELSE 0 END) as leader_count,
            SUM(CASE WHEN role = 'assistant' THEN 1 ELSE 0 END) as assistant_count
        FROM member_household 
        WHERE household_id = ? AND status = 1
    ");
    $roleStmt->execute([$householdId]);
    $roleCounts = $roleStmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'members' => $members,
        'roleCounts' => [
            'hasLeader' => ($roleCounts['leader_count'] > 0),
            'assistantCount' => (int)$roleCounts['assistant_count']
        ]
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load household members: ' . $e->getMessage()
    ]);
}

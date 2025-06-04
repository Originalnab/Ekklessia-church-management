<?php
header('Content-Type: application/json');
include "../../../config/db.php";

$household_id = filter_input(INPUT_GET, 'household_id', FILTER_SANITIZE_NUMBER_INT);

try {
    if (!$household_id) {
        throw new Exception('Missing household ID');
    }

    // Get basic household information
    $householdStmt = $pdo->prepare("
        SELECT h.*, a.name as assembly_name
        FROM households h
        LEFT JOIN assemblies a ON h.assembly_id = a.assembly_id
        WHERE h.household_id = ? AND h.status = 1
    ");
    $householdStmt->execute([$household_id]);
    $household = $householdStmt->fetch(PDO::FETCH_ASSOC);

    if (!$household) {
        throw new Exception('Household not found or inactive');
    }

    // Get all members of the household with their roles
    $membersStmt = $pdo->prepare("
        SELECT 
            m.member_id,
            m.first_name,
            m.last_name,
            m.photo,
            mh.role,
            a.name as assembly_name
        FROM member_household mh
        JOIN members m ON mh.member_id = m.member_id
        LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
        WHERE mh.household_id = ? AND mh.status = 1
        ORDER BY 
            CASE mh.role 
                WHEN 'leader' THEN 1 
                WHEN 'assistant' THEN 2 
                ELSE 3 
            END,
            m.first_name
    ");
    $membersStmt->execute([$household_id]);
    $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Process members and count roles
    $leader = null;
    $assistants = [];
    $regularMembers = [];

    foreach ($members as $member) {
        $memberInfo = [
            'member_id' => $member['member_id'],
            'name' => $member['first_name'] . ' ' . $member['last_name'],
            'photo' => $member['photo'],
            'role' => $member['role'],
            'assembly' => $member['assembly_name']
        ];

        switch ($member['role']) {
            case 'leader':
                $leader = $memberInfo;
                break;
            case 'assistant':
                $assistants[] = $memberInfo;
                break;
            default:
                $regularMembers[] = $memberInfo;
        }
    }

    echo json_encode([
        'success' => true,
        'household' => [
            'name' => $household['name'],
            'address' => $household['address'],
            'assembly' => $household['assembly_name'],
            'memberCount' => count($members),
            'leader' => $leader ? $leader['name'] : null,
            'assistants' => $assistants,
            'members' => array_merge(
                $leader ? [$leader] : [],
                $assistants,
                $regularMembers
            )
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

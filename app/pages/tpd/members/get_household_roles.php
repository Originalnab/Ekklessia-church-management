<?php
include "../../../config/db.php";

header('Content-Type: application/json');

try {
    $household_id = $_GET['household_id'] ?? null;
    
    if (!$household_id) {
        throw new Exception('Household ID is required');
    }

    // Get current household shepherd
    $stmt = $pdo->prepare("
        SELECT m.member_id, m.first_name, m.last_name
        FROM member_household mh
        JOIN members m ON m.member_id = mh.member_id
        WHERE mh.household_id = ? AND mh.role = 'shepherd'
    ");
    $stmt->execute([$household_id]);
    $shepherd = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get current assistant count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as assistant_count
        FROM member_household
        WHERE household_id = ? AND role = 'assistant'
    ");
    $stmt->execute([$household_id]);
    $assistantCount = $stmt->fetch(PDO::FETCH_ASSOC)['assistant_count'];

    // Configuration for maximum assistants per household (adjust as needed)
    $maxAssistants = 3;

    echo json_encode([
        'success' => true,
        'hasShepherd' => !empty($shepherd),
        'shepherd' => $shepherd,
        'assistantCount' => (int)$assistantCount,
        'maxAssistants' => $maxAssistants
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

<?php
ob_start();
include "../../../config/db.php";

header('Content-Type: application/json');

try {
    $member_id = $_GET['member_id'] ?? null;
    if (!$member_id) {
        echo json_encode(['household_id' => '', 'shepherd_id' => '']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT household_id, shepherd_id FROM member_household WHERE member_id = ?");
    $stmt->execute([$member_id]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($assignment) {
        echo json_encode([
            'household_id' => $assignment['household_id'] ?? '',
            'shepherd_id' => $assignment['shepherd_id'] ?? ''
        ]);
    } else {
        echo json_encode(['household_id' => '', 'shepherd_id' => '']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

ob_end_flush();
?>
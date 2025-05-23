<?php
session_start();
include "../../../config/db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['household_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$household_id = filter_input(INPUT_POST, 'household_id', FILTER_VALIDATE_INT);
if (!$household_id) {
    echo json_encode(['error' => 'Invalid household ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT m.member_id, m.first_name, m.last_name
        FROM members m
        JOIN member_household mh ON m.member_id = mh.member_id
        WHERE mh.household_id = ?
    ");
    $stmt->execute([$household_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($members);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
exit;
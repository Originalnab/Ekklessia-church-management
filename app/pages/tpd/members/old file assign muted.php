<?php
// app/pages/tpd/members/assign_household_process.php
session_start();
include "../../../config/db.php";

header('Content-Type: application/json');

$member_id = $_POST['member_id'] ?? null;
$household_id = $_POST['household_id'] ?? null;
$shepherd_id = $_POST['shepherd_id'] ?? null;

if (!$member_id || !$household_id || !$shepherd_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE members SET household_id = ?, shepherd_id = ? WHERE member_id = ?");
    $stmt->execute([$household_id, $shepherd_id, $member_id]);
    echo json_encode(['success' => true, 'message' => 'Member assigned successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}
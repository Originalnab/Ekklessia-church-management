<?php
header('Content-Type: application/json');
include "../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_POST['member_id']) || empty($_POST['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Member ID is required.']);
    exit;
}

$member_id = (int)$_POST['member_id'];

try {
    // Begin transaction to ensure data consistency
    $pdo->beginTransaction();

    // Delete related records from member_household
    $stmt = $pdo->prepare("DELETE FROM member_household WHERE member_id = ?");
    $stmt->execute([$member_id]);

    // Delete the member from the members table
    $stmt = $pdo->prepare("DELETE FROM members WHERE member_id = ?");
    $stmt->execute([$member_id]);

    // Check if the member was deleted
    if ($stmt->rowCount() > 0) {
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Member deleted successfully.']);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Member not found.']);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error deleting member: ' . $e->getMessage()]);
}
?>
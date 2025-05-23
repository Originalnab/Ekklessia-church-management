<?php
session_start();
include "../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = $_POST['member_id'] ?? null;
    $assembly_id = $_POST['assembly_id'] ?? null;

    if (!$member_id || !$assembly_id) {
        echo json_encode(['success' => false, 'message' => 'Member and assembly are required.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Update the member's assembly_id
        $stmt = $pdo->prepare("UPDATE members SET assemblies_id = ? WHERE member_id = ?");
        $stmt->execute([$assembly_id, $member_id]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error assigning assembly: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
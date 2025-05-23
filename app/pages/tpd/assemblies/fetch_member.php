<?php
header('Content-Type: application/json');
include "../../../config/db.php";

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Member ID not provided.']);
    exit;
}

$member_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT m.*, a.name AS assembly_name, cf.function_name AS role_name
        FROM members m
        LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
        LEFT JOIN church_functions cf ON m.local_function_id = cf.function_id
        WHERE m.member_id = ?
    ");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        echo json_encode(['success' => false, 'message' => 'Member not found.']);
        exit;
    }

    echo json_encode(['success' => true, 'member' => $member]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching member: ' . $e->getMessage()]);
    exit;
}
?>
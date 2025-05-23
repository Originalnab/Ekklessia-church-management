<?php
session_start();
include "../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$default_frequency = $_POST['default_frequency'] ?? null;
$level = $_POST['level'] ?? '';
$is_recurring = isset($_POST['is_recurring']) ? 1 : 0;

if (!$name || !$level) {
    echo json_encode(['success' => false, 'message' => 'Name and Level are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO event_types (name, description, default_frequency, level, is_recurring, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$name, $description, $default_frequency, $level, $is_recurring]);

    $_SESSION['success_message'] = 'Event type added successfully';
    echo json_encode(['success' => true, 'message' => 'Event type added successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error adding event type: ' . $e->getMessage()]);
}
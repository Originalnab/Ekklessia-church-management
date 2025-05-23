<?php
session_start();
include "../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$event_type_id = $_POST['event_type_id'] ?? 0;
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$default_frequency = $_POST['default_frequency'] ?? null;
$level = $_POST['level'] ?? '';
$is_recurring = isset($_POST['is_recurring']) ? 1 : 0;

if (!$event_type_id || !$name || !$level) {
    echo json_encode(['success' => false, 'message' => 'Event Type ID, Name, and Level are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE event_types
        SET name = ?, description = ?, default_frequency = ?, level = ?, is_recurring = ?, updated_at = NOW()
        WHERE event_type_id = ?
    ");
    $stmt->execute([$name, $description, $default_frequency, $level, $is_recurring, $event_type_id]);

    $_SESSION['success_message'] = 'Event type updated successfully';
    echo json_encode(['success' => true, 'message' => 'Event type updated successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating event type: ' . $e->getMessage()]);
}
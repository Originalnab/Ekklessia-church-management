<?php
session_start();
include "../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$event_type_id = $_POST['event_type_id'] ?? 0;

if (!$event_type_id) {
    echo json_encode(['success' => false, 'message' => 'Event Type ID is required']);
    exit;
}

try {
    // Check if the event type is used in any events
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE event_type_id = ?");
    $stmt->execute([$event_type_id]);
    $event_count = $stmt->fetchColumn();

    if ($event_count > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete event type because it is associated with existing events']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM event_types WHERE event_type_id = ?");
    $stmt->execute([$event_type_id]);

    $_SESSION['success_message'] = 'Event type deleted successfully';
    echo json_encode(['success' => true, 'message' => 'Event type deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error deleting event type: ' . $e->getMessage()]);
}
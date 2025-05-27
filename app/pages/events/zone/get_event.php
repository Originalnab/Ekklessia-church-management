<?php
// Returns JSON details for a single zone event
session_start();
header('Content-Type: application/json');
require_once '../../../config/db.php';
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
    exit;
}
try {
    $stmt = $pdo->prepare(
        "SELECT e.event_id, e.title, e.description, e.start_date, e.end_date, e.is_recurring,
                et.name AS event_type, COALESCE(z.name, 'Not Assigned') AS zone_name
         FROM events e
         LEFT JOIN event_types et ON e.event_type_id = et.event_type_id
         LEFT JOIN zones z ON e.zone_id = z.zone_id
         WHERE e.event_id = ? AND e.level = 3
         LIMIT 1"
    );
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
    } else {
        echo json_encode(['success' => true, 'event' => $event]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error', 'error' => $e->getMessage()]);
}

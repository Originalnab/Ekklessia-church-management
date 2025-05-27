<?php
// Assembly Events - View Event Details (AJAX)
session_start();
header('Content-Type: application/json');
include '../../../config/db.php';

if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT e.*, et.name as event_type, a.name as assembly_name, CONCAT(m.first_name, ' ', m.last_name) as created_by_name FROM events e LEFT JOIN event_types et ON e.event_type_id = et.event_type_id LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id LEFT JOIN members m ON e.created_by = m.member_id WHERE e.event_id = ? LIMIT 1");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }
    echo json_encode(['success' => true, 'event' => $event]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching event details']);
}

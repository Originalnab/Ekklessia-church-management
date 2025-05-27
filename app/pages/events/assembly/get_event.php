<?php
// Assembly Events - Get Single Event Details for Edit (AJAX)
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
    $stmt = $pdo->prepare("SELECT e.*, et.name as event_type, a.name as assembly_name FROM events e LEFT JOIN event_types et ON e.event_type_id = et.event_type_id LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id WHERE e.event_id = ? LIMIT 1");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }
    // Also fetch all assemblies and event types for dropdowns
    $assemblies = $pdo->query("SELECT assembly_id, name FROM assemblies ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $event_types = $pdo->query("SELECT event_type_id, name FROM event_types WHERE level = 'assembly' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'event' => $event, 'assemblies' => $assemblies, 'event_types' => $event_types]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching event details']);
}

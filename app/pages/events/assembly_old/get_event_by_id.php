<?php
// get_event_by_id.php - Fetch an assembly event by ID
session_start();
header('Content-Type: application/json');
include "../../../config/db.php";

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Check if event_id parameter is provided
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
    exit;
}

$event_id = intval($_GET['event_id']);

try {
    // Query for the full event details
    // Use level = 2 for assembly events (integer)
    $sql = "SELECT 
                e.event_id, 
                e.title as event_name, 
                e.description, 
                e.start_date, 
                e.end_date, 
                e.is_recurring, 
                e.frequency, 
                e.recurrence_day,
                e.event_type_id,
                et.name as event_type_name,
                e.assembly_id,
                a.name as assembly_name,
                e.created_at,
                e.updated_at
            FROM events e
            LEFT JOIN event_types et ON e.event_type_id = et.event_type_id
            LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id
            WHERE e.event_id = ? AND e.level = 2";
    
    // Log SQL and params
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " - get_event_by_id SQL: $sql | Params: [" . json_encode([$event_id]) . "]\n", FILE_APPEND);

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$event_id]);
    
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        $msg = "Event not found for event_id=$event_id and level=2 (assembly)";
        file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " - get_event_by_id ERROR: $msg\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Event not found. Please check if the event exists and is an assembly event (level=2).']);
        exit;
    }
    
    // Format dates for consistent processing
    if (isset($event['start_date'])) {
        $event['start_date'] = date('Y-m-d H:i:s', strtotime($event['start_date']));
    }
    
    if (isset($event['end_date'])) {
        $event['end_date'] = date('Y-m-d H:i:s', strtotime($event['end_date']));
    }

    // Log success
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " - get_event_by_id SUCCESS: " . json_encode($event) . "\n", FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'event' => $event
    ]);
    
} catch (PDOException $e) {
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - get_event_by_id EXCEPTION: ' . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

<?php
// Robust add_event_process.php for zone events
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

header('Content-Type: application/json');
require_once '../../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    // Get input data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid input data');
    }

    // Validate required fields
    $requiredFields = ['title', 'start_date', 'end_date', 'event_type_id', 'zone_id'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Insert event with scope_id=3 for zone level
    $sql = "INSERT INTO events (
                title, 
                description, 
                start_date, 
                end_date, 
                event_type_id, 
                zone_id, 
                scope_id,
                created_by,
                created_at,
                is_recurring,
                recurring_pattern
            ) VALUES (
                :title, 
                :description, 
                :start_date, 
                :end_date, 
                :event_type_id, 
                :zone_id, 
                3,
                :created_by,
                NOW(),
                :is_recurring,
                :recurring_pattern
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $data['title'],
        ':description' => $data['description'] ?? '',
        ':start_date' => $data['start_date'],
        ':end_date' => $data['end_date'],
        ':event_type_id' => $data['event_type_id'],
        ':zone_id' => $data['zone_id'],
        ':created_by' => $_SESSION['member_id'],
        ':is_recurring' => isset($data['is_recurring']) ? 1 : 0,
        ':recurring_pattern' => $data['recurring_pattern'] ?? null
    ]);

    $eventId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Event created successfully',
        'event_id' => $eventId
    ]);

} catch (Exception $e) {
    error_log("Error creating zone event: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error creating event: ' . $e->getMessage()
    ]);
}

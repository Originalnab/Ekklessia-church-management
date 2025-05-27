<?php
// Process Delete National Event
session_start();
require_once '../../../config/db.php';
header('Content-Type: application/json');

function log_event_debug($message) {
    $logfile = __DIR__ . '/debug.log';
    $date = date('Y-m-d H:i:s');
    file_put_contents($logfile, "[$date] $message\n", FILE_APPEND);
}

try {
    if (!isset($_SESSION['member_id'])) {
        log_event_debug('Delete Event: Not authenticated');
        throw new Exception('Not authenticated.');
    }
    
    // Handle JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $event_id = isset($input['eventId']) ? intval($input['eventId']) : 0;
    
    // Fallback to POST data if JSON input is not available
    if (!$event_id) {
        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    }
    
    if (!$event_id) {
        log_event_debug('Delete Event: Missing event_id');
        throw new Exception('Event ID is required.');
    }
    $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ? AND level = 4");
    $stmt->execute([$event_id]);
    if ($stmt->rowCount() > 0) {
        log_event_debug("Delete Event: Success - ID: $event_id, By: {$_SESSION['member_id']}");
        echo json_encode(['success' => true, 'message' => 'National event deleted successfully.']);
    } else {
        log_event_debug("Delete Event: Failed - ID: $event_id (not found or not national)");
        echo json_encode(['success' => false, 'message' => 'Event not found or not a national event.']);
    }
} catch (Exception $e) {
    log_event_debug('Delete Event: Error - ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

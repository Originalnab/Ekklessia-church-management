<?php
// Assembly Events - Delete Event Process
session_start();
include "../../../config/db.php";

header('Content-Type: application/json');

function logToDebug($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $user_id = $_SESSION['member_id'] ?? 'unknown';
    $log_entry = "[$timestamp] [$level] [Assembly Events] [User: $user_id] $message" . PHP_EOL;
    $log_file = '../../../debug.log';
    if (!file_exists($log_file)) {
        file_put_contents($log_file, "Debug Log Started - " . date('Y-m-d H:i:s') . PHP_EOL);
    }
    error_log($log_entry, 3, $log_file);
}

logToDebug('delete_event_process.php called');

if (!isset($_SESSION['member_id'])) {
    logToDebug("Authentication failed - no member_id in session", 'ERROR');
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logToDebug("Invalid request method: " . $_SERVER['REQUEST_METHOD'], 'ERROR');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get event ID from POST data
$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

if (!$event_id) {
    logToDebug("Invalid event ID", 'ERROR');
    echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
    exit;
}

try {
    // First, get the event details for logging
    $stmt = $pdo->prepare("SELECT title FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        logToDebug("Event not found: event_id=$event_id", 'ERROR');
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Delete any related records (like attendees, if applicable)
    // Example: $stmt = $pdo->prepare("DELETE FROM event_attendees WHERE event_id = ?");
    // $stmt->execute([$event_id]);
    
    // Delete the event
    $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
    $result = $stmt->execute([$event_id]);
    
    if ($result) {
        $pdo->commit();
        logToDebug("Event deleted successfully: event_id=$event_id, title={$event['title']}", 'SUCCESS');
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
    } else {
        $pdo->rollBack();
        logToDebug("Failed to delete event: event_id=$event_id", 'ERROR');
        echo json_encode(['success' => false, 'message' => 'Failed to delete event']);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error_details = "PDO Error: " . $e->getMessage() . " | Code: " . $e->getCode() . " | File: " . $e->getFile() . " | Line: " . $e->getLine();
    logToDebug($error_details, 'ERROR');
    echo json_encode(['success' => false, 'message' => 'Database error occurred while deleting event. Please try again.']);
}
?>

<?php
// add_event.php for zone events
header('Content-Type: application/json');
require_once '../../../config/db.php';

$log_file = __DIR__ . '/debug.log';
function log_event($msg) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - ' . $msg . "\n", FILE_APPEND);
}

try {
    $eventName = trim($_POST['eventName'] ?? '');
    $eventType = trim($_POST['eventType'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $endDate = trim($_POST['end_date'] ?? '');
    $isRecurring = isset($_POST['isRecurring']) && $_POST['isRecurring'] ? 1 : 0;
    $recurrenceFrequency = trim($_POST['recurrenceFrequency'] ?? '');
    $level = 'zone';

    if (!$eventName || !$eventType || !$startDate || !$endDate) {
        log_event("Add Event Error: Missing required fields: " . json_encode($_POST));
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO events (event_name, event_type_id, start_date, end_date, is_recurring, recurrence_frequency, level) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$eventName, $eventType, $startDate, $endDate, $isRecurring, $recurrenceFrequency, $level]);

    log_event("Event added successfully: $eventName");
    echo json_encode(['success' => true, 'message' => 'Event added successfully!']);
} catch (PDOException $e) {
    log_event("Add Event Error: " . $e->getMessage() . ' | POST: ' . json_encode($_POST));
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

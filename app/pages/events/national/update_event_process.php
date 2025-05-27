<?php
// Process Edit National Event
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
        log_event_debug('Edit Event: Not authenticated');
        throw new Exception('Not authenticated.');
    }    $event_id = isset($_POST['eventId']) ? intval($_POST['eventId']) : 0;
    $event_title = trim($_POST['eventName'] ?? '');
    $event_type_id = isset($_POST['eventType']) ? intval($_POST['eventType']) : null;
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $is_recurring = isset($_POST['isRecurring']) ? 1 : 0;
    $frequency = trim($_POST['recurrenceFrequency'] ?? '');
    $level = 4; // 4 for national level (scope_id)
    $updated_by = $_SESSION['member_id'];

    if (!$event_id || $event_title === '' || !$event_type_id || $start_date === '' || $end_date === '') {
        log_event_debug('Edit Event: Missing required fields');
        throw new Exception('All required fields must be filled.');
    }

    $stmt = $pdo->prepare("UPDATE events SET event_type_id=?, title=?, start_date=?, end_date=?, is_recurring=?, frequency=?, level=?, updated_at=NOW() WHERE event_id=?");
    $stmt->execute([
        $event_type_id,
        $event_title,
        $start_date,
        $end_date,
        $is_recurring,
        $frequency !== '' ? $frequency : null,
        $level,
        $event_id
    ]);

    log_event_debug("Edit Event: Success - ID: $event_id, Title: $event_title, Type: $event_type_id, Start: $start_date, End: $end_date, Recurring: $is_recurring, Frequency: $frequency, By: $updated_by");
    echo json_encode(['success' => true, 'message' => 'National event updated successfully.']);
} catch (Exception $e) {
    log_event_debug('Edit Event: Error - ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

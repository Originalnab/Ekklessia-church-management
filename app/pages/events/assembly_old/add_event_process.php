<?php
// Process Add Assembly Event
session_start();
require_once '../../../config/db.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['member_id'])) {
        throw new Exception('Not authenticated.');
    }

    $assembly_id = isset($_POST['assembly_id']) ? intval($_POST['assembly_id']) : 0;
    $event_title = trim($_POST['eventName'] ?? '');
    $event_type_id = isset($_POST['eventType']) ? intval($_POST['eventType']) : null;
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $is_recurring = !empty($_POST['recurrenceFrequency']) ? 1 : 0;
    $frequency = trim($_POST['recurrenceFrequency'] ?? '');
    $level = 2; // Always use numeric value for assembly scope_id

    // Validate that the level exists in scopes table
    $scope_check = $pdo->prepare("SELECT COUNT(*) FROM scopes WHERE scope_id = ?");
    $scope_check->execute([$level]);
    if ($scope_check->fetchColumn() == 0) {
        throw new Exception('Invalid scope_id for assembly.');
    }

    if (!$assembly_id || !$event_title || !$event_type_id || !$start_date || !$end_date) {
        throw new Exception('All required fields must be filled.');
    }

    $stmt = $pdo->prepare("INSERT INTO events (title, event_type_id, assembly_id, start_date, end_date, is_recurring, frequency, level, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([
        $event_title,
        $event_type_id,
        $assembly_id,
        $start_date,
        $end_date,
        $is_recurring,
        $is_recurring ? $frequency : null,
        $level, // This is the numeric scope_id for assembly
        $_SESSION['member_id']
    ]);

    // Log success
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - Event added successfully: ' . $event_title . "\n", FILE_APPEND);

    echo json_encode(['success' => true, 'message' => 'Event added successfully.']);
    exit;
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - add_event_process EXCEPTION: ' . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

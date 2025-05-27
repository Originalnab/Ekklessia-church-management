<?php
// Assembly Events - Edit Event Process
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

$event_id = intval($_POST['event_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$event_type_id = trim($_POST['event_type_id'] ?? '');
$assembly_id = trim($_POST['assembly_id'] ?? '');
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
$frequency = $is_recurring ? ($_POST['frequency'] ?? null) : null;

$errors = [];
if (!$event_id) $errors[] = 'Invalid event ID';
if (empty($title)) $errors[] = 'Event title is required';
if (empty($event_type_id)) $errors[] = 'Event type is required';
if (empty($assembly_id)) $errors[] = 'Assembly is required';
if (empty($start_date)) $errors[] = 'Start date is required';
if (empty($end_date)) $errors[] = 'End date is required';
if ($is_recurring && empty($frequency)) $errors[] = 'Frequency is required for recurring events';

if (!empty($start_date) && !empty($end_date)) {
    $start_datetime = DateTime::createFromFormat('Y-m-d\TH:i', $start_date);
    if (!$start_datetime) $start_datetime = DateTime::createFromFormat('Y-m-d', $start_date);
    $end_datetime = DateTime::createFromFormat('Y-m-d\TH:i', $end_date);
    if (!$end_datetime) $end_datetime = DateTime::createFromFormat('Y-m-d', $end_date);
    if (!$start_datetime || !$end_datetime) {
        $errors[] = 'Invalid date/time format';
    } elseif ($start_datetime > $end_datetime) {
        $errors[] = 'End date/time must be after start date/time';
    }
}

if (!empty($errors)) {
    logToDebug("Validation failed: " . implode(', ', $errors), 'ERROR');
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    logToDebug("Attempting to update event ID: $event_id");
    $stmt = $pdo->prepare("UPDATE events SET title=?, description=?, event_type_id=?, assembly_id=?, start_date=?, end_date=?, is_recurring=?, frequency=? WHERE event_id=?");
    $result = $stmt->execute([
        $title,
        $description,
        $event_type_id,
        $assembly_id,
        $start_date,
        $end_date,
        $is_recurring,
        $frequency,
        $event_id
    ]);
    if ($result) {
        logToDebug("SUCCESS: Event ID $event_id updated successfully");
        echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
    } else {
        logToDebug("Failed to update event ID: $event_id", 'ERROR');
        echo json_encode(['success' => false, 'message' => 'Failed to update event']);
    }
} catch (PDOException $e) {
    logToDebug("PDO Error: " . $e->getMessage() . " | Code: " . $e->getCode() . " | File: " . $e->getFile() . " | Line: " . $e->getLine(), 'ERROR');
    echo json_encode(['success' => false, 'message' => 'Database error occurred while updating event. Please try again.']);
}

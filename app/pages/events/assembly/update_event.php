<?php
// update_event.php - Handle update of assembly events
session_start();
header('Content-Type: application/json');
include "../../../config/db.php";

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
$required_fields = ['eventId', 'eventName', 'assembly_id', 'start_date', 'end_date'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Missing required field: ' . $field]);
        exit;
    }
}

// Get form data
$event_id = intval($_POST['eventId']);
$event_name = trim($_POST['eventName']);
$assembly_id = intval($_POST['assembly_id']);
$event_type_id = isset($_POST['eventType']) ? intval($_POST['eventType']) : null;
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$is_recurring = isset($_POST['isRecurring']) ? intval($_POST['isRecurring']) : 0;
$recurrence_frequency = isset($_POST['recurrenceFrequency']) && $is_recurring ? $_POST['recurrenceFrequency'] : null;

// Validate dates
$start_timestamp = strtotime($start_date);
$end_timestamp = strtotime($end_date);

if (!$start_timestamp || !$end_timestamp) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit;
}

if ($end_timestamp < $start_timestamp) {
    echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
    exit;
}

try {
    // First, check if the event exists and is an assembly event
    $check_sql = "SELECT event_id FROM events WHERE event_id = ? AND level = 'assembly'";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$event_id]);
    
    if (!$check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Event not found or not an assembly event']);
        exit;
    }
    
    // Update the event
    $sql = "UPDATE events SET 
                title = ?,
                event_type_id = ?,
                assembly_id = ?,
                start_date = ?,
                end_date = ?,
                is_recurring = ?,
                recurrence_frequency = ?,
                updated_at = NOW()
            WHERE event_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $event_name,
        $event_type_id,
        $assembly_id,
        date('Y-m-d H:i:s', $start_timestamp),
        date('Y-m-d H:i:s', $end_timestamp),
        $is_recurring,
        $recurrence_frequency,
        $event_id
    ]);
    
    if ($stmt->rowCount() > 0) {
        // Event successfully updated
        $_SESSION['success_message'] = "Event '{$event_name}' has been updated successfully";
        echo json_encode([
            'success' => true,
            'message' => 'Event updated successfully',
            'event_id' => $event_id
        ]);
    } else {
        // No rows were affected - could be because no data changed
        $_SESSION['success_message'] = "No changes were made to event '{$event_name}'";
        echo json_encode([
            'success' => true,
            'message' => 'No changes were made',
            'event_id' => $event_id
        ]);
    }
    
} catch (PDOException $e) {
    // Log error and return error message
    error_log('Error updating event: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}

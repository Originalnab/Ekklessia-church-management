<?php
session_start();
require_once "../../../config/db.php";

header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log("Starting update_event_process.php");

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    error_log("Unauthorized access attempt");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Log received data for debugging
    error_log("Received POST data: " . print_r($_POST, true));

    // Validate required fields
    $required = ['event_id', 'title', 'event_type_id', 'start_date', 'end_date'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Required field '$field' is missing");
        }
    }

    // Sanitize input
    $event_id = (int)$_POST['event_id'];
    $title = trim($_POST['title']);
    $event_type_id = (int)$_POST['event_type_id'];
    $description = trim($_POST['description'] ?? '');
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = trim($_POST['location'] ?? '');
    $levels = $_POST['levels'] ?? [];
    $frequency = $_POST['frequency'] ?? null;
    $recurrence_day = $_POST['recurrence_day'] ?? null;

    error_log("Validated inputs: event_id=$event_id, title=$title");

    // Validate dates
    $start_datetime = new DateTime($start_date);
    $end_datetime = new DateTime($end_date);
    if ($end_datetime <= $start_datetime) {
        throw new Exception('End date must be after start date');
    }

    // Get event type details
    $stmt = $pdo->prepare("SELECT is_recurring, default_frequency FROM event_types WHERE event_type_id = ?");
    $stmt->execute([$event_type_id]);
    $event_type = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event_type) {
        throw new Exception('Invalid event type selected');
    }

    // Determine level and location IDs
    $level = 'national'; // Default
    $household_id = null;
    $assembly_id = null;
    $zone_id = null;

    if (in_array('household', $levels)) {
        $level = 'household';
        $household_id = isset($_POST['household_ids'][0]) ? (int)$_POST['household_ids'][0] : null;
    } elseif (in_array('assembly', $levels)) {
        $level = 'assembly';
        $assembly_id = isset($_POST['assembly_ids'][0]) ? (int)$_POST['assembly_ids'][0] : null;
    } elseif (in_array('zone', $levels)) {
        $level = 'zone';
        $zone_id = isset($_POST['zone_ids'][0]) ? (int)$_POST['zone_ids'][0] : null;
    }

    // Begin transaction
    $pdo->beginTransaction();

    error_log("Executing UPDATE query for event_id=$event_id");

    $stmt = $pdo->prepare("
        UPDATE events 
        SET 
            event_type_id = :event_type_id,
            title = :title,
            description = :description,
            start_date = :start_date,
            end_date = :end_date,
            location = :location,
            level = :level,
            household_id = :household_id,
            assembly_id = :assembly_id,
            zone_id = :zone_id,
            is_recurring = :is_recurring,
            frequency = :frequency,
            recurrence_day = :recurrence_day,
            updated_at = NOW()
        WHERE event_id = :event_id
    ");

    $is_recurring = (int)$event_type['is_recurring'];

    $success = $stmt->execute([
        ':event_type_id' => $event_type_id,
        ':title' => $title,
        ':description' => $description,
        ':start_date' => $start_date,
        ':end_date' => $end_date,
        ':location' => $location,
        ':level' => $level,
        ':household_id' => $household_id,
        ':assembly_id' => $assembly_id,
        ':zone_id' => $zone_id,
        ':is_recurring' => $is_recurring,
        ':frequency' => $frequency,
        ':recurrence_day' => $recurrence_day,
        ':event_id' => $event_id
    ]);

    error_log("Query executed, rows affected: " . $stmt->rowCount());

    if ($stmt->rowCount() === 0) {
        throw new Exception('No changes were made or event not found');
    }

    $pdo->commit();

    error_log("Event updated successfully: event_id=$event_id");

    echo json_encode([
        'success' => true,
        'message' => 'Event updated successfully',
        'event_id' => $event_id
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
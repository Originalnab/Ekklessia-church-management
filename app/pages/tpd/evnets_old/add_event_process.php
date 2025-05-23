<?php
session_start();
require_once "../../../config/db.php";

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Get form data
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $event_type_id = isset($_POST['event_type_id']) ? (int)$_POST['event_type_id'] : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $levels = isset($_POST['levels']) ? $_POST['levels'] : [];
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    $created_by = isset($_POST['created_by']) ? (int)$_POST['created_by'] : 0;

    // Validate required fields
    if (empty($title) || $event_type_id === 0 || empty($levels) || empty($start_date) || empty($end_date) || $created_by === 0) {
        throw new Exception('All required fields must be filled.');
    }

    // Validate that end_date is after start_date
    $start = strtotime($start_date);
    $end = strtotime($end_date);
    if ($end <= $start) {
        throw new Exception('End Date & Time must be after Start Date & Time.');
    }    // Convert to DateTime objects for more accurate calculations
    $start_datetime = new DateTime($start_date);
    $end_datetime = new DateTime($end_date);
    
    // Calculate duration in minutes
    $duration = ($end_datetime->getTimestamp() - $start_datetime->getTimestamp()) / 60;
    
    // Extract start_time from start_date
    $start_time = $start_datetime->format('H:i:s');

    // Get event type details to determine if it's recurring
    $stmt = $pdo->prepare("SELECT is_recurring FROM event_types WHERE event_type_id = :event_type_id");
    $stmt->execute(['event_type_id' => $event_type_id]);
    $event_type = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event_type) {
        throw new Exception('Invalid event type.');
    }
    $is_recurring = $event_type['is_recurring'];

    // Initialize variables
    $frequency = null;
    $recurrence_day = null;
    $household_id = null;
    $assembly_id = null;
    $zone_id = null;

    // Handle levels (only one level is allowed based on the form)
    $level = $levels[0]; // Take the first selected level
    if ($level === 'household') {
        $household_ids = isset($_POST['household_ids']) ? $_POST['household_ids'] : [];
        if (!empty($household_ids)) {
            $household_id = (int)$household_ids[0]; // Take the first selected household
        }
    } elseif ($level === 'assembly') {
        $assembly_ids = isset($_POST['assembly_ids']) ? $_POST['assembly_ids'] : [];
        if (!empty($assembly_ids)) {
            $assembly_id = (int)$assembly_ids[0];
        }
    } elseif ($level === 'zone') {
        $zone_ids = isset($_POST['zone_ids']) ? $_POST['zone_ids'] : [];
        if (!empty($zone_ids)) {
            $zone_id = (int)$zone_ids[0];
        }
    }

    if ($is_recurring) {
        $frequency = isset($_POST['frequency']) ? trim($_POST['frequency']) : '';
        $recurrence_day = isset($_POST['recurrence_day']) ? trim($_POST['recurrence_day']) : null;

        if (empty($frequency)) {
            throw new Exception('Frequency is required for recurring events.');
        }
        if (($frequency === 'weekly' || $frequency === 'monthly') && empty($recurrence_day)) {
            throw new Exception('Recurrence Day is required for weekly or monthly events.');
        }
    }

    // Insert into the events table
    $stmt = $pdo->prepare("
        INSERT INTO events (
            event_type_id, title, description, start_date, start_time, duration, end_date, 
            location, level, household_id, assembly_id, zone_id, is_recurring, 
            frequency, recurrence_day, created_by, created_at
        ) VALUES (
            :event_type_id, :title, :description, :start_date, :start_time, :duration, :end_date, 
            :location, :level, :household_id, :assembly_id, :zone_id, :is_recurring, 
            :frequency, :recurrence_day, :created_by, NOW()
        )
    ");
    $stmt->execute([
        'event_type_id' => $event_type_id,
        'title' => $title,
        'description' => $description,
        'start_date' => $start_date,
        'start_time' => $start_time,
        'duration' => $duration,
        'end_date' => $end_date,
        'location' => $location,
        'level' => $level,
        'household_id' => $household_id,
        'assembly_id' => $assembly_id,
        'zone_id' => $zone_id,
        'is_recurring' => $is_recurring,
        'frequency' => $frequency,
        'recurrence_day' => $recurrence_day,
        'created_by' => $created_by
    ]);

    $response['success'] = true;
    $response['message'] = 'Event added successfully!';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
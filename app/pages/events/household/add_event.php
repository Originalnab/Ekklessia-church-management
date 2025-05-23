<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set up logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

header('Content-Type: application/json');
require_once "../../../config/db.php";
require_once "../../../pages/auth/auth_check.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify authentication
checkAuth();

try {
    // Validate required fields
    $required_fields = ['eventName', 'eventType', 'start_date', 'end_date', 'assembly_id', 'household_ids'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || (is_array($_POST[$field]) && empty($_POST[$field])) || (!is_array($_POST[$field]) && empty($_POST[$field]))) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate dates
    $start_date = new DateTime($_POST['start_date']);
    $end_date = new DateTime($_POST['end_date']);
    
    if ($end_date < $start_date) {
        throw new Exception("End date cannot be before start date");
    }

    // Get user ID from session
    if (!isset($_SESSION['member_id'])) {
        throw new Exception("User not authenticated");
    }
    $created_by = $_SESSION['member_id'];

    // Start transaction
    $pdo->beginTransaction();

    // For each household, create an event
    $household_ids = is_array($_POST['household_ids']) ? $_POST['household_ids'] : [$_POST['household_ids']];
    $event_ids = [];

    foreach ($household_ids as $household_id) {
        // First verify the household belongs to the assembly
        $verify_stmt = $pdo->prepare("
            SELECT h.household_id, h.assembly_id FROM households h
            INNER JOIN assemblies a ON h.assembly_id = a.assembly_id
            WHERE h.household_id = :household_id 
            AND h.assembly_id = :assembly_id
            AND h.status = 1 -- Only active households
        ");
        
        $verify_stmt->execute([
            ':household_id' => $household_id,
            ':assembly_id' => $_POST['assembly_id']
        ]);

        if (!$verify_stmt->fetch()) {
            throw new Exception("Household $household_id does not belong to assembly {$_POST['assembly_id']}");
        }

        // Insert into events table
        $stmt = $pdo->prepare("
            INSERT INTO events (
                title,
                event_type_id,
                start_date,
                end_date,
                is_recurring,
                frequency,
                assembly_id,
                household_id,
                created_by,
                `level`,
                created_at,
                updated_at
            ) VALUES (
                :title,
                :event_type_id,
                :start_date,
                :end_date,
                :is_recurring,
                :frequency,
                :assembly_id,
                :household_id,
                :created_by,
                1, -- Use numeric value 1 for household level
                NOW(),
                NOW()
            )
        ");

        $is_recurring = !empty($_POST['recurrenceFrequency']);

        // Execute with all required parameters
        $stmt->execute([
            ':title' => $_POST['eventName'],
            ':event_type_id' => $_POST['eventType'],
            ':start_date' => $_POST['start_date'],
            ':end_date' => $_POST['end_date'],
            ':is_recurring' => $is_recurring ? 1 : 0,
            ':frequency' => $_POST['recurrenceFrequency'] ?? null,
            ':assembly_id' => $_POST['assembly_id'], 
            ':household_id' => $household_id,
            ':created_by' => $created_by
        ]);

        $event_ids[] = $pdo->lastInsertId();
    }

    // If event is recurring, create event instances
    if ($is_recurring && !empty($_POST['recurrenceFrequency'])) {
        $instance_stmt = $pdo->prepare("
            INSERT INTO event_instances (
                event_id,
                instance_date,
                created_at
            ) VALUES (
                :event_id,
                :instance_date,
                NOW()
            )
        ");

        foreach ($event_ids as $event_id) {
            // Create instances based on frequency
            $current_date = clone $start_date;
            $frequency = $_POST['recurrenceFrequency'];
            
            while ($current_date <= $end_date) {
                $instance_stmt->execute([
                    ':event_id' => $event_id,
                    ':instance_date' => $current_date->format('Y-m-d H:i:s')
                ]);

                // Add interval based on frequency
                switch ($frequency) {
                    case 'daily':
                        $current_date->modify('+1 day');
                        break;
                    case 'weekly':
                        $current_date->modify('+1 week');
                        break;
                    case 'monthly':
                        $current_date->modify('+1 month');
                        break;
                    default:
                        break;
                }
            }
        }
    }

    // Commit transaction
    $pdo->commit();

    error_log("Successfully added events: " . implode(', ', $event_ids));

    echo json_encode([
        'success' => true,
        'message' => 'Event(s) added successfully',
        'event_ids' => $event_ids
    ]);

} catch (Exception $e) {
    // Rollback transaction if there was an error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Error in add_event.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

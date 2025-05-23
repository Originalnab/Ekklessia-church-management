<?php
session_start();
header('Content-Type: application/json');
require_once "../../../config/db.php";

// Set error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Log request details
error_log("GET /events/household/get_events.php - Session: " . session_id());

try {
    // Check database connection
    if (!$pdo) {
        error_log("Database connection failed");
        throw new Exception("Database connection failed");
    }

    error_log("Executing events query for household level (1)");
    
    $stmt = $pdo->prepare("
        SELECT 
            e.event_id,
            e.title as event_name,
            et.name as event_type,
            a.name as assembly_name,
            e.start_date,
            e.end_date,
            e.description,
            e.is_recurring,
            e.frequency,
            e.household_id,
            e.level
        FROM events e 
        LEFT JOIN event_types et ON e.event_type_id = et.event_type_id 
        LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id
        WHERE e.level = 1
        ORDER BY e.start_date DESC
    ");

    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Query executed successfully. Found " . count($events) . " events");
    if (count($events) > 0) {
        error_log("First event data: " . json_encode($events[0]));
    }

    $response = [
        'success' => true,
        'events' => $events,
        'count' => count($events)
    ];

    error_log("Sending response: " . json_encode($response));
    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Database error in get_events.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in get_events.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
?>

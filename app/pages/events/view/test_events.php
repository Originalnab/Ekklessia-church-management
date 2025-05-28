<?php
// test_events.php - Simple script to check if any events exist in the database
session_start();
require_once '../../../config/db.php';
require_once 'log_utils.php';

// Set content type to JSON
header('Content-Type: application/json');

// Create log file if it doesn't exist
$logfile = dirname(__DIR__, 3) . '/debug.log';
if (!file_exists($logfile)) {
    file_put_contents($logfile, "=== Event View Debug Log ===\n");
}

log_debug("====== Testing Events Query ======");

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    log_debug("Error: User not authenticated");
    exit;
}

// Simple query to check if any events exist
$sql = "SELECT COUNT(*) as total_events FROM events";
log_debug("SQL Query: $sql");

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_events = $result['total_events'];
    log_debug("Total events in database: $total_events");
    
    // If events exist, get a sample
    if ($total_events > 0) {
        $sql = "SELECT * FROM events LIMIT 1";
        log_debug("Sample event query: $sql");
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $sample_event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        log_debug("Sample event: ", $sample_event);
    }
    
    // Find events with specific date ranges
    $current_month_start = date('Y-m-01');
    $current_month_end = date('Y-m-t');
    
    $sql = "SELECT COUNT(*) as month_events FROM events WHERE start_date <= ? AND end_date >= ?";
    log_debug("Current month events query: $sql");
    log_debug("Parameters: [$current_month_end, $current_month_start]");
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$current_month_end, $current_month_start]);
    $month_events = $stmt->fetch(PDO::FETCH_ASSOC);
    
    log_debug("Events in current month: " . $month_events['month_events']);
    
    // Try a more inclusive query
    $sql = "SELECT * FROM events ORDER BY start_date DESC LIMIT 5";
    log_debug("Recent events query: $sql");
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $recent_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    log_debug("Found " . count($recent_events) . " recent events");
    if (count($recent_events) > 0) {
        log_debug("Most recent event: ", $recent_events[0]);
    }
    
    // Return the results
    echo json_encode([
        'success' => true, 
        'total_events' => $total_events,
        'current_month_events' => $month_events['month_events'],
        'recent_events' => count($recent_events),
        'sample' => count($recent_events) > 0 ? $recent_events[0] : null
    ]);
    
} catch (PDOException $e) {
    $error_msg = "Error querying events: " . $e->getMessage();
    log_error($e, "Event testing");
    echo json_encode(['success' => false, 'message' => $error_msg]);
}

<?php
// Assembly Events - Get Event Stats for Dashboard
session_start();
include "../../../config/db.php";

// Set content type for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

function log_debug($message) {
    $logfile = __DIR__ . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logfile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    $now = date('Y-m-d H:i:s');
    $firstDayOfMonth = date('Y-m-01 00:00:00');
    $lastDayOfMonth = date('Y-m-t 23:59:59');
    
    // Get total events count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE level = 2");
    $stmt->execute();
    $totalEvents = $stmt->fetchColumn();
    
    // Get upcoming events count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE level = 2 AND start_date >= ?");
    $stmt->execute([$now]);
    $upcomingEvents = $stmt->fetchColumn();
    
    // Get recurring events count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE level = 2 AND is_recurring = 1");
    $stmt->execute();
    $recurringEvents = $stmt->fetchColumn();
    
    // Get events this month count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE level = 2 AND 
        ((start_date BETWEEN ? AND ?) OR 
         (end_date BETWEEN ? AND ?) OR 
         (start_date <= ? AND end_date >= ?))");
    $stmt->execute([$firstDayOfMonth, $lastDayOfMonth, $firstDayOfMonth, $lastDayOfMonth, $firstDayOfMonth, $lastDayOfMonth]);
    $thisMonthEvents = $stmt->fetchColumn();
    
    log_debug("Stats fetched: total=$totalEvents, upcoming=$upcomingEvents, recurring=$recurringEvents, thisMonth=$thisMonthEvents");
    
    echo json_encode([
        'success' => true,
        'totalEvents' => $totalEvents,
        'upcomingEvents' => $upcomingEvents,
        'recurringEvents' => $recurringEvents,
        'thisMonthEvents' => $thisMonthEvents
    ]);

} catch (PDOException $e) {
    log_debug("Error fetching stats: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
    ]);
}
?>

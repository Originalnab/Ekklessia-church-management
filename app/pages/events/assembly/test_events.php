<?php
// Test file for verifying assembly events
session_start();
include "../../../config/db.php";

// Set content type for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

try {
    // Count events
    $countSql = "SELECT COUNT(*) FROM events WHERE level = 2 OR level = 'assembly'";
    $stmt = $pdo->query($countSql);
    $total = $stmt->fetchColumn();
    
    // Get sample events
    $sql = "SELECT 
                e.event_id,
                e.title,
                e.description,
                e.start_date,
                e.end_date,
                e.start_time,
                e.duration,
                CASE
                    WHEN e.start_date IS NULL AND e.start_time IS NOT NULL THEN 
                        DATE_FORMAT(CONCAT(CURDATE(), ' ', e.start_time), '%Y-%m-%dT%H:%i:%s')
                    ELSE 
                        DATE_FORMAT(e.start_date, '%Y-%m-%dT%H:%i:%s')
                END as formatted_start_date,
                CASE
                    WHEN e.end_date IS NULL AND e.start_time IS NOT NULL AND e.duration IS NOT NULL THEN
                        DATE_FORMAT(DATE_ADD(CONCAT(CURDATE(), ' ', e.start_time), INTERVAL e.duration MINUTE), '%Y-%m-%dT%H:%i:%s')
                    ELSE
                        DATE_FORMAT(e.end_date, '%Y-%m-%dT%H:%i:%s')
                END as formatted_end_date,
                e.is_recurring,
                e.frequency,
                et.name as event_type,
                a.name as assembly_name
            FROM events e
            LEFT JOIN event_types et ON e.event_type_id = et.event_type_id
            LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id
            WHERE e.level = 2 OR e.level = 'assembly'
            LIMIT 10";
            
    $stmt = $pdo->query($sql);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'total' => intval($total),
        'events' => $events,
        'message' => 'Successfully retrieved events'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'events' => []
    ]);
}
?>

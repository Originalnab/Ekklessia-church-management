<?php
// filepath: c:\xampp\htdocs\Ekklessia-church-management\app\pages\events\assembly\get_event_types.php
// Get event types for the dropdown
session_start();
header('Content-Type: application/json');
include "../../../config/db.php";

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $sql = "SELECT event_type_id, name FROM event_types WHERE level = 'assembly' ORDER BY name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'event_types' => $event_types
    ]);
    
} catch (PDOException $e) {
    // Log error and return error message
    error_log('Error fetching event types: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}

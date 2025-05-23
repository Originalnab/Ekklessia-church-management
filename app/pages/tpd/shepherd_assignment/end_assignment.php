<?php
session_start();
require_once '../../../config/config.php';
require_once '../../../functions/shepherd_functions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$assignmentId = $data['assignment_id'] ?? null;
$updatedBy = $_SESSION['user_id'] ?? null;

if (!$assignmentId || !$updatedBy) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE shepherd_assignments 
        SET status = 'inactive', 
            end_date = CURRENT_DATE, 
            updated_by = ?, 
            updated_at = CURRENT_TIMESTAMP
        WHERE assignment_id = ?
    ");
    
    $result = $stmt->execute([$updatedBy, $assignmentId]);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Assignment ended successfully' : 'Error ending assignment'
    ]);
} catch (PDOException $e) {
    error_log("Error ending assignment: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error ending assignment'
    ]);
}
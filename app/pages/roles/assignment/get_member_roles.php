<?php
session_start();
header('Content-Type: application/json');

include "../../../config/db.php";
include "../../../functions/role_management.php";

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

// Get member ID from request
$memberId = isset($_GET['member_id']) ? intval($_GET['member_id']) : 0;

if ($memberId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid member ID'
    ]);
    exit;
}

try {
    // Query to get member roles
    $query = "SELECT r.role_id, r.role_name, mr.is_primary
              FROM member_role mr 
              JOIN roles r ON mr.role_id = r.role_id 
              WHERE mr.member_id = ?
              ORDER BY mr.is_primary DESC, r.role_name ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$memberId]);
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success with roles
    echo json_encode([
        'success' => true,
        'roles' => $roles
    ]);
    
} catch (PDOException $e) {
    // Return error
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
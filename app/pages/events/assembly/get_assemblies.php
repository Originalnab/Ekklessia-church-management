<?php
// filepath: c:\xampp\htdocs\Ekklessia-church-management\app\pages\events\assembly\get_assemblies.php
// Get assemblies for the dropdown
session_start();
header('Content-Type: application/json');
include "../../../config/db.php";

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $sql = "SELECT assembly_id, name FROM assemblies WHERE status = 1 ORDER BY name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'assemblies' => $assemblies
    ]);
    
} catch (PDOException $e) {
    // Log error and return error message
    error_log('Error fetching assemblies: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}

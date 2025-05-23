<?php
session_start();
include "../../config/config.php";
include "../auth/auth_check.php";

header('Content-Type: application/json');

if (!isset($_GET['role_id'])) {
    echo json_encode(['success' => false, 'message' => 'Role ID is required']);
    exit;
}

try {
    $roleId = $_GET['role_id'];
    
    $stmt = $pdo->prepare("SELECT function_id, function_name FROM church_functions WHERE role_id = ? ORDER BY function_name");
    $stmt->execute([$roleId]);
    $functions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'functions' => $functions
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
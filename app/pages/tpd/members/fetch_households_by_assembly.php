<?php
ob_start();
include "../../../config/db.php";
header('Content-Type: application/json');
try {
    $assembly_id = $_GET['assembly_id'] ?? null;
    if (!$assembly_id) {
        echo json_encode([]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT household_id, name 
                           FROM households 
                           WHERE assembly_id = ? AND status = 1"); // Ensure active households only
    $stmt->execute([$assembly_id]);
    $households = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($households);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
ob_end_flush();
?>
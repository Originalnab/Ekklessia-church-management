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
    $stmt = $pdo->prepare("SELECT member_id, first_name, last_name 
                           FROM members 
                           WHERE assemblies_id = ? 
                           AND (status LIKE '%Worker%' OR local_function_id IS NOT NULL)");
    $stmt->execute([$assembly_id]);
    $shepherds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($shepherds);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
ob_end_flush();
?>
<?php
header('Content-Type: application/json');
require_once "../../../config/db.php";

// Debug logging
error_log("get_households_by_assembly.php called with assembly_id: " . ($_GET['assembly_id'] ?? 'null'));

if (!isset($_GET['assembly_id']) || empty($_GET['assembly_id'])) {
    echo json_encode(['success' => false, 'message' => 'Assembly ID is required']);
    exit;
}

$assembly_id = intval($_GET['assembly_id']);

try {
    // Debug: Print the SQL query
    $stmt = $pdo->prepare("
        SELECT household_id, name
        FROM households 
        WHERE assembly_id = ? AND status = 1
        ORDER BY name ASC
    ");
    
    // Debug: Log the query and parameters
    error_log("SQL Query: SELECT household_id, name FROM households WHERE assembly_id = {$assembly_id} AND status = 1 ORDER BY name ASC");
    
    $stmt->execute([$assembly_id]);
    $households = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the results
    error_log("Found households: " . json_encode($households));
    
    // Debug logging
    error_log("Found " . count($households) . " households for assembly_id: " . $assembly_id);
    
    echo json_encode([
        'success' => true,
        'households' => $households
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching households: ' . $e->getMessage()
    ]);
}
?>

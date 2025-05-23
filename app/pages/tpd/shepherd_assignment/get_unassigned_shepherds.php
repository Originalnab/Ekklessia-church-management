<?php
require_once '../../../config/config.php';
require_once '../../../functions/shepherd_functions.php';

header('Content-Type: application/json');

$assembly_id = isset($_GET['assembly_id']) ? $_GET['assembly_id'] : null;

try {
    $query = "
        SELECT s.*, m.first_name, m.last_name, m.contact, m.assemblies_id, a.name as assembly_name
        FROM shepherds s
        JOIN members m ON s.member_id = m.member_id
        LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
        LEFT JOIN shepherd_assignments sa ON s.shepherd_id = sa.shepherd_id AND sa.end_date IS NULL
        WHERE sa.assignment_id IS NULL AND s.status = 'active'";
        
    if ($assembly_id) {
        $query .= " AND m.assemblies_id = :assembly_id";
    }
    
    $query .= " ORDER BY m.first_name, m.last_name";
    
    $stmt = $pdo->prepare($query);
    
    if ($assembly_id) {
        $stmt->bindParam(':assembly_id', $assembly_id);
    }
    
    $stmt->execute();
    $unassignedShepherds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($unassignedShepherds);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
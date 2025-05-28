<?php
// get_event_types.php - Return event types for a given level (or all if none specified)
header('Content-Type: application/json');
require_once '../../../config/db.php';

$level = isset($_GET['level']) ? trim($_GET['level']) : '';

try {
    if ($level === '' || $level === null) {
        $stmt = $pdo->query("SELECT event_type_id, name FROM event_types ORDER BY name");
    } else {
        $stmt = $pdo->prepare("SELECT event_type_id, name FROM event_types WHERE level = ? ORDER BY name");
        $stmt->execute([$level]);
    }
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'types' => $types]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'types' => [], 'error' => $e->getMessage()]);
}

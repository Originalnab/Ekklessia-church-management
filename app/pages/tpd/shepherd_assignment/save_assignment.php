<?php
session_start();
require_once '../../../config/config.php';
require_once '../../../functions/shepherd_functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$shepherdId = $_POST['shepherdId'] ?? null;
$entityId = $_POST['entityId'] ?? null;
$entityType = $_POST['entityType'] ?? null;
$startDate = $_POST['startDate'] ?? date('Y-m-d');
$createdBy = $_SESSION['user_id'] ?? null;

if (!$shepherdId || !$entityId || !$entityType || !$createdBy) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$result = assignShepherd($shepherdId, $entityId, $entityType, $startDate, $createdBy);

echo json_encode([
    'success' => $result,
    'message' => $result ? 'Assignment created successfully' : 'Error creating assignment'
]);
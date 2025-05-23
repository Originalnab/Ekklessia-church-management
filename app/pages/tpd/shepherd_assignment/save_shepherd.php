<?php
session_start();
require_once '../../../config/config.php';
require_once '../../../functions/shepherd_functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$memberId = $_POST['memberId'] ?? null;
$shepherdType = $_POST['shepherdType'] ?? null;
$createdBy = $_SESSION['user_id'] ?? null;

if (!$memberId || !$shepherdType || !$createdBy) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$result = createShepherd($memberId, $shepherdType, $createdBy);

echo json_encode([
    'success' => $result,
    'message' => $result ? 'Shepherd created successfully' : 'Error creating shepherd'
]);
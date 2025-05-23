<?php
session_start();
require_once '../../../config/config.php';
require_once '../../../functions/shepherd_functions.php';

header('Content-Type: application/json');

$shepherdId = $_GET['shepherd_id'] ?? null;

if (!$shepherdId) {
    echo json_encode(['error' => 'Shepherd ID is required']);
    exit;
}

$assignments = getShepherdAssignments($shepherdId);
echo json_encode($assignments);
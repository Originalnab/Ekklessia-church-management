<?php
session_start();
require_once '../../../config/config.php';
require_once '../../../functions/shepherd_functions.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

if (!in_array($type, ['household', 'ministry'])) {
    echo json_encode(['error' => 'Invalid entity type']);
    exit;
}

$entities = [];
if ($type === 'household') {
    $entities = getUnassignedHouseholds();
} else {
    $entities = getUnassignedMinistries();
}

// Format the response to match the expected structure in JavaScript
$response = array_map(function($entity) use ($type) {
    return [
        'id' => $type === 'household' ? $entity['household_id'] : $entity['ministry_id'],
        'name' => $type === 'household' ? $entity['household_name'] : $entity['ministry_name']
    ];
}, $entities);

echo json_encode($response);
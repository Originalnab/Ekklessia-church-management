<?php
// Log event view or error to debug.log
session_start();
header('Content-Type: application/json');
$logFile = __DIR__ . '/debug.log';
$data = json_decode(file_get_contents('php://input'), true);
$eventId = isset($data['eventId']) ? $data['eventId'] : null;
$error = isset($data['error']) ? $data['error'] : null;
$logMsg = '[' . date('Y-m-d H:i:s') . '] ';
if ($eventId && !$error) {
    $logMsg .= "Zone Event View: Event ID $eventId";
} elseif ($eventId && $error) {
    $logMsg .= "Zone Event View Error: Event ID $eventId | Error: $error";
} else {
    $logMsg .= "Zone Event View Error: Invalid request or missing eventId";
}
file_put_contents($logFile, $logMsg . "\n", FILE_APPEND);
echo json_encode(['success' => true]);

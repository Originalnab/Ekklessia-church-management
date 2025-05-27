<?php
// PHP script to log debug information to debug.log
session_start();

// Set the debug log file path
$debugLogFile = __DIR__ . '/debug.log';

// Get the data from POST request
$data = $_POST['data'] ?? '';
$timestamp = date('Y-m-d H:i:s');
$sessionInfo = isset($_SESSION['member_id']) ? 'Member ID: ' . $_SESSION['member_id'] : 'No session';

// Format the log entry
$logEntry = "[{$timestamp}] [{$sessionInfo}] {$data}" . PHP_EOL;

// Append to debug log file
file_put_contents($debugLogFile, $logEntry, FILE_APPEND | LOCK_EX);

// Return success response
echo json_encode(['success' => true]);
?>

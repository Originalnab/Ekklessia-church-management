<?php
// log_js_error.php - Appends JS errors to debug.log
header('Content-Type: application/json');
$logfile = __DIR__ . '/debug.log';

// Get the error message from POST or request body
$error = isset($_POST['error']) ? $_POST['error'] : (file_get_contents('php://input') ?: 'No error message');

// Add additional context
$ts = date('Y-m-d H:i:s');
$user_id = isset($_SESSION['member_id']) ? $_SESSION['member_id'] : 'Not authenticated';
$ip = $_SERVER['REMOTE_ADDR'];
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Unknown';

// Create detailed log entry
$log_entry = "$ts - JS ERROR: $error - User: $user_id - IP: $ip - Source: $referer\n";

// Write to log file
file_put_contents($logfile, $log_entry, FILE_APPEND);

// Return success response
echo json_encode(['success' => true]);

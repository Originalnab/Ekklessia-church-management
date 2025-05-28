<?php
// Get debug log for the debug panel
session_start();
header('Content-Type: text/plain');

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo "Error: Not authenticated";
    exit;
}

$logFile = dirname(__DIR__, 3) . '/debug.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $logLines = explode("\n", $logContent);
    $lastEntries = array_slice($logLines, -100); // Get last 100 lines
    echo implode("\n", $lastEntries);
} else {
    echo "Debug log file does not exist.";
}

<?php
// Clear debug log for the debug panel
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$logFile = dirname(__DIR__, 3) . '/debug.log';
if (file_exists($logFile)) {
    // Keep the first line (header) and reset the rest
    $firstLine = "=== Event View Debug Log ===\n";
    $timestamp = date('Y-m-d H:i:s');
    $newContent = $firstLine . "[$timestamp] Debug log cleared by user ID: " . $_SESSION['member_id'] . "\n";
    
    if (file_put_contents($logFile, $newContent) !== false) {
        echo json_encode(['success' => true, 'message' => 'Debug log cleared']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to write to log file']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Debug log file does not exist']);
}

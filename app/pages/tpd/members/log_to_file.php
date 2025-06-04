<?php
function logToFile($message, $level = 'INFO', $context = null) {
    $logFile = __DIR__ . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message";
    if ($context !== null) {
        $logEntry .= " | Context: " . json_encode($context, JSON_PRETTY_PRINT);
    }
    $logEntry .= PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Handle POST requests for logging from JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    if ($contentType === 'application/json') {
        $jsonPayload = file_get_contents('php://input');
        $data = json_decode($jsonPayload, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($data['message'])) {
            $level = isset($data['level']) ? $data['level'] : 'INFO';
            $context = isset($data['context']) ? $data['context'] : null;
            logToFile($data['message'], $level, $context);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Log received']);
        } else {
            header('Content-Type: application/json', true, 400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload or missing message']);
        }
    } else {
        header('Content-Type: application/json', true, 415); // Unsupported Media Type
        echo json_encode(['status' => 'error', 'message' => 'Unsupported content type. Please send application/json.']);
    }
    exit; // Stop further script execution
}
?>

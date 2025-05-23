<?php
// Role Debug Functions
function logRoleDebug($message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    
    if ($data !== null) {
        $logMessage .= "Data: " . print_r($data, true) . "\n";
    }
    
    $logMessage .= "----------------------------------------\n";
    file_put_contents(__DIR__ . '/debug.log', $logMessage, FILE_APPEND);
}

function logRoleAction($action, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logFile = __DIR__ . '/role_actions.log';
    
    $logEntry = "[{$timestamp}] {$action}\n";
    if ($data !== null) {
        if (is_array($data)) {
            // Remove sensitive information
            if (isset($data['SESSION'])) {
                unset($data['SESSION']['password']);
            }
            $logEntry .= "Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        } else {
            $logEntry .= "Data: {$data}\n";
        }
    }
    $logEntry .= "----------------------------------------\n";
    
    error_log($logEntry, 3, $logFile);
}
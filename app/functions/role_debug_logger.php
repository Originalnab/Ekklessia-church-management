<?php
function logRoleAction($actionType, $description, $data = null, $error = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logFile = __DIR__ . '/../pages/roles/role_debug.log';
    
    $logEntry = "[{$timestamp}] {$actionType}: {$description}\n";
    
    if ($data !== null) {
        // Remove sensitive data
        if (is_array($data)) {
            unset($data['password']);
            unset($data['session_id']);
            $logEntry .= "DATA: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        } else {
            $logEntry .= "DATA: {$data}\n";
        }
    }
    
    if ($error !== null) {
        $logEntry .= "ERROR: {$error}\n";
    }
    
    $logEntry .= "----------------------------------------\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
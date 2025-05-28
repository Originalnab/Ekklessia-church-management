<?php
/**
 * Debug logger for event-related operations
 * @param mixed $data The data to log
 * @param string $prefix Optional prefix for the log entry
 * @return void
 */
function logEventDebug($data, $prefix = '') {
    $logFile = __DIR__ . '/event_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    
    // Format the data for logging
    if (is_array($data) || is_object($data)) {
        $formattedData = print_r($data, true);
    } else {
        $formattedData = (string)$data;
    }
    
    // Create log entry with timestamp, prefix, and data
    $logEntry = "[$timestamp] " . ($prefix ? "[$prefix] " : "") . $formattedData . PHP_EOL;
    
    // Append to log file
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

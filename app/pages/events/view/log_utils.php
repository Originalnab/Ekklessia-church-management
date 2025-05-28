<?php
// Helper function to log debug information
function log_debug($message, $data = null) {
    $logfile = dirname(__DIR__, 3) . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $formatted = "[$timestamp] $message";
    
    if ($data !== null) {
        if (is_array($data) || is_object($data)) {
            $formatted .= "\n" . print_r($data, true);
        } else {
            $formatted .= " - " . $data;
        }
    }
    
    file_put_contents($logfile, $formatted . "\n", FILE_APPEND);
}

/**
 * Log SQL query information for debugging
 * 
 * @param string $sql The SQL query
 * @param array $params The parameters used in the query
 */
function log_sql($sql, $params = []) {
    $logfile = dirname(__DIR__, 3) . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $formatted = "[$timestamp] SQL Query: " . $sql . "\n";
    $formatted .= "[$timestamp] SQL Params: " . print_r($params, true);
    
    file_put_contents($logfile, $formatted . "\n", FILE_APPEND);
}

/**
 * Log PDO exception details
 * 
 * @param PDOException $e The exception to log
 * @param string $context Additional context information
 */
function log_error(PDOException $e, $context = '') {
    $logfile = dirname(__DIR__, 3) . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $formatted = "[$timestamp] ERROR";
    if (!empty($context)) {
        $formatted .= " in $context";
    }
    
    $formatted .= ": " . $e->getMessage() . "\n";
    $formatted .= "[$timestamp] Code: " . $e->getCode() . "\n";
    $formatted .= "[$timestamp] File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    $formatted .= "[$timestamp] Trace: " . $e->getTraceAsString();
    
    file_put_contents($logfile, $formatted . "\n", FILE_APPEND);
}

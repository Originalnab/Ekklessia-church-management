<?php
/**
 * Role Assignment Logger
 * 
 * This file contains functions for logging role assignment operations
 * for both single member assignment and bulk assignment operations.
 */

/**
 * Log role assignment operations to a dedicated log file
 *
 * @param string $message The message to log
 * @param array $data Additional data to include in the log
 * @param string $type The type of log entry (info, error, success, warning)
 * @return void
 */
function logRoleAssignment($message, $data = [], $type = 'info') 
{
    // Define log directory as the current directory
    $logDir = __DIR__;
    $logFile = $logDir . '/role_assignment.log';
    
    // Format timestamp with microseconds for detailed logging
    $timestamp = date('Y-m-d H:i:s') . '.' . sprintf('%03d', round(microtime(true) * 1000) % 1000);
    $logType = strtoupper($type);
    
    // Format the basic log entry
    $logEntry = "[$timestamp] [$logType] $message";
    
    // Add user info if available
    if (isset($_SESSION['member_id'])) {
        $logEntry .= " | User ID: {$_SESSION['member_id']}";
    }
    
    // Add remote IP for security tracking
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $logEntry .= " | IP: $ip";
    
    // Add data as JSON if provided
    if (!empty($data)) {
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $logEntry .= " | Data: $jsonData";
    }
    
    // Add new line
    $logEntry .= PHP_EOL;
    
    // Write to log file - create if doesn't exist
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
?>
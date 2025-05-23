<?php
/**
 * Role Assignment Logger
 * Handles detailed logging of all role assignment operations
 */

function logRoleAssignmentAction($memberId, $roleId, $action, $performedBy, $details = []) {
    global $pdo;
    
    try {
        // Format details as JSON if they're not already
        $actionDetails = is_string($details) ? $details : json_encode($details);
        
        // Insert into database
        $stmt = $pdo->prepare("
            INSERT INTO role_assignment_log 
            (member_id, role_id, action, performed_by, action_details) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $memberId,
            $roleId,
            $action,
            $performedBy,
            $actionDetails
        ]);
        
        // Also log to file for debugging
        $logFile = __DIR__ . '/../pages/roles/assignment/role_assignment_debug.log';
        
        // Create log directory if it doesn't exist
        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = sprintf(
            "[%s] %s\nMember ID: %d\nRole ID: %d\nPerformed By: %s\nAction Details: %s\n----------------------------------------\n",
            $timestamp,
            $action,
            $memberId,
            $roleId,
            $performedBy,
            $actionDetails
        );
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        return true;
    } catch (Exception $e) {
        error_log("Error logging role assignment: " . $e->getMessage());
        return false;
    }
}

function logRoleAssignmentOperation($action, $data = [], $error = null) {
    $logFile = __DIR__ . '/../pages/roles/assignment/role_assignment_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    
    // Create log directory if it doesn't exist
    $logDir = dirname($logFile);
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    // Format the log message
    $logMessage = "[{$timestamp}] {$action}\n";
    
    if (!empty($data)) {
        $logMessage .= "Action Details: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
    
    if ($error) {
        $logMessage .= "Error: {$error}\n";
    }
    
    $logMessage .= "----------------------------------------\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

function logRoleAssignmentError($error, $context = []) {
    $logFile = __DIR__ . '/../pages/roles/assignment/role_assignment_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    
    // Create log directory if it doesn't exist
    $logDir = dirname($logFile);
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logMessage = "[{$timestamp}] ERROR\n";
    $logMessage .= "Message: {$error}\n";
    
    if (!empty($context)) {
        $logMessage .= "Context Details: " . json_encode($context, JSON_PRETTY_PRINT) . "\n";
    }
    
    $logMessage .= "----------------------------------------\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

function logRoleAssignmentInfo($message, $data = []) {
    $logFile = __DIR__ . '/../pages/roles/assignment/role_assignment_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    
    // Create log directory if it doesn't exist
    $logDir = dirname($logFile);
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logMessage = "[{$timestamp}] INFO: {$message}\n";
    
    if (!empty($data)) {
        $logMessage .= "Action Details: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
    
    $logMessage .= "----------------------------------------\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
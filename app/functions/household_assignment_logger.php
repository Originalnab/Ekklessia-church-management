<?php
function logHouseholdAssignment($action, $memberId, $householdId, $role, $assignedBy, $status = true, $message = '') {
    try {
        $logFile = __DIR__ . '/../pages/tpd/members/debug.log';
        $logDir = dirname($logFile);

        // Create directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Create log file if it doesn't exist
        if (!file_exists($logFile)) {
            touch($logFile);
            chmod($logFile, 0666); // Read/write for everyone (for development)
        }

        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [" . ($status ? 'SUCCESS' : 'ERROR') . "] Household $action - ";
        $logEntry .= "Member ID: $memberId, Household ID: $householdId, Role: $role, By: $assignedBy";
        if ($message) {
            $logEntry .= " | Message: $message";
        }
        $logEntry .= "\n";
        
        // Use error_log as a fallback if file_put_contents fails
        if (@file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
            error_log($logEntry); // Log to PHP's error log as fallback
            return false;
        }

        return true;
    } catch (Exception $e) {
        error_log("Household Assignment Logger Error: " . $e->getMessage());
        return false;
    }
}

function logHouseholdError($action, $memberId, $householdId, $error) {
    return logHouseholdAssignment($action, $memberId, $householdId, '', '', false, $error);
}

function validateRoleTransition($oldRole, $newRole, $householdId) {
    // List of valid role transitions
    $validTransitions = [
        'regular' => ['assistant', 'leader'],
        'assistant' => ['regular', 'leader'],
        'leader' => ['regular', 'assistant']
    ];
    
    if (!isset($validTransitions[$oldRole]) || !in_array($newRole, $validTransitions[$oldRole])) {
        return [
            'valid' => false,
            'message' => "Invalid role transition from $oldRole to $newRole"
        ];
    }
    
    return ['valid' => true];
}
?>

<?php
/**
 * Check Permissions
 * 
 * This utility script checks if a member has specific permissions
 * Useful for debugging permission issues in the system
 */

// Include database connection and required functions
require_once 'app/config/db.php';
require_once 'app/functions/role_management.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo "ERROR: You must be logged in to check permissions.";
    exit;
}

$memberId = $_SESSION['member_id'];

// Get requested permission to check
$permissionToCheck = isset($_GET['permission']) ? $_GET['permission'] : '';

// If no permission specified, show form
if (empty($permissionToCheck)) {
    // Get all member's permissions for display
    $allPermissions = getMemberPermissions($memberId);
    
    // Get member's roles
    $memberRoles = getMemberRoles($memberId);
    
    // Display header
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Check Permissions</title>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <link rel='stylesheet' href='Public/css/style.css'>
    </head>
    <body>
        <div class='container mt-4'>
            <h2>Permission Checker</h2>
            <p>Member ID: {$memberId}</p>";
    
    // Display roles
    echo "<h3>Assigned Roles:</h3>
    <ul>";
    
    foreach ($memberRoles as $role) {
        $isPrimary = $role['is_primary'] ? ' (Primary)' : '';
        echo "<li>{$role['role_name']} - {$role['hierarchy_level']}{$isPrimary}</li>";
    }
    
    echo "</ul>";
    
    // Display all permissions
    echo "<h3>All Assigned Permissions:</h3>
    <ul>";
    
    foreach ($allPermissions as $perm) {
        echo "<li>{$perm['permission_name']} - {$perm['description']}</li>";
    }
    
    echo "</ul>";
    
    // Display permission check form
    echo "<h3>Check Specific Permission:</h3>
    <form method='get'>
        <div class='form-group'>
            <label for='permission'>Permission Name:</label>
            <input type='text' class='form-control' id='permission' name='permission' required>
        </div>
        <button type='submit' class='btn btn-primary'>Check Permission</button>
    </form>";
    
    // Display all available permissions as reference
    echo "<h3>All Available Permissions:</h3>
    <ul>";
    
    $allSystemPermissions = getAllPermissions();
    
    foreach ($allSystemPermissions as $perm) {
        echo "<li>{$perm['permission_name']} - {$perm['description']}</li>";
    }
    
    echo "</ul>
        </div>
    </body>
    </html>";
} else {
    // Check if member has the requested permission
    $hasPermission = memberHasPermission($memberId, $permissionToCheck);
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Permission Check Result</title>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <link rel='stylesheet' href='Public/css/style.css'>
    </head>
    <body>
        <div class='container mt-4'>
            <h2>Permission Check Result</h2>
            <p>Member ID: {$memberId}</p>
            <p>Permission: {$permissionToCheck}</p>";
    
    if ($hasPermission) {
        echo "<div class='alert alert-success'><strong>SUCCESS:</strong> Member has the '{$permissionToCheck}' permission.</div>";
    } else {
        echo "<div class='alert alert-danger'><strong>DENIED:</strong> Member does not have the '{$permissionToCheck}' permission.</div>";
    }
    
    echo "<a href='check_permissions.php' class='btn btn-primary'>Check Another Permission</a>
        </div>
    </body>
    </html>";
}
?>
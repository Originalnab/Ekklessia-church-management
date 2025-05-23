<?php
session_start();
include "../../config/config.php";
include "../../functions/role_management.php";

header('Content-Type: application/json');

try {
    $result = setupShepherdRolePermissions();
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Shepherd role permissions have been set up successfully'
        ]);
    } else {
        throw new Exception('Failed to set up Shepherd role permissions');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
<?php
include "../../../config/config.php";

header('Content-Type: application/json');

try {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $scope_id = isset($_POST['scope_id']) ? (int)$_POST['scope_id'] : 0;
    $scope_name = trim($_POST['scope_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $user = $_SESSION['username'] ?? 'Admin';

    // Log the input data for debugging
    error_log("Saving scope with data: " . json_encode([
        'scope_id' => $scope_id,
        'scope_name' => $scope_name,
        'description' => $description,
        'user' => $user
    ]));

    // Validate input
    if (empty($scope_name)) {
        throw new Exception('Scope name is required');
    }

    // Check for duplicate scope name
    $stmt = $pdo->prepare("SELECT scope_id FROM scopes WHERE scope_name = ? AND scope_id != ?");
    $stmt->execute([$scope_name, $scope_id]);
    if ($stmt->fetch()) {
        throw new Exception('A scope with this name already exists');
    }

    // Begin transaction for data integrity
    $pdo->beginTransaction();

    if ($scope_id > 0) {
        // Update existing scope
        $stmt = $pdo->prepare("UPDATE scopes SET scope_name = ?, description = ?, updated_by = ?, updated_at = NOW() WHERE scope_id = ?");
        $stmt->execute([$scope_name, $description, $user, $scope_id]);
        $action = 'updated';
    } else {
        // Insert new scope
        $stmt = $pdo->prepare("INSERT INTO scopes (scope_name, description, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$scope_name, $description, $user]);
        $scope_id = $pdo->lastInsertId();
        $action = 'created';
    }

    // Commit the transaction
    $pdo->commit();

    // Log success
    error_log("Scope $action successfully: scope_id=$scope_id, scope_name=$scope_name");

    // Return success response
    echo json_encode([
        'success' => true,
        'scope_id' => $scope_id,
        'message' => "Scope $action successfully"
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log error
    error_log("Error saving scope: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
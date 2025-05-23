<?php
include "../../../config/config.php";

header('Content-Type: application/json');

try {
    $scope_id = isset($_GET['scope_id']) ? (int)$_GET['scope_id'] : 0;

    // Check if scope is being used
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM church_functions WHERE scope_id = ?");
    $stmt->execute([$scope_id]);
    if ($stmt->fetch()['count'] > 0) {
        throw new Exception('Cannot delete this scope as it is being used by one or more church functions');
    }

    // Delete the scope
    $stmt = $pdo->prepare("DELETE FROM scopes WHERE scope_id = ?");
    $stmt->execute([$scope_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Scope deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Scope not found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
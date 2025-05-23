<?php
include "../../../config/config.php";

$scope_id = isset($_GET['scope_id']) ? (int)$_GET['scope_id'] : 0;

try {
    $stmt = $pdo->prepare("SELECT scope_id, scope_name, description FROM scopes WHERE scope_id = ?");
    $stmt->execute([$scope_id]);
    $scope = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($scope) {
        echo json_encode([
            'success' => true,
            'scope' => $scope
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Scope not found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching scope: ' . $e->getMessage()
    ]);
}
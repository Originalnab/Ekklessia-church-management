<?php
session_start();
include "../../../app/config/config.php"; // Correct path

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error'];

try {
    // Validate input
    $ministry_id = isset($_POST['ministry_id']) && $_POST['ministry_id'] !== '' ? (int)$_POST['ministry_id'] : 0;
    $ministry_name = isset($_POST['ministry_name']) ? trim($_POST['ministry_name']) : '';
    $scope_id = isset($_POST['scope_id']) && $_POST['scope_id'] !== '' ? (int)$_POST['scope_id'] : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if (empty($ministry_name)) {
        throw new Exception('Ministry name is required');
    }
    if (empty($scope_id)) {
        throw new Exception('Scope is required');
    }

    // Validate scope_id
    $stmt = $pdo->prepare("SELECT scope_id FROM scopes WHERE scope_id = ?");
    $stmt->execute([$scope_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Invalid scope');
    }

    $pdo->beginTransaction();

    if ($ministry_id > 0) {
        // Update existing ministry
        $stmt = $pdo->prepare("UPDATE specialized_ministries SET ministry_name = ?, scope_id = ?, description = ? WHERE ministry_id = ?");
        $stmt->execute([$ministry_name, $scope_id, $description, $ministry_id]);
    } else {
        // Check for duplicate ministry_name
        $stmt = $pdo->prepare("SELECT ministry_id FROM specialized_ministries WHERE ministry_name = ?");
        $stmt->execute([$ministry_name]);
        if ($stmt->fetch()) {
            throw new Exception('Ministry name already exists');
        }
        // Insert new ministry
        $stmt = $pdo->prepare("INSERT INTO specialized_ministries (ministry_name, scope_id, description) VALUES (?, ?, ?)");
        $stmt->execute([$ministry_name, $scope_id, $description]);
    }

    $pdo->commit();
    $response = ['success' => true, 'message' => 'Ministry saved successfully'];

} catch (Exception $e) {
    $pdo->rollBack();
    $response = ['success' => false, 'message' => $e->getMessage()];
    error_log("Error saving ministry: " . $e->getMessage());
}

echo json_encode($response);
exit;
?>
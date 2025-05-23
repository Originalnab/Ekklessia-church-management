<?php
session_start();
include "../../../app/config/config.php"; // Correct path

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error'];

try {
    $ministry_id = isset($_POST['ministry_id']) ? (int)$_POST['ministry_id'] : 0;

    if ($ministry_id <= 0) {
        throw new Exception('Invalid ministry ID');
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("DELETE FROM specialized_ministries WHERE ministry_id = ?");
    $stmt->execute([$ministry_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Ministry not found');
    }

    $pdo->commit();
    $response = ['success' => true, 'message' => 'Ministry deleted successfully'];

} catch (Exception $e) {
    $pdo->rollBack();
    $response = ['success' => false, 'message' => $e->getMessage()];
    error_log("Error deleting ministry: " . $e->getMessage());
}

echo json_encode($response);
exit;
?>
<?php
session_start();
include "../../../app/config/config.php"; // Correct path

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error'];

try {
    $ministry_id = isset($_GET['ministry_id']) ? (int)$_GET['ministry_id'] : 0;

    if ($ministry_id <= 0) {
        throw new Exception('Invalid ministry ID');
    }

    $stmt = $pdo->prepare("SELECT ministry_id, ministry_name, scope_id, description FROM specialized_ministries WHERE ministry_id = ?");
    $stmt->execute([$ministry_id]);
    $ministry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ministry) {
        throw new Exception('Ministry not found');
    }

    $response = ['success' => true, 'ministry' => $ministry];

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
    error_log("Error fetching ministry: " . $e->getMessage());
}

echo json_encode($response);
exit;
?>
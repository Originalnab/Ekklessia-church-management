<?php
header('Content-Type: application/json');
include "../../../config/db.php";

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data with validation
    $household_id = $_POST['household_id'] ?? null;
    $status = isset($_POST['status']) ? (int)$_POST['status'] : null;

    // Validate required fields
    if (!$household_id || !isset($status) || ($status !== 0 && $status !== 1)) {
        $response['message'] = 'Invalid household ID or status';
        echo json_encode($response);
        exit;
    }

    try {
        // Check if the PDO connection is valid
        if (!isset($pdo)) {
            throw new Exception('Database connection failed');
        }

        // Check if household_id exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM households WHERE household_id = :household_id");
        $checkStmt->execute(['household_id' => $household_id]);
        if ($checkStmt->fetchColumn() == 0) {
            throw new Exception('Household ID does not exist');
        }

        // Prepare the update query
        $stmt = $pdo->prepare("UPDATE households SET status = :status, updated_at = NOW() WHERE household_id = :household_id");

        // Execute the query
        $stmt->execute([
            'household_id' => $household_id,
            'status' => $status
        ]);

        $response['success'] = true;
        $response['message'] = 'Status updated successfully';
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    } catch (Exception $e) {
        $response['message'] = 'General error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
exit;
?>
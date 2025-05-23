<?php
session_start();
include "../../../config/db.php";

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data with validation
    $household_id = $_POST['household_id'] ?? null;

    // Validate required field
    if (!$household_id) {
        $_SESSION['error_message'] = 'Household ID is missing';
        header('Location: index.php');
        exit;
    }

    try {
        // Check if the PDO connection is valid
        if (!isset($pdo)) {
            throw new Exception('Database connection failed');
        }

        // Check if household_id exists
        $checkHouseholdStmt = $pdo->prepare("SELECT COUNT(*) FROM households WHERE household_id = :household_id");
        $checkHouseholdStmt->execute(['household_id' => $household_id]);
        if ($checkHouseholdStmt->fetchColumn() == 0) {
            throw new Exception('Household ID does not exist');
        }

        // Prepare the delete query
        $stmt = $pdo->prepare("DELETE FROM households WHERE household_id = :household_id");

        // Execute the query
        $stmt->execute(['household_id' => $household_id]);

        // Set success message in session
        $_SESSION['success_message'] = 'Household deleted successfully';

        // Redirect back to index.php
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        // Log the database error
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        // Log any other errors
        error_log("General error: " . $e->getMessage());
        $_SESSION['error_message'] = 'General error: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
} else {
    $_SESSION['error_message'] = 'Invalid request method';
    header('Location: index.php');
    exit;
}
?>
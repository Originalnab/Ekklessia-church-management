<?php
session_start();
include "../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assembly_id = $_POST['assembly_id'] ?? null;

    // Validate required field
    if (!$assembly_id) {
        $_SESSION['error_message'] = 'Assembly ID is missing';
        header('Location: index.php');
        exit;
    }

    try {
        // Prepare the delete query
        $stmt = $pdo->prepare("DELETE FROM assemblies WHERE assembly_id = :assembly_id");
        $stmt->execute(['assembly_id' => $assembly_id]);

        // Check if any row was deleted
        if ($stmt->rowCount() === 0) {
            $_SESSION['error_message'] = 'Assembly not found or already deleted';
            header('Location: index.php');
            exit;
        }

        // Set success message in session
        $_SESSION['success_message'] = 'Assembly deleted successfully';

        // Redirect back to index.php
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        // Redirect with an error message
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
} else {
    // Redirect with an error message
    $_SESSION['error_message'] = 'Invalid request method';
    header('Location: index.php');
    exit;
}
?>
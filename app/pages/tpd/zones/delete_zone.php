<?php
session_start(); // Start the session
include "../../../config/db.php";

// Handle form submission for deleting a zone
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zone_id = $_POST['zone_id'] ?? 0;

    if (empty($zone_id)) {
        $_SESSION['error'] = "Invalid input. Zone ID is required.";
        header("Location: index.php");
        exit;
    } else {
        try {
            $query = "DELETE FROM zones WHERE zone_id = :zone_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['zone_id' => $zone_id]);
            $_SESSION['success'] = "Record deleted successfully.";
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting zone: " . $e->getMessage();
            header("Location: index.php");
            exit;
        }
    }
} else {
    // Redirect back to the zones page if accessed directly
    header("Location: index.php");
    exit;
}
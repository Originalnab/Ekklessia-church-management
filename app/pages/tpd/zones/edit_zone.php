<?php
session_start(); // Start the session
include "../../../config/db.php";

// Handle form submission for editing a zone
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zone_id = $_POST['zone_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? null;
    $created_by = $_POST['created_by'] ?? '';
    $updated_by = $_POST['updated_by'] ?? null;

    if (empty($zone_id) || empty($name) || empty($created_by)) {
        $_SESSION['error'] = "Invalid input. Zone ID, Name, and Created By are required.";
        header("Location: index.php");
        exit;
    } else {
        try {
            $query = "UPDATE zones SET name = :name, description = :description, updated_by = :updated_by WHERE zone_id = :zone_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'zone_id' => $zone_id,
                'name' => $name,
                'description' => $description,
                'updated_by' => $updated_by
            ]);
            $_SESSION['success'] = "Record updated successfully.";
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating zone: " . $e->getMessage();
            header("Location: index.php");
            exit;
        }
    }
} else {
    // Redirect back to the zones page if accessed directly
    header("Location: index.php");
    exit;
}
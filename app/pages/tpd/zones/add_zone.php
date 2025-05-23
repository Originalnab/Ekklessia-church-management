<?php
session_start(); // Start the session
include "../../../config/db.php";

// Handle form submission for adding a new zone
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? null;
    $created_by = $_POST['created_by'] ?? '';
    $updated_by = $_POST['updated_by'] ?? null;

    if (empty($name) || empty($created_by)) {
        $_SESSION['error'] = "Invalid input. Name and Created By are required.";
        header("Location: index.php");
        exit;
    } else {
        try {
            $query = "INSERT INTO zones (name, description, created_by, updated_by) 
                      VALUES (:name, :description, :created_by, :updated_by)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'name' => $name,
                'description' => $description,
                'created_by' => $created_by,
                'updated_by' => $updated_by
            ]);
            $_SESSION['success'] = "Record added successfully.";
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error adding zone: " . $e->getMessage();
            header("Location: index.php");
            exit;
        }
    }
} else {
    // Redirect back to the zones page if accessed directly
    header("Location: index.php");
    exit;
}
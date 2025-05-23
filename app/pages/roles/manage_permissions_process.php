<?php
session_start();
include "../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $permission_id = $_POST['permission_id'] ?? null;
    $permission_name = $_POST['permission_name'] ?? '';
    $description = $_POST['description'] ?? '';

    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO permissions (permission_name, description) VALUES (?, ?)");
            $stmt->execute([$permission_name, $description]);
            $_SESSION['success_message'] = "Permission added successfully";
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare("UPDATE permissions SET permission_name = ?, description = ? WHERE permission_id = ?");
            $stmt->execute([$permission_name, $description, $permission_id]);
            $_SESSION['success_message'] = "Permission updated successfully";
        } elseif ($action === 'delete') {
            // Check if permission is in use
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM role_church_function_permissions WHERE permission_id = ?");
            $checkStmt->execute([$permission_id]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("Cannot delete permission as it is currently assigned to roles");
            }

            $stmt = $pdo->prepare("DELETE FROM permissions WHERE permission_id = ?");
            $stmt->execute([$permission_id]);
            $_SESSION['success_message'] = "Permission deleted successfully";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

header("Location: permissions.php");
exit;
?>

<?php
session_start();
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    try {
        // Find the member by email
        $stmt = $pdo->prepare("SELECT member_id FROM members WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($member) {
            // Generate new temporary credentials
            $temp_username = 'temp_' . substr(md5(uniqid()), 0, 8);
            $temp_password = substr(md5(uniqid()), 0, 10);

            // Update or insert into temp_credentials
            $stmt = $pdo->prepare("
                INSERT INTO temp_credentials (member_id, temp_username, temp_password)
                VALUES (:member_id, :temp_username, :temp_password)
                ON DUPLICATE KEY UPDATE temp_username = :temp_username, temp_password = :temp_password
            ");
            $stmt->execute([
                'member_id' => $member['member_id'],
                'temp_username' => $temp_username,
                'temp_password' => $temp_password
            ]);

            // In a real app, send these credentials via email
            // For now, display them (not secure, for demo purposes only)
            $_SESSION['message'] = "New temporary credentials generated:<br>Username: $temp_username<br>Password: $temp_password<br>Please log in and change your credentials.";
        } else {
            $_SESSION['error'] = "No member found with that email.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
    header("Location: forgot_credentials.php");
    exit;
}
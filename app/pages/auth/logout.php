<?php
session_start();
require_once '../../config/config.php';

if (isset($_SESSION['member_id']) && isset($_SESSION['token'])) {
    // Delete the session token from the database
    $stmt = $pdo->prepare("DELETE FROM sessions WHERE member_id = :member_id AND token = :token");
    $stmt->execute([
        'member_id' => $_SESSION['member_id'],
        'token' => $_SESSION['token']
    ]);
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
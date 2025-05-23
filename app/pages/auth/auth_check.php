<?php
// This file ensures that the user is authenticated before accessing the page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuth() {
    if (!isset($_SESSION['member_id'])) {
        $_SESSION['error'] = "Please log in to access this page.";
        header('Location: /Ekklessia-church-management/app/pages/auth/login.php');
        exit();
    }
    
    // Check role-based access
    $currentUrl = $_SERVER['PHP_SELF'];
    if (strpos($currentUrl, 'shepherd_home.php') !== false && (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 4)) {
        $_SESSION['error'] = "Access denied. You don't have permission to access this page.";
        header('Location: /Ekklessia-church-management/app/pages/auth/login.php');
        exit();
    }
    
    return true;
}

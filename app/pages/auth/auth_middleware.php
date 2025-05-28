<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../functions/role_management.php';

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    header('Location: /Ekklessia-church-management/app/pages/auth/login.php');
    exit;
}

// Get current page URL and base URL
$currentUrl = $_SERVER['PHP_SELF'];
$baseUrl = '/Ekklessia-church-management/app/pages/';

// Get member's role information
$memberId = $_SESSION['member_id'];
$roleId = $_SESSION['role_id'] ?? null;
$hierarchyLevel = $_SESSION['hierarchy_level'] ?? null;

// TEMPORARILY DISABLED: Define access rules for different sections based on role_id
$accessRules = [
    // All rules commented out to temporarily suspend permissions
    /*
    '/roles/' => [1], // EXCO only
    '/dashboard/shepherd_home.php' => [4], // Shepherd only
    '/dashboard/presiding_elder_home.php' => [3], // Presiding Elder only
    '/dashboard/tpd_director_home.php' => [2], // TPD Director only
    '/dashboard/exco_home.php' => [1], // EXCO only
    '/assemblies/' => [1, 2, 3], // EXCO, TPD Director, Presiding Elder
    '/households/' => [1, 2, 3, 4], // All roles
    '/zones/' => [1, 2], // EXCO and TPD Director
    '/specialized_ministries/' => [1, 2, 3], // EXCO, TPD Director, Presiding Elder
    '/finance/' => [1] // EXCO only
    */
];

// TEMPORARILY DISABLED: Check access for current page
// All users now have access to all pages
/*
foreach ($accessRules as $path => $allowedRoles) {
    if (strpos($currentUrl, $path) !== false) {
        if (!in_array($roleId, $allowedRoles)) {
            // If user doesn't have appropriate role, redirect to their dashboard
            $dashboardUrl = getDashboardByRole($roleId);
            $_SESSION['error'] = "Access denied. You don't have the required role.";
            header("Location: $dashboardUrl");
            exit;
        }
        break;
    }
}
*/
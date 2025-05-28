<?php
session_start();
require_once '../../config/config.php';
require_once '../../functions/role_management.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/htdocs/Ekklessia-church-management/error_log.txt');

error_log("Login attempt - POST data: " . print_r($_POST, true));

if (!isset($pdo)) {
    error_log("Database connection not established in login_process.php. Check config.php.");
    $_SESSION['error'] = "Database connection failed. Please try again later.";
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    error_log("Attempting login for username: " . $username);

    if (empty($username) || empty($password)) {
        error_log("Login attempt failed: Username or password is empty.");
        $_SESSION['error'] = "Username and password are required.";
        header("Location: login.php");
        exit;
    }

    try {
        // Only fetch role-related information
        $stmt = $pdo->prepare("
            SELECT m.member_id, m.password, m.first_name, mr.role_id, r.role_name, r.hierarchy_level 
            FROM members m 
            LEFT JOIN member_role mr ON m.member_id = mr.member_id AND mr.is_primary = 1
            LEFT JOIN roles r ON mr.role_id = r.role_id
            WHERE m.username = ?
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session data
            $_SESSION['member_id'] = $user['member_id'];
            $_SESSION['username'] = $username;
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['hierarchy_level'] = $user['hierarchy_level'];
            $_SESSION['first_name'] = $user['first_name'] ?? '';
            $_SESSION['show_welcome'] = true;

            // Check for multiple roles
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM member_role WHERE member_id = ?");
            $countStmt->execute([$user['member_id']]);
            $roleCount = $countStmt->fetchColumn();

            // Determine redirect URL based on roles
            if ($roleCount > 1) {
                header("Location: /Ekklessia-church-management/app/pages/dashboard/multi_role_dashboard.php");
            } else {
                // Single role - redirect based on primary role_id from member_role
                if ($user['role_id'] == 4) { // Shepherd (primary)
                    header("Location: /Ekklessia-church-management/app/pages/dashboard/shepherd_home.php");
                } elseif ($user['role_id'] == 8) { // Presiding Elder (primary)
                    header("Location: /Ekklessia-church-management/app/pages/dashboard/presiding_elder_home.php");
                } elseif ($user['role_id'] == 2) { // Zone Director
                    header("Location: /Ekklessia-church-management/app/pages/dashboard/tpd_director_home.php");
                } elseif ($user['role_id'] == 1) { // EXCO
                    header("Location: /Ekklessia-church-management/app/pages/dashboard/exco_home.php");
                } else {
                    header("Location: /Ekklessia-church-management/app/pages/dashboard/member_home.php");
                }
            }
            exit;
        } else {
            error_log("Login failed: Invalid username or password for username: " . $username);
            $_SESSION['error'] = "Invalid username or password.";
            header("Location: login.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database error in login_process.php: " . $e->getMessage());
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: login.php");
        exit;
    }
} else {
    error_log("Invalid request method in login_process.php: " . $_SERVER['REQUEST_METHOD']);
    header("Location: login.php");
    exit;
}
?>
<?php
// Prevent redefining constants
// app/config/config.php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(dirname(__DIR__))); // Root of 'app' folder
    define('PUBLIC_PATH', BASE_PATH . '/../Public'); 
    define('INCLUDES_PATH', BASE_PATH . '/includes');

    // Set BASE_URL dynamically
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    
    // For LOCALHOST (XAMPP):
    if (strpos($host, 'localhost') !== false) {
        define("BASE_URL", "$protocol://$host/Ekklessia-church-management/Public");
    } 
    // For LIVE SERVER (cPanel):
    else {
        define("BASE_URL", "$protocol://$host/Public"); // Adjust if your cPanel public folder is named differently
    }
}

// Database configuration
// Define database credentials for both environments
if (strpos($host, 'localhost') !== false) {
    // Localhost (XAMPP) credentials
    $db_host = '127.0.0.1';
    $db_name = 'ekklessia_db';
    $db_user = 'root'; // Default XAMPP username
    $db_pass = '';     // Default XAMPP password (empty)
} else {
    // cPanel (Live Server) credentials
    // Replace these with your actual cPanel database credentials
    $db_host = 'localhost'; // Often 'localhost' on cPanel, but confirm with your hosting provider
    $db_name = 'your_cpanel_db_name'; // e.g., 'username_ekklessia_db'
    $db_user = 'your_cpanel_db_user'; // e.g., 'username_dbuser'
    $db_pass = 'your_cpanel_db_password'; // Your database password
}

$db_charset = 'utf8mb4';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Include Gemini AI Configuration
require_once __DIR__ . '/gemini_config.php';
?>
<?php
// Database configuration
$host = 'localhost';        // Database host (usually 'localhost' for XAMPP)
$dbname = 'ekklessia_db';  // Database name
$username = 'root';        // Default XAMPP username (change if different)
$password = '';            // Default XAMPP password (change if set)

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // Set PDO attributes
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error reporting
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Fetch as associative array
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Use real prepared statements

} catch (PDOException $e) {
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}
?>
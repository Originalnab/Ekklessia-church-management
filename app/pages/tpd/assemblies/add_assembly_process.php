<?php
session_start();
include "../../../config/db.php";

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data with validation
    $name = $_POST['name'] ?? null;
    $region = $_POST['region'] ?? null;
    $city_town = $_POST['city_town'] ?? null;
    $digital_address = $_POST['digital_address'] ?: null;
    $nearest_landmark = $_POST['nearest_landmark'] ?: null;
    $date_started = $_POST['date_started'] ?: null;
    $status = isset($_POST['status']) && $_POST['status'] === '1' ? 1 : 0;
    $zone_id = $_POST['zone_id'] ?? null;
    $created_by = $_POST['created_by'] ?? 'Admin';

    // Validate required fields
    if (!$name || !$region || !$city_town || !$zone_id || !$created_by) {
        $_SESSION['error_message'] = 'Required fields are missing: ' .
            (!$name ? 'name, ' : '') .
            (!$region ? 'region, ' : '') .
            (!$city_town ? 'city_town, ' : '') .
            (!$zone_id ? 'zone_id, ' : '') .
            (!$created_by ? 'created_by' : '');
        header('Location: index.php');
        exit;
    }

    try {
        // Check if the PDO connection is valid
        if (!isset($pdo)) {
            throw new Exception('Database connection failed');
        }

        // Check if zone_id exists
        $checkZoneStmt = $pdo->prepare("SELECT COUNT(*) FROM zones WHERE zone_id = :zone_id");
        $checkZoneStmt->execute(['zone_id' => $zone_id]);
        if ($checkZoneStmt->fetchColumn() == 0) {
            throw new Exception('Zone ID does not exist');
        }

        // Prepare the insert query
        $stmt = $pdo->prepare("INSERT INTO assemblies (name, region, city_town, digital_address, nearest_landmark, date_started, status, zone_id, created_by, created_at) 
                               VALUES (:name, :region, :city_town, :digital_address, :nearest_landmark, :date_started, :status, :zone_id, :created_by, NOW())");

        // Execute the query
        $stmt->execute([
            'name' => $name,
            'region' => $region,
            'city_town' => $city_town,
            'digital_address' => $digital_address,
            'nearest_landmark' => $nearest_landmark,
            'date_started' => $date_started,
            'status' => $status,
            'zone_id' => $zone_id,
            'created_by' => $created_by
        ]);

        // Set success message in session
        $_SESSION['success_message'] = 'Assembly added successfully';

        // Redirect back to index.php
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        // Log the database error
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        // Log any other errors
        error_log("General error: " . $e->getMessage());
        $_SESSION['error_message'] = 'General error: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
} else {
    $_SESSION['error_message'] = 'Invalid request method';
    header('Location: index.php');
    exit;
}
?>
<?php
session_start();
include "../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $assembly_id = $_POST['assembly_id'] ?? null;
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
    if (!$assembly_id || !$name || !$region || !$city_town || !$zone_id) {
        // Redirect with an error message (you can enhance this with an error alert if needed)
        $_SESSION['error_message'] = 'Required fields are missing';
        header('Location: index.php');
        exit;
    }

    try {
        // Prepare the update query
        $stmt = $pdo->prepare("UPDATE assemblies 
                               SET name = :name, 
                                   region = :region, 
                                   city_town = :city_town, 
                                   digital_address = :digital_address, 
                                   nearest_landmark = :nearest_landmark, 
                                   date_started = :date_started, 
                                   status = :status, 
                                   zone_id = :zone_id, 
                                   created_by = :created_by 
                               WHERE assembly_id = :assembly_id");

        // Bind parameters
        $stmt->execute([
            'assembly_id' => $assembly_id,
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
        $_SESSION['success_message'] = 'Assembly updated successfully';

        // Redirect back to index.php
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        // Redirect with an error message
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
} else {
    // Redirect with an error message
    $_SESSION['error_message'] = 'Invalid request method';
    header('Location: index.php');
    exit;
}
?>
<?php
session_start(); // Start the session
include "../../../config/db.php";

// Ensure the request is a GET request and zone_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['zone_id'])) {
    $zone_id = $_GET['zone_id'];

    try {
        // Fetch the zone details from the database
        $query = "SELECT zone_id, name, description, created_at, created_by, updated_by 
                  FROM zones 
                  WHERE zone_id = :zone_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['zone_id' => $zone_id]);
        $zone = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($zone) {
            // Return the zone details as JSON
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'name' => $zone['name'],
                    'description' => $zone['description'] ?? 'N/A',
                    'created_at' => $zone['created_at'],
                    'created_by' => $zone['created_by'],
                    'updated_by' => $zone['updated_by'] ?? 'N/A'
                ]
            ]);
        } else {
            // Zone not found
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Zone not found.'
            ]);
        }
    } catch (PDOException $e) {
        // Database error
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Error fetching zone: ' . $e->getMessage()
        ]);
    }
} else {
    // Invalid request
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request. Zone ID is required.'
    ]);
}
exit;
<?php
session_start();
include "../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'assembly_id' => $_POST['assembly_id'],
        'name' => $_POST['name'],
        'region' => $_POST['region'],
        'city_town' => $_POST['city_town'],
        'digital_address' => $_POST['digital_address'] ?: null,
        'nearest_landmark' => $_POST['nearest_landmark'] ?: null,
        'date_started' => $_POST['date_started'] ?: null,
        'status' => $_POST['status'] === '1' ? 1 : 0,
        'zone_id' => $_POST['zone_id'],
        'created_by' => $_POST['created_by'],
    ];

    try {
        $stmt = $pdo->prepare("UPDATE assemblies 
                               SET name = :name, region = :region, city_town = :city_town, 
                                   digital_address = :digital_address, nearest_landmark = :nearest_landmark, 
                                   date_started = :date_started, status = :status, zone_id = :zone_id, 
                                   created_by = :created_by 
                               WHERE assembly_id = :assembly_id");
        $stmt->execute($data);
        echo json_encode(['success' => true, 'message' => 'Assembly updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
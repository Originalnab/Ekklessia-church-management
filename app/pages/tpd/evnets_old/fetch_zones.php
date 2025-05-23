<?php
include "../../../config/db.php";

try {
    $stmt = $pdo->query("SELECT zone_id, name FROM zones ORDER BY name ASC");
    $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($zones);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
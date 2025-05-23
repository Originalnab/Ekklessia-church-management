<?php
include "../../../config/db.php";

try {
    $stmt = $pdo->query("SELECT household_id, name FROM households ORDER BY name ASC");
    $households = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($households);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
<?php
include "../../../config/db.php";

try {
    $stmt = $pdo->query("SELECT assembly_id, name FROM assemblies ORDER BY name ASC");
    $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($assemblies);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
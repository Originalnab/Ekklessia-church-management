<?php
require_once __DIR__ . '/../app/config/db.php';

$sql = file_get_contents(__DIR__ . '/updates/add_household_logging_tables.sql');

try {
    $pdo->exec($sql);
    echo "Tables created successfully!\n";
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
}
?>

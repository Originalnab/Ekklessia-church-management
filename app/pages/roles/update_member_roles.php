<?php
require_once __DIR__ . "/../../config/db.php";

try {
    // 1. Update Gifty Asekapta to Shepherd role
    $stmt = $pdo->prepare("UPDATE members SET local_function_id = 11 WHERE first_name = 'Gifty' AND last_name = 'Asekapta'");
    $stmt->execute();
    echo "Updated Gifty to Shepherd role\n";

    // 2. Update Benjamin Edwards to Presiding Elder role
    $stmt = $pdo->prepare("UPDATE members SET local_function_id = 8 WHERE first_name = 'Benjamin' AND last_name = 'Edwards'");
    $stmt->execute();
    echo "Updated Benjamin to Presiding Elder role\n";

    // 3. First insert Aretha as a new member
    $stmt = $pdo->prepare("INSERT INTO members (
        first_name, last_name, date_of_birth, gender, marital_status, 
        contact, email, address, digital_address, status, joined_date, 
        assemblies_id, local_function_id, created_by, username, password
    ) VALUES (
        'Aretha', 'Johnson', '1990-01-01', 'Female', 'Single',
        '0123456789', 'aretha@example.com', 'Sample Address', 'GA-123-456',
        'Active saint', CURDATE(), 4, 10, 'Admin',
        'aretha2025', '$2y$10$sS805qOvBOPiXHS6Cp/gAOV5G9/psbNM9e05Yi05dny/.Nv..9ox6'
    )");
    $stmt->execute();
    echo "Added Aretha as a new member with Elder role\n";

    echo "All role updates completed successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
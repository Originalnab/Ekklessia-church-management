<?php
include "../../../config/db.php";

// Define the gradient colors array (same as in assembly_management.php)
$gradient_colors = [
    'linear-gradient(45deg, #007bff, #00d4ff)', // Blue
    'linear-gradient(45deg, #28a745, #6fcf97)', // Green
    'linear-gradient(45deg, #ffc107, #ffca28)', // Yellow
    'linear-gradient(45deg, #17a2b8, #4fc3f7)', // Cyan
    'linear-gradient(45deg, #dc3545, #ff6b6b)', // Red
    'linear-gradient(45deg, #6c757d, #b0b5b9)', // Gray
    'linear-gradient(45deg, #343a40, #6c757d)'  // Dark
];

// Function to get gradient color based on assembly name (same logic as in assembly_management.php)
function getGradientColor($assembly_name, $gradient_colors) {
    if ($assembly_name === 'N/A' || $assembly_name === null) {
        return 'linear-gradient(45deg, #6c757d, #b0b5b9)';
    }
    $hash = 0;
    for ($i = 0; $i < strlen($assembly_name); $i++) {
        $hash = ord($assembly_name[$i]) + (($hash << 5) - $hash);
    }
    $index = abs($hash) % count($gradient_colors);
    return $gradient_colors[$index];
}

try {
    $stmt = $pdo->query("
        SELECT m.member_id, m.first_name, m.last_name, m.contact, m.email, m.status, m.profile_photo, 
               a.name AS assembly_name, cf.function_name AS role_name
        FROM members m
        LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
        JOIN church_functions cf ON m.local_function_id = cf.function_id
        WHERE cf.function_name IN ('Presiding Elder', 'Assistant Presiding Elder') AND cf.function_type = 'local'
        ORDER BY m.created_at DESC
    ");
    $presiding_elders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add badge_style to each row for the assembly name
    foreach ($presiding_elders as &$elder) {
        $assembly_name = $elder['assembly_name'] ?? 'N/A';
        $elder['badge_style'] = getGradientColor($assembly_name, $gradient_colors);
    }

    // Return the data as JSON
    echo json_encode($presiding_elders);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching presiding elders: ' . $e->getMessage()]);
}
?>
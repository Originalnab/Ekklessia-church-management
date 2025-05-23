<?php
session_start();
include "../../../app/config/config.php"; // Correct path

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error'];

try {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;
    $ministry_name = isset($_GET['ministry_name']) ? trim($_GET['ministry_name']) : '';

    // Build query
    $sql = "SELECT m.ministry_id, m.ministry_name, s.scope_name, m.description
            FROM specialized_ministries m
            INNER JOIN scopes s ON m.scope_id = s.scope_id
            WHERE 1=1";
    $params = [];

    if (!empty($ministry_name)) {
        $sql .= " AND m.ministry_name LIKE ?";
        $params[] = "%$ministry_name%";
    }

    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM specialized_ministries WHERE 1=1" . (!empty($ministry_name) ? " AND ministry_name LIKE ?" : ""));
    $countStmt->execute(!empty($ministry_name) ? ["%$ministry_name%"] : []);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total / $per_page);

    // Fetch paginated data
    $sql .= " ORDER BY m.ministry_name ASC LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $ministries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'ministries' => $ministries,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total
        ]
    ];

} catch (PDOException $e) {
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    error_log("Error fetching ministries: " . $e->getMessage());
}

echo json_encode($response);
exit;
?>
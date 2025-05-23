<?php
include "../../../config/db.php";

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

$name = isset($_GET['name']) ? $_GET['name'] : '';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$recurring = isset($_GET['recurring']) ? $_GET['recurring'] : '';

$conditions = [];
$params = [];

if ($name) {
    $conditions[] = "name LIKE ?";
    $params[] = "%$name%";
}
if ($level) {
    $conditions[] = "level = ?";
    $params[] = $level;
}
if ($recurring !== '') {
    $conditions[] = "is_recurring = ?";
    $params[] = (int)$recurring;
}

$whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

try {
    // Count total event types
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM event_types $whereClause");
    $countStmt->execute($params);
    $totalEventTypes = $countStmt->fetchColumn();
    $totalPages = ceil($totalEventTypes / $recordsPerPage);

    // Fetch paginated event types
    $query = "
        SELECT event_type_id, name, description, default_frequency, level, is_recurring, created_at, updated_at
        FROM event_types
        $whereClause
        ORDER BY name ASC
        LIMIT ? OFFSET ?
    ";
    $params[] = $recordsPerPage;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'event_types' => $event_types,
        'pagination' => [
            'total_event_types' => $totalEventTypes,
            'total_pages' => $totalPages,
            'current_page' => $page
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
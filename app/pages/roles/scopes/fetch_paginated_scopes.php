<?php
include "../../../config/config.php";

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;
$scopeName = isset($_GET['name']) ? $_GET['name'] : '';

try {
    // Get total records for pagination
    $countQuery = "SELECT COUNT(*) as total FROM scopes WHERE scope_name LIKE :scope_name";
    $stmt = $pdo->prepare($countQuery);
    $stmt->bindValue(':scope_name', '%' . $scopeName . '%', PDO::PARAM_STR);
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / $recordsPerPage);

    // Get paginated records
    $query = "SELECT scope_id, scope_name, description, created_at, updated_at FROM scopes WHERE scope_name LIKE :scope_name ORDER BY scope_name LIMIT :offset, :recordsPerPage";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':scope_name', '%' . $scopeName . '%', PDO::PARAM_STR);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':recordsPerPage', $recordsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $scopes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'scopes' => $scopes,
        'total_pages' => $totalPages,
        'total_records' => $total,
        'current_page' => $page
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching scopes: ' . $e->getMessage()
    ]);
}
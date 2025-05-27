<?php
// Assembly Events - Get Events with Pagination
session_start();
include "../../../config/db.php";

// Set content type for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Get parameters
$tab = $_GET['tab'] ?? 'upcoming';
$page = max(1, intval($_GET['page'] ?? 1));
$pageSize = (
    (isset($_GET['tab']) && $_GET['tab'] === 'calendar') ? 1000 : max(1, min(100, intval($_GET['pageSize'] ?? 10)))
);
$search = trim($_GET['search'] ?? '');
$typeFilter = $_GET['type'] ?? '';
$assemblyFilter = $_GET['assembly'] ?? '';  // Add assembly filter parameter
$startDateFilter = $_GET['startDate'] ?? '';
$endDateFilter = $_GET['endDate'] ?? '';

function log_debug($message) {
    $logfile = __DIR__ . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logfile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    log_debug("Assembly events request - Tab: $tab, Search: $search, Type: $typeFilter, Assembly: $assemblyFilter");
    
    $where = ["e.level = 2"];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "(e.title LIKE ? OR e.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($typeFilter)) {
        $where[] = "e.event_type_id = ?";
        $params[] = $typeFilter;
    }

    if (!empty($assemblyFilter)) {
        $where[] = "e.assembly_id = ?";
        $params[] = $assemblyFilter;
    }
    
    $now = date('Y-m-d H:i:s');
    if ($tab === 'upcoming') {
        $where[] = "e.start_date >= ?";
        $params[] = $now;
    } elseif ($tab === 'past') {
        $where[] = "e.end_date < ?";
        $params[] = $now;
    }
    
    if (!empty($startDateFilter)) {
        $where[] = "DATE(e.start_date) >= ?";
        $params[] = $startDateFilter;
    }
    
    if (!empty($endDateFilter)) {
        $where[] = "DATE(e.end_date) <= ?";
        $params[] = $endDateFilter;
    }
    
    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(DISTINCT e.event_id) 
                 FROM events e 
                 LEFT JOIN event_types et ON e.event_type_id = et.event_type_id
                 LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id 
                 $whereSql";
    
    log_debug("Count SQL: $countSql");
    log_debug("Params: " . json_encode($params));
    
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    
    $offset = ($page - 1) * $pageSize;
    
    // Get paginated events
    $sql = "SELECT 
                e.event_id,
                e.title,
                e.description,
                DATE_FORMAT(e.start_date, '%Y-%m-%dT%H:%i:%s') as start_date,
                DATE_FORMAT(e.end_date, '%Y-%m-%dT%H:%i:%s') as end_date,
                e.is_recurring,
                e.frequency,
                et.name as event_type,
                et.event_type_id,
                a.name as assembly_name,
                a.assembly_id,
                CONCAT(m.first_name, ' ', m.last_name) as created_by_name,
                e.created_at
            FROM events e
            LEFT JOIN event_types et ON e.event_type_id = et.event_type_id
            LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id
            LEFT JOIN members m ON e.created_by = m.member_id
            $whereSql
            ORDER BY e.start_date DESC
            LIMIT $pageSize OFFSET $offset";
            
    log_debug("Events SQL: $sql");
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'events' => $events,
        'total' => intval($total),
        'page' => $page,
        'pageSize' => $pageSize,
        'totalPages' => ceil($total / $pageSize)
    ]);

} catch (PDOException $e) {
    log_debug("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'events' => [],
        'total' => 0
    ]);
}
?>

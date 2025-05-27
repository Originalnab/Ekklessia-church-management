<?php
// get_events_paginated.php - Returns paginated, filtered assembly events as JSON
session_start();
header('Content-Type: application/json');
include '../../../config/db.php';

if (!isset($_SESSION['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get pagination and filter params
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = isset($_GET['pageSize']) ? max(1, intval($_GET['pageSize'])) : 10;
$offset = ($page - 1) * $pageSize;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$assembly = isset($_GET['assembly']) ? trim($_GET['assembly']) : '';
$startDate = isset($_GET['startDate']) ? trim($_GET['startDate']) : '';
$endDate = isset($_GET['endDate']) ? trim($_GET['endDate']) : '';

$where = ["e.level = 2"];
$params = [];
if ($search !== '') {
    $where[] = "e.title LIKE ?";
    $params[] = "%$search%";
}
if ($type !== '') {
    $where[] = "et.name = ?";
    $params[] = $type;
}
if ($assembly !== '') {
    $where[] = "a.name = ?";
    $params[] = $assembly;
}
if ($startDate !== '') {
    $where[] = "e.start_date >= ?";
    $params[] = $startDate;
}
if ($endDate !== '') {
    $where[] = "e.end_date <= ?";
    $params[] = $endDate . ' 23:59:59';
}
$tab = isset($_GET['tab']) ? $_GET['tab'] : '';
$now = date('Y-m-d H:i:s');
if ($tab === 'upcoming') {
    $where[] = "e.start_date >= ?";
    $params[] = $now;
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - Upcoming SQL: ' . "SELECT ... WHERE " . implode(' AND ', $where) . ' | Params: ' . json_encode($params) . "\n", FILE_APPEND);
} elseif ($tab === 'past') {
    $where[] = "e.end_date < ?";
    $params[] = $now;
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - Past SQL: ' . "SELECT ... WHERE " . implode(' AND ', $where) . ' | Params: ' . json_encode($params) . "\n", FILE_APPEND);
}
// For 'all', do not add any date filter, just show all assembly events
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

try {
    // Get total count
    $countSql = "SELECT COUNT(*) FROM events e LEFT JOIN event_types et ON e.event_type_id = et.event_type_id LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id $whereSql";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();    // Get paginated results
    $sql = "SELECT e.event_id, e.title as event_name, et.name as event_type, a.name as assembly_name, 
            DATE_FORMAT(e.start_date, '%Y-%m-%d %H:%i:%s') as start_date, 
            DATE_FORMAT(e.end_date, '%Y-%m-%d %H:%i:%s') as end_date, 
            e.is_recurring 
            FROM events e 
            LEFT JOIN event_types et ON e.event_type_id = et.event_type_id 
            LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id 
            $whereSql 
            ORDER BY e.start_date DESC 
            LIMIT $pageSize OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - Events fetched: ' . json_encode($events) . "\n", FILE_APPEND);
    echo json_encode([
        'success' => true,
        'events' => $events,
        'total' => intval($total),
        'page' => $page,
        'pageSize' => $pageSize
    ]);
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - get_events_paginated EXCEPTION: ' . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

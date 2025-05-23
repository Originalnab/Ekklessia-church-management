<?php
// get_events_paginated.php - Returns paginated, filtered household events as JSON
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

$where = ["e.level = 1"];
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
} elseif ($tab === 'past') {
    $where[] = "e.end_date < ?";
    $params[] = $now;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Get total count
$countSql = "SELECT COUNT(*) FROM events e LEFT JOIN event_types et ON e.event_type_id = et.event_type_id LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id $whereSql";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Get paginated results
$sql = "SELECT e.event_id, e.title as event_name, et.name as event_type, a.name as assembly_name, e.start_date, e.end_date, e.is_recurring FROM events e LEFT JOIN event_types et ON e.event_type_id = et.event_type_id LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id $whereSql ORDER BY e.start_date DESC LIMIT $pageSize OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'events' => $events,
    'total' => intval($total),
    'page' => $page,
    'pageSize' => $pageSize
]);

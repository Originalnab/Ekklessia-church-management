<?php
// export_events.php - Exports events to CSV or Excel format
session_start();
include "../../../config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get parameters
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';
$exportFormat = isset($_GET['export']) ? $_GET['export'] : 'csv';

// Build the SQL query based on filters and tab
$params = [];
$sql = "SELECT e.event_id, e.title, e.assembly_id, e.description, 
               DATE_FORMAT(e.start_date, '%Y-%m-%d %H:%i:%s') as start_date,
               DATE_FORMAT(e.end_date, '%Y-%m-%d %H:%i:%s') as end_date,
               e.is_recurring, e.frequency, e.created_at, e.created_by,
               a.name as assembly_name, et.name as event_type,
               CONCAT(m.first_name, ' ', m.last_name) as created_by_name
        FROM events e
        LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id
        LEFT JOIN event_types et ON e.event_type_id = et.event_type_id
        LEFT JOIN members m ON e.created_by = m.member_id
        WHERE e.level = 2 OR e.level = 'assembly'";

// Add tab-specific conditions
if ($tab === 'upcoming') {
    $sql .= " AND e.start_date >= CURRENT_DATE()";
} elseif ($tab === 'past') {
    $sql .= " AND e.end_date < CURRENT_DATE()";
}

// Add search filter
if (!empty($search)) {
    $sql .= " AND (e.title LIKE ? OR e.description LIKE ? OR a.name LIKE ? OR et.name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

// Add type filter
if (!empty($type)) {
    $sql .= " AND e.event_type_id = ?";
    $params[] = $type;
}

// Add date range filters
if (!empty($startDate)) {
    $sql .= " AND e.start_date >= ?";
    $params[] = $startDate . ' 00:00:00';
}

if (!empty($endDate)) {
    $sql .= " AND e.start_date <= ?";
    $params[] = $endDate . ' 23:59:59';
}

// Add order by
$sql .= " ORDER BY e.start_date ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers based on export format
    if ($exportFormat === 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="assembly_events.xls"');
    } else {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="assembly_events.csv"');
    }
    
    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel compatibility with UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Output header row
    fputcsv($output, [
        'Event ID', 
        'Title', 
        'Assembly', 
        'Event Type',
        'Start Date & Time', 
        'End Date & Time',
        'Recurring',
        'Frequency',
        'Description',
        'Created By',
        'Created At'
    ]);
    
    // Output each row of data
    foreach ($events as $event) {
        fputcsv($output, [
            $event['event_id'],
            $event['title'],
            $event['assembly_name'],
            $event['event_type'],
            $event['start_date'],
            $event['end_date'],
            $event['is_recurring'] ? 'Yes' : 'No',
            $event['frequency'],
            $event['description'],
            $event['created_by_name'],
            $event['created_at']
        ]);
    }
    
    fclose($output);
    exit;
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

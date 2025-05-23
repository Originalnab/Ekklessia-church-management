<?php
session_start();
require_once "../../../config/db.php";

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'events' => [], 'pagination' => []];

try {
    // Get pagination and filter parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $recordsPerPage = 10;
    $offset = ($page - 1) * $recordsPerPage;

    $title = isset($_GET['title']) ? trim($_GET['title']) : '';
    $event_type_name = isset($_GET['event_type_name']) ? trim($_GET['event_type_name']) : '';
    $level = isset($_GET['level']) ? trim($_GET['level']) : '';
    $start = isset($_GET['start']) ? trim($_GET['start']) : '';
    $end = isset($_GET['end']) ? trim($_GET['end']) : '';

    // Build the query
    $query = "
        SELECT 
            e.event_id,
            e.event_type_id,
            et.name AS event_type_name,
            e.title,
            e.description,
            e.start_date,
            e.end_date,
            e.location,
            e.level,
            e.household_id,
            e.assembly_id,
            e.zone_id,
            e.is_recurring,
            e.frequency,
            e.recurrence_day,
            e.created_by,
            m.username AS created_by_name,
            e.created_at
        FROM events e
        LEFT JOIN event_types et ON e.event_type_id = et.event_type_id
        LEFT JOIN members m ON e.created_by = m.member_id
        WHERE 1=1
    ";

    $params = [];

    // Apply filters
    if ($title) {
        $query .= " AND e.title LIKE :title";
        $params['title'] = "%$title%";
    }
    if ($event_type_name) {
        $query .= " AND et.name = :event_type_name";
        $params['event_type_name'] = $event_type_name;
    }
    if ($level) {
        $query .= " AND e.level = :level";
        $params['level'] = $level;
    }
    if ($start) {
        $query .= " AND e.start_date >= :start";
        $params['start'] = $start;
    }
    if ($end) {
        $query .= " AND e.end_date <= :end";
        $params['end'] = $end;
    }

    // Get total records for pagination
    $countQuery = "SELECT COUNT(e.event_id) FROM events e 
                   LEFT JOIN event_types et ON e.event_type_id = et.event_type_id
                   LEFT JOIN members m ON e.created_by = m.member_id
                   WHERE 1=1";
    
    // Add the same filters to the count query
    if ($title) {
        $countQuery .= " AND e.title LIKE :title";
    }
    if ($event_type_name) {
        $countQuery .= " AND et.name = :event_type_name";
    }
    if ($level) {
        $countQuery .= " AND e.level = :level";
    }
    if ($start) {
        $countQuery .= " AND e.start_date >= :start";
    }
    if ($end) {
        $countQuery .= " AND e.end_date <= :end";
    }
    
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // Add pagination to the query
    $query .= " ORDER BY e.created_at DESC LIMIT :offset, :recordsPerPage";
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':recordsPerPage', $recordsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process each event to match the table structure
    foreach ($events as &$event) {
        // Ensure proper datetime format for start_date and end_date
        $event['start_date'] = $event['start_date'] ?: date('Y-m-d H:i:s');
        $event['end_date'] = $event['end_date'] ?: date('Y-m-d H:i:s', strtotime($event['start_date'] . ' +1 hour'));
        
        $event['event_type_name'] = $event['event_type_name'] ?: 'N/A';
        $event['is_recurring'] = (int)$event['is_recurring'];
        $event['frequency'] = $event['frequency'] ?: 'N/A';
        $event['recurrence_day'] = $event['recurrence_day'] ?: 'N/A';
        $event['created_by_name'] = $event['created_by_name'] ?: 'Unknown';

        // Determine levels (since level is a single value in the table, but the UI expects an array)
        $event['levels'] = [$event['level']];

        // Determine location based on level
        if ($event['level'] === 'household' && $event['household_id']) {
            $stmt = $pdo->prepare("SELECT name FROM households WHERE household_id = :household_id");
            $stmt->execute(['household_id' => $event['household_id']]);
            $event['location'] = $stmt->fetchColumn() ?: $event['location'];
        } elseif ($event['level'] === 'assembly' && $event['assembly_id']) {
            $stmt = $pdo->prepare("SELECT name FROM assemblies WHERE assembly_id = :assembly_id");
            $stmt->execute(['assembly_id' => $event['assembly_id']]);
            $event['location'] = $stmt->fetchColumn() ?: $event['location'];
        } elseif ($event['level'] === 'zone' && $event['zone_id']) {
            $stmt = $pdo->prepare("SELECT name FROM zones WHERE zone_id = :zone_id");
            $stmt->execute(['zone_id' => $event['zone_id']]);
            $event['location'] = $stmt->fetchColumn() ?: $event['location'];
        } elseif ($event['level'] === 'national') {
            $event['location'] = $event['location'] ?: 'National Headquarters';
        }
    }

    $response['success'] = true;
    $response['events'] = $events;
    $response['pagination'] = [
        'total_pages' => $totalPages,
        'total_events' => $totalRecords
    ];

} catch (PDOException $e) {
    $response['message'] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = "Error: " . $e->getMessage();
}

echo json_encode($response);
exit;
?>
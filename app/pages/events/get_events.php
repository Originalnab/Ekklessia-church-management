<?php
session_start();
include "../../config/db.php";

// Get filter parameters
$level = isset($_GET['level']) ? intval($_GET['level']) : 0;
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;
$assemblyId = isset($_GET['assemblyId']) ? intval($_GET['assemblyId']) : null;
$zoneId = isset($_GET['zoneId']) ? intval($_GET['zoneId']) : null;
$householdId = isset($_GET['householdId']) ? intval($_GET['householdId']) : null;

try {
    $sql = "SELECT 
        e.*,
        et.name as event_type_name,
        et.description as event_type_description,
        a.name as assembly_name,
        z.name as zone_name 
    FROM events e 
    LEFT JOIN event_types et ON e.event_type_id = et.event_type_id
    LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id
    LEFT JOIN zones z ON e.zone_id = z.zone_id
    WHERE 1=1";

    $params = [];

    // Add filters based on event level and context
    if ($level > 0) {
        $sql .= " AND e.level = ?";
        $params[] = $level;
    }
    if ($assemblyId) {
        $sql .= " AND e.assembly_id = ?";
        $params[] = $assemblyId;
    }
    if ($zoneId) {
        $sql .= " AND e.zone_id = ?";
        $params[] = $zoneId;
    }
    if ($householdId) {
        $sql .= " AND e.household_id = ?";
        $params[] = $householdId;
    }

    // Add date range filters
    if ($startDate) {
        $sql .= " AND e.start_date >= ?";
        $params[] = $startDate;
    }
    if ($endDate) {
        $sql .= " AND e.end_date <= ?";
        $params[] = $endDate;
    }

    // Execute query with parameters
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format events for FullCalendar
    $calendarEvents = array_map(function($event) {
        return [
            'id' => $event['event_id'],
            'title' => $event['event_name'] . 
                      ($event['assembly_name'] ? ' (' . $event['assembly_name'] . ')' : ''),
            'start' => $event['start_date'],
            'end' => $event['end_date'],
            'allDay' => (
                substr($event['start_date'], -8) === '00:00:00' && 
                substr($event['end_date'], -8) === '00:00:00'
            ),
            'color' => $event['is_recurring'] ? '#17a2b8' : '#007bff',
            'borderColor' => $event['is_recurring'] ? '#17a2b8' : '#007bff',
            'extendedProps' => [
                'eventType' => $event['event_type_name'],
                'eventTypeDescription' => $event['event_type_description'],
                'assembly' => $event['assembly_name'],
                'zone' => $event['zone_name'],
                'isRecurring' => (bool)$event['is_recurring'],
                'description' => $event['description'],
                'level' => $event['level']
            ]
        ];
    }, $events);

    echo json_encode([
        'success' => true,
        'events' => $calendarEvents
    ]);
} catch (PDOException $e) {
    error_log("Error fetching events: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching events'
    ]);
}
?>

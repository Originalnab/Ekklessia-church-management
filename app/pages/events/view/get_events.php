<?php
// get_events.php - Fetch events based on user context and filters
session_start();
header('Content-Type: application/json');
require_once '../../../config/db.php';
require_once '../../../functions/user_context.php';
require_once 'log_utils.php';

// Create log file if it doesn't exist
$logfile = dirname(__DIR__, 3) . '/debug.log';
if (!file_exists($logfile)) {
    file_put_contents($logfile, "=== Event View Debug Log ===\n");
}

log_debug("====== New Request ======");
log_debug("Request method: " . $_SERVER['REQUEST_METHOD']);
log_debug("Request params: ", $_REQUEST);

if (!isset($_SESSION['member_id'])) {
    log_debug("Error: User not authenticated");
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$member_id = $_SESSION['member_id'];
log_debug("Member ID: $member_id");
$userContext = getUserContext($member_id);
log_debug("User Context: ", $userContext);

// Get filters from GET or POST
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : null;
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : null;
$eventLevel = isset($_REQUEST['eventLevel']) ? $_REQUEST['eventLevel'] : '';
$eventType = isset($_REQUEST['eventType']) ? $_REQUEST['eventType'] : '';

log_debug("Filters - Start Date: $startDate, End Date: $endDate, Level: $eventLevel, Type: $eventType");

// Build base query
$sql = "SELECT 
            e.event_id,
            e.title,
            e.description,
            e.start_date,
            e.end_date,
            e.location,
            e.level,
            e.is_recurring,
            e.frequency,
            e.recurrence_day,
            e.household_id,
            e.assembly_id,
            e.zone_id,
            et.name as event_type_name,
            et.description as event_type_description,
            a.name as assembly_name,
            z.name as zone_name,
            h.name as household_name
        FROM events e
        LEFT JOIN event_types et ON e.event_type_id = et.event_type_id
        LEFT JOIN assemblies a ON e.assembly_id = a.assembly_id
        LEFT JOIN zones z ON e.zone_id = z.zone_id
        LEFT JOIN households h ON e.household_id = h.household_id
        WHERE 1=1";
$params = [];

// Filter by user context (show only events relevant to the user)
$sql .= " AND (e.level = 4"; // National events always visible (scope_id=4)
if (!empty($userContext['zone_id'])) {
    $sql .= " OR (e.level = 3 AND e.zone_id = ?)"; // zone = 3
    $params[] = $userContext['zone_id'];
}
if (!empty($userContext['assembly_id'])) {
    $sql .= " OR (e.level = 2 AND e.assembly_id = ?)"; // assembly = 2
    $params[] = $userContext['assembly_id'];
}
if (!empty($userContext['household_id'])) {
    $sql .= " OR (e.level = 1 AND e.household_id = ?)"; // household = 1
    $params[] = $userContext['household_id'];
}
$sql .= ")";

log_debug("User context filter built");

// Filter by event level if selected
if ($eventLevel !== '') {
    $sql .= " AND e.level = ?";
    $params[] = $eventLevel;
    log_debug("Added level filter: $eventLevel");
}

// Filter by event type if selected
if ($eventType !== '') {
    $sql .= " AND e.event_type_id = ?";
    $params[] = $eventType;
    log_debug("Added type filter: $eventType");
}

// Filter by date range if provided
// If both start and end dates are empty, we fetch all events (no date filtering)
if (!empty($startDate) || !empty($endDate)) {
    log_debug("Applying date filters");
    
    if (!empty($startDate)) {
        // Include events that end on or after the start date
        // This shows events that are ongoing or start during the date range
        $sql .= " AND e.end_date >= ?";
        $params[] = $startDate;
        log_debug("Added start date filter: $startDate");
    }
    
    if (!empty($endDate)) {
        // Include events that start on or before the end date
        // This shows events that begin during the date range
        $sql .= " AND e.start_date <= ?";
        $params[] = $endDate;
        log_debug("Added end date filter: $endDate");
    }
} else {
    log_debug("No date filters provided - fetching all events");
}
$sql .= " ORDER BY e.start_date ASC";

log_debug("Final SQL query: $sql");
log_debug("Query parameters: ", $params);
log_sql($sql, $params);

try {
    $stmt = $pdo->prepare($sql);
    
    // Check for PDO prepare errors
    if (!$stmt) {
        $errorInfo = $pdo->errorInfo();
        log_debug("PDO prepare error: ", $errorInfo);
        echo json_encode(['success' => false, 'message' => 'Error preparing query', 'error' => $errorInfo[2]]);
        exit;
    }
    
    $result = $stmt->execute($params);
    
    // Check for execution errors
    if (!$result) {
        $errorInfo = $stmt->errorInfo();
        log_debug("PDO execute error: ", $errorInfo);
        echo json_encode(['success' => false, 'message' => 'Error executing query', 'error' => $errorInfo[2]]);
        exit;
    }
    
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    log_debug("Events fetched from database: " . count($events));
    if (count($events) > 0) {
        log_debug("First event sample: ", $events[0]);
    } else {
        log_debug("No events found with the current filters and user context");
    }
    
    // Format events for FullCalendar
    $formattedEvents = array_map(function($event) {
        // Ensure dates are properly formatted for FullCalendar
        $start = !empty($event['start_date']) ? $event['start_date'] : null;
        $end = !empty($event['end_date']) ? $event['end_date'] : null;
        
        // Check if this is an all-day event (no time portion or time is 00:00:00)
        $isAllDay = false;
        if ($start && $end) {
            $isAllDay = (substr($start, -8) === '00:00:00' && 
                         substr($end, -8) === '00:00:00');
        }
        
        return [
            'id' => $event['event_id'],
            'title' => $event['title'],
            'start' => $start,
            'end' => $end,
            'allDay' => $isAllDay,
            'color' => $event['is_recurring'] ? '#17a2b8' : '#007bff',
            'borderColor' => $event['is_recurring'] ? '#17a2b8' : '#007bff',
            'extendedProps' => [
                'description' => $event['description'],
                'location' => $event['location'],
                'level' => $event['level'],
                'eventType' => $event['event_type_name'],
                'eventTypeDescription' => $event['event_type_description'],
                'assembly' => $event['assembly_name'],
                'zone' => $event['zone_name'],
                'household' => $event['household_name'],
                'isRecurring' => (bool)$event['is_recurring'],
                'frequency' => $event['frequency'],
                'recurrenceDay' => $event['recurrence_day']
            ]
        ];
    }, $events);

    log_debug("Formatted events for FullCalendar: " . count($formattedEvents));
    if (count($formattedEvents) > 0) {
        log_debug("First formatted event sample: ", $formattedEvents[0]);
    }
    
    $responseData = ['success' => true, 'events' => $formattedEvents];
    log_debug("Response data: ", ['success' => true, 'count' => count($formattedEvents)]);
    
    echo json_encode($responseData);
} catch (PDOException $e) {
    $error_msg = "Error fetching events: " . $e->getMessage();
    log_error($e, "Event fetching");
    error_log($error_msg);
    echo json_encode(['success' => false, 'message' => 'Error fetching events', 'error' => $e->getMessage()]);
}

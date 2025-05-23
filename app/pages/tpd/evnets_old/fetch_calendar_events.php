<?php
include "../../../config/db.php";

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

if (!$start || !$end) {
    echo json_encode(['success' => false, 'message' => 'Start and end dates are required']);
    exit;
}

$events = [];
try {
    // Fetch one-time events
    $stmt = $pdo->prepare("
        SELECT 
            e.event_id, e.title, e.start_date, e.end_date, e.level, e.description, e.location,
            e.is_recurring, e.frequency, e.recurrence_day,
            et.name AS event_type_name
        FROM events e
        JOIN event_types et ON e.event_type_id = et.event_type_id
        WHERE e.is_recurring = 0 AND e.start_date >= ? AND e.end_date <= ?
    ");
    $stmt->execute([$start, $end]);
    $one_time_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($one_time_events as $event) {
        $events[] = [
            'id' => $event['event_id'],
            'title' => $event['title'],
            'start' => $event['start_date'],
            'end' => $event['end_date'],
            'backgroundColor' => $event['is_recurring'] ? '#ff6b6b' : '#007bff', // Red for recurring, blue for one-time
            'borderColor' => $event['is_recurring'] ? '#ff6b6b' : '#007bff',
            'extendedProps' => [
                'level' => $event['level'],
                'event_type_name' => $event['event_type_name'],
                'description' => $event['description'] ?? 'No description',
                'location' => $event['location'] ?? 'Not specified',
                'is_recurring' => $event['is_recurring'] ? 'Yes' : 'No',
                'frequency' => $event['frequency'] ?? 'N/A',
                'recurrence_day' => $event['recurrence_day'] ?? 'N/A'
            ]
        ];
    }

    // Fetch recurring event instances
    $stmt = $pdo->prepare("
        SELECT 
            ei.instance_id, ei.event_id, ei.instance_date,
            e.title, e.duration, e.level, e.description, e.location,
            e.is_recurring, e.frequency, e.recurrence_day,
            et.name AS event_type_name
        FROM event_instances ei
        JOIN events e ON ei.event_id = e.event_id
        JOIN event_types et ON e.event_type_id = et.event_type_id
        WHERE e.is_recurring = 1 AND ei.instance_date >= ? AND ei.instance_date <= ?
    ");
    $stmt->execute([$start, $end]);
    $recurring_instances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($recurring_instances as $instance) {
        $end_date = (new DateTime($instance['instance_date']))->modify("+{$instance['duration']} minutes")->format('Y-m-d H:i:s');
        $events[] = [
            'id' => $instance['event_id'] . '-' . $instance['instance_id'],
            'title' => $instance['title'],
            'start' => $instance['instance_date'],
            'end' => $end_date,
            'backgroundColor' => '#ff6b6b', // Red for recurring events
            'borderColor' => '#ff6b6b',
            'extendedProps' => [
                'level' => $instance['level'],
                'event_type_name' => $instance['event_type_name'],
                'description' => $instance['description'] ?? 'No description',
                'location' => $instance['location'] ?? 'Not specified',
                'is_recurring' => $instance['is_recurring'] ? 'Yes' : 'No',
                'frequency' => $instance['frequency'] ?? 'N/A',
                'recurrence_day' => $instance['recurrence_day'] ?? 'N/A'
            ]
        ];
    }

    echo json_encode(['success' => true, 'events' => $events]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching calendar events: ' . $e->getMessage()]);
}
?>
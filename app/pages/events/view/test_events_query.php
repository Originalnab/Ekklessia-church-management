<?php
// test_events_query.php - Run a direct SQL query to test event fetching
session_start();
header('Content-Type: text/html');
require_once '../../../config/db.php';
require_once '../../../functions/user_context.php';
require_once 'log_utils.php';

// Security check - restrict to logged in users
if (!isset($_SESSION['member_id'])) {
    die("<div style='color:red;'>Error: You must be logged in to access this tool.</div>");
}

echo "<h1>Event Query Testing Tool</h1>";
echo "<p>This script executes the same query used in the calendar to fetch events, but without any filters.</p>";

$member_id = $_SESSION['member_id'];
$userContext = getUserContext($member_id);

echo "<h2>User Context</h2>";
echo "<pre>";
print_r($userContext);
echo "</pre>";

// Build the exact same query as in get_events.php but without date filters
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
$sql .= " AND (e.level = 0"; // National events always visible
if (!empty($userContext['zone_id'])) {
    $sql .= " OR (e.level = 2 AND e.zone_id = ?)";
    $params[] = $userContext['zone_id'];
}
if (!empty($userContext['assembly_id'])) {
    $sql .= " OR (e.level = 3 AND e.assembly_id = ?)";
    $params[] = $userContext['assembly_id'];
}
if (!empty($userContext['household_id'])) {
    $sql .= " OR (e.level = 1 AND e.household_id = ?)";
    $params[] = $userContext['household_id'];
}
$sql .= ")";

// Add ordering
$sql .= " ORDER BY e.start_date ASC";

// Run the query
echo "<h2>SQL Query</h2>";
echo "<pre>" . htmlspecialchars($sql) . "</pre>";

echo "<h2>Parameters</h2>";
echo "<pre>";
print_r($params);
echo "</pre>";

echo "<h2>Results</h2>";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($events) . " events</p>";
    
    if (count($events) > 0) {
        echo "<h3>Sample Event Data (First 3 Events)</h3>";
        echo "<pre>";
        $sampleEvents = array_slice($events, 0, 3);
        print_r($sampleEvents);
        echo "</pre>";
        
        echo "<h3>All Events</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>
            <th>ID</th>
            <th>Title</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Level</th>
            <th>Type</th>
            <th>Assembly</th>
            <th>Zone</th>
            <th>Household</th>
        </tr>";
        
        foreach ($events as $event) {
            $levelName = '';
            switch ($event['level']) {
                case 0: $levelName = 'National'; break;
                case 1: $levelName = 'Household'; break;
                case 2: $levelName = 'Zone'; break;
                case 3: $levelName = 'Assembly'; break;
                default: $levelName = 'Unknown';
            }
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($event['event_id']) . "</td>";
            echo "<td>" . htmlspecialchars($event['title']) . "</td>";
            echo "<td>" . htmlspecialchars($event['start_date']) . "</td>";
            echo "<td>" . htmlspecialchars($event['end_date']) . "</td>";
            echo "<td>" . htmlspecialchars($levelName) . "</td>";
            echo "<td>" . htmlspecialchars($event['event_type_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($event['assembly_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($event['zone_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($event['household_name'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Format for calendar
        echo "<h2>Formatted for Calendar</h2>";
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
        
        echo "<pre>";
        print_r(array_slice($formattedEvents, 0, 3)); // Just show first 3 for readability
        echo "</pre>";
        
        echo "<h2>Raw JSON Response (Sample)</h2>";
        echo "<pre>";
        echo htmlspecialchars(json_encode(['success' => true, 'events' => array_slice($formattedEvents, 0, 3)], JSON_PRETTY_PRINT));
        echo "</pre>";
    } else {
        echo "<div style='color:orange;'>No events found. This could indicate there are no events in the database that match your user context.</div>";
        
        // Check if there are ANY events at all
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM events");
        $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo "<p>Total events in database (all contexts): $totalCount</p>";
        
        if ($totalCount > 0) {
            echo "<div style='color:red;'>There are events in the database, but none match your user context. This suggests your user may not have access to any events.</div>";
            
            // Show sample of events that exist
            $stmt = $pdo->query("SELECT event_id, title, level, assembly_id, zone_id, household_id FROM events LIMIT 5");
            $sampleEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Sample Events (regardless of access)</h3>";
            echo "<pre>";
            print_r($sampleEvents);
            echo "</pre>";
        } else {
            echo "<div style='color:red;'>There are no events in the database at all. You need to create some events first.</div>";
        }
    }
} catch (PDOException $e) {
    echo "<div style='color:red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<h2>Troubleshooting Suggestions</h2>";
echo "<ul>";
echo "<li>Make sure there are events in the database</li>";
echo "<li>Check that user context values (assembly_id, zone_id, household_id) match the events in the database</li>";
echo "<li>Verify event levels are set correctly (0=National, 1=Household, 2=Zone, 3=Assembly)</li>";
echo "<li>Ensure date formats are correct (YYYY-MM-DD HH:MM:SS)</li>";
echo "<li>Check for NULL values in required fields</li>";
echo "</ul>";

echo "<h2>Next Steps</h2>";
echo "<p>If this script shows events but they don't appear in the calendar:</p>";
echo "<ol>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "<li>Verify the get_events.php is returning properly formatted JSON</li>";
echo "<li>Ensure FullCalendar is configured correctly in event-viewer.js</li>";
echo "<li>Check that event dates are in the range being displayed on the calendar</li>";
echo "</ol>";

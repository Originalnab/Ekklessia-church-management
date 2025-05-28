<?php
// test_events_db.php - Diagnostic script to test event database structure and content
session_start();
header('Content-Type: text/html');
require_once '../../../config/db.php';
require_once '../../../functions/user_context.php';
require_once 'log_utils.php';

// Set to true to display detailed information
$showDetails = true;

echo "<h1>Event Database Diagnostic Tool</h1>";
echo "<p>This script runs database queries to check for events and their relationships.</p>";

// Security check - restrict to logged in users
if (!isset($_SESSION['member_id'])) {
    die("<div style='color:red;'>Error: You must be logged in to access this diagnostic tool.</div>");
}

function runQuery($pdo, $sql, $params = [], $title = "Query Results") {
    echo "<h2>$title</h2>";
    echo "<pre>SQL: $sql</pre>";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Found " . count($results) . " results</p>";
        
        if (count($results) > 0) {
            echo "<table border='1' cellpadding='5'>";
            
            // Table header
            echo "<tr>";
            foreach (array_keys($results[0]) as $column) {
                echo "<th>$column</th>";
            }
            echo "</tr>";
            
            // Table data
            foreach ($results as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<div style='color:orange;'>No results found</div>";
        }
    } catch (PDOException $e) {
        echo "<div style='color:red;'>Query Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    echo "<hr>";
}

// 1. Get tables structure
echo "<h2>Database Tables Structure</h2>";
$tables = ['events', 'event_types', 'assemblies', 'zones', 'households'];

foreach ($tables as $table) {
    try {
        $stmt = $pdo->prepare("DESCRIBE $table");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Table: $table</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } catch (PDOException $e) {
        echo "<div style='color:red;'>Error describing table $table: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
echo "<hr>";

// 2. Count total events
runQuery($pdo, "SELECT COUNT(*) as total_events FROM events", [], "Total Events Count");

// 3. Check events with incomplete dates
runQuery($pdo, "SELECT event_id, title, start_date, end_date FROM events WHERE start_date IS NULL OR end_date IS NULL", [], "Events With Missing Dates");

// 4. Get a sample of recent events
runQuery($pdo, "SELECT e.event_id, e.title, e.start_date, e.end_date, e.level, et.name as event_type 
               FROM events e 
               LEFT JOIN event_types et ON e.event_type_id = et.event_type_id 
               ORDER BY e.start_date DESC LIMIT 10", [], "Recent Events (10)");

// 5. Count events by level
runQuery($pdo, "SELECT 
                CASE 
                    WHEN level = 0 THEN 'National' 
                    WHEN level = 1 THEN 'Household' 
                    WHEN level = 2 THEN 'Zone' 
                    WHEN level = 3 THEN 'Assembly' 
                    ELSE 'Unknown'
                END as level_name,
                COUNT(*) as event_count
                FROM events 
                GROUP BY level
                ORDER BY level", [], "Events By Level");

// 6. Check for upcoming events
$today = date('Y-m-d');
runQuery($pdo, "SELECT e.event_id, e.title, e.start_date, e.end_date, e.level, et.name as event_type 
               FROM events e 
               LEFT JOIN event_types et ON e.event_type_id = et.event_type_id 
               WHERE e.start_date >= ?
               ORDER BY e.start_date ASC LIMIT 10", [$today], "Upcoming Events");

// 7. Check event and event_types relationship
runQuery($pdo, "SELECT et.event_type_id, et.name, COUNT(e.event_id) as event_count
               FROM event_types et
               LEFT JOIN events e ON et.event_type_id = e.event_type_id
               GROUP BY et.event_type_id", [], "Events By Type");

// 8. Get the current user's context
$member_id = $_SESSION['member_id'];
$userContext = getUserContext($member_id);

echo "<h2>Current User Context</h2>";
echo "<pre>";
print_r($userContext);
echo "</pre>";

// 9. Show events visible to the current user
$sql = "SELECT 
            e.event_id,
            e.title,
            e.start_date,
            e.end_date,
            e.level,
            et.name as event_type_name
        FROM events e
        LEFT JOIN event_types et ON e.event_type_id = et.event_type_id
        WHERE e.level = 0"; // National events always visible

$params = [];

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

$sql .= " ORDER BY e.start_date DESC LIMIT 10";

runQuery($pdo, $sql, $params, "Events Visible to Current User");

echo "<h2>Diagnostic Summary</h2>";
echo "<p>This tool has checked the database structure and event data. If you see any errors above, they may be related to your calendar issues.</p>";
echo "<p>Common issues to check:</p>";
echo "<ul>";
echo "<li>Make sure events exist in the database</li>";
echo "<li>Check that event dates are properly formatted (YYYY-MM-DD HH:MM:SS)</li>";
echo "<li>Verify that events have the correct level, assembly_id, zone_id, or household_id values that match the current user's context</li>";
echo "<li>Ensure event_type_id values in the events table correspond to valid entries in the event_types table</li>";
echo "</ul>";

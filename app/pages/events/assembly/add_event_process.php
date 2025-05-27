<?php
// Assembly Events - Add Event Process
session_start();
include "../../../config/db.php";

// Set content type for JSON response
header('Content-Type: application/json');

// Function to log to debug.log
function logToDebug($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $user_id = $_SESSION['member_id'] ?? 'unknown';
    $log_entry = "[$timestamp] [$level] [Assembly Events] [User: $user_id] $message" . PHP_EOL;
    
    // Create debug.log file if it doesn't exist
    $log_file = '../../../debug.log';
    if (!file_exists($log_file)) {
        file_put_contents($log_file, "Debug Log Started - " . date('Y-m-d H:i:s') . PHP_EOL);
    }
    
    error_log($log_entry, 3, $log_file);
}

// Handle frontend logging requests
if (isset($_GET['log_only']) && $_GET['log_only'] == '1') {
    if (isset($_GET['action']) && $_GET['action'] == 'frontend_request') {
        $input = json_decode(file_get_contents('php://input'), true);
        logToDebug("Frontend form submission initiated - Assemblies count: " . ($input['assemblies_count'] ?? 'unknown'));
    }
    exit; // Don't process further for logging-only requests
}

// Log the start of the request
logToDebug("Assembly event creation request started");

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    logToDebug("Authentication failed - no member_id in session", 'ERROR');
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logToDebug("Invalid request method: " . $_SERVER['REQUEST_METHOD'], 'ERROR');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Log the received data (without sensitive info)
logToDebug("Received POST data: " . json_encode([
    'assemblies_count' => count($_POST['assemblies'] ?? []),
    'event_type' => $_POST['event_type'] ?? 'not_set',
    'title' => $_POST['title'] ?? 'not_set',
    'start_date' => $_POST['start_date'] ?? 'not_set',
    'end_date' => $_POST['end_date'] ?? 'not_set',
    'is_recurring' => isset($_POST['is_recurring']) ? 'yes' : 'no'
]));

// Collect and validate form data
$assemblies = $_POST['assemblies'] ?? [];
$event_type_id = trim($_POST['event_type'] ?? '');
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
$frequency = $is_recurring ? ($_POST['frequency'] ?? null) : null;
$created_by = $_SESSION['member_id'];

// Validation
$errors = [];

if (empty($assemblies) || !is_array($assemblies)) {
    $errors[] = 'At least one assembly must be selected';
}

if (empty($event_type_id)) {
    $errors[] = 'Event type is required';
}

if (empty($title)) {
    $errors[] = 'Event title is required';
}

if (empty($start_date)) {
    $errors[] = 'Start date is required';
}

if (empty($end_date)) {
    $errors[] = 'End date is required';
}

// Validate date format and logic
if (!empty($start_date) && !empty($end_date)) {
    // Try datetime-local format first (Y-m-d\TH:i), then fallback to date format (Y-m-d)
    $start_datetime = DateTime::createFromFormat('Y-m-d\TH:i', $start_date);
    if (!$start_datetime) {
        $start_datetime = DateTime::createFromFormat('Y-m-d', $start_date);
    }
    
    $end_datetime = DateTime::createFromFormat('Y-m-d\TH:i', $end_date);
    if (!$end_datetime) {
        $end_datetime = DateTime::createFromFormat('Y-m-d', $end_date);
    }
    
    if (!$start_datetime || !$end_datetime) {
        $errors[] = 'Invalid date/time format';
    } elseif ($start_datetime > $end_datetime) {
        $errors[] = 'End date/time must be after start date/time';
    }
}

// If recurring, frequency is required
if ($is_recurring && empty($frequency)) {
    $errors[] = 'Frequency is required for recurring events';
}

// Return validation errors
if (!empty($errors)) {
    logToDebug("Validation failed: " . implode(', ', $errors), 'ERROR');
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

logToDebug("Validation passed, starting database insertion for " . count($assemblies) . " assemblies");

try {
    // Start database transaction
    logToDebug("Starting database transaction");
    $pdo->beginTransaction();
    
    $inserted_events = [];
    $assembly_names = [];
    
    // Prepare the insert statement
    logToDebug("Preparing insert statement");
    $assembly_scope_id = 2; // Update this value if your scopes table uses a different ID for 'assembly'
    $stmt = $pdo->prepare("
        INSERT INTO events (
            title, description, event_type_id, start_date, end_date, 
            is_recurring, frequency, level, assembly_id, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    // Get assembly names for response message
    if (!empty($assemblies)) {
        logToDebug("Fetching assembly names for IDs: " . implode(', ', $assemblies));
        $placeholders = str_repeat('?,', count($assemblies) - 1) . '?';
        $assembly_stmt = $pdo->prepare("SELECT assembly_id, name FROM assemblies WHERE assembly_id IN ($placeholders)");
        $assembly_stmt->execute($assemblies);
        $assembly_data = $assembly_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        logToDebug("Found " . count($assembly_data) . " assemblies in database");
        
        // Create mapping of assembly_id to name
        $assembly_map = [];
        foreach ($assembly_data as $assembly) {
            $assembly_map[$assembly['assembly_id']] = $assembly['name'];
        }
    }
      // Insert one event record per selected assembly
    foreach ($assemblies as $assembly_id) {
        // Validate assembly_id is numeric
        if (!is_numeric($assembly_id)) {
            logToDebug("Invalid assembly ID detected: $assembly_id", 'ERROR');
            throw new Exception("Invalid assembly ID: " . $assembly_id);
        }
        
        logToDebug("Inserting event for assembly ID: $assembly_id");
        
        $result = $stmt->execute([
            $title,
            $description,
            $event_type_id,
            $start_date,
            $end_date,
            $is_recurring,
            $frequency,
            $assembly_scope_id, // Use numeric scope_id for 'assembly'
            $assembly_id,
            $created_by
        ]);
        
        if ($result) {
            $event_id = $pdo->lastInsertId();
            $inserted_events[] = $event_id;
            
            logToDebug("Successfully inserted event ID: $event_id for assembly ID: $assembly_id");
            
            // Add assembly name to list
            if (isset($assembly_map[$assembly_id])) {
                $assembly_names[] = $assembly_map[$assembly_id];
            }
        } else {
            logToDebug("Failed to insert event for assembly ID: $assembly_id", 'ERROR');
            throw new Exception("Failed to insert event for assembly ID: " . $assembly_id);
        }
    }
      // Commit the transaction
    logToDebug("Committing transaction");
    $pdo->commit();
    
    // Create success message
    $count = count($inserted_events);
    $assemblies_text = count($assembly_names) <= 3 
        ? implode(', ', $assembly_names)
        : implode(', ', array_slice($assembly_names, 0, 3)) . ' and ' . (count($assembly_names) - 3) . ' more';
    
    $message = $count === 1 
        ? "Event '{$title}' created successfully for {$assemblies_text}"
        : "{$count} events created successfully for '{$title}' across {$assemblies_text}";
    
    // Log successful completion
    logToDebug("SUCCESS: " . $message . " | Event IDs: " . implode(', ', $inserted_events));
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'event_ids' => $inserted_events,
        'count' => $count
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on database error
    $pdo->rollBack();
    
    // Log the detailed error
    $error_details = "PDO Error: " . $e->getMessage() . " | Code: " . $e->getCode() . " | File: " . $e->getFile() . " | Line: " . $e->getLine();
    logToDebug($error_details, 'ERROR');
    
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred while creating events. Please try again.'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on any other error
    $pdo->rollBack();
    
    // Log the error with details
    $error_details = "General Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine();
    logToDebug($error_details, 'ERROR');
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

// Log the end of request processing
logToDebug("Assembly event creation request completed");
?>

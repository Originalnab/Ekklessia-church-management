<?php
session_start();
include "../../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: assign_leader.php");
    exit;
}

// Log raw POST data
file_put_contents('post_debug.log', "Timestamp: " . date('Y-m-d H:i:s') . "\nRaw POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Check and log assistant_ids specifically
if (isset($_POST['assistant_ids']) && is_array($_POST['assistant_ids'])) {
    file_put_contents('post_debug.log', "assistant_ids is set and is an array: " . print_r($_POST['assistant_ids'], true) . "\n", FILE_APPEND);
} else {
    file_put_contents('post_debug.log', "assistant_ids is NOT set or not an array\n", FILE_APPEND);
}

$household_id = filter_input(INPUT_POST, 'household_id', FILTER_VALIDATE_INT) ?: 0;
$leader_id = filter_input(INPUT_POST, 'leader_id', FILTER_VALIDATE_INT) ?: 0;
$assistant_ids = isset($_POST['assistant_ids']) && is_array($_POST['assistant_ids']) ? array_map('intval', $_POST['assistant_ids']) : [];
$assigned_by = isset($_SESSION['username']) ? $_SESSION['username'] : "System";

// Additional logging for debugging
file_put_contents('post_debug.log', "Processed values - household_id: $household_id, leader_id: $leader_id, assistant_ids: " . print_r($assistant_ids, true) . "\n", FILE_APPEND);

// Validate inputs
if ($household_id <= 0) {
    $_SESSION['error_message'] = "Please select a valid household.";
    file_put_contents('post_debug.log', "Error: Invalid household_id\n", FILE_APPEND);
    header("Location: assign_leader.php");
    exit;
}
if ($leader_id <= 0) {
    $_SESSION['error_message'] = "Please select a leader.";
    file_put_contents('post_debug.log', "Error: Invalid leader_id\n", FILE_APPEND);
    header("Location: assign_leader.php");
    exit;
}
if (empty($assistant_ids)) {
    $_SESSION['error_message'] = "Please select at least one assistant.";
    file_put_contents('post_debug.log', "Error: No assistants selected\n", FILE_APPEND);
    header("Location: assign_leader.php");
    exit;
}

// Ensure assistant_ids are unique and valid
$assistant_ids = array_unique($assistant_ids);
$assistant_ids = array_filter($assistant_ids, function($id) {
    return $id > 0;
});

if (empty($assistant_ids)) {
    $_SESSION['error_message'] = "Please select at least one valid assistant.";
    file_put_contents('post_debug.log', "Error: No valid assistants after filtering\n", FILE_APPEND);
    header("Location: assign_leader.php");
    exit;
}

// Rest of the validation and database logic
try {
    $pdo->beginTransaction();

    // Check for existing leader assignment
    $stmt = $pdo->prepare("SELECT assignment_id FROM household_shepherdhead_assignments WHERE household_id = ?");
    $stmt->execute([$household_id]);
    $existing_leader_assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_leader_assignment) {
        $stmt = $pdo->prepare("
            UPDATE household_shepherdhead_assignments 
            SET shepherd_member_id = ?, assigned_by = ?, assigned_at = NOW(), updated_at = NOW()
            WHERE household_id = ?
        ");
        $stmt->execute([$leader_id, $assigned_by, $household_id]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO household_shepherdhead_assignments (household_id, shepherd_member_id, assigned_by, assigned_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$household_id, $leader_id, $assigned_by]);
    }

    // Remove existing assistant assignments
    $stmt = $pdo->prepare("DELETE FROM household_assistant_assignments WHERE household_id = ?");
    $stmt->execute([$household_id]);

    // Insert new assistant assignments
    $stmt = $pdo->prepare("
        INSERT INTO household_assistant_assignments (household_id, assistant_member_id, assigned_by, assigned_at, updated_at)
        VALUES (?, ?, ?, NOW(), NOW())
    ");
    foreach ($assistant_ids as $assistant_id) {
        $stmt->execute([$household_id, $assistant_id, $assigned_by]);
    }

    $pdo->commit();
    $_SESSION['success_message'] = "Leader and assistants assigned to household successfully.";
    file_put_contents('post_debug.log', "Success: Assignment completed for household $household_id\n", FILE_APPEND);
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    file_put_contents('post_debug.log', "Error: Database error - " . $e->getMessage() . "\n", FILE_APPEND);
}

header("Location: assign_leader.php");
exit;
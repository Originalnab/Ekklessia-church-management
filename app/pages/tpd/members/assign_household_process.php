<?php
session_start();
include "../../../config/db.php";

header('Content-Type: application/json');

try {
    $member_id = $_POST['member_id'] ?? null;
    $assemblies_id = $_POST['assemblies_id'] ?? null;
    $household_id = $_POST['household_id'] ?? null;
    $shepherd_id = $_POST['shepherd_id'] ?? null;
    $action = $_POST['action'] ?? null;

    // Validate required fields
    if (!$member_id || !$household_id || !$shepherd_id || !$action) {
        echo json_encode(['success' => false, 'message' => 'All fields are required, including the action parameter.']);
        exit;
    }

    // Validate member_id
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE member_id = ?");
    $stmt->execute([$member_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid member ID.']);
        exit;
    }

    // Validate household_id
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM households WHERE household_id = ?");
    $stmt->execute([$household_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid household ID.']);
        exit;
    }

    // Validate shepherd_id
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE member_id = ?");
    $stmt->execute([$shepherd_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid shepherd ID.']);
        exit;
    }

    // Validate assemblies_id if provided
    if ($assemblies_id !== null) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM assemblies WHERE assembly_id = ?");
        $stmt->execute([$assemblies_id]);
        if ($stmt->fetchColumn() == 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid assembly ID.']);
            exit;
        }
    }

    // Check if a record already exists for this member
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM member_household WHERE member_id = ?");
    $checkStmt->execute([$member_id]);
    $recordExists = $checkStmt->fetchColumn() > 0;

    $assigned_by = isset($_SESSION['username']) ? $_SESSION['username'] : 'system';

    if ($action === 'assign') {
        if ($recordExists) {
            echo json_encode([
                'success' => false,
                'message' => 'This member already has a household and shepherd assigned. Please use the "Edit Household" option to modify the assignment.'
            ]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO member_household (member_id, assemblies_id, household_id, shepherd_id, assigned_at, assigned_by, updated_at) 
                               VALUES (?, ?, ?, ?, NOW(), ?, NOW())");
        $stmt->execute([$member_id, $assemblies_id, $household_id, $shepherd_id, $assigned_by]);
        echo json_encode(['success' => true, 'message' => 'Assignment created successfully.']);
    } elseif ($action === 'edit') {
        if (!$recordExists) {
            echo json_encode([
                'success' => false,
                'message' => 'No existing assignment found for this member. Please use the "Assign Household" option to create one.'
            ]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE member_household 
                               SET assemblies_id = ?, household_id = ?, shepherd_id = ?, assigned_at = NOW(), assigned_by = ?, updated_at = NOW()
                               WHERE member_id = ?");
        $stmt->execute([$assemblies_id, $household_id, $shepherd_id, $assigned_by, $member_id]);
        echo json_encode(['success' => true, 'message' => 'Assignment updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
        exit;
    }
} catch (PDOException $e) {
    $message = 'Database error: ' . $e->getMessage();
    if (strpos($message, '1452') !== false) {
        $message = 'Invalid member, household, or shepherd selected. Please ensure they exist.';
    } elseif (strpos($message, '1062') !== false) {
        $message = 'This member already has a household and shepherd assigned. Please use the "Edit Household" option to modify the assignment.';
    }
    echo json_encode(['success' => false, 'message' => $message]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()]);
}
?>
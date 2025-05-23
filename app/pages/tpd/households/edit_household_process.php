<?php
session_start();
include "../../../config/db.php";

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data with validation
    $household_id = $_POST['household_id'] ?? null;
    $name = $_POST['name'] ?? null;
    $assembly_id = $_POST['assembly_id'] ?? null;
    $address = $_POST['address'] ?? null;
    $digital_address = $_POST['digital_address'] ?? null;
    $status = isset($_POST['status']) && $_POST['status'] === '1' ? 1 : 0;
    $nearest_landmark = $_POST['nearest_landmark'] ?: null;
    $date_started = $_POST['date_started'] ?: null;
    $created_by = $_POST['created_by'] ?? null;
    $updated_by = $_POST['updated_by'] ?? 'Admin';

    // Validate required fields
    if (!$household_id || !$name || !$assembly_id || !$address || !$digital_address || !$created_by || !$updated_by) {
        $_SESSION['error_message'] = 'Required fields are missing: ' .
            (!$household_id ? 'household_id, ' : '') .
            (!$name ? 'name, ' : '') .
            (!$assembly_id ? 'assembly_id, ' : '') .
            (!$address ? 'address, ' : '') .
            (!$digital_address ? 'digital_address, ' : '') .
            (!$created_by ? 'created_by, ' : '') .
            (!$updated_by ? 'updated_by' : '');
        header('Location: index.php');
        exit;
    }

    try {
        // Check if the PDO connection is valid
        if (!isset($pdo)) {
            throw new Exception('Database connection failed');
        }

        // Check if household_id exists
        $checkHouseholdStmt = $pdo->prepare("SELECT COUNT(*) FROM households WHERE household_id = :household_id");
        $checkHouseholdStmt->execute(['household_id' => $household_id]);
        if ($checkHouseholdStmt->fetchColumn() == 0) {
            throw new Exception('Household ID does not exist');
        }

        // Check if assembly_id exists
        $checkAssemblyStmt = $pdo->prepare("SELECT COUNT(*) FROM assemblies WHERE assembly_id = :assembly_id");
        $checkAssemblyStmt->execute(['assembly_id' => $assembly_id]);
        if ($checkAssemblyStmt->fetchColumn() == 0) {
            throw new Exception('Assembly ID does not exist');
        }

        // Prepare the update query
        $stmt = $pdo->prepare("UPDATE households 
                               SET name = :name, 
                                   assembly_id = :assembly_id, 
                                   address = :address, 
                                   digital_address = :digital_address, 
                                   status = :status, 
                                   nearest_landmark = :nearest_landmark, 
                                   date_started = :date_started, 
                                   created_by = :created_by, 
                                   updated_by = :updated_by, 
                                   updated_at = NOW()
                               WHERE household_id = :household_id");

        // Execute the query
        $stmt->execute([
            'household_id' => $household_id,
            'name' => $name,
            'assembly_id' => $assembly_id,
            'address' => $address,
            'digital_address' => $digital_address,
            'status' => $status,
            'nearest_landmark' => $nearest_landmark,
            'date_started' => $date_started,
            'created_by' => $created_by,
            'updated_by' => $updated_by
        ]);

        // Set success message in session
        $_SESSION['success_message'] = 'Household updated successfully';

        // Redirect back to index.php
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        // Log the database error
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        // Log any other errors
        error_log("General error: " . $e->getMessage());
        $_SESSION['error_message'] = 'General error: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
} else {
    $_SESSION['error_message'] = 'Invalid request method';
    header('Location: index.php');
    exit;
}
?>
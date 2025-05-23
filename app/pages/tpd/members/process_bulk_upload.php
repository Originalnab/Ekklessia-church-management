<?php
session_start();
include "../../../config/db.php";

// Set response header
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Function to generate a 3-digit random number
function generateRandomNumber() {
    return str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT); // e.g., 123, 045, 789
}

// Function to generate a unique username
function generateUniqueUsername($first_name, $last_name, $pdo) {
    $max_attempts = 10;
    $attempts = 0;

    // Get the first two letters of first_name and last letter of last_name
    $first_part = substr($first_name, 0, 2); // First two letters of first_name
    $last_part = substr($last_name, -1); // Last letter of last_name

    do {
        $random_number = generateRandomNumber();
        $username = $first_part . $last_part . $random_number; // e.g., KwI123

        // Check if the username already exists in the members table
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM members WHERE username = ?");
        $stmt_check->execute([$username]);
        $username_exists = $stmt_check->fetchColumn() > 0;

        $attempts++;
        if ($attempts >= $max_attempts && $username_exists) {
            throw new Exception("Unable to generate a unique username after $max_attempts attempts.");
        }
    } while ($username_exists);

    return $username;
}

// Function to generate a password (same format as username but with a different random number)
function generatePassword($first_name, $last_name) {
    $first_part = substr($first_name, 0, 2); // First two letters of first_name
    $last_part = substr($last_name, -1); // Last letter of last_name
    $random_number = generateRandomNumber();
    return $first_part . $last_part . $random_number; // e.g., KwI456
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $csv_data_json = $_POST['csv_data'] ?? '';
    $assemblies_id = $_POST['assemblies_id'] ?? '';
    $local_function_id = $_POST['local_function_id'] ?? '';

    if (empty($csv_data_json) || empty($assemblies_id) || empty($local_function_id)) {
        $response['message'] = 'Missing required fields: CSV data, assembly, or local function.';
        echo json_encode($response);
        exit;
    }

    // Decode CSV data
    $csv_data = json_decode($csv_data_json, true);
    if (!$csv_data) {
        $response['message'] = 'Invalid CSV data format.';
        echo json_encode($response);
        exit;
    }

    // Validate assembly and local function
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM assemblies WHERE assembly_id = ?");
        $stmt->execute([$assemblies_id]);
        if ($stmt->fetchColumn() == 0) {
            $response['message'] = 'Invalid assembly selected.';
            echo json_encode($response);
            exit;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM church_functions WHERE function_id = ? AND function_type = 'local'");
        $stmt->execute([$local_function_id]);
        if ($stmt->fetchColumn() == 0) {
            $response['message'] = 'Invalid local function selected.';
            echo json_encode($response);
            exit;
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        echo json_encode($response);
        exit;
    }

    // Begin transaction
    try {
        $pdo->beginTransaction();

        // Prepare insert statement for members
        $stmt = $pdo->prepare("
            INSERT INTO members (
                first_name, last_name, date_of_birth, gender, marital_status, contact, email, address,
                digital_address, occupation, employer, work_phone, highest_education_level, institution,
                year_graduated, status, joined_date, assemblies_id, local_function_id, username, password, created_at, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, NOW(), ?)
        ");

        // Prepare insert statement for temp_credentials
        $temp_credentials_stmt = $pdo->prepare("
            INSERT INTO temp_credentials (member_id, temp_username, temp_password, created_at)
            VALUES (?, ?, ?, NOW())
        ");

        // Get the current user (for created_by field)
        $created_by = $_SESSION['user_id'] ?? 'System'; // Adjust based on your session variable

        // Insert each member
        foreach ($csv_data as $row) {
            // Extract data from CSV row
            [
                $first_name, $last_name, $date_of_birth, $gender, $marital_status, $contact, $email, $address,
                $digital_address, $occupation, $employer, $work_phone, $highest_education_level, $institution,
                $year_graduated, $status
            ] = $row;

            // Handle empty or short first_name and last_name
            $first_name = !empty($first_name) ? $first_name : 'XX'; // Default to 'XX' if empty
            $last_name = !empty($last_name) ? $last_name : 'Y'; // Default to 'Y' if empty
            if (strlen($first_name) < 2) {
                $first_name = str_pad($first_name, 2, 'X'); // Pad with 'X' if too short
            }
            if (strlen($last_name) < 1) {
                $last_name = 'Y'; // Ensure last_name has at least one character
            }

            // Generate unique username and password
            $username = generateUniqueUsername($first_name, $last_name, $pdo);
            $password = generatePassword($first_name, $last_name);

            // Insert into members table (joined_date is set to NOW())
            $stmt->execute([
                $first_name, $last_name, $date_of_birth, $gender, $marital_status, $contact, $email, $address,
                $digital_address, $occupation, $employer, $work_phone, $highest_education_level, $institution,
                $year_graduated, $status, $assemblies_id, $local_function_id, $username, $password, $created_by
            ]);

            // Get the last inserted member ID
            $member_id = $pdo->lastInsertId();

            // Insert into temp_credentials table
            $temp_credentials_stmt->execute([$member_id, $username, $password]);
        }

        // Commit transaction
        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'Members imported successfully. Temporary credentials have been generated.';
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $response['message'] = 'Error importing members: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit;
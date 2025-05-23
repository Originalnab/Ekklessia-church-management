<?php
header('Content-Type: application/json');
include "../../../config/db.php";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve form data
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $date_of_birth = $_POST['date_of_birth'];
        $gender = $_POST['gender'];
        $marital_status = $_POST['marital_status'];
        $contact = $_POST['contact'];
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        $digital_address = $_POST['digital_address'];
        $occupation = $_POST['occupation'] ?? '';
        $employer = $_POST['employer'] ?? '';
        $work_phone = $_POST['work_phone'] ?? '';
        $highest_education_level = $_POST['highest_education_level'] ?? '';
        $institution = $_POST['institution'] ?? '';
        $year_graduated = $_POST['year_graduated'] ?? null;
        $status = $_POST['status'];
        $joined_date = $_POST['joined_date'];
        $assemblies_id = $_POST['assemblies_id'];
        $local_function_id = $_POST['local_function_id'];
        $referral_id = $_POST['referral_id'] ?? null; // Optional field
        $group_name = $_POST['group_name'] ?? '';     // Optional field
        $created_by = $_POST['created_by'];

        // Handle profile photo upload
        $profile_photo = null;
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['name']) {
            $target_dir = "C:/xampp/htdocs/Ekklessia-church-management/app/resources/assets/images/";
            $profile_photo = basename($_FILES['profile_photo']['name']);
            $target_file = $target_dir . $profile_photo;

            if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
                throw new Exception("Error uploading profile photo.");
            }
        }

        // Generate temporary username and password
        $base_username = strtolower(substr($first_name, 0, 1)) . rand(1000, 9999); // e.g., j1234
        $temp_password = substr($first_name, 0, 3) . rand(1000, 9999); // e.g., Joh1234
        $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

        // Insert new member into the database with columns in the new order
        $stmt = $pdo->prepare("INSERT INTO members (
            first_name, last_name, date_of_birth, gender, marital_status, contact, email, address, 
            digital_address, occupation, employer, work_phone, highest_education_level, institution, 
            year_graduated, status, joined_date, assemblies_id, local_function_id, referral_id, group_name, 
            created_by, profile_photo, username, password
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $first_name, $last_name, $date_of_birth, $gender, $marital_status, $contact, $email, $address,
            $digital_address, $occupation, $employer, $work_phone, $highest_education_level, $institution,
            $year_graduated, $status, $joined_date, $assemblies_id, $local_function_id, $referral_id, $group_name,
            $created_by, $profile_photo, $base_username, $hashed_password
        ]);

        // Get the last inserted member ID
        $member_id = $pdo->lastInsertId();

        // Store temporary credentials in temp_credentials table
        $stmt = $pdo->prepare("INSERT INTO temp_credentials (member_id, temp_username, temp_password) VALUES (?, ?, ?)");
        $stmt->execute([$member_id, $base_username, $temp_password]);

        // Fetch the newly inserted member for table update
        $stmt = $pdo->prepare("SELECT m.*, a.name AS assembly_name 
                               FROM members m 
                               LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id 
                               WHERE m.member_id = ?");
        $stmt->execute([$member_id]);
        $new_member = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return success response with temporary credentials
        echo json_encode([
            'success' => true,
            'member' => $new_member,
            'temp_username' => $base_username,
            'temp_password' => $temp_password
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
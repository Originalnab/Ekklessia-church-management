<?php
// Suppress errors from being output to the client
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Log errors to a file for debugging
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/htdocs/EKKLESSIA-CHURCH-MANAGEMENT/error_log.txt');

// Ensure no output before JSON
ob_start();

header('Content-Type: application/json');

try {
    // Verify the path to db.php
    $db_path = "C:/xampp/htdocs/EKKLESSIA-CHURCH-MANAGEMENT/app/config/db.php";
    if (!file_exists($db_path)) {
        throw new Exception("Database configuration file not found at: $db_path");
    }
    require_once $db_path;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['member_id'])) {
        // Retrieve form data
        $member_id = intval($_POST['member_id']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
        $gender = !empty($_POST['gender']) ? $_POST['gender'] : null;
        $marital_status = !empty($_POST['marital_status']) ? $_POST['marital_status'] : null;
        $contact = trim($_POST['contact']);
        $email = !empty($_POST['email']) ? trim($_POST['email']) : null;
        $address = !empty($_POST['address']) ? trim($_POST['address']) : null;
        $digital_address = trim($_POST['digital_address']);
        $occupation = !empty($_POST['occupation']) ? trim($_POST['occupation']) : null;
        $employer = !empty($_POST['employer']) ? trim($_POST['employer']) : null;
        $work_phone = !empty($_POST['work_phone']) ? trim($_POST['work_phone']) : null;
        $highest_education_level = !empty($_POST['highest_education_level']) ? trim($_POST['highest_education_level']) : null;
        $institution = !empty($_POST['institution']) ? trim($_POST['institution']) : null;
        $year_graduated = !empty($_POST['year_graduated']) ? trim($_POST['year_graduated']) : null;
        $status = trim($_POST['status']);
        $joined_date = trim($_POST['joined_date']);
        $assemblies_id = !empty($_POST['assemblies_id']) ? intval($_POST['assemblies_id']) : null;
        $local_function_id = !empty($_POST['local_function_id']) ? intval($_POST['local_function_id']) : null;
        $referral_id = !empty($_POST['referral_id']) ? intval($_POST['referral_id']) : null;
        $group_name = !empty($_POST['group_name']) ? trim($_POST['group_name']) : null;
        $created_by = trim($_POST['created_by']);
        $updated_by = !empty($_POST['updated_by']) ? trim($_POST['updated_by']) : 'Admin';

        // Server-side validation for required fields
        if (empty($first_name) || empty($last_name) || empty($contact) || empty($digital_address) || empty($status) || empty($joined_date) || empty($assemblies_id) || empty($local_function_id) || empty($created_by)) {
            throw new Exception("All required fields must be filled.");
        }

        // Handle profile photo upload
        $profile_photo = null;
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "C:/xampp/htdocs/EKKLESSIA-CHURCH-MANAGEMENT/app/resources/assets/images/";
            $file_ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
            $profile_photo = uniqid() . '.' . $file_ext; // Generate a unique file name
            $target_file = $target_dir . $profile_photo;

            // Validate the image
            $check = getimagesize($_FILES['profile_photo']['tmp_name']);
            if ($check === false) {
                throw new Exception("File is not an image.");
            }

            if ($_FILES['profile_photo']['size'] > 5000000) { // 5MB limit
                throw new Exception("Sorry, your file is too large.");
            }

            if (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new Exception("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
            }

            if (!file_exists($target_dir)) {
                throw new Exception("Upload directory does not exist: $target_dir");
            }

            if (!is_writable($target_dir)) {
                throw new Exception("Upload directory is not writable: $target_dir");
            }

            if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
                throw new Exception("Sorry, there was an error uploading your file.");
            }
        }

        // Update member details with the new column order
        $stmt = $pdo->prepare("UPDATE members SET 
            first_name = ?, 
            last_name = ?, 
            date_of_birth = ?, 
            gender = ?, 
            marital_status = ?, 
            contact = ?, 
            email = ?, 
            address = ?, 
            digital_address = ?, 
            occupation = ?, 
            employer = ?, 
            work_phone = ?, 
            highest_education_level = ?, 
            institution = ?, 
            year_graduated = ?, 
            status = ?, 
            joined_date = ?, 
            assemblies_id = ?, 
            local_function_id = ?, 
            referral_id = ?, 
            group_name = ?, 
            created_by = ?, 
            updated_by = ?, 
            updated_at = NOW(),
            profile_photo = COALESCE(?, profile_photo)
            WHERE member_id = ?");
        $stmt->execute([
            $first_name, $last_name, $date_of_birth, $gender, $marital_status, $contact, $email, $address,
            $digital_address, $occupation, $employer, $work_phone, $highest_education_level, $institution,
            $year_graduated, $status, $joined_date, $assemblies_id, $local_function_id, $referral_id, $group_name,
            $created_by, $updated_by, $profile_photo, $member_id
        ]);

        // Fetch updated member data to return, including referrer details
        $stmt = $pdo->prepare("
            SELECT 
                m.*, 
                a.name AS assembly_name, 
                r.first_name AS referral_first_name, 
                r.last_name AS referral_last_name,
                CONCAT(s.first_name, ' ', s.last_name) AS shepherd_name
            FROM members m 
            LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id 
            LEFT JOIN members r ON m.referral_id = r.member_id 
            LEFT JOIN member_household mh ON m.member_id = mh.member_id
            LEFT JOIN members s ON mh.shepherd_id = s.member_id
            WHERE m.member_id = ?
        ");
        $stmt->execute([$member_id]);
        $updated_member = $stmt->fetch(PDO::FETCH_ASSOC);

        // Clear output buffer and send JSON response
        ob_end_clean();
        echo json_encode(['success' => true, 'member' => $updated_member]);
    } else {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid request method or missing member_id']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
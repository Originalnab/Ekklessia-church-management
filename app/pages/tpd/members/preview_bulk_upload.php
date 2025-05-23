<?php
session_start();
include "../../../config/db.php";

// Set response header
header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'html' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    // Validate file type
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($file_extension) !== 'csv') {
        $response['message'] = 'Please upload a valid CSV file.';
        echo json_encode($response);
        exit;
    }

    // Validate file size (e.g., max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $response['message'] = 'File size exceeds the maximum limit of 5MB.';
        echo json_encode($response);
        exit;
    }

    // Read the CSV file
    $csv_data = [];
    if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
        // Get headers
        $headers = fgetcsv($handle);
        $expected_headers = [
            'first_name', 'last_name', 'date_of_birth', 'gender', 'marital_status', 'contact', 'email', 'address',
            'digital_address', 'occupation', 'employer', 'work_phone', 'highest_education_level', 'institution',
            'year_graduated', 'status'
        ];

        // Validate headers
        if ($headers !== $expected_headers) {
            $response['message'] = 'CSV file headers do not match the expected format. Please use the sample CSV.';
            fclose($handle);
            echo json_encode($response);
            exit;
        }

        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            // Validate gender
            if (!in_array($row[3], ['Male', 'Female'])) {
                $response['message'] = "Invalid gender value in row: " . implode(',', $row) . ". Must be 'Male' or 'Female'.";
                fclose($handle);
                echo json_encode($response);
                exit;
            }

            // Validate marital status
            if (!in_array($row[4], ['Single', 'Married', 'Divorced', 'Widowed'])) {
                $response['message'] = "Invalid marital status in row: " . implode(',', $row) . ". Must be 'Single', 'Married', 'Divorced', or 'Widowed'.";
                fclose($handle);
                echo json_encode($response);
                exit;
            }

            // Validate status
            if (!in_array($row[15], ['Committed saint', 'Active saint', 'Worker', 'New saint'])) {
                $response['message'] = "Invalid status in row: " . implode(',', $row) . ". Must be 'Committed saint', 'Active saint', 'Worker', or 'New saint'.";
                fclose($handle);
                echo json_encode($response);
                exit;
            }

            // Validate date_of_birth format
            if (!empty($row[2]) && !DateTime::createFromFormat('Y-m-d', $row[2])) {
                $response['message'] = "Invalid date_of_birth format in row: " . implode(',', $row) . ". Must be in YYYY-MM-DD format.";
                fclose($handle);
                echo json_encode($response);
                exit;
            }

            $csv_data[] = $row;
        }
        fclose($handle);
    } else {
        $response['message'] = 'Failed to read the uploaded CSV file.';
        echo json_encode($response);
        exit;
    }

    // Generate preview table
    $html = '<form id="bulkUploadPreviewForm">';
    $html .= '<input type="hidden" name="csv_data" value="' . htmlspecialchars(json_encode($csv_data)) . '">';
    
    // Assembly selection
    $html .= '<div class="mb-3">';
    $html .= '<label for="previewAssemblySelect" class="form-label">Select Assembly <span class="text-danger">*</span></label>';
    $html .= '<select class="form-select" id="previewAssemblySelect" name="assemblies_id" required>';
    $html .= '<option value="">-- Select Assembly --</option>';

    // Fetch assemblies for dropdown
    try {
        $stmt = $pdo->query("SELECT assembly_id, name FROM assemblies ORDER BY name ASC");
        $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($assemblies as $assembly) {
            $html .= '<option value="' . htmlspecialchars($assembly['assembly_id']) . '">' . htmlspecialchars($assembly['name']) . '</option>';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Error fetching assemblies: ' . $e->getMessage();
        echo json_encode($response);
        exit;
    }

    $html .= '</select>';
    $html .= '</div>';

    // Local Function selection
    $html .= '<div class="mb-3">';
    $html .= '<label for="previewLocalFunctionSelect" class="form-label">Select Local Function <span class="text-danger">*</span></label>';
    $html .= '<select class="form-select" id="previewLocalFunctionSelect" name="local_function_id" required>';
    $html .= '<option value="">-- Select Local Function --</option>';

    // Fetch local functions from church_functions table where function_type = 'local'
    try {
        $stmt = $pdo->query("SELECT function_id, function_name FROM church_functions WHERE function_type = 'local' ORDER BY function_name ASC");
        $local_functions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($local_functions)) {
            $response['message'] = 'No local functions available. Please add local functions to the database before proceeding.';
            echo json_encode($response);
            exit;
        }
        foreach ($local_functions as $function) {
            $html .= '<option value="' . htmlspecialchars($function['function_id']) . '">' . htmlspecialchars($function['function_name']) . '</option>';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Error fetching local functions: ' . $e->getMessage();
        echo json_encode($response);
        exit;
    }

    $html .= '</select>';
    $html .= '</div>';

    // Preview table with styled header
    $html .= '<div class="table-responsive">';
    $html .= '<table class="table table-bordered table-striped">';
    $html .= '<thead class="bg-primary text-white">';
    $html .= '<tr>';
    foreach ($expected_headers as $header) {
        $html .= '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . '</th>';
    }
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    foreach ($csv_data as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '</div>';

    // Submit button for final import
    $html .= '<button type="submit" class="btn btn-success">Import Members</button>';
    $html .= '</form>';

    $response['success'] = true;
    $response['html'] = $html;
} else {
    $response['message'] = 'No CSV file uploaded.';
}

echo json_encode($response);
exit;
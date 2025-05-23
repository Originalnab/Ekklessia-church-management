<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sample_members_upload.csv"');

$headers = [
    'first_name', 'last_name', 'date_of_birth', 'gender', 'marital_status', 'contact', 'email', 'address',
    'digital_address', 'occupation', 'employer', 'work_phone', 'highest_education_level', 'institution',
    'year_graduated', 'status'
];

$sample_data = [
    [
        'John', 'Doe', '1990-01-01', 'Male', 'Single', '1234567890', 'john.doe@example.com', '123 Main St',
        'DIGI-123', 'Engineer', 'Tech Corp', '0987654321', 'Bachelor', 'Tech University', '2012', 'Active saint'
    ]
];

// Output CSV
$output = fopen('php://output', 'w');
fputcsv($output, $headers);
foreach ($sample_data as $row) {
    fputcsv($output, $row);
}
fclose($output);
exit;
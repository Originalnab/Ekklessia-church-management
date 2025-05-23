<?php
include "../../../config/db.php";

try {
    $stmt = $pdo->query("
        SELECT 
            m.member_id, m.first_name, m.last_name, m.date_of_birth, m.gender, m.marital_status, 
            m.contact, m.email, m.address, m.digital_address, m.occupation, m.employer, m.work_phone, 
            m.highest_education_level, m.institution, m.year_graduated, m.status, m.joined_date, 
            m.assemblies_id, m.local_function_id AS local_function_id, m.username, m.password, m.created_at, m.updated_at, 
            m.created_by, m.updated_by, m.profile_photo, m.referral_id, 
            a.name AS assembly_name, 
            r.first_name AS referral_first_name, r.last_name AS referral_last_name,
            CONCAT(s.first_name, ' ', s.last_name) AS shepherd_name,
            cf.function_name AS local_function_name
        FROM members m
        LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
        LEFT JOIN members r ON m.referral_id = r.member_id
        LEFT JOIN member_household mh ON m.member_id = mh.member_id
        LEFT JOIN members s ON mh.shepherd_id = s.member_id
        ORDER BY m.created_at DESC
    ");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($members);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching members: ' . $e->getMessage()]);
}
?>
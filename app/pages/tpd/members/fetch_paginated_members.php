<?php
include "../../../config/db.php";

// Pagination parameters
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Filter parameters
$filter_name = isset($_GET['name']) ? trim($_GET['name']) : '';
$filter_assembly = isset($_GET['assembly']) ? trim($_GET['assembly']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$filter_joined_start = isset($_GET['joined_start']) ? trim($_GET['joined_start']) : '';
$filter_joined_end = isset($_GET['joined_end']) ? trim($_GET['joined_end']) : '';

// Build the WHERE clause dynamically
$where_clauses = [];
$params = [];

if (!empty($_GET['name'])) {
    $where_clauses[] = "(m.first_name LIKE :firstname OR m.last_name LIKE :lastname)";
    $params['firstname'] = '%' . $_GET['name'] . '%';
    $params['lastname'] = '%' . $_GET['name'] . '%';
}

if (!empty($_GET['assembly'])) {
    $where_clauses[] = "a.name = :assembly";
    $params['assembly'] = $_GET['assembly'];
}

if (!empty($_GET['household'])) {
    $where_clauses[] = "mh.household_id = :household_id";
    $params['household_id'] = $_GET['household'];
}

if (!empty($_GET['shepherd'])) {
    $where_clauses[] = "mh.shepherd_id = :shepherd_id";
    $params['shepherd_id'] = $_GET['shepherd'];
}

if (!empty($_GET['status'])) {
    $where_clauses[] = "m.status = :status";
    $params['status'] = $_GET['status'];
}

if (!empty($_GET['local_role'])) {
    $where_clauses[] = "m.local_function_id = :local_function_id";
    $params['local_function_id'] = $_GET['local_role'];
}

if (!empty($_GET['joined_start'])) {
    $where_clauses[] = "m.joined_date >= :joined_start";
    $params['joined_start'] = $_GET['joined_start'];
}

if (!empty($_GET['joined_end'])) {
    $where_clauses[] = "m.joined_date <= :joined_end";
    $params['joined_end'] = $_GET['joined_end'];
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Fetch total number of members with filters
try {
    $total_query = "SELECT COUNT(*) FROM members m
                    LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
                    LEFT JOIN members r ON m.referral_id = r.member_id
                    LEFT JOIN member_household mh ON m.member_id = mh.member_id
                    LEFT JOIN members s ON mh.shepherd_id = s.member_id
                    $where_sql";
    $totalStmt = $pdo->prepare($total_query);
    $totalStmt->execute($params);
    $total_members = $totalStmt->fetchColumn();
    $total_pages = ceil($total_members / $records_per_page);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching total members: ' . $e->getMessage()]);
    exit;
}

// Fetch paginated members with filters
try {    $query = "
        SELECT 
            m.member_id, m.first_name, m.last_name, m.date_of_birth, m.gender, m.marital_status, 
            m.contact, m.email, m.address, m.digital_address, m.occupation, m.employer, m.work_phone, 
            m.highest_education_level, m.institution, m.year_graduated, m.status, m.joined_date, 
            m.assemblies_id, m.local_function_id, m.username, m.password, m.created_at, m.updated_at, 
            m.created_by, m.updated_by, m.profile_photo, m.referral_id, m.group_name, 
            a.name AS assembly_name, 
            r.first_name AS referral_first_name, r.last_name AS referral_last_name,
            CONCAT(s.first_name, ' ', s.last_name) AS shepherd_name,
            h.name AS household_name, mh.household_id, mh.shepherd_id,
            cf.function_name
        FROM members m
        LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
        LEFT JOIN members r ON m.referral_id = r.member_id
        LEFT JOIN member_household mh ON m.member_id = mh.member_id
        LEFT JOIN households h ON mh.household_id = h.household_id
        LEFT JOIN members s ON mh.shepherd_id = s.member_id
        LEFT JOIN church_functions cf ON m.local_function_id = cf.function_id
        $where_sql
        ORDER BY m.created_at DESC
        LIMIT :offset, :limit
    ";

    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching members: ' . $e->getMessage()]);
    exit;
}

// Return data as JSON
echo json_encode([
    'success' => true,
    'members' => $members,
    'pagination' => [
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'total_members' => $total_members,
        'records_per_page' => $records_per_page
    ]
]);
?>
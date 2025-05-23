<?php
session_start();
header('Content-Type: application/json');

include "../../../config/db.php";
include "../../../functions/role_management.php";
include "../../../functions/member_functions.php";

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

// Get pagination parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$assemblyId = isset($_GET['assembly_id']) ? intval($_GET['assembly_id']) : 0;
$roleId = isset($_GET['role_id']) ? intval($_GET['role_id']) : 0;

try {
    // Base query - join members with assemblies
    $query = "SELECT m.member_id, m.first_name, m.last_name, m.email, 
                m.contact, m.profile_photo, a.name AS assembly_name 
              FROM members m 
              LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id";
    
    // Where conditions
    $whereConditions = [];
    $params = [];
    
    // Filter by search term
    if (!empty($search)) {
        $whereConditions[] = "(m.first_name LIKE ? OR m.last_name LIKE ? OR m.email LIKE ? OR m.contact LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Filter by assembly
    if ($assemblyId > 0) {
        $whereConditions[] = "m.assemblies_id = ?";
        $params[] = $assemblyId;
    }
    
    // Filter by role
    if ($roleId > 0) {
        // Add a subquery to filter members by role
        $whereConditions[] = "m.member_id IN (
            SELECT mr.member_id FROM member_role mr WHERE mr.role_id = ?
        )";
        $params[] = $roleId;
    }
    
    // Combine WHERE conditions
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(' AND ', $whereConditions);
    }
    
    // Add ORDER BY
    $query .= " ORDER BY m.first_name ASC, m.last_name ASC";
    
    // Count total results for pagination
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM ($query) as count_query");
    $countStmt->execute($params);
    $totalMembers = $countStmt->fetchColumn();
    $totalPages = ceil($totalMembers / $limit);
    
    // Add LIMIT and OFFSET for pagination
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    // Execute the main query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get roles for each member
    foreach ($members as &$member) {
        // Get roles
        $rolesQuery = "SELECT r.role_name, mr.is_primary 
                      FROM member_role mr 
                      JOIN roles r ON mr.role_id = r.role_id 
                      WHERE mr.member_id = ? 
                      ORDER BY mr.is_primary DESC, r.role_name ASC";
        $rolesStmt = $pdo->prepare($rolesQuery);
        $rolesStmt->execute([$member['member_id']]);
        $roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format roles as string
        $roleNames = [];
        $primaryRole = '';
        foreach ($roles as $role) {
            $roleNames[] = $role['role_name'];
            if ($role['is_primary']) {
                $primaryRole = $role['role_name'];
            }
        }
        
        $member['roles'] = implode(', ', $roleNames);
        $member['primary_role'] = $primaryRole;
    }
    
    // Return success with members and pagination info
    echo json_encode([
        'success' => true,
        'members' => $members,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_members' => $totalMembers
        ]
    ]);
    
} catch (PDOException $e) {
    // Return error
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
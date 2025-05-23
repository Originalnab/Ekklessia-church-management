<?php
session_start();
include "../../config/config.php";
include "../../functions/role_management.php";

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;
$roleName = isset($_GET['name']) ? trim($_GET['name']) : '';

try {
    // Base query
    $baseQuery = "
        SELECT 
            r.role_id,
            r.role_name,
            r.hierarchy_level,
            r.description,
            GROUP_CONCAT(DISTINCT p.permission_name) as permissions,
            COUNT(DISTINCT mr.member_id) as assigned_members
        FROM roles r
        LEFT JOIN role_permissions rp ON r.role_id = rp.role_id
        LEFT JOIN permissions p ON rp.permission_id = p.permission_id
        LEFT JOIN member_role mr ON r.role_id = mr.role_id
        WHERE r.role_name LIKE :role_name
        GROUP BY r.role_id
        ORDER BY 
            FIELD(r.hierarchy_level, 'National', 'Zone', 'Assembly', 'Household'),
            r.role_name
    ";

    // Get total count for pagination
    $countQuery = "SELECT COUNT(DISTINCT r.role_id) FROM roles r WHERE r.role_name LIKE :role_name";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute(['role_name' => "%$roleName%"]);
    $totalRoles = $countStmt->fetchColumn();
    $totalPages = ceil($totalRoles / $recordsPerPage);

    // Get paginated results
    $query = $baseQuery . " LIMIT :offset, :limit";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':role_name', "%$roleName%", PDO::PARAM_STR);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get scope information for each role
    foreach ($roles as &$role) {
        $scopeQuery = "
            SELECT 
                s.scope_id,
                s.scope_name,
                s.description
            FROM role_scopes rs
            JOIN scopes s ON rs.scope_id = s.scope_id
            WHERE rs.role_id = :role_id
            ORDER BY s.scope_id
        ";
        $scopeStmt = $pdo->prepare($scopeQuery);
        $scopeStmt->execute(['role_id' => $role['role_id']]);
        $role['scopes'] = $scopeStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($role); // Break the reference

    // Get stats for dashboard
    $statsQuery = "
        SELECT 
            (SELECT COUNT(DISTINCT member_id) FROM member_role) as members_with_roles,
            (SELECT COUNT(*) FROM members) as total_members
    ";
    $statsStmt = $pdo->query($statsQuery);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'roles' => $roles,
        'total_pages' => $totalPages,
        'total_records' => $totalRoles,
        'stats' => [
            'members_with_roles' => (int)$stats['members_with_roles'],
            'members_without_roles' => (int)$stats['total_members'] - (int)$stats['members_with_roles']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
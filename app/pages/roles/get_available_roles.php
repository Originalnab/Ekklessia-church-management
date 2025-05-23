<?php
session_start();
include "../../config/config.php";
include "../auth/auth_check.php";

header('Content-Type: application/json');

if (!isset($_GET['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Member ID is required']);
    exit;
}

try {
    $memberId = $_GET['member_id'];
    
    // Get current roles of the member
    $currentRolesStmt = $pdo->prepare("
        SELECT mr.function_id, cf.function_name, r.role_id, r.role_name
        FROM member_role mr
        JOIN church_functions cf ON mr.function_id = cf.function_id
        JOIN roles r ON mr.role_id = r.role_id
        WHERE mr.member_id = ?
    ");
    $currentRolesStmt->execute([$memberId]);
    $currentRoles = $currentRolesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all available roles with their functions
    $stmt = $pdo->query("
        SELECT 
            r.role_id,
            r.role_name,
            r.description,
            r.parent_role_id,
            pr.role_name as parent_role_name,
            s.scope_name,
            GROUP_CONCAT(DISTINCT cf.function_id) as function_ids,
            GROUP_CONCAT(DISTINCT cf.function_name) as function_names,
            GROUP_CONCAT(DISTINCT cf.description) as function_descriptions
        FROM roles r
        LEFT JOIN roles pr ON r.parent_role_id = pr.role_id
        LEFT JOIN role_scopes rs ON r.role_id = rs.role_id
        LEFT JOIN scopes s ON rs.scope_id = s.scope_id
        LEFT JOIN church_functions cf ON cf.role_id = r.role_id
        GROUP BY r.role_id
        ORDER BY s.scope_id DESC, r.role_name
    ");
    
    $roles = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $functions = [];
        if ($row['function_ids'] && $row['function_names']) {
            $functionIds = explode(',', $row['function_ids']);
            $functionNames = explode(',', $row['function_names']);
            $functionDescs = $row['function_descriptions'] ? explode(',', $row['function_descriptions']) : array_fill(0, count($functionIds), '');
            
            for ($i = 0; $i < count($functionIds); $i++) {
                $isAssigned = false;
                foreach ($currentRoles as $currentRole) {
                    if ($currentRole['function_id'] == $functionIds[$i]) {
                        $isAssigned = true;
                        break;
                    }
                }
                
                $functions[] = [
                    'id' => $functionIds[$i],
                    'name' => $functionNames[$i],
                    'description' => $functionDescs[$i] ?? '',
                    'assigned' => $isAssigned
                ];
            }
        }
        
        $roles[] = [
            'id' => $row['role_id'],
            'name' => $row['role_name'],
            'description' => $row['description'],
            'scope' => $row['scope_name'],
            'parent_role' => [
                'id' => $row['parent_role_id'],
                'name' => $row['parent_role_name']
            ],
            'functions' => $functions
        ];
    }
    
    echo json_encode([
        'success' => true,
        'roles' => $roles,
        'current_roles' => $currentRoles
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
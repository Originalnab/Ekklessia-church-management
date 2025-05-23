<?php
/**
 * Define permission groups and their permissions
 */
function getPermissionGroups() {
    return [
        'role_management' => [
            'title' => 'Role Management',
            'permissions' => [
                'manage_roles',
                'manage_permissions',
                'assign_roles',
                'view_roles',
                'add_roles'
            ]
        ],
        'member_management' => [
            'title' => 'Member Management',
            'permissions' => [
                'manage_members',
                'view_members',
                'edit_members',
                'delete_members',
                'add_members',
                'assign_household'
            ]
        ],
        'assembly_management' => [
            'title' => 'Assembly Management',
            'permissions' => [
                'manage_assembly',
                'view_assembly',
                'edit_assembly',
                'delete_assembly',
                'add_assembly'
            ]
        ],
        'zone_management' => [
            'title' => 'Zone Management',
            'permissions' => [
                'manage_zone',
                'view_zone',
                'edit_zone',
                'delete_zone',
                'add_zone'
            ]
        ],
        'household_management' => [
            'title' => 'Household Management',
            'permissions' => [
                'manage_household',
                'view_household',
                'edit_household',
                'delete_household',
                'add_household'
            ]
        ],
        'finance_management' => [
            'title' => 'Finance Management',
            'permissions' => [
                'manage_finance',
                'view_finance',
                'edit_finance',
                'delete_finance'
            ]
        ],
        'report_management' => [
            'title' => 'Report Management',
            'permissions' => [
                'manage_reports',
                'view_reports',
                'generate_reports',
                'export_reports'
            ]
        ],
        'specialized_ministries' => [
            'title' => 'Specialized Ministries',
            'permissions' => [
                'manage_specialized_ministries',
                'view_specialized_ministries',
                'edit_specialized_ministries',
                'delete_specialized_ministries'
            ]
        ]
    ];
}

/**
 * Get the description for a specific permission
 */
function getPermissionDescription($permissionName) {
    $descriptions = [
        // Role Management
        'manage_roles' => 'Create, edit, and delete roles in the system',
        'manage_permissions' => 'Manage permission assignments to roles',
        'assign_roles' => 'Assign roles to members',
        'view_roles' => 'View role information and assignments',
        'add_roles' => 'Add new roles to the system',
        
        // Member Management
        'manage_members' => 'Full control over member records',
        'view_members' => 'View member information',
        'edit_members' => 'Edit member details',
        'delete_members' => 'Remove members from the system',
        'add_members' => 'Add new members to the system',
        'assign_household' => 'Assign members to households',
        
        // Assembly Management
        'manage_assembly' => 'Full control over assembly operations',
        'view_assembly' => 'View assembly information',
        'edit_assembly' => 'Edit assembly details',
        'delete_assembly' => 'Remove assemblies from the system',
        'add_assembly' => 'Add new assemblies to the system',
        
        // Zone Management
        'manage_zone' => 'Full control over zone operations',
        'view_zone' => 'View zone information',
        'edit_zone' => 'Edit zone details',
        'delete_zone' => 'Remove zones from the system',
        'add_zone' => 'Add new zones to the system',
        
        // Household Management
        'manage_household' => 'Full control over household operations',
        'view_household' => 'View household information',
        'edit_household' => 'Edit household details',
        'delete_household' => 'Remove households from the system',
        'add_household' => 'Add new households to the system',
        
        // Finance Management
        'manage_finance' => 'Full control over financial operations',
        'view_finance' => 'View financial information',
        'edit_finance' => 'Edit financial records',
        'delete_finance' => 'Remove financial records',
        
        // Report Management
        'manage_reports' => 'Full control over report operations',
        'view_reports' => 'View system reports',
        'generate_reports' => 'Generate new reports',
        'export_reports' => 'Export reports from the system',
        
        // Specialized Ministries
        'manage_specialized_ministries' => 'Full control over specialized ministries',
        'view_specialized_ministries' => 'View specialized ministries information',
        'edit_specialized_ministries' => 'Edit specialized ministries details',
        'delete_specialized_ministries' => 'Remove specialized ministries'
    ];
    
    return $descriptions[$permissionName] ?? 'No description available';
}

function getPermissionsByGroup($groupKey) {
    $groups = getPermissionGroups();
    return $groups[$groupKey]['permissions'] ?? [];
}

function getPermissionGroup($permissionName) {
    $groups = getPermissionGroups();
    foreach ($groups as $key => $group) {
        if (in_array($permissionName, $group['permissions'])) {
            return $key;
        }
    }
    return null;
}
?>
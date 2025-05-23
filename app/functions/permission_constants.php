<?php
/**
 * Permission Constants
 * 
 * This file serves as the single source of truth for all permission names in the application.
 * Always use these constants when referencing permissions instead of hardcoding strings.
 */

// Member Management Permissions
define('PERM_VIEW_MEMBERS', 'view_members');
define('PERM_ADD_MEMBER', 'add_member');
define('PERM_EDIT_MEMBER', 'edit_member');
define('PERM_DELETE_MEMBER', 'delete_member');
define('PERM_IMPORT_MEMBERS', 'import_members');
define('PERM_EXPORT_MEMBERS', 'export_members');
define('PERM_MANAGE_MEMBERS', 'manage_members');  // Add management permission

// Household Management Permissions
define('PERM_VIEW_HOUSEHOLD', 'view_household');
define('PERM_ADD_HOUSEHOLD', 'add_household');
define('PERM_EDIT_HOUSEHOLD', 'edit_household');
define('PERM_DELETE_HOUSEHOLD', 'delete_household');
define('PERM_MANAGE_HOUSEHOLD_MEMBERS', 'manage_household_members');

// Role Management Permissions
define('PERM_VIEW_ROLES', 'view_roles');
define('PERM_ADD_ROLE', 'add_role');
define('PERM_EDIT_ROLE', 'edit_role');
define('PERM_DELETE_ROLE', 'delete_role');
define('PERM_ASSIGN_ROLES', 'assign_roles');
define('PERM_MANAGE_ROLES', 'manage_roles');  // Add management permission

// Assembly Management Permissions
define('PERM_VIEW_ASSEMBLIES', 'view_assemblies');
define('PERM_ADD_ASSEMBLY', 'add_assembly');
define('PERM_EDIT_ASSEMBLY', 'edit_assembly');
define('PERM_DELETE_ASSEMBLY', 'delete_assembly');
define('PERM_MANAGE_ASSEMBLY', 'manage_assembly');  // Add management permission

// Zone Management Permissions
define('PERM_VIEW_ZONES', 'view_zones');
define('PERM_ADD_ZONE', 'add_zone');
define('PERM_EDIT_ZONE', 'edit_zone');
define('PERM_DELETE_ZONE', 'delete_zone');
define('PERM_MANAGE_ZONES', 'manage_zone');  // Add management permission

// Finance Management Permissions
define('PERM_VIEW_FINANCES', 'view_finances');
define('PERM_ADD_TRANSACTION', 'add_transaction');
define('PERM_EDIT_TRANSACTION', 'edit_transaction');
define('PERM_DELETE_TRANSACTION', 'delete_transaction');
define('PERM_GENERATE_FINANCIAL_REPORTS', 'generate_financial_reports');
define('PERM_MANAGE_FINANCE', 'manage_finance');  // Add management permission

// System Administration Permissions
define('PERM_MANAGE_SYSTEM_SETTINGS', 'manage_system_settings');
define('PERM_VIEW_AUDIT_LOGS', 'view_audit_logs');
define('PERM_MANAGE_PERMISSIONS', 'manage_permissions');
define('PERM_BACKUP_DATABASE', 'backup_database');
define('PERM_RESTORE_DATABASE', 'restore_database');

/**
 * Permission Display Names
 * Maps database permission names to human-readable display names
 */
$PERMISSION_DISPLAY_NAMES = [
    // Member Management
    PERM_VIEW_MEMBERS => 'View Members',
    PERM_ADD_MEMBER => 'Add Member',
    PERM_EDIT_MEMBER => 'Edit Member',
    PERM_DELETE_MEMBER => 'Delete Member',
    PERM_IMPORT_MEMBERS => 'Import Members',
    PERM_EXPORT_MEMBERS => 'Export Members',
    PERM_MANAGE_MEMBERS => 'Manage Members',  // Add display name
    
    // Household Management
    PERM_VIEW_HOUSEHOLD => 'View Households',
    PERM_ADD_HOUSEHOLD => 'Add Household',
    PERM_EDIT_HOUSEHOLD => 'Edit Household',
    PERM_DELETE_HOUSEHOLD => 'Delete Household',
    PERM_MANAGE_HOUSEHOLD_MEMBERS => 'Manage Household Members',
    
    // Role Management
    PERM_VIEW_ROLES => 'View Roles',
    PERM_ADD_ROLE => 'Add Role',
    PERM_EDIT_ROLE => 'Edit Role',
    PERM_DELETE_ROLE => 'Delete Role',
    PERM_ASSIGN_ROLES => 'Assign Roles',
    PERM_MANAGE_ROLES => 'Manage Roles',  // Add display name
    
    // Assembly Management
    PERM_VIEW_ASSEMBLIES => 'View Assemblies',
    PERM_ADD_ASSEMBLY => 'Add Assembly',
    PERM_EDIT_ASSEMBLY => 'Edit Assembly',
    PERM_DELETE_ASSEMBLY => 'Delete Assembly',
    PERM_MANAGE_ASSEMBLY => 'Manage Assembly',  // Add display name
    
    // Zone Management
    PERM_VIEW_ZONES => 'View Zones',
    PERM_ADD_ZONE => 'Add Zone',
    PERM_EDIT_ZONE => 'Edit Zone',
    PERM_DELETE_ZONE => 'Delete Zone',
    PERM_MANAGE_ZONES => 'Manage Zones',  // Add display name
    
    // Finance Management
    PERM_VIEW_FINANCES => 'View Finances',
    PERM_ADD_TRANSACTION => 'Add Transaction',
    PERM_EDIT_TRANSACTION => 'Edit Transaction',
    PERM_DELETE_TRANSACTION => 'Delete Transaction',
    PERM_GENERATE_FINANCIAL_REPORTS => 'Generate Financial Reports',
    PERM_MANAGE_FINANCE => 'Manage Finance',  // Add display name
    
    // System Administration
    PERM_MANAGE_SYSTEM_SETTINGS => 'Manage System Settings',
    PERM_VIEW_AUDIT_LOGS => 'View Audit Logs',
    PERM_MANAGE_PERMISSIONS => 'Manage Permissions',
    PERM_BACKUP_DATABASE => 'Backup Database',
    PERM_RESTORE_DATABASE => 'Restore Database',
];

/**
 * Permission Form to Database Mapping
 * Maps form input values to database permission names
 */
$PERMISSION_MAPPING = [
    // Member Management
    'view_members' => PERM_VIEW_MEMBERS,
    'add_members' => PERM_ADD_MEMBER,
    'edit_members' => PERM_EDIT_MEMBER,
    'delete_members' => PERM_DELETE_MEMBER,
    'import_members' => PERM_IMPORT_MEMBERS,
    'export_members' => PERM_EXPORT_MEMBERS,
    'manage_members' => 'manage_members',  // Add management permission
    
    // Household Management
    'view_household' => PERM_VIEW_HOUSEHOLD,
    'add_household' => PERM_ADD_HOUSEHOLD,
    'edit_household' => PERM_EDIT_HOUSEHOLD,
    'delete_household' => PERM_DELETE_HOUSEHOLD,
    'assign_household' => PERM_MANAGE_HOUSEHOLD_MEMBERS,
    'manage_household' => PERM_MANAGE_HOUSEHOLD_MEMBERS,
    
    // Role Management
    'view_roles' => PERM_VIEW_ROLES,
    'add_roles' => PERM_ADD_ROLE,
    'edit_roles' => PERM_EDIT_ROLE,
    'delete_roles' => PERM_DELETE_ROLE,
    'assign_roles' => PERM_ASSIGN_ROLES,
    'manage_roles' => 'manage_roles',  // Add management permission
    
    // Assembly Management
    'view_assembly' => PERM_VIEW_ASSEMBLIES,
    'add_assembly' => PERM_ADD_ASSEMBLY,
    'edit_assembly' => PERM_EDIT_ASSEMBLY,
    'delete_assembly' => PERM_DELETE_ASSEMBLY,
    'manage_assembly' => 'manage_assembly',  // Add management permission
    
    // Zone Management
    'view_zone' => PERM_VIEW_ZONES,
    'add_zone' => PERM_ADD_ZONE,
    'edit_zone' => PERM_EDIT_ZONE,
    'delete_zone' => PERM_DELETE_ZONE,
    'manage_zone' => 'manage_zone',  // Add management permission
    
    // Finance Management
    'view_finances' => PERM_VIEW_FINANCES,
    'add_transaction' => PERM_ADD_TRANSACTION,
    'edit_transaction' => PERM_EDIT_TRANSACTION,
    'delete_transaction' => PERM_DELETE_TRANSACTION,
    'generate_reports' => PERM_GENERATE_FINANCIAL_REPORTS,
    'manage_finance' => 'manage_finance',  // Add management permission
    
    // System Administration
    'manage_settings' => PERM_MANAGE_SYSTEM_SETTINGS,
    'view_logs' => PERM_VIEW_AUDIT_LOGS,
    'manage_permissions' => PERM_MANAGE_PERMISSIONS,
    'backup_database' => PERM_BACKUP_DATABASE,
    'restore_database' => PERM_RESTORE_DATABASE,
];

/**
 * Permission Groups for UI organization
 * Groups permissions by functional area for display in the UI
 */
$PERMISSION_GROUPS = [
    'Member Management' => [
        PERM_VIEW_MEMBERS,
        PERM_ADD_MEMBER,
        PERM_EDIT_MEMBER,
        PERM_DELETE_MEMBER,
        PERM_IMPORT_MEMBERS,
        PERM_EXPORT_MEMBERS,
        PERM_MANAGE_MEMBERS,  // Add management permission
    ],
    'Household Management' => [
        PERM_VIEW_HOUSEHOLD,
        PERM_ADD_HOUSEHOLD,
        PERM_EDIT_HOUSEHOLD,
        PERM_DELETE_HOUSEHOLD,
        PERM_MANAGE_HOUSEHOLD_MEMBERS,
    ],
    'Role Management' => [
        PERM_VIEW_ROLES,
        PERM_ADD_ROLE,
        PERM_EDIT_ROLE,
        PERM_DELETE_ROLE,
        PERM_ASSIGN_ROLES,
        PERM_MANAGE_ROLES,  // Add management permission
    ],
    'Assembly Management' => [
        PERM_VIEW_ASSEMBLIES,
        PERM_ADD_ASSEMBLY,
        PERM_EDIT_ASSEMBLY,
        PERM_DELETE_ASSEMBLY,
        PERM_MANAGE_ASSEMBLY,  // Add management permission
    ],
    'Zone Management' => [
        PERM_VIEW_ZONES,
        PERM_ADD_ZONE,
        PERM_EDIT_ZONE,
        PERM_DELETE_ZONE,
        PERM_MANAGE_ZONES,  // Add management permission
    ],
    'Finance Management' => [
        PERM_VIEW_FINANCES,
        PERM_ADD_TRANSACTION,
        PERM_EDIT_TRANSACTION,
        PERM_DELETE_TRANSACTION,
        PERM_GENERATE_FINANCIAL_REPORTS,
        PERM_MANAGE_FINANCE,  // Add management permission
    ],
    'System Administration' => [
        PERM_MANAGE_SYSTEM_SETTINGS,
        PERM_VIEW_AUDIT_LOGS,
        PERM_MANAGE_PERMISSIONS,
        PERM_BACKUP_DATABASE,
        PERM_RESTORE_DATABASE,
    ],
];

/**
 * Helper function to get display name for a permission
 * 
 * @param string $permissionName The permission name from the database
 * @return string Human-readable display name
 */
function getPermissionDisplayName($permissionName) {
    global $PERMISSION_DISPLAY_NAMES;
    return $PERMISSION_DISPLAY_NAMES[$permissionName] ?? ucwords(str_replace('_', ' ', $permissionName));
}

/**
 * Helper function to map a form input value to database permission name
 * 
 * @param string $formValue The value from the form
 * @return string Database permission name
 */
function mapFormValueToPermission($formValue) {
    global $PERMISSION_MAPPING;
    return $PERMISSION_MAPPING[$formValue] ?? $formValue;
}

/**
 * Helper function to map all form permission values to database permission names
 * 
 * @param array $formValues Array of permission values from form
 * @return array Mapped permission names
 */
function mapAllFormPermissions($formValues) {
    $mappedPermissions = [];
    foreach ($formValues as $permission) {
        $mappedPermissions[] = mapFormValueToPermission($permission);
    }
    return $mappedPermissions;
}

/**
 * Helper function to get all permission constants
 * 
 * @return array Array of all permission constants with name => value pairs
 */
function getAllPermissionConstants() {
    $reflectionClass = new ReflectionClass('\\');
    $constants = $reflectionClass->getConstants();
    
    $permissionConstants = [];
    foreach ($constants as $name => $value) {
        if (strpos($name, 'PERM_') === 0) {
            $permissionConstants[$name] = $value;
        }
    }
    
    return $permissionConstants;
}
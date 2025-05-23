-- SQL script to assign scope permissions to the shepherd role

-- First, identify the role_id for the shepherd role and function_id
SET @shepherd_role_id = (SELECT role_id FROM roles WHERE role_name LIKE '%Shepherd%' LIMIT 1);
SET @shepherd_function_id = (SELECT function_id FROM church_functions WHERE function_name LIKE '%Shepherd%' LIMIT 1);

-- Get the permission IDs for scope permissions
SET @view_scopes_id = (SELECT permission_id FROM permissions WHERE permission_name = 'view_scopes');
SET @add_scope_id = (SELECT permission_id FROM permissions WHERE permission_name = 'add_scope');
SET @edit_scope_id = (SELECT permission_id FROM permissions WHERE permission_name = 'edit_scope');
SET @delete_scope_id = (SELECT permission_id FROM permissions WHERE permission_name = 'delete_scope');
SET @manage_scopes_id = (SELECT permission_id FROM permissions WHERE permission_name = 'manage_scopes');

-- Insert permissions for the shepherd role (if role and permissions exist)
-- Using INSERT IGNORE to avoid duplicate entries
INSERT IGNORE INTO role_church_function_permissions (permission_id, role_id, function_id, is_active) 
SELECT @view_scopes_id, @shepherd_role_id, @shepherd_function_id, 1 
WHERE @shepherd_role_id IS NOT NULL AND @view_scopes_id IS NOT NULL;

INSERT IGNORE INTO role_church_function_permissions (permission_id, role_id, function_id, is_active) 
SELECT @add_scope_id, @shepherd_role_id, @shepherd_function_id, 1 
WHERE @shepherd_role_id IS NOT NULL AND @add_scope_id IS NOT NULL;

INSERT IGNORE INTO role_church_function_permissions (permission_id, role_id, function_id, is_active) 
SELECT @edit_scope_id, @shepherd_role_id, @shepherd_function_id, 1 
WHERE @shepherd_role_id IS NOT NULL AND @edit_scope_id IS NOT NULL;

INSERT IGNORE INTO role_church_function_permissions (permission_id, role_id, function_id, is_active) 
SELECT @delete_scope_id, @shepherd_role_id, @shepherd_function_id, 1 
WHERE @shepherd_role_id IS NOT NULL AND @delete_scope_id IS NOT NULL;

INSERT IGNORE INTO role_church_function_permissions (permission_id, role_id, function_id, is_active) 
SELECT @manage_scopes_id, @shepherd_role_id, @shepherd_function_id, 1 
WHERE @shepherd_role_id IS NOT NULL AND @manage_scopes_id IS NOT NULL;

-- Show the assigned permissions to verify
SELECT 
    p.permission_name, 
    r.role_name,
    cf.function_name,
    rp.is_active
FROM role_church_function_permissions rp
JOIN permissions p ON rp.permission_id = p.permission_id
JOIN roles r ON rp.role_id = r.role_id
JOIN church_functions cf ON rp.function_id = cf.function_id
WHERE p.permission_name LIKE '%scope%'
AND r.role_id = @shepherd_role_id;
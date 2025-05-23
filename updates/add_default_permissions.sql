-- Insert default permissions for role management
INSERT INTO permissions (permission_name, description) VALUES
('manage_roles', 'Create, edit, and delete roles in the system'),
('manage_permissions', 'Manage permission assignments to roles'),
('assign_roles', 'Assign roles to members'),
('view_roles', 'View role information and assignments')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Insert default permissions for member management
INSERT INTO permissions (permission_name, description) VALUES
('manage_members', 'Full control over member records'),
('view_members', 'View member information'),
('edit_members', 'Edit member details'),
('delete_members', 'Remove members from the system')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Insert default permissions for assembly management
INSERT INTO permissions (permission_name, description) VALUES
('manage_assembly', 'Full control over assembly operations'),
('view_assembly', 'View assembly information'),
('edit_assembly', 'Edit assembly details'),
('delete_assembly', 'Remove assemblies from the system')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Insert default permissions for zone management
INSERT INTO permissions (permission_name, description) VALUES
('manage_zone', 'Full control over zone operations'),
('view_zone', 'View zone information'),
('edit_zone', 'Edit zone details'),
('delete_zone', 'Remove zones from the system')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Insert default permissions for household management
INSERT INTO permissions (permission_name, description) VALUES
('manage_household', 'Full control over household operations'),
('view_household', 'View household information'),
('edit_household', 'Edit household details'),
('delete_household', 'Remove households from the system')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Insert default permissions for finance management
INSERT INTO permissions (permission_name, description) VALUES
('manage_finance', 'Full control over financial operations'),
('view_finance', 'View financial information'),
('edit_finance', 'Edit financial records'),
('delete_finance', 'Remove financial records')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Insert default permissions for report management
INSERT INTO permissions (permission_name, description) VALUES
('manage_reports', 'Full control over report operations'),
('view_reports', 'View system reports'),
('generate_reports', 'Generate new reports'),
('export_reports', 'Export reports from the system')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Insert default permissions for specialized ministries
INSERT INTO permissions (permission_name, description) VALUES
('manage_specialized_ministries', 'Full control over specialized ministries'),
('view_specialized_ministries', 'View specialized ministries information'),
('edit_specialized_ministries', 'Edit specialized ministries details'),
('delete_specialized_ministries', 'Remove specialized ministries')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Create default roles if they don't exist
INSERT INTO roles (role_name, hierarchy_level, description) VALUES
('EXCO', 'National', 'Executive Committee Member with national-level access'),
('Zone Director', 'Zone', 'Director overseeing zone operations'),
('Presiding Elder', 'Assembly', 'Elder responsible for assembly management'),
('Shepherd', 'Household', 'Leader managing household operations')
ON DUPLICATE KEY UPDATE 
    hierarchy_level = VALUES(hierarchy_level),
    description = VALUES(description);

-- Assign default permissions to roles
INSERT INTO role_permissions (role_id, permission_id, is_active, created_by)
SELECT r.role_id, p.permission_id, 1, 'System'
FROM roles r
CROSS JOIN permissions p
WHERE r.role_name = 'EXCO'
AND p.permission_name IN (
    'manage_roles', 'manage_permissions', 'assign_roles', 'view_roles',
    'manage_members', 'manage_assembly', 'manage_zone', 'manage_finance',
    'manage_reports', 'manage_specialized_ministries'
)
ON DUPLICATE KEY UPDATE is_active = 1;
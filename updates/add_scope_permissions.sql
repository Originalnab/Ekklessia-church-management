-- SQL script to add scope management permissions
INSERT INTO `permissions` (`permission_name`, `description`, `created_at`, `updated_at`) VALUES
('view_scopes', 'View scope records', NOW(), NOW()),
('add_scope', 'Add a new scope', NOW(), NOW()),
('edit_scope', 'Edit scope details', NOW(), NOW()),
('delete_scope', 'Delete a scope', NOW(), NOW()),
('manage_scopes', 'Manage scopes and their assignments', NOW(), NOW());
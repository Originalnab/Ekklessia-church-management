-- SQL script to create a new role_permissions table without church_functions dependency
-- This creates a simpler permission model with direct role-to-permission mapping

-- Create the new role_permissions table
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` varchar(50) NOT NULL DEFAULT 'System',
  PRIMARY KEY (`role_id`, `permission_id`),
  KEY `fk_role_permissions_permission_id` (`permission_id`),
  CONSTRAINT `fk_role_permissions_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_role_permissions_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migrate existing permissions from role_church_function_permissions
-- This consolidates permissions by role, removing the function_id dependency
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `is_active`, `created_by`)
SELECT DISTINCT 
    rcfp.role_id,
    rcfp.permission_id,
    rcfp.is_active,
    'Migration'
FROM 
    `role_church_function_permissions` rcfp;

-- Add index to improve query performance
CREATE INDEX `idx_role_permissions_role_id` ON `role_permissions` (`role_id`);
CREATE INDEX `idx_role_permissions_permission_id` ON `role_permissions` (`permission_id`);
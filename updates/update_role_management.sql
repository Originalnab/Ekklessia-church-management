-- Add hierarchy_level to roles table if it doesn't exist
ALTER TABLE roles
ADD COLUMN IF NOT EXISTS hierarchy_level ENUM('National', 'Zone', 'Assembly', 'Household') NOT NULL DEFAULT 'Assembly' AFTER role_name;

-- Add timestamps and audit fields if they don't exist
ALTER TABLE roles
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS created_by VARCHAR(50) NOT NULL DEFAULT 'System',
ADD COLUMN IF NOT EXISTS updated_by VARCHAR(50) NULL;

-- Update the member_role table structure
ALTER TABLE member_role
ADD COLUMN IF NOT EXISTS is_primary TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS assigned_by VARCHAR(50) NOT NULL DEFAULT 'System';

-- Create unique index to prevent duplicate role assignments
ALTER TABLE member_role
ADD UNIQUE KEY IF NOT EXISTS unique_member_role (member_id, role_id);

-- Create role_permissions table if it doesn't exist
CREATE TABLE IF NOT EXISTS role_permissions (
    role_permission_id INT(11) NOT NULL AUTO_INCREMENT,
    role_id INT(11) NOT NULL,
    permission_id INT(11) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(50) NOT NULL DEFAULT 'System',
    updated_by VARCHAR(50) NULL,
    PRIMARY KEY (role_permission_id),
    UNIQUE KEY unique_role_permission (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles (role_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions (permission_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create role_audit_log table for tracking role changes
CREATE TABLE IF NOT EXISTS role_audit_log (
    log_id INT(11) NOT NULL AUTO_INCREMENT,
    role_id INT(11) NOT NULL,
    member_id INT(11) NULL,
    action ENUM('CREATE', 'UPDATE', 'DELETE', 'ASSIGN', 'UNASSIGN') NOT NULL,
    action_details JSON NULL,
    performed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    performed_by VARCHAR(50) NOT NULL,
    PRIMARY KEY (log_id),
    KEY idx_role_audit_role (role_id),
    KEY idx_role_audit_member (member_id),
    CONSTRAINT fk_role_audit_role FOREIGN KEY (role_id) REFERENCES roles (role_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_role_audit_member FOREIGN KEY (member_id) REFERENCES members (member_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
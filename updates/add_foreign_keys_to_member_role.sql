-- SQL script to add foreign key constraints to member_role table
USE ekklessia_db;

-- Add indexes first to improve performance
ALTER TABLE member_role ADD INDEX idx_role_id (role_id);
ALTER TABLE member_role ADD INDEX idx_function_id (function_id);

-- Add foreign key constraints
ALTER TABLE member_role
ADD CONSTRAINT fk_member_role_member_id
FOREIGN KEY (member_id) REFERENCES members(member_id)
ON DELETE CASCADE
ON UPDATE CASCADE;

ALTER TABLE member_role
ADD CONSTRAINT fk_member_role_role_id
FOREIGN KEY (role_id) REFERENCES roles(role_id)
ON DELETE RESTRICT
ON UPDATE CASCADE;

ALTER TABLE member_role
ADD CONSTRAINT fk_member_role_function_id
FOREIGN KEY (function_id) REFERENCES church_functions(function_id)
ON DELETE RESTRICT
ON UPDATE CASCADE;


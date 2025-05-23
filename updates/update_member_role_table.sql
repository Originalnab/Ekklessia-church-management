-- SQL script to update member_role table to remove function_id dependency
-- This script modifies the member_role table to support the new role management system

-- First, add an is_primary field to identify the primary role of a member
-- This replaces the functionality previously handled by local_function_id
ALTER TABLE `member_role` 
ADD COLUMN `is_primary` TINYINT(1) NOT NULL DEFAULT 0 AFTER `function_id`;

-- Add indexes for better performance
CREATE INDEX `idx_member_role_is_primary` ON `member_role` (`is_primary`);
CREATE INDEX `idx_member_role_member_role` ON `member_role` (`member_id`, `role_id`);

-- Set the primary role for each member based on current function assignments
-- This identifies one role per member as primary (the first one found)
UPDATE `member_role` mr1
JOIN (
    SELECT member_id, MIN(member_role_id) as first_role_id
    FROM `member_role`
    GROUP BY member_id
) mr2 ON mr1.member_id = mr2.member_id AND mr1.member_role_id = mr2.first_role_id
SET mr1.is_primary = 1;

-- Create a temporary table to store member-role pairs without function_id
CREATE TABLE `temp_member_role` (
  `member_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` varchar(50) NOT NULL,
  PRIMARY KEY (`member_id`, `role_id`)
);

-- Copy distinct member-role pairs from member_role to the temporary table
-- keeping the is_primary status and latest assignment info
INSERT INTO `temp_member_role` (member_id, role_id, is_primary, assigned_at, assigned_by)
SELECT 
    member_id,
    role_id,
    MAX(is_primary), -- Keep primary status if any role instance is primary
    MAX(assigned_at), -- Use the most recent assignment date
    MAX(assigned_by) -- Use the most recent assigner
FROM 
    `member_role`
GROUP BY 
    member_id, role_id;

-- Note: This script prepares the database for a transition
-- The function_id column is retained for now but not required for new code
-- A complete migration would require:
--   1. Adding new constraint to member_role (member_id, role_id)
--   2. Dropping the function_id foreign key constraint
--   3. Eventually removing the function_id column

-- You can run the following at a later phase when ready to fully drop function_id:
/*
-- Drop the foreign key constraint to church_functions
ALTER TABLE `member_role` DROP FOREIGN KEY `fk_member_role_function_id`;

-- Drop the unique constraint that includes function_id
ALTER TABLE `member_role` DROP INDEX `unique_member_role_function`;

-- Add a new unique constraint without function_id
ALTER TABLE `member_role` ADD UNIQUE KEY `unique_member_role` (`member_id`, `role_id`);

-- Remove the function_id column
ALTER TABLE `member_role` DROP COLUMN `function_id`;
*/
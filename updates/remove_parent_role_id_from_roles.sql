-- Script to remove parent_role_id from roles table
ALTER TABLE roles DROP INDEX fk_roles_parent_role;
ALTER TABLE roles DROP COLUMN parent_role_id;

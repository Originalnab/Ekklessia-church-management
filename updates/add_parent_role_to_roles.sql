ALTER TABLE `roles`
ADD COLUMN `parent_role_id` INT(11) DEFAULT NULL AFTER `role_name`,
ADD CONSTRAINT `fk_roles_parent_role`
FOREIGN KEY (`parent_role_id`) REFERENCES `roles`(`role_id`) ON DELETE SET NULL ON UPDATE CASCADE;

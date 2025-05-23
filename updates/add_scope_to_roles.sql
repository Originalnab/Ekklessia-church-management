ALTER TABLE `roles`
ADD COLUMN `scope` ENUM('Global', 'Local') NOT NULL DEFAULT 'Local' AFTER `role_name`;

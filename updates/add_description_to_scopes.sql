ALTER TABLE `scopes`
ADD COLUMN `description` text DEFAULT NULL,
ADD COLUMN `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
ADD COLUMN `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
ADD COLUMN `created_by` varchar(50) NOT NULL DEFAULT 'Admin',
ADD COLUMN `updated_by` varchar(50) DEFAULT NULL;
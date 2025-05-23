CREATE TABLE `member_roles` (
  `member_role_id` INT(11) NOT NULL AUTO_INCREMENT,
  `member_id` INT(11) NOT NULL,
  `function_id` INT(11) NOT NULL,
  `assigned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`member_role_id`),
  FOREIGN KEY (`member_id`) REFERENCES `members`(`member_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`function_id`) REFERENCES `church_functions`(`function_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

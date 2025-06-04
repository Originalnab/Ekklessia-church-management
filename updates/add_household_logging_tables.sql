-- Create the household_role_history table
CREATE TABLE IF NOT EXISTS household_role_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    household_id INT NOT NULL,
    old_role VARCHAR(50) NOT NULL,
    new_role VARCHAR(50) NOT NULL,
    changed_by VARCHAR(100) NOT NULL,
    changed_at DATETIME NOT NULL,
    INDEX idx_member (member_id),
    INDEX idx_household (household_id),
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    FOREIGN KEY (household_id) REFERENCES households(household_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create the household_assignment_log table
CREATE TABLE IF NOT EXISTS household_assignment_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    household_id INT NOT NULL,
    role VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    performed_by VARCHAR(100) NOT NULL,
    performed_at DATETIME NOT NULL,
    details TEXT,
    INDEX idx_member (member_id),
    INDEX idx_household (household_id),
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    FOREIGN KEY (household_id) REFERENCES households(household_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

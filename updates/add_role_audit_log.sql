
CREATE TABLE role_audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT,
    action_type ENUM('create', 'update', 'delete'),
    changed_by INT,
    change_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    old_values JSON,
    new_values JSON,
    FOREIGN KEY (role_id) REFERENCES roles(role_id),
    FOREIGN KEY (changed_by) REFERENCES users(user_id)
);

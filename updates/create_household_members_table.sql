-- Drop existing tables after migrating data
DROP TABLE IF EXISTS household_shepherdhead_assignments;
DROP TABLE IF EXISTS household_assistant_assignments;
DROP TABLE IF EXISTS member_household;

-- Create new household_members table
CREATE TABLE household_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    household_id INT NOT NULL,
    member_id INT NOT NULL,
    is_leader TINYINT(1) DEFAULT 0,
    is_assistant TINYINT(1) DEFAULT 0,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by VARCHAR(50) NOT NULL,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status TINYINT(1) DEFAULT 1,
    FOREIGN KEY (household_id) REFERENCES households(household_id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    UNIQUE KEY unique_member_household (member_id, household_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migrate existing household heads
INSERT INTO household_members (household_id, member_id, is_leader, assigned_by, assigned_at)
SELECT 
    hsa.household_id,
    hsa.shepherd_member_id,
    1, -- is_leader
    hsa.assigned_by,
    hsa.assigned_at
FROM household_shepherdhead_assignments hsa;

-- Migrate existing household assistants
INSERT INTO household_members (household_id, member_id, is_assistant, assigned_by, assigned_at)
SELECT 
    ha.household_id,
    ha.assistant_member_id,
    1, -- is_assistant
    ha.assigned_by,
    ha.assigned_at
FROM household_assistant_assignments ha;

-- Migrate regular members
INSERT INTO household_members (household_id, member_id, assigned_by, assigned_at)
SELECT 
    mh.household_id,
    mh.member_id,
    mh.assigned_by,
    mh.assigned_at
FROM member_household mh
WHERE mh.member_id NOT IN (
    SELECT shepherd_member_id FROM household_shepherdhead_assignments
    UNION
    SELECT assistant_member_id FROM household_assistant_assignments
);
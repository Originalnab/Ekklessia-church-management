ALTER TABLE member_household 
ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'regular' AFTER household_id,
ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 1 AFTER role,
ADD CONSTRAINT chk_role CHECK (role IN ('leader', 'assistant', 'regular'));

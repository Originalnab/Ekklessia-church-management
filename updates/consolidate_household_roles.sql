-- First update leader assignments from household_shepherdhead_assignments
UPDATE member_household mh
INNER JOIN household_shepherdhead_assignments hsa 
  ON hsa.household_id = mh.household_id 
  AND hsa.shepherd_member_id = mh.member_id
SET mh.role = 'leader' 
WHERE mh.status = 1;

-- Then update assistant assignments from household_assistant_assignments
UPDATE member_household mh
INNER JOIN household_assistant_assignments haa 
  ON haa.household_id = mh.household_id 
  AND haa.assistant_member_id = mh.member_id
SET mh.role = 'assistant' 
WHERE mh.status = 1;

-- After confirming the data is migrated correctly, you can drop the old tables
-- DROP TABLE IF EXISTS household_assistant_assignments;
-- DROP TABLE IF EXISTS household_shepherdhead_assignments;

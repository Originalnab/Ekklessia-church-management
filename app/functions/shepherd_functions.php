<?php
function createShepherd($memberId, $shepherdType, $createdBy) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO shepherds (member_id, shepherd_type, created_by)
            VALUES (?, ?, ?)
        ");
        
        return $stmt->execute([$memberId, $shepherdType, $createdBy]);
    } catch (PDOException $e) {
        error_log("Error creating shepherd: " . $e->getMessage());
        return false;
    }
}

function assignShepherd($shepherdId, $entityId, $entityType, $startDate, $createdBy) {
    global $pdo;
    
    try {
        // First deactivate any existing active assignments for this entity
        $stmt = $pdo->prepare("
            UPDATE shepherd_assignments 
            SET status = 'inactive', end_date = CURRENT_DATE, updated_by = ?
            WHERE entity_id = ? AND entity_type = ? AND status = 'active'
        ");
        $stmt->execute([$createdBy, $entityId, $entityType]);
        
        // Create new assignment
        $stmt = $pdo->prepare("
            INSERT INTO shepherd_assignments 
            (shepherd_id, entity_id, entity_type, start_date, created_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([$shepherdId, $entityId, $entityType, $startDate, $createdBy]);
    } catch (PDOException $e) {
        error_log("Error assigning shepherd: " . $e->getMessage());
        return false;
    }
}

function getShepherdsByType($type, $assembly_id = null) {
    global $pdo;
    
    $query = "
        SELECT s.*, m.first_name, m.last_name, m.contact, m.assemblies_id, a.name as assembly_name
        FROM shepherds s
        JOIN members m ON s.member_id = m.member_id
        LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
        WHERE s.shepherd_type = :type
        ORDER BY m.first_name, m.last_name";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':type', $type);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getShepherdAssignments($shepherdId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT sa.*, 
                   CASE 
                       WHEN sa.entity_type = 'household' THEN h.household_name
                       WHEN sa.entity_type = 'ministry' THEN m.ministry_name
                   END as entity_name
            FROM shepherd_assignments sa
            LEFT JOIN households h ON sa.entity_type = 'household' AND sa.entity_id = h.household_id
            LEFT JOIN ministries m ON sa.entity_type = 'ministry' AND sa.entity_id = m.ministry_id
            WHERE sa.shepherd_id = ? AND sa.status = 'active'
            ORDER BY sa.start_date DESC
        ");
        
        $stmt->execute([$shepherdId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching shepherd assignments: " . $e->getMessage());
        return [];
    }
}

function getAvailableMembers() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT m.member_id, m.first_name, m.last_name
            FROM members m
            LEFT JOIN shepherds s ON m.member_id = s.member_id
            WHERE s.shepherd_id IS NULL
            ORDER BY m.first_name, m.last_name
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching available members: " . $e->getMessage());
        return [];
    }
}

function getUnassignedHouseholds() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT h.household_id, h.household_name
            FROM households h
            LEFT JOIN shepherd_assignments sa ON h.household_id = sa.entity_id 
                AND sa.entity_type = 'household' 
                AND sa.status = 'active'
            WHERE sa.assignment_id IS NULL
            ORDER BY h.household_name
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching unassigned households: " . $e->getMessage());
        return [];
    }
}

function getUnassignedMinistries() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT m.ministry_id, m.ministry_name
            FROM ministries m
            LEFT JOIN shepherd_assignments sa ON m.ministry_id = sa.entity_id 
                AND sa.entity_type = 'ministry' 
                AND sa.status = 'active'
            WHERE sa.assignment_id IS NULL
            ORDER BY m.ministry_name
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching unassigned ministries: " . $e->getMessage());
        return [];
    }
}

function getUnassignedShepherds() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT s.*, m.first_name, m.last_name, m.contact
            FROM shepherds s
            JOIN members m ON s.member_id = m.member_id
            LEFT JOIN shepherd_assignments sa ON s.shepherd_id = sa.shepherd_id 
                AND sa.status = 'active'
            WHERE s.status = 'active' 
            AND sa.assignment_id IS NULL
            ORDER BY m.first_name, m.last_name
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching unassigned shepherds: " . $e->getMessage());
        return [];
    }
}
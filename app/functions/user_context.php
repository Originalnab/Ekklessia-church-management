<?php
/**
 * Get user context information such as zone_id, assembly_id, household_id
 * @param int $member_id The member ID
 * @return array User context containing zone_id, assembly_id, household_id
 */
function getUserContext($member_id) {
    global $pdo;
    
    $context = [
        'zone_id' => null,
        'assembly_id' => null, 
        'household_id' => null
    ];
    
    try {
        // Get member's context information - use member_household table instead of direct column
        $stmt = $pdo->prepare("SELECT mh.household_id, h.assembly_id, a.zone_id 
                               FROM members m 
                               LEFT JOIN member_household mh ON m.member_id = mh.member_id
                               LEFT JOIN households h ON mh.household_id = h.household_id 
                               LEFT JOIN assemblies a ON h.assembly_id = a.assembly_id 
                               WHERE m.member_id = ?");
        $stmt->execute([$member_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $context['household_id'] = $result['household_id'];
            $context['assembly_id'] = $result['assembly_id'];
            $context['zone_id'] = $result['zone_id'];
        }
    } catch (PDOException $e) {
        // Log error
        error_log("Error getting user context: " . $e->getMessage());
    }
    
    return $context;
}
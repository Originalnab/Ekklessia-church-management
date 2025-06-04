<?php
// app/pages/tpd/members/assign_household.php
include "../../../config/db.php";

// Fetch all households with assembly association
try {
    $householdsStmt = $pdo->query("SELECT household_id, h.name AS household_name, assembly_id, a.name AS assembly_name 
                                 FROM households h 
                                 LEFT JOIN assemblies a ON h.assembly_id = a.assembly_id");
    $households = $householdsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching households: " . $e->getMessage();
    exit;
}

// Fetch all members for shepherd assignment (assuming shepherds are members with a specific role or status)
try {
    $shepherdsStmt = $pdo->query("SELECT member_id, first_name, last_name, assemblies_id, a.name AS assembly_name 
                                FROM members m 
                                LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id 
                                WHERE m.status LIKE '%Worker%' OR m.local_function_id IS NOT NULL");
    $shepherds = $shepherdsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching shepherds: " . $e->getMessage();
    exit;
}
?>

<?php foreach ($members as $member): ?>
    <div class="modal fade" id="assignHouseholdModal-<?= $member['member_id'] ?>" tabindex="-1" aria-labelledby="assignHouseholdModalLabel-<?= $member['member_id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="assignHouseholdModalLabel-<?= $member['member_id'] ?>">Assign Household for <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <!-- Member Information Section -->
                        <div class="col-md-5">
                            <div class="card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <img src="<?= $member['profile_photo'] ? '/Ekklessia-church-management/app/resources/assets/images/' . htmlspecialchars($member['profile_photo']) : '/Ekklessia-church-management/app/resources/assets/images/default.jpg' ?>" alt="Profile Photo" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                                    <h5 class="card-title"><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></h5>
                                    <p class="card-text text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($member['contact']) ?></p>
                                    <p class="card-text text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($member['address'] ?? 'N/A') ?></p>
                                    <p class="card-text text-muted"><i class="bi bi-pin-map"></i> <?= htmlspecialchars($member['digital_address'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                        </div>
                        <!-- Assignment Section -->
                        <div class="col-md-7">
                            <form id="assignHouseholdForm-<?= $member['member_id'] ?>" action="assign_household_process.php" method="POST">
                                <input type="hidden" name="member_id" value="<?= $member['member_id'] ?>">
                                <input type="hidden" name="assemblies_id" value="<?= $member['assemblies_id'] ?>">
                                <div class="mb-3">
                                    <label for="assembly-<?= $member['member_id'] ?>" class="form-label">Assembly</label>
                                    <input type="text" class="form-control bg-light" id="assembly-<?= $member['member_id'] ?>" value="<?= htmlspecialchars($member['assembly_name'] ?? 'N/A') ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="household-<?= $member['member_id'] ?>" class="form-label">Select Household <span class="text-danger">*</span></label>
                                    <select class="form-select" id="household-<?= $member['member_id'] ?>" name="household_id" required>
                                        <option value="">Select a Household</option>
                                        <?php foreach ($households as $household): ?>
                                            <?php if ($household['assembly_name'] === $member['assembly_name']): ?>
                                                <option value="<?= $household['household_id'] ?>"><?= htmlspecialchars($household['household_name']) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Select Role(s)</label>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="isLeader-<?= $member['member_id'] ?>" name="is_leader" value="1">
                                        <label class="form-check-label" for="isLeader-<?= $member['member_id'] ?>">Household Leader</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="isAssistant-<?= $member['member_id'] ?>" name="is_assistant" value="1">
                                        <label class="form-check-label" for="isAssistant-<?= $member['member_id'] ?>">Assistant Leader</label>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Assign</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle role checkbox logic for each member's modal
    document.querySelectorAll('[id^="assignHouseholdModal-"]').forEach(modal => {
        const memberId = modal.id.split('-')[1];
        const leaderCheckbox = modal.querySelector(`#isLeader-${memberId}`);
        const assistantCheckbox = modal.querySelector(`#isAssistant-${memberId}`);
        
        // Prevent user from selecting both leader and assistant roles
        leaderCheckbox.addEventListener('change', function() {
            if (this.checked) {
                assistantCheckbox.checked = false;
            }
        });
        
        assistantCheckbox.addEventListener('change', function() {
            if (this.checked) {
                leaderCheckbox.checked = false;
            }
        });

        // Form validation before submission
        modal.querySelector('form').addEventListener('submit', function(e) {
            const householdSelect = this.querySelector('select[name="household_id"]');
            
            if (!householdSelect.value) {
                e.preventDefault();
                alert('Please select a household.');
                return;
            }

            if (!leaderCheckbox.checked && !assistantCheckbox.checked) {
                e.preventDefault();
                alert('Please select at least one role (Leader or Assistant).');
                return;
            }
        });
    });
});
</script>
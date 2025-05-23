<?php
include "../../../config/db.php";

// Fetch assemblies for the dropdown
$assemblies = $pdo->query("SELECT assembly_id, name FROM assemblies")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="modal fade" id="addHouseholdModal" tabindex="-1" aria-labelledby="addHouseholdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addHouseholdModalLabel">Add New Household</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="add_household_process.php" method="POST">
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label for="householdName" class="form-label">Household Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="householdName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="assemblyId" class="form-label">Assembly <span class="text-danger">*</span></label>
                            <select class="form-select" id="assemblyId" name="assembly_id" required>
                                <option value="">Select Assembly</option>
                                <?php foreach ($assemblies as $assembly): ?>
                                    <option value="<?= $assembly['assembly_id'] ?>"><?= htmlspecialchars($assembly['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="col-md-6">
                            <label for="digitalAddress" class="form-label">Digital Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="digitalAddress" name="digital_address" placeholder="e.g., GH-123-456" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <div class="form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1" checked>
                                <label class="form-check-label" for="status" id="statusLabel">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="nearestLandmark" class="form-label">Nearest Landmark</label>
                            <input type="text" class="form-control" id="nearestLandmark" name="nearest_landmark" placeholder="e.g., Central Market">
                        </div>
                        <div class="col-md-6">
                            <label for="dateStarted" class="form-label">Date Started</label>
                            <input type="date" class="form-control" id="dateStarted" name="date_started">
                        </div>
                        <div class="col-md-6">
                            <label for="createdBy" class="form-label">Created By <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="createdBy" name="created_by" value="Admin" required readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="updatedBy" class="form-label">Updated By</label>
                            <input type="text" class="form-control" id="updatedBy" name="updated_by" value="Admin" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Household</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Update the status label when the toggle is changed
document.getElementById('status').addEventListener('change', function() {
    const label = document.getElementById('statusLabel');
    label.textContent = this.checked ? 'Active' : 'Inactive';
});
</script>
<?php
include "../../../config/db.php";

// Fetch zones for the dropdown
$zones = $pdo->query("SELECT zone_id, name FROM zones")->fetchAll(PDO::FETCH_ASSOC);

// List of 16 regions in Ghana
$regions = [
    'Ahafo',
    'Ashanti',
    'Bono',
    'Bono East',
    'Central',
    'Eastern',
    'Greater Accra',
    'North East',
    'Northern',
    'Oti',
    'Savannah',
    'Upper East',
    'Upper West',
    'Volta',
    'Western',
    'Western North'
];
?>

<div class="modal fade" id="addAssemblyModal" tabindex="-1" aria-labelledby="addAssemblyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addAssemblyModalLabel">Add New Assembly</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="add_assembly_process.php" method="POST">
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label for="assemblyName" class="form-label">Assembly Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="assemblyName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="region" class="form-label">Region <span class="text-danger">*</span></label>
                            <select class="form-select" id="region" name="region" required>
                                <option value="">Select Region</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= htmlspecialchars($region) ?>"><?= htmlspecialchars($region) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="cityTown" class="form-label">City/Town <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cityTown" name="city_town" required>
                        </div>
                        <div class="col-md-6">
                            <label for="digitalAddress" class="form-label">Digital Address</label>
                            <input type="text" class="form-control" id="digitalAddress" name="digital_address" placeholder="e.g., GH-123-456">
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
                            <label for="status" class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="status" name="status" value="1" checked>
                                <label class="form-check-label" for="status" id="statusLabel">Yes</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="zoneId" class="form-label">Zone <span class="text-danger">*</span></label>
                            <select class="form-select" id="zoneId" name="zone_id" required>
                                <option value="">Select Zone</option>
                                <?php foreach ($zones as $zone): ?>
                                    <option value="<?= $zone['zone_id'] ?>"><?= htmlspecialchars($zone['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="createdBy" class="form-label">Created By <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="createdBy" name="created_by" value="Admin" required readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Assembly</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
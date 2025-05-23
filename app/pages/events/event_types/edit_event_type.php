<?php
include "../../../config/db.php";

$event_type_id = $_GET['event_type_id'] ?? 0;

try {
    $stmt = $pdo->prepare("
        SELECT event_type_id, name, description, default_frequency, level, is_recurring
        FROM event_types
        WHERE event_type_id = ?
    ");
    $stmt->execute([$event_type_id]);
    $event_type = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event_type) {
        echo "Event type not found";
        exit;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<div class="modal fade" id="editEventTypeModal-<?= $event_type['event_type_id'] ?>" tabindex="-1" aria-labelledby="editEventTypeModalLabel-<?= $event_type['event_type_id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editEventTypeModalLabel-<?= $event_type['event_type_id'] ?>">Edit Event Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEventTypeForm-<?= $event_type['event_type_id'] ?>">
                    <input type="hidden" name="event_type_id" value="<?= $event_type['event_type_id'] ?>">
                    <div class="mb-3">
                        <label for="editEventTypeName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editEventTypeName" name="name" value="<?= htmlspecialchars($event_type['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEventTypeDescription" class="form-label">Description</label>
                        <textarea class нат="form-control" id="editEventTypeDescription" name="description" rows="3"><?= htmlspecialchars($event_type['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editDefaultFrequency" class="form-label">Default Frequency</label>
                        <select class="form-select" id="editDefaultFrequency" name="default_frequency">
                            <option value="">-- Select Frequency --</option>
                            <option value="daily" <?= $event_type['default_frequency'] === 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= $event_type['default_frequency'] === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="monthly" <?= $event_type['default_frequency'] === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="yearly" <?= $event_type['default_frequency'] === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editEventTypeLevel" class="form-label">Level <span class="text-danger">*</span></label>
                        <select class="form-select" id="editEventTypeLevel" name="level" required>
                            <option value="">-- Select Level --</option>
                            <option value="household" <?= $event_type['level'] === 'household' ? 'selected' : '' ?>>Household</option>
                            <option value="assembly" <?= $event_type['level'] === 'assembly' ? 'selected' : '' ?>>Assembly</option>
                            <option value="zone" <?= $event_type['level'] === 'zone' ? 'selected' : '' ?>>Zone</option>
                            <option value="national" <?= $event_type['level'] === 'national' ? 'selected' : '' ?>>National</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editIsRecurring" name="is_recurring" <?= $event_type['is_recurring'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="editIsRecurring">Recurring Event Type</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Event Type</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('editEventTypeForm-<?= $event_type['event_type_id'] ?>').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('update_event_type_process.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Event type updated successfully');
                bootstrap.Modal.getInstance(document.getElementById('editEventTypeModal-<?= $event_type['event_type_id'] ?>')).hide();
                fetchPaginatedEventTypes(currentPage);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => alert('An error occurred while updating the event type.'));
});
</script>
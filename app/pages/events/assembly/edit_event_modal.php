<!-- Edit Event Modal for Assembly Events -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEventModalLabel">Edit Assembly Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEventForm">
                    <input type="hidden" id="editEventId" name="eventId">
                    <div class="mb-3">
                        <label for="editAssemblySelect" class="form-label">Assembly <span class="text-danger">*</span></label>
                        <select class="form-select" id="editAssemblySelect" name="assembly_id" required>
                            <option value="">Select Assembly</option>
                            <?php
                            // Fetch all assemblies for the dropdown
                            try {
                                $stmt = $pdo->query("SELECT assembly_id, name FROM assemblies WHERE status = 1 ORDER BY name ASC");
                                $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            } catch (PDOException $e) {
                                $assemblies = [];
                            }
                            foreach ($assemblies as $assembly): ?>
                                <option value="<?= $assembly['assembly_id'] ?>"><?= htmlspecialchars($assembly['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editEventName" class="form-label">Event Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editEventName" name="eventName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEventType" class="form-label">Event Type</label>
                        <select class="form-select" id="editEventType" name="eventType" required>
                            <option value="">Select Event Type</option>
                            <?php foreach ($event_types as $event_type): ?>
                                <option value="<?= $event_type['event_type_id'] ?>"><?= htmlspecialchars($event_type['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editStartDate" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="editStartDate" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEndDate" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="editEndDate" name="end_date" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="editIsRecurring" name="isRecurring">
                        <label class="form-check-label" for="editIsRecurring">Is this a recurring event?</label>
                    </div>
                    <div class="mb-3" id="editRecurrenceOptions" style="display: none;">
                        <label for="editRecurrenceFrequency" class="form-label">Recurrence Frequency</label>
                        <select class="form-select" id="editRecurrenceFrequency" name="recurrenceFrequency">
                            <option value="">Select Frequency</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var recurringCheckbox = document.getElementById('editIsRecurring');
    var recurrenceOptions = document.getElementById('editRecurrenceOptions');
    if (recurringCheckbox && recurrenceOptions) {
        recurringCheckbox.addEventListener('change', function () {
            recurrenceOptions.style.display = this.checked ? '' : 'none';
        });
    }
});
</script>

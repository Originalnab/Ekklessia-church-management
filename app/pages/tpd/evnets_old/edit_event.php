<?php
session_start();
include "../../../config/db.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch event types
try {
    $stmt = $pdo->query("SELECT event_type_id, name, is_recurring, default_frequency, level FROM event_types ORDER BY name ASC");
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching event types: " . $e->getMessage();
    exit;
}

$event_id = $_GET['event_id'] ?? 0;
$current_page = $_GET['current_page'] ?? 1;

try {
    $stmt = $pdo->prepare("
        SELECT event_id, event_type_id, title, description, start_date, end_date, location, is_recurring, frequency, recurrence_day, level, household_id, assembly_id, zone_id
        FROM events
        WHERE event_id = ?
    ");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo "Event not found";
        exit;
    }

    $stmt = $pdo->prepare("SELECT is_recurring, default_frequency FROM event_types WHERE event_type_id = ?");
    $stmt->execute([$event['event_type_id']]);
    $event_type = $stmt->fetch(PDO::FETCH_ASSOC);
    $is_event_type_recurring = $event_type['is_recurring'];

    $selected_levels = [];
    $selected_locations = [];

    if ($event['household_id']) {
        $selected_levels[] = 'household';
        $selected_locations['household_ids'] = [$event['household_id']];
    }
    if ($event['assembly_id']) {
        $selected_levels[] = 'assembly';
        $selected_locations['assembly_ids'] = [$event['assembly_id']];
    }
    if ($event['zone_id']) {
        $selected_levels[] = 'zone';
        $selected_locations['zone_ids'] = [$event['zone_id']];
    }
    if (!$event['household_id'] && !$event['assembly_id'] && !$event['zone_id']) {
        $selected_levels[] = 'national';
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<div class="modal fade" id="editEventModal-<?= $event['event_id'] ?>" tabindex="-1" aria-labelledby="editEventModalLabel-<?= $event['event_id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editEventModalLabel-<?= $event['event_id'] ?>">Edit Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEventForm-<?= $event['event_id'] ?>" method="POST">
                    <input type="hidden" id="eventId-<?= $event['event_id'] ?>" name="event_id" value="<?= $event['event_id'] ?>">
                    <div class="mb-3">
                        <label for="editEventTitle-<?= $event['event_id'] ?>" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editEventTitle-<?= $event['event_id'] ?>" name="title" value="<?= htmlspecialchars($event['title']) ?>" autocomplete="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEventType-<?= $event['event_id'] ?>" class="form-label">Event Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="editEventType-<?= $event['event_id'] ?>" name="event_type_id" required>
                            <option value="">-- Select Event Type --</option>
                            <?php foreach ($event_types as $type): ?>
                                <option value="<?= $type['event_type_id'] ?>" 
                                        data-is-recurring="<?= $type['is_recurring'] ?>" 
                                        data-level="<?= $type['level'] ?>" 
                                        data-frequency="<?= $type['default_frequency'] ?>" 
                                        <?= $event['event_type_id'] == $type['event_type_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editEventDescription-<?= $event['event_id'] ?>" class="form-label">Description</label>
                        <textarea class="form-control" id="editEventDescription-<?= $event['event_id'] ?>" name="description" rows="3" autocomplete="on"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <fieldset>
                            <legend class="form-label">Level <span class="text-danger">*</span></legend>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input edit-level-checkbox" type="checkbox" name="levels[]" value="household" id="editLevelHousehold-<?= $event['event_id'] ?>" <?= in_array('household', $selected_levels) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="editLevelHousehold-<?= $event['event_id'] ?>">Household</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input edit-level-checkbox" type="checkbox" name="levels[]" value="assembly" id="editLevelAssembly-<?= $event['event_id'] ?>" <?= in_array('assembly', $selected_levels) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="editLevelAssembly-<?= $event['event_id'] ?>">Assembly</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input edit-level-checkbox" type="checkbox" name="levels[]" value="zone" id="editLevelZone-<?= $event['event_id'] ?>" <?= in_array('zone', $selected_levels) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="editLevelZone-<?= $event['event_id'] ?>">Zone</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input edit-level-checkbox" type="checkbox" name="levels[]" value="national" id="editLevelNational-<?= $event['event_id'] ?>" <?= in_array('national', $selected_levels) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="editLevelNational-<?= $event['event_id'] ?>">National</label>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="mb-3" id="editLocationSelect-<?= $event['event_id'] ?>" style="display: none;">
                        <fieldset>
                            <legend class="form-label">Locations <span class="text-danger">*</span></legend>
                            <div id="editLocationCheckboxes-<?= $event['event_id'] ?>" class="d-flex flex-wrap gap-3"></div>
                        </fieldset>
                    </div>
                    <div class="mb-3">
                        <label for="editEventLocation-<?= $event['event_id'] ?>" class="form-label">Venue</label>
                        <input type="text" class="form-control" id="editEventLocation-<?= $event['event_id'] ?>" name="location" value="<?= htmlspecialchars($event['location'] ?? '') ?>" autocomplete="street-address">
                    </div>
                    <div class="mb-3">
                        <label for="editStartDate-<?= $event['event_id'] ?>" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="editStartDate-<?= $event['event_id'] ?>" name="start_date" value="<?= $event['start_date'] ? date('Y-m-d\TH:i', strtotime($event['start_date'])) : '' ?>" autocomplete="off" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEndDate-<?= $event['event_id'] ?>" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="editEndDate-<?= $event['event_id'] ?>" name="end_date" value="<?= $event['end_date'] ? date('Y-m-d\TH:i', strtotime($event['end_date'])) : '' ?>" autocomplete="off" required>
                    </div>
                    <div id="editRecurringFields-<?= $event['event_id'] ?>" style="display: <?= $event['is_recurring'] ? 'block' : 'none' ?>;">
                        <div class="mb-3">
                            <label for="editEventFrequency-<?= $event['event_id'] ?>" class="form-label">Frequency</label>
                            <select class="form-select" id="editEventFrequency-<?= $event['event_id'] ?>" name="frequency">
                                <option value="daily" <?= $event['frequency'] === 'daily' ? 'selected' : '' ?>>Daily</option>
                                <option value="weekly" <?= $event['frequency'] === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                                <option value="monthly" <?= $event['frequency'] === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                <option value="quarterly" <?= $event['frequency'] === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                <option value="yearly" <?= $event['frequency'] === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                            </select>
                        </div>
                        <div class="mb-3" id="editRecurrenceDayField-<?= $event['event_id'] ?>" style="display: <?= ($event['frequency'] === 'weekly' || $event['frequency'] === 'monthly') ? 'block' : 'none' ?>;">
                            <label for="editRecurrenceDay-<?= $event['event_id'] ?>" class="form-label">Recurrence Day</label>
                            <select class="form-select" id="editRecurrenceDay-<?= $event['event_id'] ?>" name="recurrence_day" <?= ($event['frequency'] === 'weekly' || $event['frequency'] === 'monthly') ? 'required' : '' ?>>
                                <option value="">-- Select Day --</option>
                                <option value="Sunday" <?= $event['recurrence_day'] === 'Sunday' ? 'selected' : '' ?>>Sunday</option>
                                <option value="Monday" <?= $event['recurrence_day'] === 'Monday' ? 'selected' : '' ?>>Monday</option>
                                <option value="Tuesday" <?= $event['recurrence_day'] === 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                                <option value="Wednesday" <?= $event['recurrence_day'] === 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                                <option value="Thursday" <?= $event['recurrence_day'] === 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                                <option value="Friday" <?= $event['recurrence_day'] === 'Friday' ? 'selected' : '' ?>>Friday</option>
                                <option value="Saturday" <?= $event['recurrence_day'] === 'Saturday' ? 'selected' : '' ?>>Saturday</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Event</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventId = <?= $event['event_id'] ?>;
    const form = document.getElementById(`editEventForm-${eventId}`);
    const eventTypeSelect = document.getElementById(`editEventType-${eventId}`);
    const recurringFieldsDiv = document.getElementById(`editRecurringFields-${eventId}`);
    const frequencySelect = document.getElementById(`editEventFrequency-${eventId}`);
    const recurrenceDayField = document.getElementById(`editRecurrenceDayField-${eventId}`);
    const recurrenceDaySelect = document.getElementById(`editRecurrenceDay-${eventId}`);
    const levelCheckboxes = document.querySelectorAll(`#editEventForm-${eventId} .edit-level-checkbox`);
    const locationSelectDiv = document.getElementById(`editLocationSelect-${eventId}`);
    const locationCheckboxesDiv = document.getElementById(`editLocationCheckboxes-${eventId}`);
    const currentPage = <?= $current_page ?>;
    const editModalElement = document.getElementById(`editEventModal-${eventId}`);

    function showAlert(type, message, containerId = null) {
        const alertContainer = containerId ? document.getElementById(containerId) : document.body;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1060;">
                <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        alertContainer.appendChild(wrapper);
        setTimeout(() => {
            const alert = wrapper.querySelector('.alert');
            if (alert) bootstrap.Alert.getInstance(alert)?.close();
        }, 5000);
    }

    async function fetchLocations(selectedLevels) {
        if (selectedLevels.length === 0 || selectedLevels.includes('national')) {
            locationSelectDiv.style.display = 'none';
            return;
        }

        locationSelectDiv.style.display = 'block';
        locationCheckboxesDiv.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';

        const fetchPromises = [];
        if (selectedLevels.includes('household')) fetchPromises.push(fetch('../fetch_households.php').then(res => res.json()));
        else fetchPromises.push(Promise.resolve([]));
        if (selectedLevels.includes('assembly')) fetchPromises.push(fetch('../fetch_assemblies.php').then(res => res.json()));
        else fetchPromises.push(Promise.resolve([]));
        if (selectedLevels.includes('zone')) fetchPromises.push(fetch('../fetch_zones.php').then(res => res.json()));
        else fetchPromises.push(Promise.resolve([]));

        try {
            const [households, assemblies, zones] = await Promise.all(fetchPromises);
            locationCheckboxesDiv.innerHTML = '';
            const preSelectedHouseholdIds = <?= json_encode($selected_locations['household_ids'] ?? []) ?>;
            const preSelectedAssemblyIds = <?= json_encode($selected_locations['assembly_ids'] ?? []) ?>;
            const preSelectedZoneIds = <?= json_encode($selected_locations['zone_ids'] ?? []) ?>;

            households.forEach(h => {
                const isChecked = preSelectedHouseholdIds.includes(h.household_id.toString());
                locationCheckboxesDiv.innerHTML += `
                    <div class="form-check">
                        <input class="form-check-input edit-location-checkbox" type="checkbox" name="household_ids[]" value="${h.household_id}" id="editHousehold-${h.household_id}-${eventId}" ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label" for="editHousehold-${h.household_id}-${eventId}">${h.name}</label>
                    </div>`;
            });
            assemblies.forEach(a => {
                const isChecked = preSelectedAssemblyIds.includes(a.assembly_id.toString());
                locationCheckboxesDiv.innerHTML += `
                    <div class="form-check">
                        <input class="form-check-input edit-location-checkbox" type="checkbox" name="assembly_ids[]" value="${a.assembly_id}" id="editAssembly-${a.assembly_id}-${eventId}" ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label" for="editAssembly-${a.assembly_id}-${eventId}">${a.name}</label>
                    </div>`;
            });
            zones.forEach(z => {
                const isChecked = preSelectedZoneIds.includes(z.zone_id.toString());
                locationCheckboxesDiv.innerHTML += `
                    <div class="form-check">
                        <input class="form-check-input edit-location-checkbox" type="checkbox" name="zone_ids[]" value="${z.zone_id}" id="editZone-${z.zone_id}-${eventId}" ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label" for="editZone-${z.zone_id}-${eventId}">${z.name}</label>
                    </div>`;
            });
        } catch (error) {
            locationCheckboxesDiv.innerHTML = '<span class="text-danger">Error loading locations.</span>';
            showAlert('danger', 'Error fetching locations: ' + error.message);
        }
    }

    function handleEventTypeChange() {
        const selectedOption = eventTypeSelect.options[eventTypeSelect.selectedIndex];
        const isRecurring = selectedOption.getAttribute('data-is-recurring') === '1';
        recurringFieldsDiv.style.display = isRecurring ? 'block' : 'none';
        if (isRecurring) {
            frequencySelect.value = selectedOption.getAttribute('data-frequency') || '';
            handleFrequencyChange();
        } else {
            frequencySelect.value = '';
            recurrenceDaySelect.value = '';
            recurrenceDayField.style.display = 'none';
            recurrenceDaySelect.required = false;
        }
    }

    function handleFrequencyChange() {
        const frequency = frequencySelect.value;
        const showDayField = frequency === 'weekly' || frequency === 'monthly';
        recurrenceDayField.style.display = showDayField ? 'block' : 'none';
        recurrenceDaySelect.required = showDayField;
    }

    function handleLevelChange() {
        const selectedLevels = Array.from(levelCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
        const nationalCheckbox = document.getElementById(`editLevelNational-${eventId}`);
        if (nationalCheckbox.checked) {
            levelCheckboxes.forEach(cb => {
                if (cb.value !== 'national') cb.checked = false, cb.disabled = true;
            });
            locationSelectDiv.style.display = 'none';
            locationCheckboxesDiv.innerHTML = '';
        } else {
            levelCheckboxes.forEach(cb => cb.disabled = false);
            const nonNationalSelected = selectedLevels.filter(level => level !== 'national');
            if (nonNationalSelected.length > 0) fetchLocations(nonNationalSelected);
            else {
                locationSelectDiv.style.display = 'none';
                locationCheckboxesDiv.innerHTML = '';
            }
        }
    }

    // Event listeners
    eventTypeSelect.addEventListener('change', handleEventTypeChange);
    frequencySelect.addEventListener('change', handleFrequencyChange);
    levelCheckboxes.forEach(checkbox => checkbox.addEventListener('change', handleLevelChange));

    // Form submission handler
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate form
        if (!this.checkValidity()) {
            showAlert('danger', 'Please fill out all required fields.', `editEventModal-${eventId}`);
            this.reportValidity();
            return;
        }

        const selectedLevels = Array.from(levelCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
        if (selectedLevels.length === 0) {
            showAlert('danger', 'Please select at least one level.', `editEventModal-${eventId}`);
            return;
        }

        const nonNationalLevels = selectedLevels.filter(level => level !== 'national');
        if (nonNationalLevels.length > 0) {
            const selectedLocations = Array.from(locationCheckboxesDiv.querySelectorAll('.edit-location-checkbox:checked')).map(cb => cb.value);
            if (selectedLocations.length === 0) {
                showAlert('danger', 'Please select at least one location.', `editEventModal-${eventId}`);
                return;
            }
        }

        const startDate = new Date(document.getElementById(`editStartDate-${eventId}`).value);
        const endDate = new Date(document.getElementById(`editEndDate-${eventId}`).value);
        if (endDate <= startDate) {
            showAlert('danger', 'End date must be after start date.', `editEventModal-${eventId}`);
            return;
        }

        const selectedOption = eventTypeSelect.options[eventTypeSelect.selectedIndex];
        const isRecurring = selectedOption.getAttribute('data-is-recurring') === '1';
        if (isRecurring) {
            const frequency = frequencySelect.value;
            if (!frequency) {
                showAlert('danger', 'Frequency is required for recurring events.', `editEventModal-${eventId}`);
                return;
            }
            if ((frequency === 'weekly' || frequency === 'monthly') && !recurrenceDaySelect.value) {
                showAlert('danger', 'Recurrence day is required.', `editEventModal-${eventId}`);
                return;
            }
        }

        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';

        try {
            const formData = new FormData(this);
            
            // Log form data for debugging
            console.log('Submitting form data:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            const response = await fetch('update_event_process.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                showAlert('success', data.message || 'Event updated successfully!');
                
                // Close modal and refresh events
                const modal = bootstrap.Modal.getInstance(editModalElement);
                modal.hide();
                
                // Dispatch event to refresh the page
                window.dispatchEvent(new CustomEvent('eventUpdated', { 
                    detail: { 
                        currentPage,
                        message: data.message || 'Event updated successfully!'
                    } 
                }));
                
                // Force page reload if needed
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert('danger', data.message || 'Failed to update event.');
            }
        } catch (error) {
            console.error('Update error:', error);
            showAlert('danger', 'Error updating event: ' + error.message);
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    });

    editModalElement.addEventListener('hidden.bs.modal', () => {
        editModalElement.remove();
    });

    // Initialize form state
    handleEventTypeChange();
    handleLevelChange();
});
</script>
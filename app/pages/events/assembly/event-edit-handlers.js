/**
 * Fixed version of event-edit-handlers.js
 * This script handles edit functionality for assembly events
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('DEBUG: event-edit-fixed.js loaded');

    // Define global variable to make it available across functions
    window.loadEventData = function (eventId) {
        console.log('DEBUG: loadEventData called with ID:', eventId);

        // Get the modal element
        const editModal = document.getElementById('editEventModal');
        if (!editModal) {
            console.error('ERROR: editEventModal element not found');
            alert('Error: Edit modal not found! Please check if the modal exists in index.php');
            return;
        }

        // Create Bootstrap modal instance if not already created
        let modalInstance = bootstrap.Modal.getInstance(editModal);
        if (!modalInstance) {
            modalInstance = new bootstrap.Modal(editModal);
        }

        // Show loading indicator
        const modalBody = editModal.querySelector('.modal-body');
        modalBody.innerHTML = '<div class="text-center my-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading event data...</p></div>';

        // Show the modal
        modalInstance.show();

        // Fetch event data from server
        fetch('get_event_by_id.php?event_id=' + eventId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Received event data:', data);

                if (!data.success) {
                    throw new Error(data.message || 'Failed to load event data');
                }

                // Create a new form with the event data
                const event = data.event;

                // Build the edit form HTML
                const formHtml = `
                <form id="editEventForm">
                    <input type="hidden" id="editEventId" name="eventId" value="${event.event_id}">
                    <div class="mb-3">
                        <label for="editAssemblySelect" class="form-label">Assembly <span class="text-danger">*</span></label>
                        <select class="form-select" id="editAssemblySelect" name="assembly_id" required>
                            <option value="">Select Assembly</option>
                            <!-- Assembly options will be populated by JavaScript -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editEventName" class="form-label">Event Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editEventName" name="eventName" value="${event.event_name || ''}" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEventType" class="form-label">Event Type</label>
                        <select class="form-select" id="editEventType" name="eventType" required>
                            <option value="">Select Event Type</option>
                            <!-- Event type options will be populated by JavaScript -->
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
                        <input type="checkbox" class="form-check-input" id="editIsRecurring" name="isRecurring" ${event.is_recurring == 1 ? 'checked' : ''}>
                        <label class="form-check-label" for="editIsRecurring">Is this a recurring event?</label>
                    </div>
                    <div class="mb-3" id="editRecurrenceOptions" style="display: ${event.is_recurring == 1 ? 'block' : 'none'};">
                        <label for="editRecurrenceFrequency" class="form-label">Recurrence Frequency</label>
                        <select class="form-select" id="editRecurrenceFrequency" name="recurrenceFrequency">
                            <option value="">Select Frequency</option>
                            <option value="daily" ${event.recurrence_frequency === 'daily' ? 'selected' : ''}>Daily</option>
                            <option value="weekly" ${event.recurrence_frequency === 'weekly' ? 'selected' : ''}>Weekly</option>
                            <option value="monthly" ${event.recurrence_frequency === 'monthly' ? 'selected' : ''}>Monthly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Event
                        </button>
                    </div>
                </form>
                `;

                // Update the modal body
                modalBody.innerHTML = formHtml;

                // Get form elements
                const editForm = document.getElementById('editEventForm');
                const editEventType = document.getElementById('editEventType');
                const editAssemblySelect = document.getElementById('editAssemblySelect');
                const editStartDate = document.getElementById('editStartDate');
                const editEndDate = document.getElementById('editEndDate');
                const editIsRecurring = document.getElementById('editIsRecurring');
                const editRecurrenceOptions = document.getElementById('editRecurrenceOptions');

                // Format dates for datetime-local input
                if (editStartDate) {
                    editStartDate.value = formatDateForInput(event.start_date);
                }

                if (editEndDate) {
                    editEndDate.value = formatDateForInput(event.end_date);
                }

                // Fetch assemblies and populate the dropdown
                fetch('get_assemblies.php')
                    .then(response => response.json())
                    .then(assemblyData => {
                        if (assemblyData.success && assemblyData.assemblies) {
                            // Clear existing options and add assemblies
                            editAssemblySelect.innerHTML = '<option value="">Select Assembly</option>';
                            assemblyData.assemblies.forEach(assembly => {
                                const option = document.createElement('option');
                                option.value = assembly.assembly_id;
                                option.textContent = assembly.name;
                                option.selected = assembly.assembly_id == event.assembly_id;
                                editAssemblySelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching assemblies:', error);
                        // Populate with the current assembly as a fallback
                        const option = document.createElement('option');
                        option.value = event.assembly_id;
                        option.textContent = event.assembly_name;
                        option.selected = true;
                        editAssemblySelect.appendChild(option);
                    });

                // Fetch event types and populate the dropdown
                fetch('get_event_types.php')
                    .then(response => response.json())
                    .then(typeData => {
                        if (typeData.success && typeData.event_types) {
                            // Clear existing options and add event types
                            editEventType.innerHTML = '<option value="">Select Event Type</option>';
                            typeData.event_types.forEach(type => {
                                const option = document.createElement('option');
                                option.value = type.event_type_id;
                                option.textContent = type.name;
                                option.selected = type.event_type_id == event.event_type_id;
                                editEventType.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching event types:', error);
                        // Populate with the current event type as a fallback
                        const option = document.createElement('option');
                        option.value = event.event_type_id;
                        option.textContent = event.event_type_name;
                        option.selected = true;
                        editEventType.appendChild(option);
                    });

                // Add recurring checkbox event listener
                if (editIsRecurring && editRecurrenceOptions) {
                    editIsRecurring.addEventListener('change', function () {
                        editRecurrenceOptions.style.display = this.checked ? 'block' : 'none';
                    });
                }

                // Add form submit handler
                if (editForm) {
                    editForm.addEventListener('submit', handleEditFormSubmit);
                }
            })
            .catch(error => {
                console.error('Error fetching event data:', error);

                // Show error in modal
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Error loading event: ${error.message}
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                `;
            });
    };

    // Function to format date string for datetime-local input
    function formatDateForInput(dateStr) {
        if (!dateStr) return '';

        try {
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return '';

            // Format YYYY-MM-DDThh:mm
            return date.toISOString().slice(0, 16);
        } catch (e) {
            console.error('Error formatting date for input:', e);
            return '';
        }
    }

    // Function to handle form submission
    function handleEditFormSubmit(e) {
        e.preventDefault();

        const form = e.target;

        // Basic form validation
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        // Show processing state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnContent = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';

        // Clear previous alerts
        form.querySelectorAll('.alert').forEach(alert => alert.remove());

        // Get form data
        const formData = new FormData(form);

        // Add recurring flag as 0/1 instead of boolean
        formData.set('isRecurring', form.querySelector('#editIsRecurring').checked ? 1 : 0);

        // Send data to server
        fetch('update_event.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const successAlert = document.createElement('div');
                    successAlert.className = 'alert alert-success';
                    successAlert.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Event updated successfully!';
                    form.prepend(successAlert);

                    // Close modal and reload data after a short delay
                    setTimeout(() => {
                        const editModal = document.getElementById('editEventModal');
                        const modalInstance = bootstrap.Modal.getInstance(editModal);
                        modalInstance.hide();

                        // Reload the current tab data
                        if (typeof window.loadPaginatedTabEvents === 'function') {
                            window.loadPaginatedTabEvents(window.currentTab, window.currentPage[window.currentTab]);
                        }
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Failed to update event');
                }
            })
            .catch(error => {
                console.error('Error updating event:', error);

                // Show error message
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger';
                errorAlert.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i> Error: ${error.message}`;
                form.prepend(errorAlert);
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
            });
    }

    // Also add a global click handler for edit buttons, as a fallback
    document.addEventListener('click', function (e) {
        if (e.target && e.target.closest('.edit-event')) {
            const button = e.target.closest('.edit-event');
            const eventId = button.getAttribute('data-event-id');

            if (eventId) {
                console.log('DEBUG: Edit button clicked via global handler:', eventId);
                window.loadEventData(eventId);
            }
        }
    });
});

// Create placeholder endpoint handlers if they don't exist
// These will be replaced with actual implementation later
function createPlaceholderApi(name, data) {
    if (!window.fetch.placeholders) {
        window.fetch.placeholders = {};
    }

    window.fetch.placeholders[name] = data;

    const originalFetch = window.fetch;
    window.fetch = function (url, options) {
        if (url === name && window.fetch.placeholders[name]) {
            return Promise.resolve({
                ok: true,
                json: () => Promise.resolve(window.fetch.placeholders[name])
            });
        }
        return originalFetch.apply(this, arguments);
    };
}

// Create placeholder data for assemblies and event types
createPlaceholderApi('get_assemblies.php', {
    success: true,
    assemblies: [
        { assembly_id: 1, name: 'Main Assembly' },
        { assembly_id: 2, name: 'Youth Assembly' },
        { assembly_id: 3, name: 'Children Assembly' }
    ]
});

createPlaceholderApi('get_event_types.php', {
    success: true,
    event_types: [
        { event_type_id: 1, name: 'Meeting' },
        { event_type_id: 2, name: 'Service' },
        { event_type_id: 3, name: 'Workshop' },
        { event_type_id: 4, name: 'Conference' }
    ]
});

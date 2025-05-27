document.addEventListener('DOMContentLoaded', function () {
    // DOM Elements
    const assemblySelect = document.getElementById('assemblySelect');
    const householdCheckboxList = document.getElementById('householdCheckboxList');
    const householdCheckboxesDiv = document.getElementById('householdCheckboxes');
    const selectAllCheckbox = document.getElementById('selectAllHouseholdsCheckbox');
    const addEventForm = document.getElementById('addEventForm');
    const editEventForm = document.getElementById('editEventForm');
    const isRecurringCheckbox = document.getElementById('isRecurring');
    const recurrenceOptions = document.getElementById('recurrenceOptions');
    const editIsRecurringCheckbox = document.getElementById('editIsRecurring');
    const editRecurrenceOptions = document.getElementById('editRecurrenceOptions');

    function renderHouseholdCheckboxes(households) {
        householdCheckboxesDiv.innerHTML = '';
        if (!Array.isArray(households) || households.length === 0) {
            householdCheckboxesDiv.innerHTML = '<div class="alert alert-info">No households found for this assembly</div>';
            selectAllCheckbox.disabled = true;
            return;
        }

        // Enable the select all checkbox since we have households
        selectAllCheckbox.disabled = false;

        households.forEach(hh => {
            const wrapper = document.createElement('div');
            wrapper.className = 'form-check mb-2';

            const input = document.createElement('input');
            input.className = 'form-check-input household-checkbox';
            input.type = 'checkbox';
            input.name = 'household_ids[]';
            input.value = hh.household_id;
            input.id = 'household_' + hh.household_id;

            const label = document.createElement('label');
            label.className = 'form-check-label ms-2';
            label.setAttribute('for', input.id);
            label.textContent = hh.name;

            wrapper.appendChild(input);
            wrapper.appendChild(label);
            householdCheckboxesDiv.appendChild(wrapper);

            // Add event listener to update select all state
            input.addEventListener('change', updateSelectAllState);
        });
    }

    function updateSelectAllState() {
        const checkboxes = document.querySelectorAll('.household-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
    }

    // Handle select all checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.household-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    // Handle assembly selection
    if (assemblySelect) {
        assemblySelect.addEventListener('change', function () {
            const assemblyId = this.value;

            // Show loading state
            householdCheckboxesDiv.innerHTML = '<div class="text-center my-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            selectAllCheckbox.checked = false;
            selectAllCheckbox.disabled = true;

            if (!assemblyId) {
                householdCheckboxesDiv.innerHTML = '<div class="alert alert-info">Please select an assembly first</div>';
                return;
            }

            fetch('get_households_by_assembly.php?assembly_id=' + encodeURIComponent(assemblyId))
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    console.log('Response:', data); // For debugging
                    if (data.success) {
                        renderHouseholdCheckboxes(data.households);
                    } else {
                        throw new Error(data.message || 'Failed to fetch households');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    householdCheckboxesDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                    selectAllCheckbox.disabled = true;
                });
        });
    }

    // Form handling
    if (addEventForm) {
        addEventForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';

            const formData = new FormData(this);

            // Log form data for debugging
            console.log('Submitting event with data:', Object.fromEntries(formData));

            fetch('add_event.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success';
                        alertDiv.textContent = 'Event added successfully!';
                        document.querySelector('.modal-body').prepend(alertDiv);

                        // Close modal and reload after a short delay
                        setTimeout(() => {
                            $('#addEventModal').modal('hide');
                            location.reload();
                        }, 1500);
                    } else {
                        throw new Error(data.message || 'Failed to add event');
                    }
                })
                .catch(error => {
                    console.error('Error adding event:', error);
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.textContent = 'Error adding event: ' + error.message;
                    document.querySelector('.modal-body').prepend(alertDiv);
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
        });
    }

    if (editEventForm) {
        editEventForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('edit_event.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#editEventModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error updating event: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    }

    // Handle recurring event checkboxes
    if (isRecurringCheckbox && recurrenceOptions) {
        isRecurringCheckbox.addEventListener('change', function () {
            recurrenceOptions.style.display = this.checked ? 'block' : 'none';
        });
    }

    if (editIsRecurringCheckbox && editRecurrenceOptions) {
        editIsRecurringCheckbox.addEventListener('change', function () {
            editRecurrenceOptions.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Load and display events data
    function loadEventsData() {
        fetch('get_events.php?level=household')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate events table
                    const eventsTableBody = document.querySelector('#eventsTable tbody');
                    eventsTableBody.innerHTML = '';
                    data.events.forEach((event, index) => {
                        const row = document.createElement('tr'); row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${event.event_name}</td>
                            <td>${event.event_type || 'N/A'}</td>
                            <td>${new Date(event.start_date).toLocaleString()}</td>
                            <td>${new Date(event.end_date).toLocaleString()}</td>
                            <td>${event.is_recurring ? 'Yes' : 'No'}</td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-event-btn" data-id="${event.event_id}">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger delete-event-btn" data-id="${event.event_id}">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        `;
                        eventsTableBody.appendChild(row);
                    });

                    // Update dashboard counters                    const now = new Date();
                    const upcomingEvents = data.events.filter(event => new Date(event.start_date) >= now);
                    const recurringEvents = data.events.filter(event => event.is_recurring);
                    const thisMonthEvents = data.events.filter(event => {
                        const eventDate = new Date(event.start_date);
                        return eventDate.getMonth() === now.getMonth() && eventDate.getFullYear() === now.getFullYear();
                    });

                    document.getElementById('upcomingEventsCount').textContent = upcomingEvents.length;
                    document.getElementById('recurringEventsCount').textContent = recurringEvents.length;
                    document.getElementById('thisMonthEventsCount').textContent = thisMonthEvents.length;
                }
            })
            .catch(error => {
                console.error('Error loading events:', error);
                const eventsTableBody = document.querySelector('#eventsTable tbody');
                eventsTableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            Error loading events. Please try again later.
                        </td>
                    </tr>
                `;
            });
    }

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Load initial events data
    loadEventsData();

    // Edit event button handler
    document.addEventListener('click', function (e) {
        if (e.target.closest('.edit-event-btn')) {
            const btn = e.target.closest('.edit-event-btn');
            const eventId = btn.getAttribute('data-id');
            const row = btn.closest('tr');
            if (!row) return;
            // Extract data from table cells (adjust indices as needed)
            const eventName = row.children[1].textContent.trim();
            const eventType = row.children[2].textContent.trim();
            const startDate = row.children[3].textContent.trim();
            const endDate = row.children[4].textContent.trim();
            const isRecurring = row.children[5].textContent.trim() === 'Yes';
            // Populate modal form
            document.getElementById('editEventId').value = eventId;
            document.getElementById('editEventName').value = eventName;
            document.getElementById('editEventType').value = '';
            // Try to select the correct event type option
            const eventTypeSelect = document.getElementById('editEventType');
            for (let i = 0; i < eventTypeSelect.options.length; i++) {
                if (eventTypeSelect.options[i].text.trim() === eventType) {
                    eventTypeSelect.selectedIndex = i;
                    break;
                }
            }
            // Set date/time fields using robust formatting
            document.getElementById('editStartDate').value = formatDateForInput(startDate);
            document.getElementById('editEndDate').value = formatDateForInput(endDate);
            // Set recurring checkbox
            document.getElementById('editIsRecurring').checked = isRecurring;
            document.getElementById('editRecurrenceOptions').style.display = isRecurring ? 'block' : 'none';
            // Show modal
            const editModal = new bootstrap.Modal(document.getElementById('editEventModal'));
            editModal.show();
        }
    });

    // Helper to format date string for datetime-local input
    function formatDateForInput(dateStr) {
        if (!dateStr) return '';
        // Try to parse as local string or ISO string
        let d = new Date(dateStr);
        if (isNaN(d.getTime())) {
            // Try to parse as dd/mm/yyyy hh:mm or other formats if needed
            const parts = dateStr.match(/(\d{4})[-\/]?(\d{2})[-\/]?(\d{2})[ T](\d{2}):(\d{2})/);
            if (parts) {
                d = new Date(parts[1], parts[2] - 1, parts[3], parts[4], parts[5]);
            } else {
                return '';
            }
        }
        // Format as yyyy-MM-ddThh:mm
        const pad = n => n.toString().padStart(2, '0');
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
});

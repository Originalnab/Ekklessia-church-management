// event-handlers.js for assembly events
// Handles loading, filtering, and paginating events for all tabs (upcoming, past, all)

document.addEventListener('DOMContentLoaded', function () {
  // Utility functions for showing messages
  window.showSuccessMessage = function (message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3';
    alert.style.zIndex = '1050';
    alert.innerHTML = `
      <strong>Success!</strong> ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alert);
    setTimeout(() => {
      if (alert.parentNode) {
        alert.parentNode.removeChild(alert);
      }
    }, 5000);
  }
  window.showErrorMessage = function (message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3';
    alert.style.zIndex = '1050';
    alert.innerHTML = `
      <strong>Error!</strong> ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alert);
    setTimeout(() => {
      if (alert.parentNode) {
        alert.parentNode.removeChild(alert);
      }
    }, 7000);
  }

  const tabIds = ['upcoming', 'past', 'all'];
  const tableIds = {
    upcoming: 'upcomingEventsTable',
    past: 'pastEventsTable',
    all: 'eventsTable'
  };
  const paginationIds = {
    upcoming: 'upcomingEventsPagination',
    past: 'pastEventsPagination',
    all: 'eventsPagination'
  };
  let currentTab = 'upcoming';
  let currentPage = { upcoming: 1, past: 1, all: 1 };
  let pageSize = 10;
  // Cache for event data
  const eventCache = {};

  // Filter elements
  const searchInput = document.getElementById('eventSearchInput');
  const typeFilter = document.getElementById('eventTypeFilter');
  const assemblyFilter = document.getElementById('assemblyFilter');
  const startDateFilter = document.getElementById('startDateFilter');
  const endDateFilter = document.getElementById('endDateFilter');

  // Load assembly filter options on page load
  if (assemblyFilter) {
    fetch('get_assemblies.php')
      .then(res => res.json())
      .then(data => {
        if (data.success && Array.isArray(data.assemblies)) {
          assemblyFilter.innerHTML = '<option value="">All Assemblies</option>';
          data.assemblies.forEach(asm => {
            const opt = document.createElement('option');
            opt.value = asm.assembly_id;
            opt.textContent = asm.name;
            assemblyFilter.appendChild(opt);
          });
        }
      })
      .catch(error => {
        console.error('Error loading assemblies:', error);
        showErrorMessage('Error loading assemblies. Please try again.');
      });
  }

  // Export data function
  window.exportEventData = function (format) {
    // Get the current tab
    const activeTab = document.querySelector('#eventViewTabs .nav-link.active').id;
    const tabName = activeTab.replace('-events-tab', '').replace('-view-tab', '');

    // Get filter values
    const searchValue = searchInput ? searchInput.value : '';
    const typeValue = typeFilter ? typeFilter.value : '';
    const assemblyValue = assemblyFilter ? assemblyFilter.value : '';
    const startDateValue = startDateFilter ? startDateFilter.value : '';
    const endDateValue = endDateFilter ? endDateFilter.value : '';

    const params = new URLSearchParams({
      tab: tabName,
      page: 1,
      pageSize: 1000, // Get all matching events for export
      search: searchValue,
      type: typeValue,
      assembly: assemblyValue,
      startDate: startDateValue,
      endDate: endDateValue,
      export: format
    });

    // Open in a new tab or trigger download
    window.open('export_events.php?' + params.toString(), '_blank');
  };
  // Define globally before any usage
  window.loadPaginatedTabEvents = function (tab, page) {
    currentTab = tab;
    currentPage[tab] = page;

    // Get filter values
    const searchValue = searchInput ? searchInput.value : '';
    const typeValue = typeFilter ? typeFilter.value : '';
    const assemblyValue = assemblyFilter ? assemblyFilter.value : '';
    const startDateValue = startDateFilter ? startDateFilter.value : '';
    const endDateValue = endDateFilter ? endDateFilter.value : '';

    const params = new URLSearchParams({
      tab: tab,
      page: page,
      pageSize: tab === 'calendar' ? 1000 : pageSize, // Load all events for calendar
      search: searchValue,
      type: typeValue,
      assembly: assemblyValue,
      startDate: startDateValue,
      endDate: endDateValue
    });

    // Show active filters indicator if any filters are applied
    updateActiveFiltersIndicator(searchValue, typeValue, startDateValue, endDateValue);

    fetch('get_events_paginated.php?' + params.toString())
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          if (tab === 'calendar' && typeof renderAssemblyCalendar === 'function') {
            renderAssemblyCalendar(data.events);
          } else {
            // Update table view for other tabs
            renderEventsTable(tab, data.events, page);
            renderPagination(data.total, data.page, data.pageSize, p => loadPaginatedTabEvents(tab, p), paginationIds[tab]);
          }

          // Update dashboard counts
          if (tab === 'upcoming') {
            document.getElementById('upcomingEventsCount').textContent = data.total;
          }
          if (tab === 'all') {
            document.getElementById('totalEventsCount').textContent = data.total;
          }
        } else {
          if (tab === 'calendar' && typeof renderAssemblyCalendar === 'function') {
            renderAssemblyCalendar([]);
          } else {
            renderEventsTable(tab, [], page);
            renderPagination(0, 1, pageSize, () => { }, paginationIds[tab]);
          }
        }
      }).catch((error) => {
        console.error('Error loading events:', error);
        if (tab === 'calendar' && typeof renderAssemblyCalendar === 'function') {
          renderAssemblyCalendar([]);
        } else {
          renderEventsTable(tab, [], page);
          renderPagination(0, 1, pageSize, () => { }, paginationIds[tab]);
        }
        showErrorMessage('Error loading events. Please try again or contact support if the problem persists.');
      });
  };

  // Render events in the table
  function renderEventsTable(tab, events, page) {
    const table = document.getElementById(tableIds[tab]);
    if (!table) return;

    // Initialize table headers if needed
    const thead = table.querySelector('thead tr');
    if (!thead || !thead.children.length) {
      const headerRow = document.createElement('tr');
      headerRow.innerHTML = `
        <th>#</th>
        <th>Event Name</th>
        <th>Event Type</th>
        <th>Assembly</th>
        <th>Start Date & Time</th>
        <th>End Date & Time</th>
        <th>Recurring</th>
        <th>Actions</th>
      `;
      table.querySelector('thead').appendChild(headerRow);
    }

    const tableBody = table.querySelector('tbody');
    tableBody.innerHTML = '';
    if (!events.length) {
      tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No events found.</td></tr>';
      return;
    }
    events.forEach((event, idx) => {
      const row = document.createElement('tr');
      row.innerHTML = `
                <td>${(page - 1) * pageSize + idx + 1}</td>
                <td>${event.title || event.event_name}</td>
                <td>${event.event_type || ''}</td>
                <td><span class="assembly-name-badge">${event.assembly_name || ''}</span></td>
                <td>${formatDateTime(event.start_date)}</td>
                <td>${formatDateTime(event.end_date)}</td>
                <td>${event.is_recurring == 1 ? 'Yes' : 'No'}</td>
                <td>
                  <div class="d-flex gap-2 justify-content-center align-items-center">
                    <button class="btn btn-sm btn-info view-event-btn" data-id="${event.event_id}" title="View"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-sm btn-warning edit-event-btn" data-id="${event.event_id}" title="Edit"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-danger delete-event-btn" data-id="${event.event_id}" title="Delete"><i class="bi bi-trash"></i></button>
                  </div>
                </td>
            `;
      tableBody.appendChild(row);
    });
  }
  // Render pagination controls
  function renderPagination(total, page, pageSize, onPageChange, paginationId) {
    const totalPages = Math.ceil(total / pageSize);
    const pag = document.getElementById(paginationId);
    if (!pag) return;
    pag.innerHTML = '';

    // Don't render pagination if there's only one page or no pages
    if (totalPages <= 1) return;

    // Add first page and previous buttons
    if (page > 1) {
      addPaginationButton(pag, 1, '<<', onPageChange);
      addPaginationButton(pag, page - 1, '<', onPageChange);
    }

    // Calculate range of pages to show
    let startPage = Math.max(1, page - 2);
    let endPage = Math.min(totalPages, page + 2);

    // Ensure we always show at least 5 pages if available
    if (endPage - startPage < 4) {
      if (startPage === 1) {
        endPage = Math.min(totalPages, startPage + 4);
      } else if (endPage === totalPages) {
        startPage = Math.max(1, endPage - 4);
      }
    }

    // Add page numbers
    for (let p = startPage; p <= endPage; p++) {
      const li = document.createElement('li');
      li.className = 'page-item' + (p === page ? ' active' : '');
      const a = document.createElement('a');
      a.className = 'page-link';
      a.href = '#';
      a.textContent = p;
      a.addEventListener('click', function (e) {
        e.preventDefault();
        onPageChange(p);
      });
      li.appendChild(a);
      pag.appendChild(li);
    }

    // Add next and last page buttons
    if (page < totalPages) {
      addPaginationButton(pag, page + 1, '>', onPageChange);
      addPaginationButton(pag, totalPages, '>>', onPageChange);
    }
  }

  // Helper function to add pagination buttons
  function addPaginationButton(container, page, text, onPageChange) {
    const li = document.createElement('li');
    li.className = 'page-item';
    const a = document.createElement('a');
    a.className = 'page-link';
    a.href = '#';
    a.textContent = text;
    a.addEventListener('click', function (e) {
      e.preventDefault();
      onPageChange(page);
    });
    li.appendChild(a);
    container.appendChild(li);
  }
  // Format date/time for display
  function formatDateTime(dt) {
    if (!dt) return '';
    try {
      const d = new Date(dt.replace(' ', 'T'));
      const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      };
      return d.toLocaleString(undefined, options);
    } catch (e) {
      // Fallback to basic formatting if any error occurs
      return dt;
    }
  }

  // Tab switching
  tabIds.forEach(tab => {
    document.getElementById(tab + '-events-tab').addEventListener('click', function (e) {
      e.preventDefault();
      loadPaginatedTabEvents(tab, 1);
    });
  });

  // Add calendar tab handling
  const calendarTab = document.getElementById('calendar-view-tab');
  if (calendarTab) {
    calendarTab.addEventListener('shown.bs.tab', function () {
      loadAssemblyCalendarEvents();
    });
  }

  // Filter controls - enhanced for responsive filtering
  // Handle search input with debounce for better performance
  let searchTimeout = null;
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        loadPaginatedTabEvents(currentTab, 1);
      }, 300); // 300ms debounce
    });
  }

  // Add immediate response to select filters and date filters
  [typeFilter, assemblyFilter, startDateFilter, endDateFilter].forEach(el => {
    if (el) {
      el.addEventListener('change', function () {
        loadPaginatedTabEvents(currentTab, 1);
      });
    }
  });

  // View event modal logic
  document.body.addEventListener('click', function (e) {
    if (e.target.closest('.view-event-btn')) {
      const btn = e.target.closest('.view-event-btn');
      const eventId = btn.getAttribute('data-id');
      const modal = new bootstrap.Modal(document.getElementById('viewEventModal'));
      const modalBody = document.getElementById('viewEventModalBody');
      // Show loading spinner
      modalBody.innerHTML = '<div class="d-flex justify-content-center align-items-center" style="min-height:200px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
      modal.show();
      fetch('view_event.php?event_id=' + encodeURIComponent(eventId))
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const ev = data.event;
            // Detect theme (light/dark)
            const isDark = document.body.getAttribute('data-bs-theme') === 'dark';
            let cardStyle, titleStyle, labelStyle, textStyle, descStyle;
            if (isDark) {
              cardStyle = 'background: linear-gradient(120deg, #232526 0%, #414345 100%); color: #f3f3f3;';
              titleStyle = 'color:#aee7ff;';
              labelStyle = 'color:#b3e5fc!important;';
              textStyle = 'color:#fff;';
              descStyle = 'background:rgba(255,255,255,0.07); color:#f3f3f3;';
            } else {
              cardStyle = 'background: linear-gradient(120deg, #e0c3fc 0%, #8ec5fc 100%); color: #222;';
              titleStyle = 'color:#3a3a7c;';
              labelStyle = 'color:#2b4c7e!important;';
              textStyle = 'color:#222;';
              descStyle = 'background:rgba(255,255,255,0.7); color:#222;';
            }
            modalBody.innerHTML = `
                        <div class="card shadow border-0" style="border-radius: 1rem; ${cardStyle}">
                          <div class="card-body p-4">
                            <h4 class="card-title mb-2" style="${titleStyle}"><i class="bi bi-calendar-event me-2"></i>${ev.title}</h4>
                            <div class="mb-3 text-muted" style="${labelStyle}">${ev.event_type ? `<span class='badge bg-info text-dark me-2'>${ev.event_type}</span>` : ''}${ev.is_recurring == 1 ? `<span class='badge bg-warning text-dark'>Recurring: ${ev.frequency || ''}</span>` : ''}</div>
                            <div class="row mb-3">
                              <div class="col-md-6 mb-2">
                                <strong style="${labelStyle}">Start:</strong> <span style="${textStyle}">${formatDateTime(ev.start_date)}</span>
                              </div>
                              <div class="col-md-6 mb-2">
                                <strong style="${labelStyle}">End:</strong> <span style="${textStyle}">${formatDateTime(ev.end_date)}</span>
                              </div>
                            </div>
                            <div class="mb-3">
                              <strong style="${labelStyle}">Assembly:</strong> <span style="${textStyle}">${ev.assembly_name || ''}</span>
                            </div>
                            <div class="mb-3">
                              <strong style="${labelStyle}">Description:</strong><br>
                              <div class="border rounded p-2" style="${descStyle}">${ev.description ? ev.description.replace(/\n/g, '<br>') : '<em>No description</em>'}</div>
                            </div>
                            <div class="text-end text-muted small" style="${labelStyle}">
                              <span>Created by: ${ev.created_by_name || 'N/A'}</span> &middot; <span>${formatDateTime(ev.created_at)}</span>
                            </div>
                          </div>
                        </div>`;
          } else {
            modalBody.innerHTML = `<div class='alert alert-danger'>${data.message || 'Could not load event details.'}</div>`;
          }
        })
        .catch(() => {
          modalBody.innerHTML = `<div class='alert alert-danger'>Could not load event details.</div>`;
        });
    }
  });

  // Edit event modal logic
  document.body.addEventListener('click', function (e) {
    if (e.target.closest('.edit-event-btn')) {
      const btn = e.target.closest('.edit-event-btn');
      const eventId = btn.getAttribute('data-id');
      const modal = new bootstrap.Modal(document.getElementById('editEventModal'));
      const modalBody = document.getElementById('editEventModalBody');
      // Show loading spinner
      modalBody.innerHTML = '<div class="d-flex justify-content-center align-items-center" style="min-height:200px;"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>';
      modal.show();
      fetch('get_event.php?event_id=' + encodeURIComponent(eventId))
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const ev = data.event;
            const assemblies = data.assemblies;
            const eventTypes = data.event_types;
            // Render form fields
            modalBody.innerHTML = `
                        <input type="hidden" name="event_id" value="${ev.event_id}">
                        <div class="mb-3">
                          <label for="editEventTitle" class="form-label">Title</label>
                          <input type="text" id="editEventTitle" name="title" class="form-control" value="${ev.title || ''}" required>
                        </div>
                        <div class="mb-3">
                          <label for="editAssemblySelect" class="form-label">Assembly</label>
                          <select id="editAssemblySelect" name="assembly_id" class="form-select" required>
                            ${assemblies.map(a => `<option value="${a.assembly_id}" ${a.assembly_id == ev.assembly_id ? 'selected' : ''}>${a.name}</option>`).join('')}
                          </select>
                        </div>
                        <div class="mb-3">
                          <label for="editEventTypeSelect" class="form-label">Event Type</label>
                          <select id="editEventTypeSelect" name="event_type_id" class="form-select" required>
                            ${eventTypes.map(et => `<option value="${et.event_type_id}" ${et.event_type_id == ev.event_type_id ? 'selected' : ''}>${et.name}</option>`).join('')}
                          </select>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label for="editStartDate" class="form-label">Start Date & Time</label>
                            <input type="datetime-local" id="editStartDate" name="start_date" class="form-control" value="${ev.start_date ? ev.start_date.replace(' ', 'T').slice(0, 16) : ''}" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label for="editEndDate" class="form-label">End Date & Time</label>
                            <input type="datetime-local" id="editEndDate" name="end_date" class="form-control" value="${ev.end_date ? ev.end_date.replace(' ', 'T').slice(0, 16) : ''}" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label for="editEventDescription" class="form-label">Description</label>
                          <textarea id="editEventDescription" name="description" class="form-control">${ev.description || ''}</textarea>
                        </div>                        <div class="mb-3 form-check">
                          <input type="checkbox" class="form-check-input" id="editIsRecurring" name="is_recurring" ${ev.is_recurring == 1 ? 'checked' : ''}>
                          <label class="form-check-label" for="editIsRecurring">Recurring Event</label>
                        </div>
                          <div class="mb-3" id="editFrequencyField" style="display:${ev.is_recurring == 1 ? 'block' : 'none'};">
                          <label for="editFrequency" class="form-label">Frequency <span class="text-danger">*</span></label>
                          <select id="editFrequency" name="frequency" class="form-select">
                            <option value="">Select frequency</option>
                            <option value="daily" ${ev.frequency === 'daily' ? 'selected' : ''}>Daily</option>
                            <option value="weekly" ${ev.frequency === 'weekly' ? 'selected' : ''}>Weekly</option>
                            <option value="monthly" ${ev.frequency === 'monthly' ? 'selected' : ''}>Monthly</option>
                            <option value="yearly" ${ev.frequency === 'yearly' ? 'selected' : ''}>Yearly</option>
                          </select>
                        </div>`;// Show/hide frequency field
            const recurring = document.getElementById('editIsRecurring');
            const freqField = document.getElementById('editFrequencyField');
            const freqSelect = document.getElementById('editFrequency'); if (recurring && freqField && freqSelect) {
              // Initialize state based on current value
              freqField.style.display = recurring.checked ? 'block' : 'none';

              // Add event listener for changes
              recurring.addEventListener('change', function () {
                freqField.style.display = this.checked ? 'block' : 'none';

                // Clear selection when unchecked
                if (!this.checked) {
                  freqSelect.value = '';
                }
              });
            }
          } else {
            modalBody.innerHTML = `<div class='alert alert-danger'>${data.message || 'Could not load event details.'}</div>`;
          }
        })
        .catch(() => {
          modalBody.innerHTML = `<div class='alert alert-danger'>Could not load event details.</div>`;
        });
    }
  });
  // Handle Edit Event Form Submission
  const editEventModal = document.getElementById('editEventModal');
  if (editEventModal) {
    editEventModal.addEventListener('submit', function (e) {
      if (e.target && e.target.id === 'editEventForm') {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';        // Collect form data
        const formData = new FormData(form);

        // Validate start and end dates
        const startDate = new Date(form.querySelector('#editStartDate').value);
        const endDate = new Date(form.querySelector('#editEndDate').value);

        if (startDate > endDate) {
          showErrorMessage('End date/time must be after start date/time');
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnText;
          return;
        }

        // Handle recurring event logic
        const isRecurring = form.querySelector('#editIsRecurring').checked;

        // Handle recurring event logic
        if (isRecurring) {
          // If event is recurring, frequency is required
          const frequency = form.querySelector('#editFrequency').value;
          if (!frequency) {
            showErrorMessage('Frequency is required for recurring events');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            return;
          }
          formData.set('is_recurring', '1');
          formData.set('frequency', frequency);
        } else {
          // If event is not recurring, set frequency to empty
          formData.set('is_recurring', '0');
          formData.set('frequency', '');
        }

        fetch('update_event_process.php', {
          method: 'POST',
          body: formData
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              // Close modal and show success message
              bootstrap.Modal.getInstance(editEventModal).hide();
              showSuccessMessage(data.message || 'Event updated successfully');

              // Refresh current tab and update stats
              setTimeout(() => {
                // Reload events for the current tab
                const activeTab = document.querySelector('#eventViewTabs .nav-link.active').id;
                const tabName = activeTab.replace('-events-tab', '').replace('-view-tab', '');
                loadPaginatedTabEvents(tabName, 1);

                // Also reload calendar if it's initialized
                if (tabName === 'calendar' && typeof loadAssemblyCalendarEvents === 'function') {
                  loadAssemblyCalendarEvents();
                }

                // Update dashboard stats
                fetch('get_event_stats.php')
                  .then(res => res.json())
                  .then(stats => {
                    if (stats.success) {
                      if (stats.totalEvents) document.getElementById('totalEventsCount').textContent = stats.totalEvents;
                      if (stats.upcomingEvents) document.getElementById('upcomingEventsCount').textContent = stats.upcomingEvents;
                      if (stats.recurringEvents) document.getElementById('recurringEventsCount').textContent = stats.recurringEvents;
                      if (stats.thisMonthEvents) document.getElementById('thisMonthEventsCount').textContent = stats.thisMonthEvents;
                    }
                  })
                  .catch(e => console.error('Failed to update stats:', e));
              }, 1000);
            } else {
              showErrorMessage(data.message || 'Could not update event.');
            }
          })
          .catch(() => {
            showErrorMessage('Error updating event. Please try again.');
          })
          .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
          });
      }
    });
  }

  // Handle Delete Event Button Click
  document.body.addEventListener('click', function (e) {
    if (e.target.closest('.delete-event-btn')) {
      const btn = e.target.closest('.delete-event-btn');
      const eventId = btn.getAttribute('data-id');
      const row = btn.closest('tr');
      const eventTitle = row.querySelector('td:nth-child(2)').textContent;

      // Set event ID and title in delete modal
      document.getElementById('deleteEventId').value = eventId;
      document.getElementById('deleteEventTitle').textContent = eventTitle;

      // Show the delete modal
      const deleteModal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
      deleteModal.show();
    }
  });

  // Handle Delete Event Form Submission
  const deleteEventForm = document.getElementById('deleteEventForm');
  if (deleteEventForm) {
    deleteEventForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const eventId = document.getElementById('deleteEventId').value;
      const eventTitle = document.getElementById('deleteEventTitle').textContent;
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.innerHTML;

      // Disable the submit button and show loading state
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Deleting...';

      const formData = new FormData();
      formData.append('event_id', eventId);

      fetch('delete_event_process.php', {
        method: 'POST',
        body: formData
      })
        .then(res => res.json()).then(data => {
          if (data.success) {
            // Close modal and show success message
            bootstrap.Modal.getInstance(document.getElementById('deleteEventModal')).hide();
            showSuccessMessage(data.message || `Event "${eventTitle}" deleted successfully`);

            // Refresh current tab and update stats
            setTimeout(() => {
              // Reload events for the current tab
              const activeTab = document.querySelector('#eventViewTabs .nav-link.active').id;
              const tabName = activeTab.replace('-events-tab', '').replace('-view-tab', '');
              loadPaginatedTabEvents(tabName, 1);

              // Also reload calendar if it's initialized
              if (typeof loadAssemblyCalendarEvents === 'function') {
                loadAssemblyCalendarEvents();
              }

              // Update dashboard stats
              fetch('get_event_stats.php')
                .then(res => res.json())
                .then(stats => {
                  if (stats.success) {
                    if (stats.totalEvents) document.getElementById('totalEventsCount').textContent = stats.totalEvents;
                    if (stats.upcomingEvents) document.getElementById('upcomingEventsCount').textContent = stats.upcomingEvents;
                    if (stats.recurringEvents) document.getElementById('recurringEventsCount').textContent = stats.recurringEvents;
                    if (stats.thisMonthEvents) document.getElementById('thisMonthEventsCount').textContent = stats.thisMonthEvents;
                  }
                })
                .catch(e => console.error('Failed to update stats:', e));
            }, 1000);
          } else {
            showErrorMessage(data.message || 'Could not delete event.');
          }
        })
        .catch(() => {
          showErrorMessage('Error deleting event. Please try again.');
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnText;
        });
    });
  }  // Initial load
  loadPaginatedTabEvents('upcoming', 1);

  // Initialize dashboard counts
  fetch('get_event_stats.php')
    .then(res => res.json())
    .then(stats => {
      if (stats.success) {
        if (stats.recurringEvents) document.getElementById('recurringEventsCount').textContent = stats.recurringEvents;
        if (stats.thisMonthEvents) document.getElementById('thisMonthEventsCount').textContent = stats.thisMonthEvents;
      }
    })
    .catch(e => console.error('Failed to load initial stats:', e));
  // Function to show active filters indicator
  function updateActiveFiltersIndicator(searchValue, typeValue, startDateValue, endDateValue) {
    const filterIndicator = document.getElementById('activeFiltersIndicator');
    const assemblyValue = assemblyFilter ? assemblyFilter.value : '';

    // Create indicator if it doesn't exist
    if (!filterIndicator) {
      const filtersDiv = document.getElementById('eventFilters');
      if (filtersDiv) {
        const indicator = document.createElement('div');
        indicator.id = 'activeFiltersIndicator';
        indicator.className = 'mt-2 d-flex flex-wrap gap-2';
        filtersDiv.appendChild(indicator);
      }
    }

    // Get or re-get the indicator (in case it was just created)
    const indicator = document.getElementById('activeFiltersIndicator');
    if (!indicator) return;

    indicator.innerHTML = '';

    const hasFilters = searchValue || typeValue || assemblyValue || startDateValue || endDateValue;

    if (hasFilters) {
      // Add clear all button first
      const clearAllBtn = document.createElement('button');
      clearAllBtn.className = 'btn btn-sm btn-outline-secondary';
      clearAllBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i>Clear all filters';
      clearAllBtn.addEventListener('click', function () {
        if (searchInput) searchInput.value = '';
        if (typeFilter) typeFilter.value = '';
        if (assemblyFilter) assemblyFilter.value = '';
        if (startDateFilter) startDateFilter.value = '';
        if (endDateFilter) endDateFilter.value = '';
        loadPaginatedTabEvents(currentTab, 1);
      });
      indicator.appendChild(clearAllBtn);

      // Add individual filter badges
      if (searchValue) {
        addFilterBadge(indicator, 'Search: ' + searchValue, () => {
          searchInput.value = '';
          loadPaginatedTabEvents(currentTab, 1);
        });
      }

      if (typeValue && typeFilter && typeFilter.selectedIndex >= 0) {
        const typeText = typeFilter.options[typeFilter.selectedIndex].text;
        addFilterBadge(indicator, 'Type: ' + typeText, () => {
          typeFilter.value = '';
          loadPaginatedTabEvents(currentTab, 1);
        });
      }

      if (assemblyValue && assemblyFilter && assemblyFilter.selectedIndex >= 0) {
        const assemblyText = assemblyFilter.options[assemblyFilter.selectedIndex].text;
        addFilterBadge(indicator, 'Assembly: ' + assemblyText, () => {
          assemblyFilter.value = '';
          loadPaginatedTabEvents(currentTab, 1);
        });
      }

      if (startDateValue) {
        addFilterBadge(indicator, 'From: ' + formatDate(startDateValue), () => {
          startDateFilter.value = '';
          loadPaginatedTabEvents(currentTab, 1);
        });
      }

      if (endDateValue) {
        addFilterBadge(indicator, 'To: ' + formatDate(endDateValue), () => {
          endDateFilter.value = '';
          loadPaginatedTabEvents(currentTab, 1);
        });
      }
    }
  }

  // Helper to create filter badges
  function addFilterBadge(container, text, clearFn) {
    const badge = document.createElement('span');
    badge.className = 'badge bg-light text-dark d-flex align-items-center';
    badge.style.padding = '0.5em 0.7em';
    badge.style.border = '1px solid #ddd';
    badge.innerHTML = text + ' <button class="btn btn-sm text-danger p-0 ms-2" style="font-size: 14px;"><i class="bi bi-x"></i></button>';
    badge.querySelector('button').addEventListener('click', clearFn);
    container.appendChild(badge);
  }

  // Simple date formatter
  function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString();
  }

  // Event handlers for the calendar
  document.getElementById('calendar-view-tab')?.addEventListener('shown.bs.tab', function (e) {
    if (typeof loadAssemblyCalendarEvents === 'function') {
      setTimeout(() => {
        loadAssemblyCalendarEvents();
      }, 100);
    }
  });
  // Initialize assembly filter
  if (assemblyFilter) {
    fetch('get_assemblies.php')
      .then(res => res.json())
      .then(data => {
        if (data.success && Array.isArray(data.assemblies)) {
          assemblyFilter.innerHTML = '<option value="">All Assemblies</option>';
          data.assemblies.forEach(asm => {
            const opt = document.createElement('option');
            opt.value = asm.assembly_id;
            opt.textContent = asm.name;
            assemblyFilter.appendChild(opt);
          });
        }
      })
      .catch(error => {
        console.error('Error loading assemblies:', error);
        showErrorMessage('Error loading assemblies. Please try again.');
      });
  }
});

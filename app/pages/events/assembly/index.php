<?php
// Assembly Events Index Page
session_start();
$page_title = "Assembly Event Management";
include "../../../config/db.php";

if (!isset($_SESSION['member_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /Ekklessia-church-management/app/pages/login.php?redirect=$redirect_url");
    exit;
}

$logged_in_member_id = $_SESSION['member_id'];

try {
    $stmt = $pdo->query("SELECT event_type_id, name, is_recurring, default_frequency, level FROM event_types WHERE level = 'assembly' ORDER BY name ASC");
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $event_types = [];
    $_SESSION['error_message'] = "Error fetching event types: " . $e->getMessage();
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_events FROM events WHERE level = 'assembly'");
    $total_events = $stmt->fetch(PDO::FETCH_ASSOC)['total_events'];
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching total events: " . $e->getMessage();
    $total_events = 0;
}

$base_url = '/Ekklessia-church-management/app/pages';
?>
<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
<head>
    <!-- Add FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <!-- Custom calendar styles -->
    <style>
        .fc .fc-button-primary {
            background-color: #007bff;
            border-color: #0056b3;
        }
        .fc .fc-button-primary:not(:disabled):active,
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #0056b3;
            border-color: #003d80;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<main class="container flex-grow-1 py-2">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Success!</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Error!</strong> <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <!-- Navigation Card -->
    <div class="card nav-card" style="margin-top: -30px; position: relative; top: -10px;">
        <div class="card-body py-3">
            <div class="row g-3">
                <!-- ...existing navigation links... -->
            </div>
        </div>
    </div>
    <!-- Mini Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #007bff, #00d4ff); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-event fs-2"></i>
                    <h6 class="card-title text-white">Total Events</h6>
                    <h3 class="card-text" id="totalEventsCount"><?= $total_events ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #28a745, #6fcf97); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check fs-2"></i>
                    <h6 class="card-title text-white">Upcoming Events</h6>
                    <h3 class="card-text" id="upcomingEventsCount">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #17a2b8, #4fc3f7); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-repeat fs-2"></i>
                    <h6 class="card-title text-white">Recurring Events</h6>
                    <h3 class="card-text" id="recurringEventsCount">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #dc3545, #ff6b6b); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-clock fs-2"></i>
                    <h6 class="card-title text-white">Events This Month</h6>
                    <h3 class="card-text" id="thisMonthEventsCount">0</h3>
                </div>
            </div>
        </div>
    </div>
    <!-- Events Table and Tabs -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Assembly Events</h5>
            <div class="btn-group float-end" role="group" aria-label="Event Actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <i class="bi bi-plus-circle"></i> Add Event
                </button>
            </div>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="eventViewTabs">
                <li class="nav-item">
                    <a class="nav-link active" id="upcoming-events-tab" data-bs-toggle="tab" href="#upcoming-events" role="tab">Upcoming Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="past-events-tab" data-bs-toggle="tab" href="#past-events" role="tab">Past Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="all-events-tab" data-bs-toggle="tab" href="#all-events" role="tab">All Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="calendar-view-tab" data-bs-toggle="tab" href="#calendar-view" role="tab">Calendar View</a>
                </li>
            </ul>
            <!-- Filter Controls -->
            <div class="mb-3" id="eventFilters">                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <input type="text" id="eventSearchInput" class="form-control" placeholder="Search events by name...">
                    </div>
                    <div class="col-md-2">
                        <select id="eventTypeFilter" class="form-select">
                            <option value="">All Event Types</option>
                            <?php foreach ($event_types as $event_type): ?>
                                <option value="<?= htmlspecialchars($event_type['event_type_id']) ?>"><?= htmlspecialchars($event_type['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="assemblyFilter" class="form-select">
                            <option value="">All Assemblies</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="startDateFilter" class="form-control" placeholder="Start date">
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="endDateFilter" class="form-control" placeholder="End date">
                    </div>
                </div>
            </div>
            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Upcoming Events -->
                <div class="tab-pane fade show active" id="upcoming-events" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="upcomingEventsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Event Name</th>
                                    <th>Event Type</th>
                                    <th>Assembly</th>
                                    <th>Start Date & Time</th>
                                    <th>End Date & Time</th>
                                    <th>Recurring</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <nav>
                        <ul class="pagination justify-content-center" id="upcomingEventsPagination"></ul>
                    </nav>
                </div>
                <!-- Past Events -->
                <div class="tab-pane fade" id="past-events" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="pastEventsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Event Name</th>
                                    <th>Event Type</th>
                                    <th>Assembly</th>
                                    <th>Start Date & Time</th>
                                    <th>End Date & Time</th>
                                    <th>Recurring</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <nav>
                        <ul class="pagination justify-content-center" id="pastEventsPagination"></ul>
                    </nav>
                </div>
                <!-- All Events -->
                <div class="tab-pane fade" id="all-events" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="eventsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Event Name</th>
                                    <th>Event Type</th>
                                    <th>Assembly</th>
                                    <th>Start Date & Time</th>
                                    <th>End Date & Time</th>
                                    <th>Recurring</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <nav>
                        <ul class="pagination justify-content-center" id="eventsPagination"></ul>
                    </nav>
                </div>
                <!-- Calendar View -->
                <div class="tab-pane fade" id="calendar-view" role="tabpanel">
                    <!-- Calendar Filters -->
                    <div class="mb-3" id="calendarFilters">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label for="startDateFilter" class="form-label">Start Date</label>
                                <input type="date" id="startDateFilter" class="form-control" placeholder="Start date">
                            </div>
                            <div class="col-md-3">
                                <label for="endDateFilter" class="form-label">End Date</label>
                                <input type="date" id="endDateFilter" class="form-control" placeholder="End date">
                            </div>
                            <div class="col-md-3">
                                <label for="levelFilter" class="form-label">Event Level</label>
                                <select id="levelFilter" class="form-select">
                                    <option value="">All Levels</option>
                                    <option value="0">National</option>
                                    <option value="1">Assembly</option>
                                    <option value="2">Zone</option>
                                    <option value="3">Household</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Calendar Container -->
                    <div id="assemblyCalendar" style="min-height: 700px;"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="addEventForm" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addEventModalLabel">Add Assembly Event</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>          <div class="modal-body">
            <!-- Title -->
            <div class="mb-3">
              <label for="eventTitle" class="form-label">Title</label>
              <input type="text" id="eventTitle" name="title" class="form-control" required>
            </div>
            <!-- Assemblies Multi-select Checkbox Dropdown -->
            <div class="mb-3">
              <label for="assembliesDropdown" class="form-label">Assemblies</label>
              <div class="dropdown">
                <button class="form-select dropdown-toggle" type="button" id="assembliesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  Select Assemblies
                </button>
                <ul class="dropdown-menu w-100" aria-labelledby="assembliesDropdown" style="max-height: 300px; overflow-y: auto;">
                  <li>
                    <label class="dropdown-item">
                      <input type="checkbox" id="selectAllAssemblies"> <strong>Select All</strong>
                    </label>
                  </li>
                  <?php
                  try {
                      $stmt = $pdo->query("SELECT assembly_id, name FROM assemblies ORDER BY name ASC");
                      $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  } catch (PDOException $e) {
                      $assemblies = [];
                  }
                  foreach ($assemblies as $assembly): ?>
                    <li>
                      <label class="dropdown-item">
                        <input type="checkbox" name="assemblies[]" value="<?= htmlspecialchars($assembly['assembly_id']) ?>"> <?= htmlspecialchars($assembly['name']) ?>
                      </label>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <!-- Event Type -->
            <div class="mb-3">
              <label for="eventTypeSelect" class="form-label">Event Type</label>
              <select id="eventTypeSelect" name="event_type" class="form-select" required>
                <option value="">Select Event Type</option>
                <?php foreach ($event_types as $event_type): ?>
                  <option value="<?= htmlspecialchars($event_type['event_type_id']) ?>"><?= htmlspecialchars($event_type['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>            <!-- Start/End Date -->
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="startDate" class="form-label">Start Date & Time</label>
                <input type="datetime-local" id="startDate" name="start_date" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="endDate" class="form-label">End Date & Time</label>
                <input type="datetime-local" id="endDate" name="end_date" class="form-control" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="eventDescription" class="form-label">Description</label>
              <textarea id="eventDescription" name="description" class="form-control"></textarea>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="isRecurring" name="is_recurring">
              <label class="form-check-label" for="isRecurring">Recurring Event</label>
            </div>
            <div class="mb-3" id="frequencyField" style="display:none;">
              <label for="frequency" class="form-label">Frequency</label>
              <select id="frequency" name="frequency" class="form-select">
                <option value="">Select frequency</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Add Event</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
    <!-- View Event Modal -->
    <div class="modal fade" id="viewEventModal" tabindex="-1" aria-labelledby="viewEventModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 1.2rem;">
          <div class="modal-header bg-primary text-white" style="border-top-left-radius: 1.2rem; border-top-right-radius: 1.2rem;">
            <h5 class="modal-title" id="viewEventModalLabel"><i class="bi bi-calendar-event me-2"></i>Event Details</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4" id="viewEventModalBody">
            <div class="d-flex justify-content-center align-items-center" style="min-height:200px;">
              <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Edit Event Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content shadow-lg border-0" id="editEventForm" style="border-radius: 1.2rem;">
          <div class="modal-header text-white" style="border-top-left-radius: 1.2rem; border-top-right-radius: 1.2rem; background: linear-gradient(90deg, #007bff 0%, #00d4ff 100%);">
            <h5 class="modal-title" id="editEventModalLabel"><i class="bi bi-pencil me-2"></i>Edit Assembly Event</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4" id="editEventModalBody">
            <div class="d-flex justify-content-center align-items-center" style="min-height:200px;">
              <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn text-white" style="background: linear-gradient(90deg, #007bff 0%, #00d4ff 100%);">Save Changes</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
    <!-- Add/Edit/View/Delete Modals would go here (reuse from national) -->
</main>
<?php include "../../../includes/footer.php"; ?>
<style>
/* Tabs and table head: light mode (default) */
.nav-tabs .nav-link,
.table thead th,
.table thead td,
.table thead {
    color: #111 !important;
}
.nav-tabs .nav-link.active {
    background: linear-gradient(90deg, #007bff 0%, #00d4ff 100%) !important;
    color: #fff !important;
    border: none;
}
.table thead th,
.table thead td,
.table thead {
    background: #0d6efd !important;
    color: #fff !important;
}

/* Tabs: dark mode */
[data-bs-theme="dark"] .nav-tabs .nav-link {
    background: #0d6efd !important;
    color: #fff !important;
    border: none;
}
[data-bs-theme="dark"] .nav-tabs .nav-link.active {
    background: linear-gradient(90deg, #007bff 0%, #00d4ff 100%) !important;
    color: #fff !important;
    border: none;
}
[data-bs-theme="dark"] .table thead th,
[data-bs-theme="dark"] .table thead td,
[data-bs-theme="dark"] .table thead {
    background: #0d6efd !important;
    color: #fff !important;
}

/* Assembly name badge styling */
.assembly-name-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    line-height: 1.4;
    color: #fff;
    background: linear-gradient(90deg, #0d6efd 0%, #00d4ff 100%);
    border-radius: 999px;
    white-space: nowrap;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.15);
    transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.assembly-name-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0, 123, 255, 0.2);
}

/* Dark mode adjustments for assembly badges */
[data-bs-theme="dark"] .assembly-name-badge {
    box-shadow: 0 2px 4px rgba(0, 195, 255, 0.2);
}

[data-bs-theme="dark"] .assembly-name-badge:hover {
    box-shadow: 0 4px 6px rgba(0, 195, 255, 0.25);
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Show/hide frequency field based on recurring checkbox
  const recurring = document.getElementById('isRecurring');
  const freqField = document.getElementById('frequencyField');
  if (recurring && freqField) {
    recurring.addEventListener('change', function() {
      freqField.style.display = this.checked ? 'block' : 'none';
    });
  }  // Dropdown label update for assemblies
  const dropdown = document.getElementById('assembliesDropdown');
  const menu = dropdown ? dropdown.closest('.dropdown').querySelector('.dropdown-menu') : null; // More reliable way to get the dropdown menu
  function updateDropdownLabel() {
    if (!menu) return;
    const checkboxes = menu.querySelectorAll('input[name="assemblies[]"]');
    const checked = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.parentNode.textContent.trim());
    dropdown.textContent = checked.length ? checked.join(', ') : 'Select Assemblies';
  }
  if (dropdown && menu) {
    // Select All logic
    const selectAll = menu.querySelector('#selectAllAssemblies');
    if (selectAll) {
      selectAll.addEventListener('change', function() {
        const all = menu.querySelectorAll('input[name="assemblies[]"]');
        all.forEach(cb => { cb.checked = selectAll.checked; });
        updateDropdownLabel();
      });
    }
    // Individual checkbox logic
    menu.addEventListener('change', function(e) {
      if (e.target.name === 'assemblies[]') {
        // If any are unchecked, uncheck select all
        if (!e.target.checked) {
          if (selectAll) selectAll.checked = false;
        } else {
          // If all are checked, check select all
          const allBoxes = menu.querySelectorAll('input[name="assemblies[]"]');
          const allChecked = Array.from(allBoxes).every(cb => cb.checked);
          if (allChecked && selectAll) selectAll.checked = true;
        }
        updateDropdownLabel();
      }
    });    updateDropdownLabel();
  } else {
    console.log('Dropdown or menu not found:', { dropdown, menu });
  }
  // Handle Add Event Form Submission
  const addEventForm = document.getElementById('addEventForm');
  if (addEventForm) {
    addEventForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Log form submission attempt
      console.log('Assembly event form submission started');
      
      // Show loading state
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating...';
      
      // Collect selected assemblies
      const selectedAssemblies = [];
      const assemblyCheckboxes = menu.querySelectorAll('input[name="assemblies[]"]:checked');
      assemblyCheckboxes.forEach(checkbox => {
        selectedAssemblies.push(checkbox.value);
      });
      
      // Log collected data
      console.log('Form data collected:', {
        assembliesCount: selectedAssemblies.length,
        eventType: document.getElementById('eventTypeSelect').value,
        title: document.getElementById('eventTitle').value,
        startDate: document.getElementById('startDate').value,
        endDate: document.getElementById('endDate').value,
        isRecurring: document.getElementById('isRecurring').checked
      });
      
      // Log frontend request to backend for tracking
      fetch('add_event_process.php?log_only=1&action=frontend_request', { 
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
          timestamp: new Date().toISOString(),
          assemblies_count: selectedAssemblies.length,
          user_agent: navigator.userAgent 
        })
      }).catch(() => {}); // Silent fail for logging request
      
      // Validate at least one assembly is selected
      if (selectedAssemblies.length === 0) {
        console.error('Validation failed: No assemblies selected');
        showErrorMessage('Please select at least one assembly');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
        return;
      }
      
      // Prepare form data
      const formData = new FormData();
      selectedAssemblies.forEach(id => formData.append('assemblies[]', id));
      formData.append('event_type', document.getElementById('eventTypeSelect').value);
      formData.append('title', document.getElementById('eventTitle').value);
      formData.append('description', document.getElementById('eventDescription').value);
      formData.append('start_date', document.getElementById('startDate').value);
      formData.append('end_date', document.getElementById('endDate').value);
      
      if (document.getElementById('isRecurring').checked) {
        formData.append('is_recurring', '1');
        formData.append('frequency', document.getElementById('frequency').value);
      }
      // Submit via AJAX      console.log('Submitting form data to backend');
      fetch('add_event_process.php', {
        method: 'POST',
        body: formData
      })
      .then(response => {
        console.log('Received response from backend, status:', response.status);
        return response.json();
      })
      .then(data => {
        console.log('Backend response data:', data);
        if (data.success) {
          console.log('SUCCESS: Event(s) created successfully');
          // Close modal and show success message
          bootstrap.Modal.getInstance(document.getElementById('addEventModal')).hide();
          showSuccessMessage(data.message);
          
          // Reset form
          addEventForm.reset();
          updateDropdownLabel();
          document.getElementById('frequencyField').style.display = 'none';
            // Refresh events table (if you have a refresh function)
          // You may want to implement this similar to national events
          setTimeout(() => {
            console.log('Refreshing events view...');
            // Reload the current tab's events
            if (typeof loadPaginatedTabEvents === 'function') {
              const activeTab = document.querySelector('#eventViewTabs .nav-link.active').id;
              const tabName = activeTab.replace('-events-tab', '').replace('-view-tab', '');
              loadPaginatedTabEvents(tabName, 1);
              
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
                .catch(() => {});
            } else {
              // Fallback to page reload if function not available
              location.reload();
            }
          }, 1000);
          
        } else {
          console.error('Backend returned error:', data.message);
          showErrorMessage(data.message);
        }
      })
      .catch(error => {
        console.error('AJAX request failed:', error);
        showErrorMessage('Error creating event. Please try again.');
      })
      .finally(() => {
        console.log('Form submission completed');
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
      });
    });
  }

  // Utility functions for showing messages
  function showSuccessMessage(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3';
    alert.style.zIndex = '1050';
    alert.innerHTML = `
      <strong>Success!</strong> ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alert);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
      if (alert.parentNode) {
        alert.parentNode.removeChild(alert);
      }
    }, 5000);
  }

  function showErrorMessage(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3';
    alert.style.zIndex = '1050';
    alert.innerHTML = `
      <strong>Error!</strong> ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alert);
    
    // Auto-remove after 7 seconds
    setTimeout(() => {
      if (alert.parentNode) {
        alert.parentNode.removeChild(alert);
      }
    }, 7000);
  }
});
</script>
<script src="event-handlers.js"></script>

<!-- Delete Event Modal -->
<div class="modal fade" id="deleteEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient" style="background-image: linear-gradient(to right, #ff416c, #ff4b2b); color: white;">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill"></i> Delete Event</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteEventForm">
                <div class="modal-body text-center">
                    <input type="hidden" id="deleteEventId" name="event_id">
                    <div class="mb-4">
                        <i class="bi bi-trash-fill text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="mb-3">Are you sure?</h4>
                    <p class="text-muted">You are about to delete the event "<span id="deleteEventTitle" class="fw-bold"></span>".</p>
                    <p class="text-danger mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-trash me-2"></i> Delete Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="../calendar-utils.js"></script> 
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize calendar when tab is shown
        document.getElementById('calendar-view-tab')?.addEventListener('shown.bs.tab', function () {
            const calendarEl = document.getElementById('assemblyCalendar');
            if (!calendarEl) return;

            if (window.assemblyCalendar) {
                window.assemblyCalendar.destroy();
            }

            // Initialize the calendar with assembly-specific options
            window.assemblyCalendar = initializeCalendar(calendarEl, {
                eventClick: function(info) {
                    // Load event details when an event is clicked
                    const eventId = info.event.id;
                    loadEventForView(eventId);
                }
            });

            // Initialize filters with assembly context
            initializeFilters(window.assemblyCalendar, {
                level: 1 // Assembly level
            });

            // Enable dark mode support
            initializeDarkMode();

            // Render the calendar
            window.assemblyCalendar.render();
        });

        // Initialize immediately if calendar tab is active
        if (document.getElementById('calendar-view').classList.contains('show')) {
            document.getElementById('calendar-view-tab').dispatchEvent(new Event('shown.bs.tab'));
        }
    });
</script>
<style>
#assemblyCalendar {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 24px 0 rgba(0,0,0,0.10), 0 1.5px 4px 0 rgba(0,0,0,0.08);
    padding: 18px 8px 8px 8px;
    margin: 0 auto 24px auto;
    transition: box-shadow 0.2s;
}
.fc .fc-toolbar-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #222;
}
.fc .fc-daygrid-day {
    border-radius: 8px;
    transition: background 0.2s;
}
.fc .fc-daygrid-day:hover {
    background: #f0f4ff;
}
.fc .fc-event {
    border-radius: 8px;
    padding: 2px 4px;
    margin: 1px 2px;
    border: none;
    transition: transform 0.15s ease;
}
.fc .fc-event:hover {
    transform: scale(1.02);
}
.fc .fc-daygrid-event-dot {
    border-radius: 50%;
}
.fc .fc-daygrid-day-number {
    font-weight: 500;
}
@media (prefers-color-scheme: dark) {
    #assemblyCalendar {
        background: #23272f;
        color: #fff;
    }
    .fc .fc-toolbar-title {
        color: #fff;
    }
    .fc .fc-daygrid-day {
        background: #23272f;
    }
    .fc .fc-daygrid-day:hover {
        background: #2a2e38;
    }
}

/* Support for explicit dark theme setting in addition to prefers-color-scheme */
[data-bs-theme="dark"] #assemblyCalendar {
    background: #23272f;
    color: #fff;
}
[data-bs-theme="dark"] .fc .fc-toolbar-title {
    color: #fff;
}
[data-bs-theme="dark"] .fc .fc-daygrid-day {
    background: #23272f;
}
[data-bs-theme="dark"] .fc .fc-daygrid-day:hover {
    background: #2a2e38;
}
</style>

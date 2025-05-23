<?php
session_start();
$page_title = "Event Types Management";
include "../../../config/db.php";

// Fetch event types
try {
    $stmt = $pdo->query("
        SELECT event_type_id, name, description, default_frequency, level, is_recurring, created_at, updated_at
        FROM event_types
        ORDER BY name ASC
        LIMIT 10
    ");
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching event types: " . $e->getMessage();
    exit;
}

// Fetch total event types count
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_event_types FROM event_types");
    $total_event_types = $stmt->fetch(PDO::FETCH_ASSOC)['total_event_types'];
} catch (PDOException $e) {
    echo "Error fetching total event types: " . $e->getMessage();
    exit;
}

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
<body class="d-flex flex-column min-vh-100">
<main class="container flex-grow-1 py-2">
    <!-- Success/Error Alerts -->
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
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/members/index.php" class="nav-link-btn">
                        <i class="bi bi-people-fill text-primary"></i>
                        <span>Members</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/assemblies/index.php" class="nav-link-btn">
                        <i class="bi bi-building-fill text-success"></i>
                        <span>Assemblies</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/households/index.php" class="nav-link-btn">
                        <i class="bi bi-house-fill text-warning"></i>
                        <span>Households</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/zones/index.php" class="nav-link-btn">
                        <i class="bi bi-globe-americas text-info"></i>
                        <span>Zones</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/events/index.php" class="nav-link-btn">
                        <i class="bi bi-calendar-event text-danger"></i>
                        <span>Events</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/event_types/index.php" class="nav-link-btn">
                        <i class="bi bi-tags-fill text-secondary"></i>
                        <span>Event Types</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mini Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #007bff, #00d4ff); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-tags-fill fs-2"></i>
                    <h6 class="card-title text-white">Total Event Types</h6>
                    <h3 class="card-text" id="totalEventTypesCount"><?= $total_event_types ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #28a745, #6fcf97); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-repeat fs-2"></i>
                    <h6 class="card-title text-white">Recurring Event Types</h6>
                    <h3 class="card-text" id="recurringEventTypesCount">
                        <?= count(array_filter($event_types, fn($et) => $et['is_recurring'])) ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #dc3545, #ff6b6b); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-building fs-2"></i>
                    <h6 class="card-title text-white">Assembly-Level Types</h6>
                    <h3 class="card-text" id="assemblyLevelTypesCount">
                        <?= count(array_filter($event_types, fn($et) => $et['level'] === 'assembly')) ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Event Types Management</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addEventTypeModal">
                        <i class="bi bi-plus-circle"></i> Add New Event Type
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="filterName" class="form-label"><i class="bi bi-search me-2"></i>Name</label>
                    <input type="text" class="form-control" id="filterName" placeholder="Search by name">
                </div>
                <div class="col-md-3">
                    <label for="filterLevel" class="form-label"><i class="bi bi-globe me-2"></i>Level</label>
                    <select class="form-select" id="filterLevel">
                        <option value="">All Levels</option>
                        <option value="household">Household</option>
                        <option value="assembly">Assembly</option>
                        <option value="zone">Zone</option>
                        <option value="national">National</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filterRecurring" class="form-label"><i class="bi bi-arrow-repeat me-2"></i>Recurring</label>
                    <select class="form-select" id="filterRecurring">
                        <option value="">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="eventTypesTable">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Default Frequency</th>
                            <th>Level</th>
                            <th>Recurring</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($event_types as $index => $event_type): ?>
                            <tr data-id="<?= $event_type['event_type_id'] ?>"
                                data-name="<?= htmlspecialchars($event_type['name']) ?>"
                                data-description="<?= htmlspecialchars($event_type['description'] ?? '') ?>"
                                data-default-frequency="<?= htmlspecialchars($event_type['default_frequency'] ?? '') ?>"
                                data-level="<?= htmlspecialchars($event_type['level'] ?? '') ?>"
                                data-is-recurring="<?= $event_type['is_recurring'] ? 'Yes' : 'No' ?>">
                                <td><input type="checkbox" class="row-checkbox" data-id="<?= $event_type['event_type_id'] ?>"></td>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <button class="btn btn-gradient-blue text-nowrap clickable" style="min-width: 150px;" data-event-type-id="<?= $event_type['event_type_id'] ?>">
                                        <?= htmlspecialchars($event_type['name']) ?>
                                    </button>
                                </td>
                                <td><?= htmlspecialchars($event_type['description'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($event_type['default_frequency'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($event_type['level'] ?? 'N/A') ?></td>
                                <td><?= $event_type['is_recurring'] ? 'Yes' : 'No' ?></td>
                                <td><?= htmlspecialchars($event_type['created_at']) ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#viewEventTypeModal<?= $event_type['event_type_id'] ?>">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary load-edit-modal" data-event-type-id="<?= $event_type['event_type_id'] ?>" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-event-type-btn" data-event-type-id="<?= $event_type['event_type_id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteEventTypeModal-<?= $event_type['event_type_id'] ?>" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination Placeholder -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="paginationControls"></ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Add Event Type Modal -->
    <div class="modal fade" id="addEventTypeModal" tabindex="-1" aria-labelledby="addEventTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addEventTypeModalLabel">Add New Event Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addEventTypeForm">
                        <div class="mb-3">
                            <label for="eventTypeName" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="eventTypeName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="eventTypeDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="eventTypeDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="defaultFrequency" class="form-label">Default Frequency</label>
                            <select class="form-select" id="defaultFrequency" name="default_frequency">
                                <option value="">-- Select Frequency --</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="eventTypeLevel" class="form-label">Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="eventTypeLevel" name="level" required>
                                <option value="">-- Select Level --</option>
                                <option value="household">Household</option>
                                <option value="assembly">Assembly</option>
                                <option value="zone">Zone</option>
                                <option value="national">National</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isRecurring" name="is_recurring">
                                <label class="form-check-label" for="isRecurring">Recurring Event Type</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Event Type</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- All View Modals (placed outside the table for stability) -->
    <?php foreach ($event_types as $event_type): ?>
    <div class="modal fade" id="viewEventTypeModal<?= $event_type['event_type_id'] ?>" tabindex="-1" aria-labelledby="viewEventTypeModalLabel<?= $event_type['event_type_id'] ?>" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title" id="viewEventTypeModalLabel<?= $event_type['event_type_id'] ?>">View Event Type</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <dl class="row">
              <dt class="col-sm-4">Name</dt>
              <dd class="col-sm-8"><?= htmlspecialchars($event_type['name']) ?></dd>
              <dt class="col-sm-4">Description</dt>
              <dd class="col-sm-8"><?= htmlspecialchars($event_type['description'] ?? 'N/A') ?></dd>
              <dt class="col-sm-4">Level</dt>
              <dd class="col-sm-8 text-capitalize"><?= htmlspecialchars($event_type['level']) ?></dd>
              <dt class="col-sm-4">Default Frequency</dt>
              <dd class="col-sm-8"><?= htmlspecialchars($event_type['default_frequency']) ?></dd>
              <dt class="col-sm-4">Is Recurring</dt>
              <dd class="col-sm-8"><?= $event_type['is_recurring'] ? 'Yes' : 'No' ?></dd>
            </dl>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
</main>

<?php include "../../../includes/footer.php"; ?>

<style>
    .nav-card { background-color: #ffffff; border: none; box-shadow: 0 -2px 6px -2px rgba(0, 0, 0, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 10px; padding: 20px; margin-bottom: 20px; margin-top: -30px; position: relative; top: -10px; }
    .nav-card .nav-link-btn { display: flex; flex-direction: column; align-items: center; text-align: center; text-decoration: none; color: #333; padding: 15px; border-radius: 8px; transition: background-color 0.3s, transform 0.2s; }
    .nav-link-btn:hover { background-color: #f1f3f5; transform: scale(1.05); }
    .nav-link-btn i { font-size: 1.5rem; margin-bottom: 8px; }
    .nav-link-btn span { font-size: 0.9rem; font-weight: 500; }
    [data-bs-theme="dark"] .nav-card { background-color: var(--card-bg-dark); }
    [data-bs-theme="dark"] .nav-link-btn { color: #e0e0e0; }
    [data-bs-theme="dark"] .nav-link-btn:hover { background-color: rgba(255, 255, 255, 0.1); }
    .btn-gradient-blue { background: linear-gradient(45deg, #007bff, #00d4ff); color: white; border: none; padding: 5px 10px; border-radius: 5px; transition: transform 0.3s; min-width: 150px; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .btn-gradient-blue.clickable:hover { transform: scale(1.05); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); cursor: pointer; }
    .btn-gradient-blue:hover { transform: scale(1.05); }
    .card { border: 1px solid #e0e0e0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: box-shadow 0.3s ease; }
    @media (prefers-color-scheme: dark) { .card { border: 1px solid #4a90e2 !important; box-shadow: 0 4px 12px -2px rgba(74, 144, 226, 0.5), 0 0 20px rgba(74, 144, 226, 0.3) !important; } }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JavaScript is not loaded.');
        return;
    }

    // Select-all checkbox functionality
    document.getElementById('select-all').addEventListener('change', function () {
        document.querySelectorAll('.row-checkbox').forEach(checkbox => checkbox.checked = this.checked);
    });

    // Load edit modal dynamically
    document.querySelectorAll('.load-edit-modal').forEach(button => {
        button.addEventListener('click', function () {
            const eventTypeId = this.getAttribute('data-event-type-id');
            fetch(`edit_event_type.php?event_type_id=${eventTypeId}`)
                .then(response => response.text())
                .then(html => {
                    document.body.insertAdjacentHTML('beforeend', html);
                    const modal = new bootstrap.Modal(document.getElementById(`editEventTypeModal-${eventTypeId}`));
                    modal.show();
                })
                .catch(error => console.error('Error loading modal:', error));
        });
    });

    // Pagination and filtering setup
    let currentPage = 1;
    const recordsPerPage = 10;

    function fetchPaginatedEventTypes(page) {
        const filterName = document.getElementById('filterName').value.toLowerCase();
        const filterLevel = document.getElementById('filterLevel').value;
        const filterRecurring = document.getElementById('filterRecurring').value;

        const params = new URLSearchParams({
            page: page,
            name: filterName,
            level: filterLevel,
            recurring: filterRecurring
        });

        return fetch(`fetch_paginated_event_types.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderTable(data.event_types);
                    generatePaginationControls(page, data.pagination.total_pages);
                    updateDashboardCounts(data.pagination.total_event_types, data.event_types);
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching event types:', error);
                showAlert('danger', 'Failed to load event types: ' + error.message);
            });
    }

    function updateDashboardCounts(totalEventTypes, eventTypes) {
        const recurringEventTypes = eventTypes.filter(et => et.is_recurring).length;
        const assemblyLevelTypes = eventTypes.filter(et => et.level === 'assembly').length;

        document.getElementById('totalEventTypesCount').textContent = totalEventTypes;
        document.getElementById('recurringEventTypesCount').textContent = recurringEventTypes;
        document.getElementById('assemblyLevelTypesCount').textContent = assemblyLevelTypes;
    }

    function renderTable(eventTypes) {
        const tbody = document.querySelector('#eventTypesTable tbody');
        tbody.innerHTML = '';
        eventTypes.forEach((eventType, index) => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', eventType.event_type_id);
            row.setAttribute('data-name', eventType.name);
            row.setAttribute('data-description', eventType.description || '');
            row.setAttribute('data-default-frequency', eventType.default_frequency || '');
            row.setAttribute('data-level', eventType.level || '');
            row.setAttribute('data-is-recurring', eventType.is_recurring ? 'Yes' : 'No');

            row.innerHTML = `
                <td><input type="checkbox" class="row-checkbox" data-id="${eventType.event_type_id}"></td>
                <td>${((currentPage - 1) * recordsPerPage) + index + 1}</td>
                <td>
                    <button class="btn btn-gradient-blue text-nowrap clickable" style="min-width: 150px;" data-event-type-id="${eventType.event_type_id}">
                        ${eventType.name}
                    </button>
                </td>
                <td>${eventType.description || 'N/A'}</td>
                <td>${eventType.default_frequency || 'N/A'}</td>
                <td>${eventType.level || 'N/A'}</td>
                <td>${eventType.is_recurring ? 'Yes' : 'No'}</td>
                <td>${eventType.created_at}</td>
                <td>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#viewEventTypeModal${eventType.event_type_id}">
                            <i class="bi bi-eye"></i> View
                        </button>
                        <button class="btn btn-sm btn-outline-primary load-edit-modal" data-event-type-id="${eventType.event_type_id}" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-event-type-btn" data-event-type-id="${eventType.event_type_id}" data-bs-toggle="modal" data-bs-target="#deleteEventTypeModal-${eventType.event_type_id}" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);

            const deleteModal = document.createElement('div');
            deleteModal.className = 'modal fade';
            deleteModal.id = `deleteEventTypeModal-${eventType.event_type_id}`;
            deleteModal.setAttribute('tabindex', '-1');
            deleteModal.setAttribute('aria-labelledby', `deleteEventTypeModalLabel-${eventType.event_type_id}`);
            deleteModal.setAttribute('aria-hidden', 'true');
            deleteModal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="deleteEventTypeModalLabel-${eventType.event_type_id}">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete the event type <strong>${eventType.name}</strong>?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                            <button type="button" class="btn btn-danger confirm-delete-btn" data-event-type-id="${eventType.event_type_id}">Yes</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(deleteModal);
        });

        attachEventListeners();
    }

    function attachEventListeners() {
        document.querySelectorAll('.load-edit-modal').forEach(button => {
            button.addEventListener('click', function() {
                const eventTypeId = this.getAttribute('data-event-type-id');
                fetch(`edit_event_type.php?event_type_id=${eventTypeId}`)
                    .then(response => response.text())
                    .then(html => {
                        document.body.insertAdjacentHTML('beforeend', html);
                        new bootstrap.Modal(document.getElementById(`editEventTypeModal-${eventTypeId}`)).show();
                    })
                    .catch(error => console.error('Error loading modal:', error));
            });
        });

        document.querySelectorAll('.delete-event-type-btn').forEach(button => {
            button.addEventListener('click', function() {
                const eventTypeId = this.getAttribute('data-event-type-id');
                const modal = document.getElementById(`deleteEventTypeModal-${eventTypeId}`);
                if (modal) new bootstrap.Modal(modal).show();
            });
        });

        document.querySelectorAll('.confirm-delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const eventTypeId = this.getAttribute('data-event-type-id');
                deleteEventType(eventTypeId);
            });
        });

        document.querySelectorAll('.clickable').forEach(button => {
            button.addEventListener('click', function() {
                const eventTypeId = this.getAttribute('data-event-type-id');
                // Add view modal logic here if needed
            });
        });
    }

    function generatePaginationControls(currentPage, totalPages) {
        const paginationContainer = document.getElementById('paginationControls');
        paginationContainer.innerHTML = '';
        const maxPagesToShow = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous"><span aria-hidden="true">«</span></a>`;
        paginationContainer.appendChild(prevLi);

        for (let i = startPage; i <= endPage; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            paginationContainer.appendChild(pageLi);
        }

        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next"><span aria-hidden="true">»</span></a>`;
        paginationContainer.appendChild(nextLi);

        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page >= 1 && page <= totalPages) {
                    currentPage = page;
                    fetchPaginatedEventTypes(currentPage);
                }
            });
        });
    }

    function deleteEventType(eventTypeId) {
        fetch('delete_event_type_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `event_type_id=${encodeURIComponent(eventTypeId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById(`deleteEventTypeModal-${eventTypeId}`));
                modal.hide();
                fetchPaginatedEventTypes(currentPage);
            } else {
                showAlert('danger', 'Error: ' + data.message);
            }
        })
        .catch(error => showAlert('danger', 'Network error: ' + error.message));
    }

    const addEventTypeModal = new bootstrap.Modal(document.getElementById('addEventTypeModal'));

    // Initial load
    fetchPaginatedEventTypes(currentPage);

    const filterName = document.getElementById('filterName');
    const filterLevel = document.getElementById('filterLevel');
    const filterRecurring = document.getElementById('filterRecurring');

    function applyFilters() {
        currentPage = 1;
        fetchPaginatedEventTypes(currentPage);
    }

    filterName.addEventListener('input', applyFilters);
    filterLevel.addEventListener('change', applyFilters);
    filterRecurring.addEventListener('change', applyFilters);

    document.getElementById('addEventTypeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('add_event_type_process.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    addEventTypeModal.hide();
                    fetchPaginatedEventTypes(currentPage);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => showAlert('danger', 'An error occurred while adding the event type.'));
    });

    function showAlert(type, message) {
        const alertContainer = document.createElement('div');
        alertContainer.className = `alert alert-${type} alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3`;
        alertContainer.style.zIndex = '1050';
        alertContainer.role = 'alert';
        alertContainer.innerHTML = `
            <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertContainer);
        setTimeout(() => {
            alertContainer.classList.add('fade');
            setTimeout(() => alertContainer.remove(), 150);
        }, 5000);
    }
});
</script>
</body>
</html>
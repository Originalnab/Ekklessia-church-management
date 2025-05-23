<?php
session_start();
$page_title = "Assign Leader to Household";
include "../../../config/db.php";

// Fetch all households with their assembly, leader, assistant leaders, total members, and date created
try {
    $stmt = $pdo->query("
        SELECT 
            h.household_id, 
            h.name AS household_name, 
            h.date_started AS date_created, 
            h.assembly_id,
            a.name AS assembly_name,
            (SELECT COUNT(*) FROM member_household mh WHERE mh.household_id = h.household_id) AS total_members,
            (SELECT CONCAT(m.first_name, ' ', m.last_name) 
             FROM members m 
             JOIN household_shepherdhead_assignments hsa ON m.member_id = hsa.shepherd_member_id 
             WHERE hsa.household_id = h.household_id LIMIT 1) AS leader_name,
            (SELECT GROUP_CONCAT(CONCAT(m.first_name, ' ', m.last_name) SEPARATOR ', ') 
             FROM members m 
             JOIN household_assistant_assignments haa ON m.member_id = haa.assistant_member_id 
             WHERE haa.household_id = h.household_id) AS assistant_names,
            (SELECT hsa.shepherd_member_id 
             FROM household_shepherdhead_assignments hsa 
             WHERE hsa.household_id = h.household_id LIMIT 1) AS leader_id,
            (SELECT GROUP_CONCAT(haa.assistant_member_id) 
             FROM household_assistant_assignments haa 
             WHERE haa.household_id = h.household_id) AS assistant_ids
        FROM households h
        LEFT JOIN assemblies a ON h.assembly_id = a.assembly_id
        ORDER BY h.name ASC
    ");
    $households = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mini dashboard calculations
    $total_households = count($households);
    $assigned_leaders = count(array_filter($households, fn($h) => !empty($h['leader_id'])));
    $assigned_assistants = count(array_filter($households, fn($h) => !empty($h['assistant_ids'])));
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching households: " . $e->getMessage();
    $households = [];
}

// Fetch assemblies for the modal dropdown and filters
try {
    $stmt = $pdo->query("SELECT assembly_id, name FROM assemblies ORDER BY name ASC");
    $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching assemblies: " . $e->getMessage();
    $assemblies = [];
}

// Base URL for navigation links
$base_url = '/Ekklessia-church-management/app/pages';

// Check for success or error messages in session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
unset($_SESSION['success_message']);
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
<body class="d-flex flex-column min-vh-100">

<main class="container flex-grow-1 py-2">
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Success!</strong> <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 1050;">
            <strong>Error!</strong> <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card nav-card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2"><a href="<?= $base_url ?>/tpd/members/index.php" class="nav-link-btn"><i class="bi bi-people-fill text-primary"></i><span>Members</span></a></div>
                <div class="col-6 col-md-4 col-lg-2"><a href="<?= $base_url ?>/tpd/assemblies/index.php" class="nav-link-btn"><i class="bi bi-building-fill text-success"></i><span>Assemblies</span></a></div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="dropdown">
                        <a class="nav-link-btn dropdown-toggle" href="#" role="button" id="householdsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-house-fill text-warning"></i><span>Households</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="householdsDropdown">
                            <li><a class="dropdown-item" href="<?= $base_url ?>/tpd/households/index.php"><i class="bi bi-house-fill me-2"></i>View Households</a></li>
                            <li><a class="dropdown-item" href="<?= $base_url ?>/tpd/households/assign_leader.php"><i class="bi bi-person-check-fill me-2"></i>Assign Leader</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2"><a href="<?= $base_url ?>/tpd/zones/index.php" class="nav-link-btn"><i class="bi bi-globe-americas text-info"></i><span>Zones</span></a></div>
            </div>
        </div>
    </div>

    <!-- Mini Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #007bff, #00d4ff); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-house fs-2"></i>
                    <h6 class="card-title text-white">Total Households</h6>
                    <h3 class="card-text"><?= $total_households ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #28a745, #6fcf97); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-person-check fs-2"></i>
                    <h6 class="card-title text-white">Assigned Leaders</h6>
                    <h3 class="card-text"><?= $assigned_leaders ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #17a2b8, #4fc3f7); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-person-check fs-2"></i>
                    <h6 class="card-title text-white">Assigned Assistants</h6>
                    <h3 class="card-text"><?= $assigned_assistants ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Assign Leader to Household</h4>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text filter-icon"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search households..." id="searchFilter">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text filter-icon"><i class="bi bi-building"></i></span>
                        <select class="form-select" id="assemblyFilter">
                            <option value="">All Assemblies</option>
                            <?php foreach ($assemblies as $assembly): ?>
                                <option value="<?= $assembly['assembly_id'] ?>"><?= htmlspecialchars($assembly['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text filter-icon"><i class="bi bi-person-check"></i></span>
                        <select class="form-select" id="leaderStatusFilter">
                            <option value="">All Leader Status</option>
                            <option value="assigned">Assigned</option>
                            <option value="unassigned">Unassigned</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text filter-icon"><i class="bi bi-calendar"></i></span>
                        <input type="date" class="form-control" id="startDateFilter" placeholder="Start Date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text filter-icon"><i class="bi bi-calendar"></i></span>
                        <input type="date" class="form-control" id="endDateFilter" placeholder="End Date">
                    </div>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-primary w-100" id="clearFilters">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover align-middle" id="householdTable">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Household Name</th>
                            <th>Assembly</th>
                            <th>Total Members</th>
                            <th>Date Created</th>
                            <th>Leader Name</th>
                            <th>Assistant Name(s)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($households)): ?>
                            <tr><td colspan="8" class="text-center text-muted">No households found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($households as $index => $household): ?>
                                <?php $date_created = $household['date_created'] ? (new DateTime($household['date_created']))->format('jS F, Y') : 'N/A'; ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($household['household_name']) ?></td>
                                    <td><?= htmlspecialchars($household['assembly_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($household['total_members']) ?></td>
                                    <td class="date-created" data-date="<?= htmlspecialchars($household['date_created'] ?? '') ?>"><?= $date_created ?></td>
                                    <td><?= htmlspecialchars($household['leader_name'] ?? 'Not Assigned') ?></td>
                                    <td><?= htmlspecialchars($household['assistant_names'] ?? 'Not Assigned') ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-person-check"></i> Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#assignLeaderModal-<?= $household['household_id'] ?>" data-mode="assign" data-assembly-id="<?= $household['assembly_id'] ?>" data-household-id="<?= $household['household_id'] ?>" data-household-name="<?= htmlspecialchars($household['household_name']) ?>"><i class="bi bi-person-plus me-2"></i>Assign Leaders</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#assignLeaderModal-<?= $household['household_id'] ?>" data-mode="edit" data-leader-id="<?= $household['leader_id'] ?? '' ?>" data-assistant-ids="<?= $household['assistant_ids'] ?? '' ?>" data-assembly-id="<?= $household['assembly_id'] ?>" data-household-id="<?= $household['household_id'] ?>" data-household-name="<?= htmlspecialchars($household['household_name']) ?>"><i class="bi bi-pencil me-2"></i>Edit Leaders</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="assignLeaderModal-<?= $household['household_id'] ?>" tabindex="-1" aria-labelledby="assignLeaderModalLabel-<?= $household['household_id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title" id="assignLeaderModalLabel-<?= $household['household_id'] ?>">Assign Leader and Assistants for <?= htmlspecialchars($household['household_name']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form id="assignForm-<?= $household['household_id'] ?>" method="POST" action="assign_leader_process.php">
                                                    <input type="hidden" name="household_id" value="<?= $household['household_id'] ?>">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label for="modal_assembly_id-<?= $household['household_id'] ?>" class="form-label">Assembly</label>
                                                            <select class="form-select" id="modal_assembly_id-<?= $household['household_id'] ?>" name="modal_assembly_id" disabled>
                                                                <option value="">Select Assembly</option>
                                                                <?php foreach ($assemblies as $assembly): ?>
                                                                    <option value="<?= $assembly['assembly_id'] ?>" <?= $assembly['assembly_id'] == $household['assembly_id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($assembly['name']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="modal_household_id-<?= $household['household_id'] ?>" class="form-label">Household</label>
                                                            <select class="form-select" id="modal_household_id-<?= $household['household_id'] ?>" name="modal_household_id" disabled>
                                                                <option value="<?= $household['household_id'] ?>"><?= htmlspecialchars($household['household_name']) ?></option>
                                                            </select>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Select Leader and Assistants</label>
                                                            <div class="mb-3">
                                                                <label class="form-label">Select Leader (Shepherd)</label>
                                                                <select class="form-select" id="leader-select-<?= $household['household_id'] ?>" name="leader_id" required>
                                                                    <option value="">Select Leader</option>
                                                                    <!-- Populated by JS -->
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Select Assistant Leaders (Select at least 1)</label>
                                                                <div id="assistant-list-<?= $household['household_id'] ?>" class="assistant-leaders-list">
                                                                    <!-- Populated by JS -->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Save Assignment</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<style>
    .nav-card { background-color: #ffffff; border: none; box-shadow: 0 -2px 6px -2px rgba(0, 0, 0, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 10px; padding: 20px; margin-bottom: 20px; }
    .nav-card .nav-link-btn { display: flex; flex-direction: column; align-items: center; text-align: center; text-decoration: none; color: #333; padding: 15px; border-radius: 8px; transition: background-color 0.3s, transform 0.2s; }
    .nav-link-btn:hover { background-color: #f1f3f5; transform: scale(1.05); }
    .nav-link-btn i { font-size: 1.5rem; margin-bottom: 8px; }
    .nav-link-btn span { font-size: 0.9rem; font-weight: 500; }
    [data-bs-theme="dark"] .nav-card { background-color: var(--card-bg-dark); }
    [data-bs-theme="dark"] .nav-link-btn { color: #e0e0e0; }
    [data-bs-theme="dark"] .nav-link-btn:hover { background-color: rgba(255, 255, 255, 0.1); }
    .table { border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
    .table thead th { background-color: #343a40; color: #ffffff; text-align: center; vertical-align: middle; border-bottom: 2px solid #dee2e6; }
    .table tbody tr { transition: background-color 0.3s ease; }
    .table tbody tr:hover { background-color: #f8f9fa; }
    .table tbody td { vertical-align: middle; text-align: center; }
    .table .btn-group .btn { font-size: 0.875rem; padding: 0.25rem 0.5rem; }
    .table .dropdown-menu { min-width: 150px; }
    .table .dropdown-item i { margin-right: 8px; }
    .filter-icon { background-color: #007bff; color: white; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script>
function fetchMembers(householdId, leaderSelectId, assistantListId, preselectedLeaderId = null, preselectedAssistantIds = []) {
    const leaderSelect = document.getElementById(leaderSelectId);
    const assistantList = document.getElementById(assistantListId);

    fetch('/Ekklessia-church-management/app/pages/tpd/households/fetch_members.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'household_id=' + encodeURIComponent(householdId)
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        console.log('fetch_members.php response:', data);
        if (data.error || !Array.isArray(data) || data.length === 0) {
            assistantList.innerHTML = '<p class="text-muted">No members found or error: ' + (data.error || 'Empty result') + '</p>';
            return;
        }

        // Populate leader select
        leaderSelect.innerHTML = '<option value="">Select Leader</option>';
        data.forEach(member => {
            const isSelected = preselectedLeaderId == member.member_id ? 'selected' : '';
            leaderSelect.innerHTML += `<option value="${member.member_id}" ${isSelected}>${member.first_name} ${member.last_name}</option>`;
        });

        // Populate assistant checkboxes
        assistantList.innerHTML = '';
        data.forEach(member => {
            const isChecked = preselectedAssistantIds.includes(String(member.member_id)) ? 'checked' : '';
            assistantList.innerHTML += `
                <div class="form-check">
                    <input class="form-check-input assistant-checkbox" type="checkbox" name="assistant_ids[]" value="${member.member_id}" id="assistant-${member.member_id}-${householdId}" ${isChecked}>
                    <label class="form-check-label" for="assistant-${member.member_id}-${householdId}">
                        ${member.first_name} ${member.last_name}
                    </label>
                </div>
            `;
        });

        // Log checkbox changes
        const assistantCheckboxes = document.querySelectorAll(`#${assistantListId} .assistant-checkbox`);
        assistantCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                console.log(`Assistant ID: ${this.value}, Checked: ${this.checked}`);
            });
        });

        // Log leader change
        leaderSelect.addEventListener('change', function() {
            console.log(`Selected Leader ID: ${this.value}`);
        });
    })
    .catch(error => {
        console.error('Fetch error:', error);
        assistantList.innerHTML = '<p class="text-danger">Error loading members: ' + error.message + '</p>';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.dropdown-toggle').forEach(button => {
        const dropdown = new bootstrap.Dropdown(button);
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                dropdown.show();
            }
        });
    });

    document.addEventListener('click', function(e) {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            const dropdownToggle = menu.previousElementSibling;
            if (dropdownToggle && !dropdownToggle.contains(e.target) && !menu.contains(e.target)) {
                const dropdown = bootstrap.Dropdown.getInstance(dropdownToggle);
                if (dropdown) dropdown.hide();
            }
        });
    });

    document.querySelectorAll('[data-bs-target^="#assignLeaderModal-"]').forEach(button => {
        button.addEventListener('click', function() {
            const mode = this.getAttribute('data-mode');
            const modalId = this.getAttribute('data-bs-target').substring(1);
            const modal = document.getElementById(modalId);
            const modalTitle = modal.querySelector('.modal-title');
            const householdId = modalId.replace('assignLeaderModal-', '');
            const assemblySelect = document.getElementById(`modal_assembly_id-${householdId}`);
            const householdSelect = document.getElementById(`modal_household_id-${householdId}`);
            const leaderSelectId = `leader-select-${householdId}`;
            const assistantListId = `assistant-list-${householdId}`;

            if (mode === 'assign') {
                modalTitle.textContent = `Assign Leader and Assistants for ${modalTitle.textContent.split('for ')[1]}`;
                const assemblyId = this.getAttribute('data-assembly-id');
                const preselectedHouseholdId = this.getAttribute('data-household-id');
                if (assemblyId) assemblySelect.value = assemblyId;
                householdSelect.value = preselectedHouseholdId;
                if (preselectedHouseholdId) fetchMembers(preselectedHouseholdId, leaderSelectId, assistantListId);
            } else if (mode === 'edit') {
                modalTitle.textContent = `Edit Leader and Assistants for ${modalTitle.textContent.split('for ')[1]}`;
                const assemblyId = this.getAttribute('data-assembly-id');
                const preselectedHouseholdId = this.getAttribute('data-household-id');
                const preselectedLeaderId = this.getAttribute('data-leader-id');
                const preselectedAssistantIds = this.getAttribute('data-assistant-ids') ? this.getAttribute('data-assistant-ids').split(',') : [];
                if (assemblyId) assemblySelect.value = assemblyId;
                householdSelect.value = preselectedHouseholdId;
                if (preselectedHouseholdId) fetchMembers(preselectedHouseholdId, leaderSelectId, assistantListId, preselectedLeaderId, preselectedAssistantIds);
            }

            // Enhanced form submission with alert and fallback
            const form = document.getElementById(`assignForm-${householdId}`);
            form.addEventListener('submit', function(e) {
                const leaderValue = document.getElementById(leaderSelectId).value;
                const assistantCheckboxes = document.querySelectorAll(`#${assistantListId} .assistant-checkbox`);
                const checkedAssistants = Array.from(assistantCheckboxes).filter(cb => cb.checked);
                const assistantIds = checkedAssistants.map(cb => cb.value);

                // Show alert with leader_id and assistant_ids
                const alertMessage = `Form Data:\nLeader ID: ${leaderValue}\nAssistant IDs: ${assistantIds.length > 0 ? assistantIds.join(', ') : 'None selected'}`;
                alert(alertMessage);

                // Fallback: Ensure assistant_ids are included in the form
                if (checkedAssistants.length > 0) {
                    // Remove any existing hidden inputs to avoid duplicates
                    const existingHiddenInputs = form.querySelectorAll('input[name="assistant_ids[]"][type="hidden"]');
                    existingHiddenInputs.forEach(input => input.remove());
                    // Add hidden inputs for checked assistants
                    checkedAssistants.forEach(cb => {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'assistant_ids[]';
                        hiddenInput.value = cb.value;
                        form.appendChild(hiddenInput);
                    });
                }

                // Validation
                if (!leaderValue) {
                    e.preventDefault();
                    alert('Please select a leader.');
                    return;
                }
                if (checkedAssistants.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one assistant.');
                    return;
                }

                // Log final form data for confirmation
                const formData = new FormData(this);
                console.log('Final Form Data before submission:');
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }
            });
        });
    });

    // Filter functionality
    const searchFilter = document.getElementById('searchFilter');
    const assemblyFilter = document.getElementById('assemblyFilter');
    const leaderStatusFilter = document.getElementById('leaderStatusFilter');
    const startDateFilter = document.getElementById('startDateFilter');
    const endDateFilter = document.getElementById('endDateFilter');
    const clearFilters = document.getElementById('clearFilters');
    const tableBody = document.querySelector('#householdTable tbody');

    function applyFilters() {
        const searchText = searchFilter.value.toLowerCase();
        const selectedAssembly = assemblyFilter.value;
        const leaderStatus = leaderStatusFilter.value;
        const startDate = startDateFilter.value;
        const endDate = endDateFilter.value;

        Array.from(tableBody.children).forEach(row => {
            const householdName = row.cells[1].textContent.toLowerCase();
            const assembly = row.cells[2].textContent;
            const leaderName = row.cells[5].textContent;
            const dateCreated = row.cells[4].getAttribute('data-date');

            let matchesSearch = householdName.includes(searchText);
            let matchesAssembly = !selectedAssembly || assembly === document.querySelector(`#assemblyFilter option[value="${selectedAssembly}"]`).textContent;
            let matchesLeaderStatus = !leaderStatus || 
                (leaderStatus === 'assigned' && leaderName !== 'Not Assigned') || 
                (leaderStatus === 'unassigned' && leaderName === 'Not Assigned');
            let matchesDate = true;
            if (startDate || endDate) {
                const rowDate = dateCreated ? new Date(dateCreated) : null;
                const start = startDate ? new Date(startDate) : null;
                const end = endDate ? new Date(endDate) : null;
                matchesDate = rowDate && 
                    (!start || rowDate >= start) && 
                    (!end || rowDate <= end);
            }

            row.style.display = matchesSearch && matchesAssembly && matchesLeaderStatus && matchesDate ? '' : 'none';
        });
    }

    searchFilter.addEventListener('input', applyFilters);
    assemblyFilter.addEventListener('change', applyFilters);
    leaderStatusFilter.addEventListener('change', applyFilters);
    startDateFilter.addEventListener('change', applyFilters);
    endDateFilter.addEventListener('change', applyFilters);
    clearFilters.addEventListener('click', function() {
        searchFilter.value = '';
        assemblyFilter.value = '';
        leaderStatusFilter.value = '';
        startDateFilter.value = '';
        endDateFilter.value = '';
        applyFilters();
    });

    // Initial filter application
    applyFilters();
});
</script>
</body>
</html>
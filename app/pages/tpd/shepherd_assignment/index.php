<?php
session_start();
require_once '../../../config/config.php';
require_once '../../../functions/shepherd_functions.php';
require_once '../../../functions/permission_constants.php';
require_once '../../../functions/assemblies_functions.php';

// Temporary function to prevent the fatal error
function getAllAssemblies() {
    return array(); // Returns empty array for now as requested
}

$assemblies = getAllAssemblies(); // This won't cause an error now

// Get assembly_id from query parameter if set
$assembly_id = isset($_GET['assembly_id']) ? $_GET['assembly_id'] : null;

// Get the shepherds data with assembly filter
$householdShepherds = getShepherdsByType('household', $assembly_id);
$ministryShepherds = getShepherdsByType('ministry', $assembly_id);

$page_title = "Shepherd Assignments";
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../../../includes/header.php'; ?>

<style>
.container-fluid {
    max-width: 1200px;
    margin: 0 auto;
}
.card {
    margin-bottom: 1rem;
}
.nav-tabs .nav-link {
    background: linear-gradient(135deg, #1e88e5, #1565c0);
    color: white;
    margin-right: 4px;
    border: none;
}
.nav-tabs .nav-link:hover {
    background: linear-gradient(135deg, #1976d2, #0d47a1);
    color: white;
}
.nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #2196f3, #1976d2);
    color: white;
    border: none;
}
.filter-section {
    margin-bottom: 1rem;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
}
</style>

<div class="container-fluid py-4">
    <!-- Navigation Card -->
    <?php include '../../../includes/nav_card.php'; ?>

    <!-- Tabs for different shepherd types -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="household-tab" data-bs-toggle="tab" href="#household" role="tab">
                        <i class="bi bi-house-heart"></i> Household Shepherds
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="ministry-tab" data-bs-toggle="tab" href="#ministry" role="tab">
                        <i class="bi bi-people"></i> Ministry Shepherds
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="unassigned-tab" data-bs-toggle="tab" href="#unassigned" role="tab">
                        <i class="bi bi-person-x"></i> Unassigned Shepherds
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <!-- Assembly Filter -->
            <div class="filter-section">
                <form id="filterForm" class="row align-items-center">
                    <div class="col-auto">
                        <label for="assembly_filter" class="form-label">Filter by Assembly:</label>
                        <select class="form-select" id="assembly_filter" name="assembly_id" onchange="this.form.submit()">
                            <option value="">All Assemblies</option>
                            <?php foreach ($assemblies as $assembly): ?>
                                <option value="<?= $assembly['assembly_id'] ?>" 
                                    <?= ($assembly_id == $assembly['assembly_id'] ? 'selected' : '') ?>>
                                    <?= htmlspecialchars($assembly['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>

            <div class="tab-content">
                <!-- Household Shepherds Tab -->
                <div class="tab-pane fade show active" id="household" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Household Shepherds</h5>
                        <button type="button" class="btn btn-primary" onclick="showAddShepherdModal('household')">
                            <i class="bi bi-plus-circle"></i> Add Household Shepherd
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Assigned Households</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($householdShepherds as $shepherd): ?>
                                    <?php 
                                    $assignments = getShepherdAssignments($shepherd['shepherd_id']);
                                    $assignmentCount = count($assignments);
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($shepherd['first_name'] . ' ' . $shepherd['last_name']) ?></td>
                                        <td><?= htmlspecialchars($shepherd['contact']) ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= $assignmentCount ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $shepherd['status'] === 'active' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($shepherd['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        onclick="showAssignmentModal(<?= $shepherd['shepherd_id'] ?>, 'household')">
                                                    <i class="bi bi-house"></i> Assign
                                                </button>
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        onclick="viewAssignments(<?= $shepherd['shepherd_id'] ?>)">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        onclick="editShepherd(<?= $shepherd['shepherd_id'] ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Ministry Shepherds Tab -->
                <div class="tab-pane fade" id="ministry" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Ministry Shepherds</h5>
                        <button type="button" class="btn btn-primary" onclick="showAddShepherdModal('ministry')">
                            <i class="bi bi-plus-circle"></i> Add Ministry Shepherd
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Assigned Ministries</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ministryShepherds as $shepherd): ?>
                                    <?php 
                                    $assignments = getShepherdAssignments($shepherd['shepherd_id']);
                                    $assignmentCount = count($assignments);
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($shepherd['first_name'] . ' ' . $shepherd['last_name']) ?></td>
                                        <td><?= htmlspecialchars($shepherd['contact']) ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= $assignmentCount ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $shepherd['status'] === 'active' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($shepherd['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        onclick="showAssignmentModal(<?= $shepherd['shepherd_id'] ?>, 'ministry')">
                                                    <i class="bi bi-people"></i> Assign
                                                </button>
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        onclick="viewAssignments(<?= $shepherd['shepherd_id'] ?>)">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        onclick="editShepherd(<?= $shepherd['shepherd_id'] ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Unassigned Shepherds Tab -->
                <div class="tab-pane fade" id="unassigned" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Unassigned Shepherds</h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="unassignedShepherdsTable">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Shepherd Modal -->
<div class="modal fade" id="addShepherdModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Shepherd</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addShepherdForm">
                    <input type="hidden" id="shepherdType" name="shepherdType">
                    <div class="mb-3">
                        <label for="memberId" class="form-label">Select Member</label>
                        <select class="form-select" id="memberId" name="memberId" required>
                            <option value="">Select a member...</option>
                            <?php 
                            $availableMembers = getAvailableMembers();
                            foreach ($availableMembers as $member): 
                            ?>
                                <option value="<?= $member['member_id'] ?>">
                                    <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveShepherd()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Assignment Modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Shepherd</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignmentForm">
                    <input type="hidden" id="assignShepherdId" name="shepherdId">
                    <input type="hidden" id="assignmentType" name="entityType">
                    <div class="mb-3">
                        <label for="entityId" class="form-label">Select Entity</label>
                        <select class="form-select" id="entityId" name="entityId" required>
                            <option value="">Loading...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startDate" name="startDate" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAssignment()">Assign</button>
            </div>
        </div>
    </div>
</div>

<!-- View Assignments Modal -->
<div class="modal fade" id="viewAssignmentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Shepherd Assignments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="assignmentsList">
                    Loading...
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../../includes/footer.php'; ?>

<script>
let currentShepherdType = 'household';

function showAddShepherdModal(type) {
    currentShepherdType = type;
    document.getElementById('shepherdType').value = type;
    const modal = new bootstrap.Modal(document.getElementById('addShepherdModal'));
    modal.show();
}

function showAssignmentModal(shepherdId, type) {
    document.getElementById('assignShepherdId').value = shepherdId;
    document.getElementById('assignmentType').value = type;
    document.getElementById('startDate').value = new Date().toISOString().split('T')[0];
    
    // Load available entities
    fetch(`get_unassigned_entities.php?type=${type}`)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('entityId');
            select.innerHTML = '<option value="">Select...</option>';
            data.forEach(entity => {
                const option = document.createElement('option');
                option.value = entity.id;
                option.textContent = entity.name;
                select.appendChild(option);
            });
        });
    
    const modal = new bootstrap.Modal(document.getElementById('assignmentModal'));
    modal.show();
}

function saveShepherd() {
    const form = document.getElementById('addShepherdForm');
    const formData = new FormData(form);
    
    fetch('save_shepherd.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error saving shepherd');
        }
    });
}

function saveAssignment() {
    const form = document.getElementById('assignmentForm');
    const formData = new FormData(form);
    
    fetch('save_assignment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error saving assignment');
        }
    });
}

function viewAssignments(shepherdId) {
    fetch(`get_assignments.php?shepherd_id=${shepherdId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('assignmentsList');
            if (data.length === 0) {
                container.innerHTML = '<p class="text-muted">No active assignments found.</p>';
            } else {
                const list = document.createElement('ul');
                list.className = 'list-group';
                data.forEach(assignment => {
                    const item = document.createElement('li');
                    item.className = 'list-group-item';
                    item.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${assignment.entity_name}</strong>
                                <br>
                                <small class="text-muted">Since: ${assignment.start_date}</small>
                            </div>
                            <button class="btn btn-sm btn-danger" onclick="endAssignment(${assignment.assignment_id})">
                                <i class="bi bi-x-circle"></i> End
                            </button>
                        </div>
                    `;
                    list.appendChild(item);
                });
                container.innerHTML = '';
                container.appendChild(list);
            }
        });
    
    const modal = new bootstrap.Modal(document.getElementById('viewAssignmentsModal'));
    modal.show();
}

function endAssignment(assignmentId) {
    if (confirm('Are you sure you want to end this assignment?')) {
        fetch('end_assignment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ assignment_id: assignmentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error ending assignment');
            }
        });
    }
}

function loadUnassignedShepherds() {
    const assembly_id = document.getElementById('assembly_filter').value;
    fetch(`get_unassigned_shepherds.php${assembly_id ? `?assembly_id=${assembly_id}` : ''}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('unassignedShepherdsTable');
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No unassigned shepherds found
                        </td>
                    </tr>`;
                return;
            }
            
            data.forEach(shepherd => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${shepherd.first_name} ${shepherd.last_name}</td>
                    <td>${shepherd.contact}</td>
                    <td><span class="badge bg-secondary">${shepherd.type}</span></td>
                    <td>
                        <span class="badge bg-${shepherd.status === 'active' ? 'success' : 'danger'}">
                            ${shepherd.status}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-primary" 
                                    onclick="showAssignmentModal(${shepherd.shepherd_id}, '${shepherd.type}')">
                                <i class="bi bi-${shepherd.type === 'household' ? 'house' : 'people'}"></i> Assign
                            </button>
                            <button type="button" class="btn btn-sm btn-warning" 
                                    onclick="editShepherd(${shepherd.shepherd_id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Error loading unassigned shepherds:', error);
            document.getElementById('unassignedShepherdsTable').innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger">
                        Error loading unassigned shepherds
                    </td>
                </tr>`;
        });
}

// Load unassigned shepherds when their tab is shown
document.getElementById('unassigned-tab').addEventListener('shown.bs.tab', loadUnassignedShepherds);

// Reload unassigned shepherds when assembly filter changes
document.getElementById('assembly_filter').addEventListener('change', function() {
    if (document.getElementById('unassigned').classList.contains('active')) {
        loadUnassignedShepherds();
    }
});

</script>
</body>
</html>
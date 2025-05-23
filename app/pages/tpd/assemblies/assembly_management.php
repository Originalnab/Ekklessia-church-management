<?php
session_start();
$page_title = "Assembly Management";
include "../../../config/db.php";

// Fetch Presiding Elders and Assistant Presiding Elders
try {
    $stmt = $pdo->query("
        SELECT m.member_id, m.first_name, m.last_name, m.contact, m.email, m.status, m.profile_photo, a.name AS assembly_name, cf.function_name AS role_name
        FROM members m
        LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id
        JOIN church_functions cf ON m.local_function_id = cf.function_id
        WHERE cf.function_name IN ('Presiding Elder', 'Assistant Presiding Elder') AND cf.function_type = 'local'
        ORDER BY m.created_at DESC
    ");
    $presiding_elders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching presiding elders: " . $e->getMessage();
    exit;
}

// Fetch all assemblies for the filter dropdown
try {
    $stmt = $pdo->query("SELECT assembly_id, name FROM assemblies ORDER BY name ASC");
    $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching assemblies: " . $e->getMessage();
    exit;
}

// Fetch unique assemblies for the filter dropdown
$assembly_names = array_unique(array_column($presiding_elders, 'assembly_name'));
$assembly_names = array_filter($assembly_names); // Remove null/empty values
sort($assembly_names);

// Calculate counts
$total_presiding_elders = count(array_filter($presiding_elders, fn($e) => $e['role_name'] === 'Presiding Elder'));
$total_assistant_presiding_elders = count(array_filter($presiding_elders, fn($e) => $e['role_name'] === 'Assistant Presiding Elder'));
$total_assemblies_assigned = count(array_filter($presiding_elders, fn($e) => !empty($e['assembly_name'])));

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
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
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $base_url ?>/tpd/members/index.php" class="nav-link-btn">
                        <i class="bi bi-people-fill text-primary"></i>
                        <span>Members</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="dropdown dropdown-hover">
                        <a href="#" class="nav-link-btn dropdown-toggle" id="assembliesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-building-fill text-success"></i>
                            <span>Assemblies</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="assembliesDropdown">
                            <li><a class="dropdown-item" href="<?= $base_url ?>/tpd/assemblies/index.php">Assemblies</a></li>
                            <li><a class="dropdown-item" href="<?= $base_url ?>/tpd/assemblies/assembly_management.php">Assembly Management</a></li>
                        </ul>
                    </div>
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
                    <a href="<?= $base_url ?>/roles/index.php" class="nav-link-btn">
                        <i class="bi bi-person-gear text-secondary"></i>
                        <span>Roles</span>
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
                    <i class="bi bi-person-check fs-2"></i>
                    <h6 class="card-title text-white">Total Presiding Elders</h6>
                    <h3 class="card-text"><?= $total_presiding_elders ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #28a745, #6fcf97); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-person-check fs-2"></i>
                    <h6 class="card-title text-white">Total Assistant Presiding Elders</h6>
                    <h3 class="card-text"><?= $total_assistant_presiding_elders ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="background: linear-gradient(45deg, #17a2b8, #4fc3f7); color: white;">
                <div class="card-body text-center">
                    <i class="bi bi-building fs-2"></i>
                    <h6 class="card-title text-white">Assemblies Assigned</h6>
                    <h3 class="card-text"><?= $total_assemblies_assigned ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Assembly Management</h4>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="filterName" class="form-label"><i class="bi bi-search me-2"></i>Full Name</label>
                    <input type="text" class="form-control" id="filterName" placeholder="Search by name">
                </div>
                <div class="col-md-4">
                    <label for="filterAssembly" class="form-label"><i class="bi bi-building me-2"></i>Assembly</label>
                    <select class="form-select" id="filterAssembly">
                        <option value="">All Assemblies</option>
                        <?php foreach ($assembly_names as $assembly): ?>
                            <option value="<?= htmlspecialchars($assembly) ?>"><?= htmlspecialchars($assembly) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filterStatus" class="form-label"><i class="bi bi-person-check me-2"></i>Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All Statuses</option>
                        <option value="Committed saint">Committed Saint</option>
                        <option value="Active saint">Active Saint</option>
                        <option value="Worker">Worker</option>
                        <option value="New Saint">New Saint</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="presidingEldersTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Full Name</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Current Assembly</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($presiding_elders as $index => $elder): ?>
                            <tr data-id="<?= $elder['member_id'] ?>" 
                                data-name="<?= htmlspecialchars($elder['first_name'] . ' ' . $elder['last_name']) ?>" 
                                data-assembly="<?= htmlspecialchars($elder['assembly_name'] ?? 'N/A') ?>" 
                                data-status="<?= htmlspecialchars($elder['status']) ?>" 
                                data-role="<?= htmlspecialchars($elder['role_name']) ?>">
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <?php if ($elder['profile_photo']): ?>
                                        <img src="/Ekklessia-church-management/app/resources/assets/images/<?= htmlspecialchars($elder['profile_photo']) ?>" alt="Profile Photo" class="profile-photo">
                                    <?php else: ?>
                                        <img src="/Ekklessia-church-management/app/resources/assets/images/default.jpg" alt="Default Photo" class="profile-photo">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-gradient-blue text-nowrap view-member-btn" style="min-width: 150px;" data-member-id="<?= $elder['member_id'] ?>" data-bs-toggle="modal" data-bs-target="#viewMemberModal">
                                        <?= htmlspecialchars($elder['first_name'] . ' ' . $elder['last_name']) ?>
                                    </button>
                                </td>
                                <td><?= htmlspecialchars($elder['contact']) ?></td>
                                <td><?= htmlspecialchars($elder['email'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge assembly-badge" data-assembly-name="<?= htmlspecialchars($elder['assembly_name'] ?? 'N/A') ?>">
                                        <?= htmlspecialchars($elder['assembly_name'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($elder['role_name']) ?></td>
                                <td><?= htmlspecialchars($elder['status']) ?></td>
                                <td>
                                    <button class="btn btn-gradient-blue assign-assembly-btn" data-member-id="<?= $elder['member_id'] ?>" data-bs-toggle="modal" data-bs-target="#assignAssemblyModal">
                                        <i class="bi bi-building-fill me-2"></i>Assign Assembly
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- View Member Modal -->
    <div class="modal fade" id="viewMemberModal" tabindex="-1" aria-labelledby="viewMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewMemberModalLabel">Member Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="" alt="Profile Photo" class="profile-photo mb-3" id="modalProfilePhoto">
                            <h5 id="modalFullName"></h5>
                            <p class="text-muted" id="modalRoleName"></p>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>Contact:</strong></label>
                                    <p id="modalContact"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>Email:</strong></label>
                                    <p id="modalEmail"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>Assembly:</strong></label>
                                    <p id="modalAssembly"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>Status:</strong></label>
                                    <p id="modalStatus"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>Created At:</strong></label>
                                    <p id="modalCreatedAt"></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>Updated At:</strong></label>
                                    <p id="modalUpdatedAt"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "assign_assembly.php"; ?>
</main>

<style>
    .nav-card {
        background-color: #ffffff;
        border: none;
        box-shadow: 0 -2px 6px -2px rgba(0, 0, 0, 0.1), 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        margin-top: -30px;
        position: relative;
        top: -10px;
    }
    .nav-card .nav-link-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        text-decoration: none;
        color: #333;
        padding: 15px;
        border-radius: 8px;
        transition: background-color 0.3s, transform 0.2s;
    }
    .nav-link-btn:hover {
        background-color: #f1f3f5;
        transform: scale(1.05);
    }
    .nav-link-btn i {
        font-size: 1.5rem;
        margin-bottom: 8px;
    }
    .nav-link-btn span {
        font-size: 0.9rem;
        font-weight: 500;
    }
    [data-bs-theme="dark"] .nav-card {
        background-color: var(--card-bg-dark);
    }
    [data-bs-theme="dark"] .nav-link-btn {
        color: #e0e0e0;
    }
    [data-bs-theme="dark"] .nav-link-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    .profile-photo {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #007bff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .profile-photo:hover {
        transform: scale(1.2);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    #modalProfilePhoto {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #007bff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    #modalProfilePhoto:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .btn-gradient-blue {
        background: linear-gradient(45deg, #007bff, #00d4ff);
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 5px;
        transition: transform 0.2s;
        min-width: 150px;
        text-align: center;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .btn-gradient-blue:hover {
        transform: scale(1.05);
    }
    .assign-assembly-btn {
        min-width: auto;
    }
    .assembly-badge {
        font-size: 0.9rem;
        padding: 5px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .dropdown-hover:hover .dropdown-menu {
        display: block;
    }
    .dropdown-hover .dropdown-menu {
        display: none;
        margin-top: 0;
    }
    .nav-card .nav-link-btn.dropdown-toggle {
        cursor: pointer;
    }
    .modal-content {
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    .modal-body .form-label {
        font-weight: 500;
        color: #333;
    }
    .modal-body p {
        margin-bottom: 0;
        color: #555;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Filter functionality
    const filterName = document.getElementById('filterName');
    const filterAssembly = document.getElementById('filterAssembly');
    const filterStatus = document.getElementById('filterStatus');

    function applyFilters() {
        const nameFilter = filterName.value.toLowerCase();
        const assemblyFilter = filterAssembly.value;
        const statusFilter = filterStatus.value;

        const rows = document.querySelectorAll('#presidingEldersTable tbody tr');
        let visibleIndex = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name').toLowerCase();
            const assembly = row.getAttribute('data-assembly');
            const status = row.getAttribute('data-status');
            const role = row.getAttribute('data-role');

            let nameMatch = name.includes(nameFilter);
            let assemblyMatch = !assemblyFilter || assembly === assemblyFilter;
            let statusMatch = !statusFilter || status === statusFilter;
            let roleMatch = !['Presiding Elder', 'Assistant Presiding Elder'].includes(role) || ['Presiding Elder', 'Assistant Presiding Elder'].includes(role);

            if (nameMatch && assemblyMatch && statusMatch && roleMatch) {
                row.style.display = '';
                visibleIndex++;
                row.querySelector('td:first-child').textContent = visibleIndex; // Update row number
            } else {
                row.style.display = 'none';
            }
        });
    }

    filterName.addEventListener('input', applyFilters);
    filterAssembly.addEventListener('change', applyFilters);
    filterStatus.addEventListener('change', applyFilters);

    // Assign unique gradient colors to assembly badges
    const gradientColors = [
        'linear-gradient(45deg, #007bff, #00d4ff)', // Blue
        'linear-gradient(45deg, #28a745, #6fcf97)', // Green
        'linear-gradient(45deg, #ffc107, #ffca28)', // Yellow
        'linear-gradient(45deg, #17a2b8, #4fc3f7)', // Cyan
        'linear-gradient(45deg, #dc3545, #ff6b6b)', // Red
        'linear-gradient(45deg, #6c757d, #b0b5b9)', // Gray
        'linear-gradient(45deg, #343a40, #6c757d)'  // Dark
    ];

    function getGradientColor(assemblyName) {
        if (assemblyName === 'N/A') return 'linear-gradient(45deg, #6c757d, #b0b5b9)';
        let hash = 0;
        for (let i = 0; i < assemblyName.length; i++) {
            hash = assemblyName.charCodeAt(i) + ((hash << 5) - hash);
        }
        const index = Math.abs(hash) % gradientColors.length;
        return gradientColors[index];
    }

    document.querySelectorAll('.assembly-badge').forEach(badge => {
        const assemblyName = badge.getAttribute('data-assembly-name');
        const gradientClass = getGradientColor(assemblyName);
        badge.style.background = gradientClass;
        badge.style.color = 'white';
    });

    // View Member Modal - Fetch member data via AJAX
    document.querySelectorAll('.view-member-btn').forEach(button => {
        button.addEventListener('click', function () {
            const memberId = this.getAttribute('data-member-id');

            fetch(`fetch_member.php?id=${memberId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const member = data.member;
                        document.getElementById('modalProfilePhoto').src = member.profile_photo ? 
                            `/Ekklessia-church-management/app/resources/assets/images/${member.profile_photo}` : 
                            '/Ekklessia-church-management/app/resources/assets/images/default.jpg';
                        document.getElementById('modalFullName').textContent = `${member.first_name} ${member.last_name}`;
                        document.getElementById('modalRoleName').textContent = member.role_name || 'N/A';
                        document.getElementById('modalContact').textContent = member.contact || 'N/A';
                        document.getElementById('modalEmail').textContent = member.email || 'N/A';
                        document.getElementById('modalAssembly').textContent = member.assembly_name || 'Not Assigned';
                        document.getElementById('modalStatus').textContent = member.status || 'N/A';
                        document.getElementById('modalCreatedAt').textContent = member.created_at || 'N/A';
                        document.getElementById('modalUpdatedAt').textContent = member.updated_at || 'N/A';
                    } else {
                        // Show error in the modal
                        document.querySelector('#viewMemberModal .modal-body').innerHTML = `
                            <div class="alert alert-danger" role="alert">
                                <strong>Error!</strong> ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching member:', error);
                    document.querySelector('#viewMemberModal .modal-body').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <strong>Error!</strong> An error occurred while fetching member details.
                        </div>
                    `;
                });
        });
    });

    // Reapply gradient colors and modal trigger after refreshing the table
    function refreshTable() {
        fetch('fetch_presiding_elders.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#presidingEldersTable tbody');
                tbody.innerHTML = '';
                data.forEach((elder, index) => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', elder.member_id);
                    row.setAttribute('data-name', `${elder.first_name} ${elder.last_name}`);
                    row.setAttribute('data-assembly', elder.assembly_name || 'N/A');
                    row.setAttribute('data-status', elder.status);
                    row.setAttribute('data-role', elder.role_name);
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>
                            ${elder.profile_photo ? 
                                `<img src="/Ekklessia-church-management/app/resources/assets/images/${elder.profile_photo}" alt="Profile Photo" class="profile-photo">` : 
                                `<img src="/Ekklessia-church-management/app/resources/assets/images/default.jpg" alt="Profile Photo" class="profile-photo">`}
                        </td>
                        <td>
                            <button class="btn btn-gradient-blue text-nowrap view-member-btn" style="min-width: 150px;" data-member-id="${elder.member_id}" data-bs-toggle="modal" data-bs-target="#viewMemberModal">
                                ${elder.first_name} ${elder.last_name}
                            </button>
                        </td>
                        <td>${elder.contact}</td>
                        <td>${elder.email || 'N/A'}</td>
                        <td>
                            <span class="badge assembly-badge" data-assembly-name="${elder.assembly_name || 'N/A'}">
                                ${elder.assembly_name || 'N/A'}
                            </span>
                        </td>
                        <td>${elder.role_name}</td>
                        <td>${elder.status}</td>
                        <td>
                            <button class="btn btn-gradient-blue assign-assembly-btn" data-member-id="${elder.member_id}" data-bs-toggle="modal" data-bs-target="#assignAssemblyModal">
                                <i class="bi bi-building-fill me-2"></i>Assign Assembly
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                // Reapply gradient colors after refreshing the table
                document.querySelectorAll('.assembly-badge').forEach(badge => {
                    const assemblyName = badge.getAttribute('data-assembly-name');
                    const gradientClass = getGradientColor(assemblyName);
                    badge.style.background = gradientClass;
                    badge.style.color = 'white';
                });

                // Reapply modal trigger for new buttons
                document.querySelectorAll('.view-member-btn').forEach(button => {
                    button.addEventListener('click', function () {
                        const memberId = this.getAttribute('data-member-id');

                        fetch(`fetch_member.php?id=${memberId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const member = data.member;
                                    document.getElementById('modalProfilePhoto').src = member.profile_photo ? 
                                        `/Ekklessia-church-management/app/resources/assets/images/${member.profile_photo}` : 
                                        '/Ekklessia-church-management/app/resources/assets/images/default.jpg';
                                    document.getElementById('modalFullName').textContent = `${member.first_name} ${member.last_name}`;
                                    document.getElementById('modalRoleName').textContent = member.role_name || 'N/A';
                                    document.getElementById('modalContact').textContent = member.contact || 'N/A';
                                    document.getElementById('modalEmail').textContent = member.email || 'N/A';
                                    document.getElementById('modalAssembly').textContent = member.assembly_name || 'Not Assigned';
                                    document.getElementById('modalStatus').textContent = member.status || 'N/A';
                                    document.getElementById('modalCreatedAt').textContent = member.created_at || 'N/A';
                                    document.getElementById('modalUpdatedAt').textContent = member.updated_at || 'N/A';
                                } else {
                                    document.querySelector('#viewMemberModal .modal-body').innerHTML = `
                                        <div class="alert alert-danger" role="alert">
                                            <strong>Error!</strong> ${data.message}
                                        </div>
                                    `;
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching member:', error);
                                document.querySelector('#viewMemberModal .modal-body').innerHTML = `
                                    <div class="alert alert-danger" role="alert">
                                        <strong>Error!</strong> An error occurred while fetching member details.
                                    </div>
                                `;
                            });
                    });
                });

                // Reapply filters after refreshing the table
                applyFilters();
            })
            .catch(error => console.error('Error refreshing table:', error));
    }

    window.refreshTable = refreshTable; // Make refreshTable globally accessible
});
</script>
</body>
</html>
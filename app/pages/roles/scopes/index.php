<?php
session_start();
$page_title = "Scope Management";
include "../../../config/config.php";
include "../../auth/auth_check.php"; // Centralized authentication check

// Fetch total scopes count for dashboard
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_scopes FROM scopes");
    $total_scopes = $stmt->fetch(PDO::FETCH_ASSOC)['total_scopes'];
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching total scopes: " . $e->getMessage();
    $total_scopes = 0;
}

$base_url = '/Ekklessia-church-management/app/pages';
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../../../includes/header.php"; ?>
<body class="d-flex flex-column min-vh-100">
<main class="container flex-grow-1 py-2">
    <!-- Success/Error Alerts -->
    <?php include "../../../includes/alerts.php"; ?>

    <!-- Navigation Card -->
    <?php include "../../../includes/nav_card.php"; ?>

    <!-- Mini Dashboard -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm bg-gradient-primary text-white">
                <div class="card-body text-center">
                    <i class="bi bi-diagram-3-fill fs-2"></i>
                    <h6 class="card-title">Total Scopes</h6>
                    <h3 class="card-text"><?= $total_scopes ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Scope Management Section -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Scope Management</h4>
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addScopeModal">
                    <i class="bi bi-plus-circle"></i> Add New Scope
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Alerts Container -->
            <div id="alertsContainer"></div>

            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="filterScopeName" class="form-label"><i class="bi bi-search me-2"></i>Scope Name</label>
                    <input type="text" class="form-control" id="filterScopeName" placeholder="Search by scope name">
                </div>
            </div>

            <!-- Scopes Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="scopesTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Scope Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="scopesTableBody">
                        <!-- Scopes will be loaded here dynamically -->
                    </tbody>
                </table>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted" id="tableInfo">
                        Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span id="totalEntries">0</span> entries
                    </div>
                    <nav aria-label="Scope navigation">
                        <ul class="pagination" id="scopePagination">
                            <!-- Pagination will be generated here -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Scope Modal -->
    <div class="modal fade" id="addScopeModal" tabindex="-1" aria-labelledby="addScopeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addScopeModalLabel">Add New Scope</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="scopeForm">
                    <div class="modal-body">
                        <input type="hidden" id="scopeId" name="scope_id" value="0">
                        <div class="mb-3">
                            <label for="scopeName" class="form-label">Scope Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="scopeName" name="scope_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Scope</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include "../../../includes/footer.php"; ?>

<script>
let currentPage = 1;
const itemsPerPage = 10;

function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alertsContainer');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alertDiv);

    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const alert = bootstrap.Alert.getInstance(alertDiv);
        if (alert) {
            alert.close();
        } else {
            alertDiv.remove();
        }
    }, 5000);
}

function loadScopes(page = 1) {
    const filterName = document.getElementById('filterScopeName').value;
    
    fetch(`fetch_paginated_scopes.php?page=${page}&name=${filterName}`)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('scopesTableBody');
            tableBody.innerHTML = '';
            
            data.scopes.forEach((scope, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${(page - 1) * itemsPerPage + index + 1}</td>
                    <td>${scope.scope_name}</td>
                    <td>${scope.description || '<em class="text-muted">No description</em>'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editScope(${scope.scope_id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteScope(${scope.scope_id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });

            // Update pagination
            updatePagination(data.total_pages, page);
            
            // Update table info
            document.getElementById('showingStart').textContent = ((page - 1) * itemsPerPage + 1);
            document.getElementById('showingEnd').textContent = Math.min(page * itemsPerPage, data.total_records);
            document.getElementById('totalEntries').textContent = data.total_records;
        })
        .catch(error => {
            console.error('Error loading scopes:', error);
            alert('Error loading scopes. Please try again.');
        });
}

function updatePagination(totalPages, currentPage) {
    const pagination = document.getElementById('scopePagination');
    pagination.innerHTML = '';
    
    // Previous button
    pagination.innerHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadScopes(${currentPage - 1})">&laquo;</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        pagination.innerHTML += `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadScopes(${i})">${i}</a>
            </li>
        `;
    }
    
    // Next button
    pagination.innerHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadScopes(${currentPage + 1})">&raquo;</a>
        </li>
    `;
}

// Initialize scopes table
document.addEventListener('DOMContentLoaded', () => {
    loadScopes();
    
    // Add filter listener
    document.getElementById('filterScopeName').addEventListener('input', debounce(() => {
        loadScopes(1);
    }, 300));

    // Handle form submission
    document.getElementById('scopeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('save_scope.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json().catch(error => {
                throw new Error('Invalid JSON response: ' + error.message);
            });
        })
        .then(data => {
            if (data.success) {
                showAlert(data.message || 'Scope saved successfully', 'success');
                $('#addScopeModal').modal('hide');
                loadScopes(currentPage);
                this.reset();
            } else {
                showAlert(data.message || 'Error saving scope', 'danger');
            }
        })
        .catch(error => {
            console.error('Error saving scope:', error);
            showAlert('Error saving scope. Please check console for details.', 'danger');
        });
    });
});

function editScope(scopeId) {
    fetch(`get_scope.php?scope_id=${scopeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('scopeId').value = data.scope.scope_id;
                document.getElementById('scopeName').value = data.scope.scope_name;
                document.getElementById('description').value = data.scope.description || '';
                $('#addScopeModal').modal('show');
            } else {
                showAlert(data.message || 'Error fetching scope details', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error fetching scope details. Please try again.', 'danger');
        });
}

function deleteScope(scopeId) {
    if (confirm('Are you sure you want to delete this scope?')) {
        fetch(`delete_scope.php?scope_id=${scopeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message || 'Scope deleted successfully', 'success');
                    loadScopes(currentPage);
                } else {
                    showAlert(data.message || 'Error deleting scope', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error deleting scope. Please try again.', 'danger');
            });
    }
}

// Debounce function to limit API calls while typing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
</body>
</html>
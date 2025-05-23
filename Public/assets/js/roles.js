document.addEventListener('DOMContentLoaded', function () {
    let currentPage = 1;
    const itemsPerPage = 10;

    // Function to show Bootstrap alert
    function showAlert(message, type = 'success') {
        const alertsContainer = document.getElementById('alerts');
        if (!alertsContainer) return;

        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertsContainer.appendChild(alert);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alert) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        }, 5000);
    }

    // Load roles with pagination and filtering
    function loadRoles(page = 1) {
        const filterName = document.getElementById('filterRoleName')?.value || '';

        fetch(`fetch_paginated_roles.php?page=${page}&name=${encodeURIComponent(filterName)}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load roles');
                }

                const tbody = document.querySelector('tbody');
                tbody.innerHTML = '';

                data.roles.forEach((role, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${role.role_name}</td>
                        <td><span class="badge bg-primary">${role.hierarchy_level}</span></td>
                        <td>${role.description || ''}</td>
                        <td>${role.permissions ? role.permissions.split(',').map(p =>
                        `<span class="badge bg-secondary me-1">${p.trim()}</span>`).join('') : ''}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary edit-role" 
                                        data-role-id="${role.role_id}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editRoleModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-role" 
                                        data-role-id="${role.role_id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Update pagination
                updatePagination(data.total_pages, page);

                // Update stats
                if (data.stats) {
                    document.getElementById('totalRoles').textContent = data.total_records;
                    document.getElementById('membersWithRoles').textContent = data.stats.members_with_roles;
                    document.getElementById('membersWithoutRoles').textContent = data.stats.members_without_roles;
                }
            })
            .catch(error => {
                console.error('Error loading roles:', error);
                showAlert(error.message, 'danger');
            });
    }

    // Update pagination controls
    function updatePagination(totalPages, currentPage) {
        const pagination = document.getElementById('pagination');
        if (!pagination) return;

        pagination.innerHTML = '';

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `
            <button class="page-link" onclick="loadRoles(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                Previous
            </button>
        `;
        pagination.appendChild(prevLi);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${currentPage === i ? 'active' : ''}`;
            li.innerHTML = `
                <button class="page-link" onclick="loadRoles(${i})">${i}</button>
            `;
            pagination.appendChild(li);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `
            <button class="page-link" onclick="loadRoles(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                Next
            </button>
        `;
        pagination.appendChild(nextLi);
    }

    // Handle form submissions
    document.querySelectorAll('#addRoleForm, #editRoleForm').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const isEdit = this.id === 'editRoleForm';

            fetch('save_role.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(isEdit ? 'Role updated successfully' : 'Role created successfully');
                        bootstrap.Modal.getInstance(this.closest('.modal')).hide();
                        loadRoles(currentPage);
                    } else {
                        throw new Error(data.message || 'Failed to save role');
                    }
                })
                .catch(error => {
                    console.error('Error saving role:', error);
                    showAlert(error.message, 'danger');
                });
        });
    });

    // Handle role deletion
    document.addEventListener('click', function (e) {
        if (e.target.closest('.delete-role')) {
            const button = e.target.closest('.delete-role');
            const roleId = button.dataset.roleId;

            if (confirm('Are you sure you want to delete this role?')) {
                fetch('delete_role.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `role_id=${roleId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('Role deleted successfully');
                            loadRoles(currentPage);
                        } else {
                            throw new Error(data.message || 'Failed to delete role');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting role:', error);
                        showAlert(error.message, 'danger');
                    });
            }
        }
    });

    // Filter roles on input
    if (document.getElementById('filterRoleName')) {
        document.getElementById('filterRoleName').addEventListener('input', debounce(() => {
            currentPage = 1;
            loadRoles(1);
        }, 300));
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

    // Initialize
    loadRoles(1);
});
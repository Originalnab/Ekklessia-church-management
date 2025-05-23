$(document).ready(function () {
    let currentPage = 1;

    // Show Bootstrap alert
    function showAlert(message, type = 'success') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#ministryFormAlerts').html(alertHtml);
        setTimeout(() => {
            $('#ministryFormAlerts .alert').alert('close');
        }, 5000);
    }

    // Load ministries with pagination and filters
    function loadMinistries(page = 1, ministryName = '') {
        $.ajax({
            url: '/Ekklessia-church-management/app/pages/specialized_ministries/fetch_paginated_ministries.php',
            method: 'GET',
            data: { page: page, ministry_name: ministryName },
            dataType: 'json',
            success: function (response) {
                console.log('Load ministries response:', response);
                if (response.success) {
                    let html = '';
                    response.ministries.forEach((ministry, index) => {
                        const badgeClass = {
                            'National': 'bg-danger',
                            'Zone': 'bg-warning',
                            'Assembly': 'bg-success',
                            'Household': 'bg-info'
                        }[ministry.scope_name] || 'bg-secondary';
                        html += `
                            <tr>
                                <td>${(page - 1) * 10 + index + 1}</td>
                                <td>${ministry.ministry_name}</td>
                                <td><span class="badge ${badgeClass}">${ministry.scope_name}</span></td>
                                <td>${ministry.description || ''}</td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-primary edit-ministry-btn" data-ministry-id="${ministry.ministry_id}" data-bs-toggle="modal" data-bs-target="#addMinistryModal"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-danger delete-ministry-btn" data-ministry-id="${ministry.ministry_id}"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    $('#ministriesTable tbody').html(html);

                    // Update pagination
                    let paginationHtml = '';
                    for (let i = 1; i <= response.pagination.total_pages; i++) {
                        paginationHtml += `<button class="btn btn-sm btn-${i === page ? 'primary' : 'outline-primary'} mx-1" data-page="${i}">${i}</button>`;
                    }
                    $('#pagination').html(paginationHtml);
                } else {
                    showAlert('Failed to load ministries: ' + (response.message || 'Unknown error'), 'danger');
                }
            },
            error: function (xhr) {
                console.error('Error loading ministries:', xhr.responseText);
                showAlert('Failed to load ministries.', 'danger');
            }
        });
    }

    // Filter ministries on input
    $('#filterMinistryName').on('input', function () {
        currentPage = 1;
        loadMinistries(currentPage, $(this).val());
    });

    // Pagination click handler
    $(document).on('click', '#pagination .btn', function () {
        currentPage = parseInt($(this).data('page'));
        loadMinistries(currentPage, $('#filterMinistryName').val());
    });

    // Edit ministry button
    $(document).on('click', '.edit-ministry-btn', function () {
        const ministryId = $(this).data('ministry-id');
        $.ajax({
            url: '/Ekklessia-church-management/app/pages/specialized_ministries/get_ministry.php',
            method: 'GET',
            data: { ministry_id: ministryId },
            dataType: 'json',
            success: function (response) {
                console.log('Edit ministry response:', response);
                if (response.success) {
                    $('#ministryId').val(response.ministry.ministry_id);
                    $('#ministryName').val(response.ministry.ministry_name);
                    $('#scopeId').val(response.ministry.scope_id);
                    $('#description').val(response.ministry.description);
                    $('#addMinistryModalLabel').text('Edit Ministry');
                } else {
                    showAlert('Failed to load ministry: ' + (response.message || 'Unknown error'), 'danger');
                }
            },
            error: function (xhr) {
                console.error('Error loading ministry:', xhr.responseText);
                showAlert('Failed to load ministry.', 'danger');
            }
        });
    });

    // Reset add ministry modal
    $(document).on('show.bs.modal', '#addMinistryModal', function (e) {
        if (!$(e.relatedTarget).hasClass('edit-ministry-btn')) {
            $('#ministryForm')[0].reset();
            $('#ministryId').val('');
            $('#scopeId').val('');
            $('#ministryFormAlerts').empty();
            $('#addMinistryModalLabel').text('Add New Ministry');
        }
    });

    // Save ministry
    $('#ministryForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        console.log('Submitting form data:', Object.fromEntries(formData));
        $.ajax({
            url: '/Ekklessia-church-management/app/pages/specialized_ministries/save_ministry.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                console.log('Save ministry response:', response);
                if (response.success) {
                    showAlert('Ministry saved successfully!', 'success');
                    $('#addMinistryModal').modal('hide');
                    loadMinistries(currentPage, $('#filterMinistryName').val());
                } else {
                    showAlert('Failed to save ministry: ' + (response.message || 'Unknown error'), 'danger');
                }
            },
            error: function (xhr) {
                console.error('Error saving ministry:', xhr.responseText);
                try {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('Failed to save ministry: ' + (response.message || 'Server error'), 'danger');
                } catch (e) {
                    showAlert('Failed to save ministry: Invalid server response.', 'danger');
                }
            }
        });
    });

    // Delete ministry
    $(document).on('click', '.delete-ministry-btn', function () {
        if (!confirm('Are you sure you want to delete this ministry?')) return;
        const ministryId = $(this).data('ministry-id');
        $.ajax({
            url: '/Ekklessia-church-management/app/pages/specialized_ministries/delete_ministry.php',
            method: 'POST',
            data: { ministry_id: ministryId },
            dataType: 'json',
            success: function (response) {
                console.log('Delete ministry response:', response);
                if (response.success) {
                    showAlert('Ministry deleted successfully!', 'success');
                    loadMinistries(currentPage, $('#filterMinistryName').val());
                } else {
                    showAlert('Failed to delete ministry: ' + (response.message || 'Unknown error'), 'danger');
                }
            },
            error: function (xhr) {
                console.error('Error deleting ministry:', xhr.responseText);
                showAlert('Failed to delete ministry.', 'danger');
            }
        });
    });

    // Initialize
    loadMinistries();
});
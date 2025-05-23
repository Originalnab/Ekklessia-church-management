$(document).ready(function () {
    // Initialize bootstrap modal
    const permissionModal = new bootstrap.Modal(document.getElementById('permissionModal'), {
        backdrop: 'static',
        keyboard: false
    });

    // Filter permissions
    $('#filterPermissionName').on('input', function () {
        const searchText = $(this).val().toLowerCase();
        $('.permission-table tbody tr').each(function () {
            const permissionName = $(this).find('td:nth-child(2)').text().toLowerCase();
            $(this).toggle(permissionName.includes(searchText));
        });
    });

    // Filter by usage
    $('#filterUsage').on('change', function () {
        const usage = $(this).val();
        $('.permission-table tbody tr').each(function () {
            const roles = $(this).find('td:nth-child(4)').text();
            if (usage === 'assigned') {
                $(this).toggle(roles !== 'Not assigned');
            } else if (usage === 'unassigned') {
                $(this).toggle(roles === 'Not assigned');
            } else {
                $(this).show();
            }
        });
    });

    // Handle add new permission button
    $('[data-bs-target="#permissionModal"]').on('click', function (e) {
        if (!$(e.target).closest('.edit-permission-btn').length) {
            $('#permissionForm')[0].reset();
            $('#permissionId').val('');
            $('#permissionForm input[name="action"]').remove();
            $('#permissionForm').append('<input type="hidden" name="action" value="add">');
            $('#permissionModalLabel').text('Add New Permission');
            permissionModal.show();
        }
    });

    // Edit Permission
    $(document).on('click', '.edit-permission-btn', function (e) {
        e.preventDefault();
        const permissionId = $(this).data('permission-id');
        const permissionName = $(this).data('permission-name');
        const permissionDesc = $(this).data('permission-desc');
        const permissionGroup = $(this).data('permission-group');

        $('#permissionId').val(permissionId);
        $('#permissionName').val(permissionName);
        $('#permissionDescription').val(permissionDesc);
        $('#permissionGroup').val(permissionGroup);
        $('#permissionForm input[name="action"]').remove();
        $('#permissionForm').append('<input type="hidden" name="action" value="edit">');
        $('#permissionModalLabel').text('Edit Permission');
        permissionModal.show();
    });

    // Reset modal on hidden
    $('#permissionModal').on('hidden.bs.modal', function () {
        $('#permissionForm')[0].reset();
        $('#permissionForm').removeClass('was-validated');
        $('#permissionId').val('');
        $('#permissionForm input[name="action"]').remove();
    });

    // Delete Permission
    $(document).on('click', '.delete-permission-btn', function () {
        if (confirm('Are you sure you want to delete this permission?')) {
            const permissionId = $(this).data('permission-id');
            const form = $('<form method="POST" action="manage_permissions_process.php">')
                .append('<input type="hidden" name="action" value="delete">')
                .append(`<input type="hidden" name="permission_id" value="${permissionId}">`);
            $('body').append(form);
            form.submit();
        }
    });

    // Form validation
    $('#permissionForm').on('submit', function (e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
});
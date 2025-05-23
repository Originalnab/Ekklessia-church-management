// Initialize all dropdowns
document.addEventListener('DOMContentLoaded', function () {
    // Initialize all dropdowns with data-bs-toggle="dropdown"
    const dropdownElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    dropdownElements.forEach(element => {
        new bootstrap.Dropdown(element, {
            offset: [0, 2], // Add a small offset to prevent overlap
            boundary: 'viewport' // Ensure dropdowns stay within viewport
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function (event) {
        const dropdowns = document.querySelectorAll('.dropdown-menu.show');
        dropdowns.forEach(dropdown => {
            const dropdownToggle = dropdown.previousElementSibling;
            if (dropdownToggle && !dropdownToggle.contains(event.target) && !dropdown.contains(event.target)) {
                const bsDropdown = bootstrap.Dropdown.getInstance(dropdownToggle);
                if (bsDropdown) bsDropdown.hide();
            }
        });
    });

    // Assembly filter for unassigned shepherds
    $('#assemblyFilter').on('change', function () {
        const selectedAssembly = $(this).val();

        $('#unassignedShepherdsTable tbody tr').each(function () {
            const assemblyId = $(this).data('assembly-id');
            if (selectedAssembly === '' || assemblyId === selectedAssembly) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
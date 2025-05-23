// Filter unassigned shepherds
function filterUnassignedShepherds() {
    const nameSearch = $('#nameSearch').val().toLowerCase();
    const assemblyFilter = $('#unassignedAssemblyFilter').val();
    const statusFilter = $('#statusFilter').val();

    $('#unassignedShepherdsTable tbody tr').each(function () {
        const $row = $(this);
        const name = $row.find('td:eq(0)').text().toLowerCase();
        const assembly = $row.find('td:eq(1)').data('assembly-id');
        const status = $row.find('td:eq(2)').text().toLowerCase();

        const nameMatch = name.includes(nameSearch);
        const assemblyMatch = !assemblyFilter || assembly == assemblyFilter;
        const statusMatch = !statusFilter || status.includes(statusFilter);

        if (nameMatch && assemblyMatch && statusMatch) {
            $row.show();
        } else {
            $row.hide();
        }
    });
}

// Event listeners for filters
$('#nameSearch, #unassignedAssemblyFilter, #statusFilter').on('change keyup', function () {
    filterUnassignedShepherds();
});
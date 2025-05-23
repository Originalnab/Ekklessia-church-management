<?php
include "../../../config/db.php";
?>

<!-- Assign Assembly Modal -->
<div class="modal fade" id="assignAssemblyModal" tabindex="-1" aria-labelledby="assignAssemblyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="assignAssemblyModalLabel">Assign Assembly</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignAssemblyForm">
                    <div class="mb-3">
                        <label for="memberId" class="form-label">Select Member (Presiding Elder or Assistant Presiding Elder)</label>
                        <select class="form-select" id="memberId" name="member_id" required>
                            <option value="">Select a Member</option>
                            <?php
                            try {
                                $stmt = $pdo->query("
                                    SELECT m.member_id, m.first_name, m.last_name, cf.function_name
                                    FROM members m
                                    JOIN church_functions cf ON m.local_function_id = cf.function_id
                                    WHERE cf.function_name IN ('Presiding Elder', 'Assistant Presiding Elder') AND cf.function_type = 'local'
                                    ORDER BY m.last_name ASC
                                ");
                                $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($members as $member) {
                                    echo "<option value='" . htmlspecialchars($member['member_id']) . "'>" . htmlspecialchars($member['first_name'] . ' ' . $member['last_name'] . ' (' . $member['function_name'] . ')') . "</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>Error loading members</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="assemblyId" class="form-label">Select Assembly</label>
                        <select class="form-select" id="assemblyId" name="assembly_id" required>
                            <option value="">Select an Assembly</option>
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT assembly_id, name FROM assemblies ORDER BY name ASC");
                                $assemblies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($assemblies as $assembly) {
                                    echo "<option value='" . htmlspecialchars($assembly['assembly_id']) . "'>" . htmlspecialchars($assembly['name']) . "</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>Error loading assemblies</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-content {
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    .form-select {
        height: auto; /* Reset height since it's a single select */
    }
    .modal-backdrop {
        transition: opacity 0.15s linear; /* Smooth transition for backdrop */
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const assignAssemblyForm = document.getElementById('assignAssemblyForm');
    const assignAssemblyModalElement = document.getElementById('assignAssemblyModal');
    const assignAssemblyModal = new bootstrap.Modal(assignAssemblyModalElement);

    // Handle Assign Assembly button clicks to prefill the member ID
    document.querySelectorAll('.assign-assembly-btn').forEach(button => {
        button.addEventListener('click', function () {
            const memberId = this.getAttribute('data-member-id');
            document.getElementById('memberId').value = memberId;
            assignAssemblyModal.show(); // Show the modal
        });
    });

    assignAssemblyForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('assign_assembly_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the modal immediately after success
                assignAssemblyModal.hide();

                // Clean up modal backdrop and body classes
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.style.overflow = 'auto'; // Restore scroll behavior

                // Show Bootstrap alert for success
                const alertContainer = document.createElement('div');
                alertContainer.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                alertContainer.style.zIndex = '1050';
                alertContainer.role = 'alert';
                alertContainer.innerHTML = `
                    <strong>Success!</strong> Assembly assigned successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(alertContainer);

                // Refresh the table immediately
                window.refreshTable();

                // Fade out and remove the alert after 3 seconds
                setTimeout(() => {
                    alertContainer.classList.remove('show');
                    alertContainer.classList.add('fade');
                    setTimeout(() => alertContainer.remove(), 150); // Wait for fade transition
                }, 3000);

                // Reset the form
                assignAssemblyForm.reset();
            } else {
                // Show Bootstrap alert for error
                const alertContainer = document.createElement('div');
                alertContainer.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                alertContainer.style.zIndex = '1050';
                alertContainer.role = 'alert';
                alertContainer.innerHTML = `
                    <strong>Error!</strong> ${data.message || 'Failed to assign assembly.'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(alertContainer);

                // Fade out and remove the alert after 3 seconds
                setTimeout(() => {
                    alertContainer.classList.remove('show');
                    alertContainer.classList.add('fade');
                    setTimeout(() => alertContainer.remove(), 150); // Wait for fade transition
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error assigning assembly:', error);
            const alertContainer = document.createElement('div');
            alertContainer.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alertContainer.style.zIndex = '1050';
            alertContainer.role = 'alert';
            alertContainer.innerHTML = `
                <strong>Error!</strong> An error occurred while assigning the assembly.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(alertContainer);
            setTimeout(() => {
                alertContainer.classList.remove('show');
                alertContainer.classList.add('fade');
                setTimeout(() => alertContainer.remove(), 150); // Wait for fade transition
            }, 3000);
        });
    });

    // Ensure backdrop is removed when modal is hidden
    assignAssemblyModalElement.addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        document.body.style.overflow = 'auto'; // Restore scroll behavior
        // Reset the form when the modal is closed
        assignAssemblyForm.reset();
    });
});
</script>
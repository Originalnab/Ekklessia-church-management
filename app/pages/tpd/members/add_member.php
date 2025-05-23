<?php
include "../../../config/db.php";

// Fetch assemblies, local functions, and all members for dropdowns
$assemblies = [];
$localFunctions = [];
$allMembers = [];
try {
    $assemblyStmt = $pdo->query("SELECT assembly_id, name FROM assemblies");
    $assemblies = $assemblyStmt->fetchAll(PDO::FETCH_ASSOC);

    $functionStmt = $pdo->query("SELECT function_id, function_name FROM church_functions WHERE function_type = 'local'");
    $localFunctions = $functionStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all members for the referral dropdown
    $membersStmt = $pdo->query("SELECT member_id, first_name, last_name FROM members ORDER BY first_name ASC");
    $allMembers = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching dropdown data: " . $e->getMessage());
}
?>

<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addMemberModalLabel">Add New Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="addMemberTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="add-bio-tab" data-bs-toggle="tab" data-bs-target="#add-bio" type="button" role="tab" aria-controls="add-bio" aria-selected="true">Bio</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-contacts-tab" data-bs-toggle="tab" data-bs-target="#add-contacts" type="button" role="tab" aria-controls="add-contacts" aria-selected="false">Contacts</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-assembly-household-tab" data-bs-toggle="tab" data-bs-target="#add-assembly-household" type="button" role="tab" aria-controls="add-assembly-household" aria-selected="false">Assembly & Household</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-occupation-tab" data-bs-toggle="tab" data-bs-target="#add-occupation" type="button" role="tab" aria-controls="add-occupation" aria-selected="false">Occupation</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-education-tab" data-bs-toggle="tab" data-bs-target="#add-education" type="button" role="tab" aria-controls="add-education" aria-selected="false">Education</button>
                    </li>
                </ul>
                <div class="tab-content" id="addMemberTabsContent">
                    <!-- Bio Tab -->
                    <div class="tab-pane fade show active" id="add-bio" role="tabpanel" aria-labelledby="add-bio-tab">
                        <form id="addMemberFormBio">
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label for="addFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="addFirstName" name="first_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="addLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="addLastName" name="last_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="addDateOfBirth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="addDateOfBirth" name="date_of_birth" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="addGender" class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select" id="addGender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="addMaritalStatus" class="form-label">Marital Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="addMaritalStatus" name="marital_status" required>
                                        <option value="">Select Marital Status</option>
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                        <option value="Divorced">Divorced</option>
                                        <option value="Widowed">Widowed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="addStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="addStatus" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="Committed saint">Committed Saint</option>
                                        <option value="Active saint">Active Saint</option>
                                        <option value="Worker">Worker</option>
                                        <option value="New Saint">New Saint</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="addJoinedDate" class="form-label">Joined Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="addJoinedDate" name="joined_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="addCreatedBy" class="form-label">Created By <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="addCreatedBy" name="created_by" value="Admin" required readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="addProfilePhoto" class="form-label">Profile Photo</label>
                                    <input type="file" class="form-control" id="addProfilePhoto" name="profile_photo" accept="image/*">
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Contacts Tab -->
                    <div class="tab-pane fade" id="add-contacts" role="tabpanel" aria-labelledby="add-contacts-tab">
                        <form id="addMemberFormContacts">
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label for="addContact" class="form-label">Contact <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="addContact" name="contact" placeholder="+254 7XX XXX XXX" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="addEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="addEmail" name="email">
                                </div>
                                <div class="col-md-6">
                                    <label for="addAddress" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="addAddress" name="address">
                                </div>
                                <div class="col-md-6">
                                    <label for="addDigitalAddress" class="form-label">Digital Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="addDigitalAddress" name="digital_address" required>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Assembly & Household Tab -->
                    <div class="tab-pane fade" id="add-assembly-household" role="tabpanel" aria-labelledby="add-assembly-household-tab">
                        <form id="addMemberFormAssemblyHousehold">
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label for="addAssemblies" class="form-label">Assemblies <span class="text-danger">*</span></label>
                                    <select class="form-select" id="addAssemblies" name="assemblies_id" required>
                                        <option value="">Select Assembly</option>
                                        <?php foreach ($assemblies as $assembly): ?>
                                            <option value="<?= htmlspecialchars($assembly['assembly_id']) ?>">
                                                <?= htmlspecialchars($assembly['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="addLocalFunction" class="form-label">Local Function <span class="text-danger">*</span></label>
                                    <select class="form-select" id="addLocalFunction" name="local_function_id" required>
                                        <option value="">Select Local Function</option>
                                        <?php foreach ($localFunctions as $function): ?>
                                            <option value="<?= htmlspecialchars($function['function_id']) ?>">
                                                <?= htmlspecialchars($function['function_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="addReferral" class="form-label">Referral</label>
                                    <select class="form-select" id="addReferral" name="referral_id">
                                        <option value="">Select Referral</option>
                                        <?php foreach ($allMembers as $referral): ?>
                                            <option value="<?= htmlspecialchars($referral['member_id']) ?>">
                                                <?= htmlspecialchars($referral['first_name'] . ' ' . $referral['last_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="addGroup" class="form-label">Group</label>
                                    <select class="form-select" id="addGroup" name="group_name">
                                        <option value="">Select Group</option>
                                        <option value="Adult Ministry">Adult Ministry</option>
                                        <option value="Children's Ministry">Children's Ministry</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Occupation Tab -->
                    <div class="tab-pane fade" id="add-occupation" role="tabpanel" aria-labelledby="add-occupation-tab">
                        <form id="addMemberFormOccupation">
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label for="addOccupation" class="form-label">Occupation</label>
                                    <input type="text" class="form-control" id="addOccupation" name="occupation">
                                </div>
                                <div class="col-md-6">
                                    <label for="addEmployer" class="form-label">Employer</label>
                                    <input type="text" class="form-control" id="addEmployer" name="employer">
                                </div>
                                <div class="col-md-6">
                                    <label for="addWorkPhone" class="form-label">Work Phone</label>
                                    <input type="tel" class="form-control" id="addWorkPhone" name="work_phone">
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Education Tab -->
                    <div class="tab-pane fade" id="add-education" role="tabpanel" aria-labelledby="add-education-tab">
                        <form id="addMemberFormEducation">
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label for="addHighestEducationLevel" class="form-label">Highest Education Level</label>
                                    <select class="form-select" id="addHighestEducationLevel" name="highest_education_level">
                                        <option value="">Select Education Level</option>
                                        <option value="Primary">Primary</option>
                                        <option value="Secondary">Secondary</option>
                                        <option value="Diploma">Diploma</option>
                                        <option value="Bachelor's Degree">Bachelor's Degree</option>
                                        <option value="Master's Degree">Master's Degree</option>
                                        <option value="Doctorate">Doctorate</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="addInstitution" class="form-label">Institution</label>
                                    <input type="text" class="form-control" id="addInstitution" name="institution">
                                </div>
                                <div class="col-md-6">
                                    <label for="addYearGraduated" class="form-label">Year Graduated</label>
                                    <input type="number" class="form-control" id="addYearGraduated" name="year_graduated" min="1900" max="<?= date('Y') ?>">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveMemberBtn">Save Member</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addMemberModal = new bootstrap.Modal(document.getElementById('addMemberModal'));

    document.getElementById('saveMemberBtn').addEventListener('click', function () {
        // Collect data from all forms
        const bioForm = document.getElementById('addMemberFormBio');
        const contactsForm = document.getElementById('addMemberFormContacts');
        const assemblyHouseholdForm = document.getElementById('addMemberFormAssemblyHousehold');
        const occupationForm = document.getElementById('addMemberFormOccupation');
        const educationForm = document.getElementById('addMemberFormEducation');

        // Validate required fields
        if (!bioForm.checkValidity() || !contactsForm.checkValidity() || !assemblyHouseholdForm.checkValidity()) {
            bioForm.reportValidity();
            contactsForm.reportValidity();
            assemblyHouseholdForm.reportValidity();
            return;
        }

        const formData = new FormData();

        // Bio Tab
        formData.append('first_name', bioForm.querySelector('[name="first_name"]').value);
        formData.append('last_name', bioForm.querySelector('[name="last_name"]').value);
        formData.append('date_of_birth', bioForm.querySelector('[name="date_of_birth"]').value);
        formData.append('gender', bioForm.querySelector('[name="gender"]').value);
        formData.append('marital_status', bioForm.querySelector('[name="marital_status"]').value);
        formData.append('status', bioForm.querySelector('[name="status"]').value);
        formData.append('joined_date', bioForm.querySelector('[name="joined_date"]').value);
        formData.append('created_by', bioForm.querySelector('[name="created_by"]').value);

        const profilePhoto = bioForm.querySelector('[name="profile_photo"]').files[0];
        if (profilePhoto) {
            formData.append('profile_photo', profilePhoto);
        }

        // Contacts Tab
        formData.append('contact', contactsForm.querySelector('[name="contact"]').value);
        formData.append('email', contactsForm.querySelector('[name="email"]').value);
        formData.append('address', contactsForm.querySelector('[name="address"]').value);
        formData.append('digital_address', contactsForm.querySelector('[name="digital_address"]').value);

        // Assembly & Household Tab
        formData.append('assemblies_id', assemblyHouseholdForm.querySelector('[name="assemblies_id"]').value);
        formData.append('local_function_id', assemblyHouseholdForm.querySelector('[name="local_function_id"]').value);
        formData.append('referral_id', assemblyHouseholdForm.querySelector('[name="referral_id"]').value);
        formData.append('group_name', assemblyHouseholdForm.querySelector('[name="group_name"]').value);

        // Occupation Tab
        formData.append('occupation', occupationForm.querySelector('[name="occupation"]').value);
        formData.append('employer', occupationForm.querySelector('[name="employer"]').value);
        formData.append('work_phone', occupationForm.querySelector('[name="work_phone"]').value);

        // Education Tab
        formData.append('highest_education_level', educationForm.querySelector('[name="highest_education_level"]').value);
        formData.append('institution', educationForm.querySelector('[name="institution"]').value);
        formData.append('year_graduated', educationForm.querySelector('[name="year_graduated"]').value);

        // Send data to the server
        fetch('add_member_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Member added successfully. Username: ' + data.temp_username + ', Password: ' + data.temp_password);
                addMemberModal.hide();
                refreshMembersTable();
            } else {
                showAlert('danger', 'Error: ' + data.message);
            }
        })
        .catch(error => {
            showAlert('danger', 'Network error: ' + error.message);
        });
    });

    function showAlert(type, message) {
        const alertContainer = document.createElement('div');
        alertContainer.className = `alert alert-${type} alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3`;
        alertContainer.style.zIndex = '1050';
        alertContainer.role = 'alert';
        alertContainer.innerHTML = `
            <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertContainer);

        setTimeout(() => {
            alertContainer.classList.remove('show');
            setTimeout(() => alertContainer.remove(), 150);
        }, 3000);
    }
});
</script>
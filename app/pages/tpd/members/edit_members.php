<?php
// Remove session_start() from here
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

// Check if member data is provided (e.g., via a variable or AJAX)
$member = isset($member) ? $member : null; // This will be set by index.php or AJAX
$member_id = isset($member['member_id']) ? $member['member_id'] : (isset($_GET['member_id']) ? $_GET['member_id'] : null);
if ($member_id && !$member) {
    try {
        $stmt = $pdo->prepare("SELECT m.*, a.name AS assembly_name FROM members m LEFT JOIN assemblies a ON m.assemblies_id = a.assembly_id WHERE m.member_id = :member_id");
        $stmt->execute(['member_id' => $member_id]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$member) {
            die("Member not found.");
        }
    } catch (PDOException $e) {
        die("Error fetching member: " . $e->getMessage());
    }
}
?>

<?php if ($member): ?>
<div class="modal fade" id="editMemberModal-<?= $member['member_id'] ?>" tabindex="-1" aria-labelledby="editMemberModalLabel-<?= $member['member_id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editMemberModalLabel-<?= $member['member_id'] ?>">Edit Member: <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="editMemberTabs-<?= $member['member_id'] ?>" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="edit-bio-tab-<?= $member['member_id'] ?>" data-bs-toggle="tab" data-bs-target="#edit-bio-<?= $member['member_id'] ?>" type="button" role="tab" aria-controls="edit-bio-<?= $member['member_id'] ?>" aria-selected="true">Bio</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="edit-contacts-tab-<?= $member['member_id'] ?>" data-bs-toggle="tab" data-bs-target="#edit-contacts-<?= $member['member_id'] ?>" type="button" role="tab" aria-controls="edit-contacts-<?= $member['member_id'] ?>" aria-selected="false">Contacts</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="edit-assembly-household-tab-<?= $member['member_id'] ?>" data-bs-toggle="tab" data-bs-target="#edit-assembly-household-<?= $member['member_id'] ?>" type="button" role="tab" aria-controls="edit-assembly-household-<?= $member['member_id'] ?>" aria-selected="false">Assembly & Household</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="edit-occupation-tab-<?= $member['member_id'] ?>" data-bs-toggle="tab" data-bs-target="#edit-occupation-<?= $member['member_id'] ?>" type="button" role="tab" aria-controls="edit-occupation-<?= $member['member_id'] ?>" aria-selected="false">Occupation</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="edit-education-tab-<?= $member['member_id'] ?>" data-bs-toggle="tab" data-bs-target="#edit-education-<?= $member['member_id'] ?>" type="button" role="tab" aria-controls="edit-education-<?= $member['member_id'] ?>" aria-selected="false">Education</button>
                    </li>
                </ul>
                <div class="tab-content" id="editMemberTabsContent-<?= $member['member_id'] ?>">
                    <!-- Bio Tab -->
                    <div class="tab-pane fade show active" id="edit-bio-<?= $member['member_id'] ?>" role="tabpanel" aria-labelledby="edit-bio-tab-<?= $member['member_id'] ?>">
                        <form id="editMemberFormBio-<?= $member['member_id'] ?>">
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label for="editFirstName-<?= $member['member_id'] ?>" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editFirstName-<?= $member['member_id'] ?>" name="first_name" value="<?= htmlspecialchars($member['first_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editLastName-<?= $member['member_id'] ?>" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editLastName-<?= $member['member_id'] ?>" name="last_name" value="<?= htmlspecialchars($member['last_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editDateOfBirth-<?= $member['member_id'] ?>" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="editDateOfBirth-<?= $member['member_id'] ?>" name="date_of_birth" value="<?= htmlspecialchars($member['date_of_birth']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editGender-<?= $member['member_id'] ?>" class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editGender-<?= $member['member_id'] ?>" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?= $member['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= $member['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= $member['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editMaritalStatus-<?= $member['member_id'] ?>" class="form-label">Marital Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editMaritalStatus-<?= $member['member_id'] ?>" name="marital_status" required>
                                        <option value="">Select Marital Status</option>
                                        <option value="Single" <?= $member['marital_status'] === 'Single' ? 'selected' : '' ?>>Single</option>
                                        <option value="Married" <?= $member['marital_status'] === 'Married' ? 'selected' : '' ?>>Married</option>
                                        <option value="Divorced" <?= $member['marital_status'] === 'Divorced' ? 'selected' : '' ?>>Divorced</option>
                                        <option value="Widowed" <?= $member['marital_status'] === 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editStatus-<?= $member['member_id'] ?>" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editStatus-<?= $member['member_id'] ?>" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="Committed saint" <?= $member['status'] === 'Committed saint' ? 'selected' : '' ?>>Committed Saint</option>
                                        <option value="Active saint" <?= $member['status'] === 'Active saint' ? 'selected' : '' ?>>Active Saint</option>
                                        <option value="Worker" <?= $member['status'] === 'Worker' ? 'selected' : '' ?>>Worker</option>
                                        <option value="New Saint" <?= $member['status'] === 'New Saint' ? 'selected' : '' ?>>New Saint</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editJoinedDate-<?= $member['member_id'] ?>" class="form-label">Joined Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="editJoinedDate-<?= $member['member_id'] ?>" name="joined_date" value="<?= htmlspecialchars($member['joined_date']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editCreatedBy-<?= $member['member_id'] ?>" class="form-label">Created By <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editCreatedBy-<?= $member['member_id'] ?>" name="created_by" value="<?= htmlspecialchars($member['created_by']) ?>" required readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="editUpdatedBy-<?= $member['member_id'] ?>" class="form-label">Updated By</label>
                                    <input type="text" class="form-control" id="editUpdatedBy-<?= $member['member_id'] ?>" name="updated_by" value="Admin" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="editProfilePhoto-<?= $member['member_id'] ?>" class="form-label">Profile Photo</label>
                                    <input type="file" class="form-control" id="editProfilePhoto-<?= $member['member_id'] ?>" name="profile_photo" accept="image/*">
                                    <?php if ($member['profile_photo']): ?>
                                        <small>Current Photo: <img src="/EKKLESSIA-CHURCH-MANAGEMENT/app/resources/assets/images/<?= htmlspecialchars($member['profile_photo']) ?>" alt="Current Photo" width="50" height="50" style="border-radius: 50%; margin-top: 5px;"></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Contacts Tab -->
                    <div class="tab-pane fade" id="edit-contacts-<?= $member['member_id'] ?>" role="tabpanel" aria-labelledby="edit-contacts-tab-<?= $member['member_id'] ?>">
                        <form id="editMemberFormContacts-<?= $member['member_id'] ?>">
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label for="editContact-<?= $member['member_id'] ?>" class="form-label">Contact <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="editContact-<?= $member['member_id'] ?>" name="contact" value="<?= htmlspecialchars($member['contact']) ?>" placeholder="+254 7XX XXX XXX" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editEmail-<?= $member['member_id'] ?>" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="editEmail-<?= $member['member_id'] ?>" name="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="editAddress-<?= $member['member_id'] ?>" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="editAddress-<?= $member['member_id'] ?>" name="address" value="<?= htmlspecialchars($member['address'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="editDigitalAddress-<?= $member['member_id'] ?>" class="form-label">Digital Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editDigitalAddress-<?= $member['member_id'] ?>" name="digital_address" value="<?= htmlspecialchars($member['digital_address']) ?>" placeholder="e.g., GH-123-456" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editWorkPhone-<?= $member['member_id'] ?>" class="form-label">Work Phone</label>
                                    <input type="tel" class="form-control" id="editWorkPhone-<?= $member['member_id'] ?>" name="work_phone" value="<?= htmlspecialchars($member['work_phone'] ?? '') ?>" placeholder="+254 7XX XXX XXX">
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Assembly & Household Tab -->
                    <div class="tab-pane fade" id="edit-assembly-household-<?= $member['member_id'] ?>" role="tabpanel" aria-labelledby="edit-assembly-household-tab-<?= $member['member_id'] ?>">
                        <form id="editMemberFormAssemblyHousehold-<?= $member['member_id'] ?>">
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label for="editAssembliesId-<?= $member['member_id'] ?>" class="form-label">Assembly ID <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editAssembliesId-<?= $member['member_id'] ?>" name="assemblies_id" required>
                                        <option value="">Select Assembly</option>
                                        <?php foreach ($assemblies as $assembly): ?>
                                            <option value="<?= $assembly['assembly_id'] ?>" <?= $member['assemblies_id'] == $assembly['assembly_id'] ? 'selected' : '' ?>><?= htmlspecialchars($assembly['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editLocalFunctionId-<?= $member['member_id'] ?>" class="form-label">Local Function ID <span class="text-danger">*</span></label>
                                    <select class="form-select" id="editLocalFunctionId-<?= $member['member_id'] ?>" name="local_function_id" required>
                                        <option value="">Select Local Function</option>
                                        <?php foreach ($localFunctions as $function): ?>
                                            <option value="<?= $function['function_id'] ?>" <?= $member['local_function_id'] == $function['function_id'] ? 'selected' : '' ?>><?= htmlspecialchars($function['function_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editReferral-<?= $member['member_id'] ?>" class="form-label">Referral</label>
                                    <select class="form-select" id="editReferral-<?= $member['member_id'] ?>" name="referral_id">
                                        <option value="">Select Referral</option>
                                        <?php foreach ($allMembers as $referral): ?>
                                            <?php if ($referral['member_id'] != $member['member_id']): // Prevent self-referral ?>
                                                <option value="<?= htmlspecialchars($referral['member_id']) ?>" <?= $member['referral_id'] == $referral['member_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($referral['first_name'] . ' ' . $referral['last_name']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editGroup-<?= $member['member_id'] ?>" class="form-label">Group</label>
                                    <select class="form-select" id="editGroup-<?= $member['member_id'] ?>" name="group_name">
                                        <option value="">Select Group</option>
                                        <option value="Adult Ministry" <?= $member['group_name'] === 'Adult Ministry' ? 'selected' : '' ?>>Adult Ministry</option>
                                        <option value="Children's Ministry" <?= $member['group_name'] === "Children's Ministry" ? 'selected' : '' ?>>Children's Ministry</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Occupation Tab -->
                    <div class="tab-pane fade" id="edit-occupation-<?= $member['member_id'] ?>" role="tabpanel" aria-labelledby="edit-occupation-tab-<?= $member['member_id'] ?>">
                        <form id="editMemberFormOccupation-<?= $member['member_id'] ?>">
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label for="editOccupation-<?= $member['member_id'] ?>" class="form-label">Occupation</label>
                                    <input type="text" class="form-control" id="editOccupation-<?= $member['member_id'] ?>" name="occupation" value="<?= htmlspecialchars($member['occupation'] ?? '') ?>" placeholder="e.g., Teacher">
                                </div>
                                <div class="col-md-6">
                                    <label for="editEmployer-<?= $member['member_id'] ?>" class="form-label">Employer</label>
                                    <input type="text" class="form-control" id="editEmployer-<?= $member['member_id'] ?>" name="employer" value="<?= htmlspecialchars($member['employer'] ?? '') ?>" placeholder="e.g., ABC School">
                                </div>
                                <div class="col-md-6">
                                    <label for="editWorkPhone-<?= $member['member_id'] ?>" class="form-label">Work Phone</label>
                                    <input type="tel" class="form-control" id="editWorkPhone-<?= $member['member_id'] ?>" name="work_phone" value="<?= htmlspecialchars($member['work_phone'] ?? '') ?>" placeholder="+254 7XX XXX XXX">
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Education Tab -->
                    <div class="tab-pane fade" id="edit-education-<?= $member['member_id'] ?>" role="tabpanel" aria-labelledby="edit-education-tab-<?= $member['member_id'] ?>">
                        <form id="editMemberFormEducation-<?= $member['member_id'] ?>">
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label for="editHighestEducationLevel-<?= $member['member_id'] ?>" class="form-label">Highest Education Level</label>
                                    <select class="form-select" id="editHighestEducationLevel-<?= $member['member_id'] ?>" name="highest_education_level">
                                        <option value="">Select Level</option>
                                        <option value="None" <?= $member['highest_education_level'] === 'None' ? 'selected' : '' ?>>None</option>
                                        <option value="Primary" <?= $member['highest_education_level'] === 'Primary' ? 'selected' : '' ?>>Primary</option>
                                        <option value="Secondary" <?= $member['highest_education_level'] === 'Secondary' ? 'selected' : '' ?>>Secondary</option>
                                        <option value="Diploma" <?= $member['highest_education_level'] === 'Diploma' ? 'selected' : '' ?>>Diploma</option>
                                        <option value="Bachelor's" <?= $member['highest_education_level'] === "Bachelor's" ? 'selected' : '' ?>>Bachelor's</option>
                                        <option value="Master's" <?= $member['highest_education_level'] === "Master's" ? 'selected' : '' ?>>Master's</option>
                                        <option value="PhD" <?= $member['highest_education_level'] === 'PhD' ? 'selected' : '' ?>>PhD</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="editInstitution-<?= $member['member_id'] ?>" class="form-label">Institution</label>
                                    <input type="text" class="form-control" id="editInstitution-<?= $member['member_id'] ?>" name="institution" value="<?= htmlspecialchars($member['institution'] ?? '') ?>" placeholder="e.g., University of Ghana">
                                </div>
                                <div class="col-md-6">
                                    <label for="editYearGraduated-<?= $member['member_id'] ?>" class="form-label">Year Graduated</label>
                                    <input type="number" class="form-control" id="editYearGraduated-<?= $member['member_id'] ?>" name="year_graduated" value="<?= htmlspecialchars($member['year_graduated'] ?? '') ?>" min="1900" max="2025" placeholder="e.g., 2020">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateMember(<?= $member['member_id'] ?>)">Update Member</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function updateMember(memberId) {
    const bioForm = document.getElementById(`editMemberFormBio-${memberId}`);
    const contactsForm = document.getElementById(`editMemberFormContacts-${memberId}`);
    const assemblyHouseholdForm = document.getElementById(`editMemberFormAssemblyHousehold-${memberId}`);
    const occupationForm = document.getElementById(`editMemberFormOccupation-${memberId}`);
    const educationForm = document.getElementById(`editMemberFormEducation-${memberId}`);

    // Basic validation
    if (!bioForm.checkValidity() || !contactsForm.checkValidity() || !assemblyHouseholdForm.checkValidity() || !occupationForm.checkValidity() || !educationForm.checkValidity()) {
        alert('Please fill all required fields.');
        return;
    }

    const formData = new FormData();
    formData.append('member_id', memberId);
    formData.append('first_name', bioForm.querySelector(`#editFirstName-${memberId}`).value);
    formData.append('last_name', bioForm.querySelector(`#editLastName-${memberId}`).value);
    formData.append('date_of_birth', bioForm.querySelector(`#editDateOfBirth-${memberId}`).value);
    formData.append('gender', bioForm.querySelector(`#editGender-${memberId}`).value);
    formData.append('marital_status', bioForm.querySelector(`#editMaritalStatus-${memberId}`).value);
    formData.append('contact', contactsForm.querySelector(`#editContact-${memberId}`).value);
    formData.append('email', contactsForm.querySelector(`#editEmail-${memberId}`).value || '');
    formData.append('address', contactsForm.querySelector(`#editAddress-${memberId}`).value || '');
    formData.append('digital_address', contactsForm.querySelector(`#editDigitalAddress-${memberId}`).value);
    formData.append('occupation', occupationForm.querySelector(`#editOccupation-${memberId}`).value || '');
    formData.append('employer', occupationForm.querySelector(`#editEmployer-${memberId}`).value || '');
    formData.append('work_phone', contactsForm.querySelector(`#editWorkPhone-${memberId}`).value || '');
    formData.append('highest_education_level', educationForm.querySelector(`#editHighestEducationLevel-${memberId}`).value || '');
    formData.append('institution', educationForm.querySelector(`#editInstitution-${memberId}`).value || '');
    formData.append('year_graduated', educationForm.querySelector(`#editYearGraduated-${memberId}`).value || '');
    formData.append('status', bioForm.querySelector(`#editStatus-${memberId}`).value);
    formData.append('joined_date', bioForm.querySelector(`#editJoinedDate-${memberId}`).value);
    formData.append('assemblies_id', assemblyHouseholdForm.querySelector(`#editAssembliesId-${memberId}`).value);
    formData.append('local_function_id', assemblyHouseholdForm.querySelector(`#editLocalFunctionId-${memberId}`).value);
    formData.append('referral_id', assemblyHouseholdForm.querySelector(`#editReferral-${memberId}`).value || '');
    formData.append('group_name', assemblyHouseholdForm.querySelector(`#editGroup-${memberId}`).value || '');
    formData.append('created_by', bioForm.querySelector(`#editCreatedBy-${memberId}`).value);
    formData.append('updated_by', bioForm.querySelector(`#editUpdatedBy-${memberId}`).value || '');
    const profilePhoto = bioForm.querySelector(`#editProfilePhoto-${memberId}`).files[0];
    if (profilePhoto) formData.append('profile_photo', profilePhoto);

    // AJAX request to update the member
    fetch('edit_member_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Member updated successfully!');
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById(`editMemberModal-${memberId}`));
                if (modal) modal.hide();
                // Update the table row
                const row = document.querySelector(`#membersTable tbody tr[data-id="${memberId}"]`);
                if (row) {
                    row.setAttribute('data-first-name', data.member.first_name);
                    row.setAttribute('data-last-name', data.member.last_name);
                    row.setAttribute('data-dob', data.member.date_of_birth);
                    row.setAttribute('data-gender', data.member.gender);
                    row.setAttribute('data-marital-status', data.member.marital_status);
                    row.setAttribute('data-contact', data.member.contact);
                    row.setAttribute('data-email', data.member.email || 'N/A');
                    row.setAttribute('data-address', data.member.address || 'N/A');
                    row.setAttribute('data-digital-address', data.member.digital_address);
                    row.setAttribute('data-occupation', data.member.occupation || 'N/A');
                    row.setAttribute('data-employer', data.member.employer || 'N/A');
                    row.setAttribute('data-work-phone', data.member.work_phone || 'N/A');
                    row.setAttribute('data-education', data.member.highest_education_level || 'N/A');
                    row.setAttribute('data-institution', data.member.institution || 'N/A');
                    row.setAttribute('data-year-graduated', data.member.year_graduated || 'N/A');
                    row.setAttribute('data-assembly', data.member.assembly_name || 'N/A');
                    row.setAttribute('data-status', data.member.status);
                    row.setAttribute('data-joined-date', data.member.joined_date);
                    row.setAttribute('data-assemblies-id', data.member.assemblies_id);
                    row.setAttribute('data-referral-id', data.member.referral_id || 'N/A');
                    row.setAttribute('data-referral-name', (data.member.referral_first_name || '') + ' ' + (data.member.referral_last_name || ''));
                    row.setAttribute('data-shepherd-name', data.member.shepherd_name || 'Not Assigned');
                    row.innerHTML = `
                        <td><input type="checkbox" class="row-checkbox" data-id="${memberId}"></td>
                        <td>${row.cells[1].textContent}</td>
                        <td>
                            ${data.member.profile_photo ? 
                                `<img src="/EKKLESSIA-CHURCH-MANAGEMENT/app/resources/assets/images/${data.member.profile_photo}" alt="Profile Photo" class="profile-photo clickable" data-member-id="${memberId}">` : 
                                `<img src="/EKKLESSIA-CHURCH-MANAGEMENT/app/resources/assets/images/default.jpg" alt="Default Photo" class="profile-photo clickable" data-member-id="${memberId}">`}
                        </td>
                        <td>
                            <button class="btn btn-gradient-blue text-nowrap clickable" style="min-width: 150px;" data-member-id="${memberId}">
                                ${data.member.first_name} ${data.member.last_name}
                            </button>
                        </td>
                        <td>${data.member.contact}</td>
                        <td>
                            <span class="badge assembly-badge" data-assemblies-id="${data.member.assemblies_id || 'N/A'}" data-assembly-name="${data.member.assembly_name || 'N/A'}">
                                ${data.member.assembly_name || 'N/A'}
                            </span>
                        </td>
                        <td>${data.member.shepherd_name || 'Not Assigned'}</td>
                        <td>${data.member.status}</td>
                        <td>${data.member.joined_date}</td>
                        <td>${data.member.referral_id ? (data.member.referral_first_name + ' ' + data.member.referral_last_name) : 'N/A'}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary load-edit-modal" data-member-id="${memberId}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteMemberModal-${memberId}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <div class="btn-group dropdown" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="assignDropdown-${memberId}">
                                        <i class="bi bi-person-plus"></i> Assign
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" data-action="assign-household" data-member-id="${memberId}">Assign Household</a></li>
                                        <li><a class="dropdown-item" href="#" data-action="edit-household" data-member-id="${memberId}">Edit Household</a></li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    `;
                    // Reapply assembly badge styling
                    const badge = row.querySelector('.assembly-badge');
                    if (badge) {
                        const assemblyId = badge.getAttribute('data-assemblies-id');
                        const gradientColor = getGradientColor(assemblyId);
                        badge.style.background = gradientColor;
                        badge.style.color = 'white';
                    }
                    // Reapply click handlers for the updated row
                    row.querySelectorAll('.clickable').forEach(element => {
                        element.addEventListener('click', function () {
                            const memberId = this.getAttribute('data-member-id');
                            const row = document.querySelector(`#membersTable tr[data-id="${memberId}"]`);
                            if (row) {
                                const firstName = row.getAttribute('data-first-name');
                                const lastName = row.getAttribute('data-last-name');
                                const dob = row.getAttribute('data-dob');
                                const gender = row.getAttribute('data-gender');
                                const maritalStatus = row.getAttribute('data-marital-status');
                                const contact = row.getAttribute('data-contact');
                                const email = row.getAttribute('data-email');
                                const address = row.getAttribute('data-address');
                                const digitalAddress = row.getAttribute('data-digital-address');
                                const occupation = row.getAttribute('data-occupation');
                                const employer = row.getAttribute('data-employer');
                                const workPhone = row.getAttribute('data-work-phone');
                                const education = row.getAttribute('data-education');
                                const institution = row.getAttribute('data-institution');
                                const yearGraduated = row.getAttribute('data-year-graduated');
                                const assembly = row.getAttribute('data-assembly');
                                const status = row.getAttribute('data-status');
                                const joinedDate = row.getAttribute('data-joined-date');
                                const referralName = row.getAttribute('data-referral-name');
                                const shepherdName = row.getAttribute('data-shepherd-name');
                                const photo = row.querySelector('.profile-photo').src;

                                document.getElementById('viewMemberName').textContent = firstName + ' ' + lastName;
                                document.getElementById('viewMemberFirstName').textContent = firstName;
                                document.getElementById('viewMemberLastName').textContent = lastName;
                                document.getElementById('viewMemberDOB').textContent = dob;
                                document.getElementById('viewMemberGender').textContent = gender;
                                document.getElementById('viewMemberMaritalStatus').textContent = maritalStatus;
                                document.getElementById('viewMemberContact').textContent = contact;
                                document.getElementById('viewMemberEmail').textContent = email;
                                document.getElementById('viewMemberAddress').textContent = address;
                                document.getElementById('viewMemberDigitalAddress').textContent = digitalAddress;
                                document.getElementById('viewMemberOccupation').textContent = occupation;
                                document.getElementById('viewMemberEmployer').textContent = employer;
                                document.getElementById('viewMemberWorkPhone').textContent = workPhone;
                                document.getElementById('viewMemberEducation').textContent = education;
                                document.getElementById('viewMemberInstitution').textContent = institution;
                                document.getElementById('viewMemberYearGraduated').textContent = yearGraduated;
                                document.getElementById('viewMemberAssembly').textContent = assembly;
                                document.getElementById('viewMemberStatus').textContent = status;
                                document.getElementById('viewMemberJoinedDate').textContent = joinedDate;
                                document.getElementById('viewMemberReferral').textContent = referralName;
                                document.getElementById('viewMemberShepherd').textContent = shepherdName;
                                document.getElementById('viewMemberPhoto').src = photo;

                                const viewMemberModal = new bootstrap.Modal(document.getElementById('viewMemberModal'));
                                viewMemberModal.show();
                            }
                        });
                    });
                }
            }, 1000);
        } else {
            showAlert('danger', 'Error: ' + data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'An error occurred: ' + error.message);
    });
}

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
}

// Add this function for consistent badge coloring
function getGradientColor(assemblyId) {
    // Use the same color logic as in index.php
    const gradientColors = [
        'linear-gradient(45deg, #007bff, #00d4ff)',
        'linear-gradient(45deg, #28a745, #6fcf97)',
        'linear-gradient(45deg, #ffc107, #ffca28)',
        'linear-gradient(45deg, #17a2b8, #4fc3f7)',
        'linear-gradient(45deg, #dc3545, #ff6b6b)',
        'linear-gradient(45deg, #6c757d, #b0b5b9)',
        'linear-gradient(45deg, #ff6f61, #ff9f84)',
        'linear-gradient(45deg, #9c27b0, #ce93d8)',
        'linear-gradient(45deg, #ff9800, #ffb74d)',
        'linear-gradient(45deg, #e91e63, #f06292)',
        'linear-gradient(45deg, #4caf50, #81c784)',
        'linear-gradient(45deg, #3f51b5, #7986cb)'
    ];
    if (!window.assemblyMap) return 'linear-gradient(45deg, #6c757d, #b0b5b9)';
    if (assemblyId === 'N/A' || !assemblyId || window.assemblyMap[assemblyId] === 'N/A') {
        return 'linear-gradient(45deg, #6c757d, #b0b5b9)';
    }
    // Hash the assemblyId to get a consistent color
    let hash = 0;
    for (let i = 0; i < String(assemblyId).length; i++) {
        hash = String(assemblyId).charCodeAt(i) + ((hash << 5) - hash);
    }
    const index = Math.abs(hash) % gradientColors.length;
    return gradientColors[index];
}
</script>
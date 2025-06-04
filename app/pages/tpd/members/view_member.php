<!-- View Member Modal -->

<div class="modal fade" id="viewMemberModal" tabindex="-1" aria-labelledby="viewMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content view-member-modal">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewMemberModalLabel">Member Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header text-center border-0 bg-transparent">
                        <img src="" alt="Profile Photo" class="rounded-circle profile-photo-large" id="viewMemberPhoto">
                        <h4 class="mt-2" id="viewMemberName"></h4>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-3" id="memberProfileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab" aria-controls="personal" aria-selected="true">
                                    <i class="bi bi-person-fill me-2"></i> Personal Details
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">
                                    <i class="bi bi-telephone-fill me-2"></i> Contact Info
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button" role="tab" aria-controls="employment" aria-selected="false">
                                    <i class="bi bi-briefcase-fill me-2"></i> Employment
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="education-tab" data-bs-toggle="tab" data-bs-target="#education" type="button" role="tab" aria-controls="education" aria-selected="false">
                                    <i class="bi bi-book-fill me-2"></i> Education
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="membership-tab" data-bs-toggle="tab" data-bs-target="#membership" type="button" role="tab" aria-controls="membership" aria-selected="false">
                                    <i class="bi bi-building-fill me-2"></i> Membership
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="credentials-tab" data-bs-toggle="tab" data-bs-target="#credentials" type="button" role="tab" aria-controls="credentials" aria-selected="false">
                                    <i class="bi bi-key-fill me-2"></i> Temporal Credentials
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content" id="memberProfileTabContent">
                            <!-- Personal Details Tab -->
                            <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th>First Name</th>
                                            <td id="viewMemberFirstName"></td>
                                        </tr>
                                        <tr>
                                            <th>Last Name</th>
                                            <td id="viewMemberLastName"></td>
                                        </tr>
                                        <tr>
                                            <th>Date of Birth</th>
                                            <td id="viewMemberDOB"></td>
                                        </tr>
                                        <tr>
                                            <th>Gender</th>
                                            <td id="viewMemberGender"></td>
                                        </tr>
                                        <tr>
                                            <th>Marital Status</th>
                                            <td id="viewMemberMaritalStatus"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Contact Info Tab -->
                            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th>Contact</th>
                                            <td id="viewMemberContact"></td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td id="viewMemberEmail"></td>
                                        </tr>
                                        <tr>
                                            <th>Address</th>
                                            <td id="viewMemberAddress"></td>
                                        </tr>
                                        <tr>
                                            <th>Digital Address</th>
                                            <td id="viewMemberDigitalAddress"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Employment Tab -->
                            <div class="tab-pane fade" id="employment" role="tabpanel" aria-labelledby="employment-tab">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th>Occupation</th>
                                            <td id="viewMemberOccupation"></td>
                                        </tr>
                                        <tr>
                                            <th>Employer</th>
                                            <td id="viewMemberEmployer"></td>
                                        </tr>
                                        <tr>
                                            <th>Work Phone</th>
                                            <td id="viewMemberWorkPhone"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Education Tab -->
                            <div class="tab-pane fade" id="education" role="tabpanel" aria-labelledby="education-tab">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th>Highest Education Level</th>
                                            <td id="viewMemberEducation"></td>
                                        </tr>
                                        <tr>
                                            <th>Institution</th>
                                            <td id="viewMemberInstitution"></td>
                                        </tr>
                                        <tr>
                                            <th>Year Graduated</th>
                                            <td id="viewMemberYearGraduated"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Membership Tab -->
                            <div class="tab-pane fade" id="membership" role="tabpanel" aria-labelledby="membership-tab">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th>Assembly</th>
                                            <td id="viewMemberAssembly"></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td id="viewMemberStatus"></td>
                                        </tr>
                                        <tr>
                                            <th>Joined Date</th>
                                            <td id="viewMemberJoinedDate"></td>
                                        </tr>
                                        <tr>
                                            <th>Referral</th>
                                            <td id="viewMemberReferral"></td>
                                        </tr>
                                        <tr>
                                            <th>Household</th>
                                            <td id="viewMemberHousehold"></td>
                                        </tr>
                                        <tr>
                                            <th>Group</th>
                                            <td id="viewMemberGroupName"></td>
                                        </tr>
                                        <tr>
                                            <th>Shepherd</th>
                                            <td id="viewMemberShepherdName"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Temporal Credentials Tab -->
                            <div class="tab-pane fade" id="credentials" role="tabpanel" aria-labelledby="credentials-tab">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th>Username</th>
                                            <td id="viewMemberUsername"></td>
                                        </tr>
                                        <tr>
                                            <th>Password</th>
                                            <td id="viewMemberPassword"></td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td id="viewMemberCreatedAt"></td>
                                        </tr>
                                        <tr>
                                            <th>Updated At</th>
                                            <td id="viewMemberUpdatedAt"></td>
                                        </tr>
                                        <tr>
                                            <th>Created By</th>
                                            <td id="viewMemberCreatedBy"></td>
                                        </tr>
                                        <tr>
                                            <th>Updated By</th>
                                            <td id="viewMemberUpdatedBy"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .view-member-modal .modal-content {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3); /* Strong border shadow */
    }
    .view-member-modal .card {
        border: none;
    }
    .view-member-modal .nav-tabs {
        border-bottom: none;
    }
    .view-member-modal .nav-link {
        background: linear-gradient(45deg, #007bff, #00d4ff); /* Gradient blue */
        color: white;
        border: none;
        border-radius: 5px 5px 0 0;
        margin-right: 5px;
        transition: background 0.3s;
    }
    .view-member-modal .nav-link.active {
        background: linear-gradient(45deg, #0056b3, #0096c7); /* Darker gradient for active tab */
        color: white;
    }
    .view-member-modal .nav-link:hover {
        background: linear-gradient(45deg, #0056b3, #0096c7);
    }
    .view-member-modal .tab-content {
        border: 1px solid #dee2e6;
        border-radius: 0 0 5px 5px;
        padding: 20px;
    }
</style>

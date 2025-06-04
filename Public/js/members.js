// Function to handle image loading errors
function handleImageError(img) {
    img.onerror = null;
    img.src = '../../../resources/assets/default-profile.jpg';
}

// Function to show alerts
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
        alertContainer.classList.add('fade');
        setTimeout(() => alertContainer.remove(), 150);
    }, 5000);
}

// Function to populate the view modal
function populateViewModal(row) {
    try {
        console.log('Starting to populate view modal');

        // Set member name in header
        const firstName = row.getAttribute('data-first-name');
        const lastName = row.getAttribute('data-last-name');
        const fullName = `${firstName} ${lastName}`;
        document.getElementById('viewMemberName').textContent = fullName;
        console.log(`Populating modal for member: ${fullName}`);

        // Populate profile photo
        const photoElement = document.getElementById('viewMemberPhoto');
        if (photoElement) {
            const profileImg = row.querySelector('.profile-photo');
            if (profileImg) {
                photoElement.src = profileImg.src;
                photoElement.onerror = () => handleImageError(photoElement);
            }
        }

        // Populate all fields
        const fields = {
            'FirstName': 'first-name',
            'LastName': 'last-name',
            'DOB': 'dob',
            'Gender': 'gender',
            'MaritalStatus': 'marital-status',
            'Contact': 'contact',
            'Email': 'email',
            'Address': 'address',
            'DigitalAddress': 'digital-address',
            'Occupation': 'occupation',
            'Employer': 'employer',
            'WorkPhone': 'work-phone',
            'Education': 'education',
            'Institution': 'institution',
            'YearGraduated': 'year-graduated',
            'Assembly': 'assembly',
            'Status': 'status',
            'JoinedDate': 'joined-date',
            'Household': 'household',
            'Referral': 'referral',
            'Shepherd': 'shepherd-name'
        };

        for (const [elementId, attr] of Object.entries(fields)) {
            const element = document.getElementById(`viewMember${elementId}`);
            if (element) {
                const value = row.getAttribute(`data-${attr}`);
                element.textContent = value || 'N/A';
            }
        }

        // Show the modal
        const viewModal = new bootstrap.Modal(document.getElementById('viewMemberModal'));
        viewModal.show();
        console.log('Modal shown successfully');
    } catch (error) {
        console.error('Error populating modal:', error);
        showAlert('danger', 'Error showing member details');
    }
}

// Function to attach click handlers to clickable elements
function attachClickHandlers() {
    // Handle clickable elements for view modal
    document.querySelectorAll('.profile-photo, .member-name, .btn-gradient-blue.clickable').forEach(element => {
        element.addEventListener('click', function (e) {
            e.preventDefault();
            const memberId = this.getAttribute('data-member-id') ||
                (this.closest('tr') ? this.closest('tr').getAttribute('data-member-id') : null);

            if (memberId) {
                const row = document.querySelector(`tr[data-member-id="${memberId}"]`);
                if (row) {
                    populateViewModal(row);
                } else {
                    console.error('No row found for member ID:', memberId);
                }
            } else {
                console.error('No member ID found');
            }
        });
    });
}

// Initialize when the DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    // Add event listeners to all images for error handling
    document.querySelectorAll('.profile-photo').forEach(img => {
        img.onerror = () => handleImageError(img);
    });

    // Attach click handlers
    attachClickHandlers();

    // Re-attach handlers after any table updates
    const targetNode = document.querySelector('#membersTable tbody');
    const config = { childList: true, subtree: true };
    const observer = new MutationObserver(() => attachClickHandlers());
    observer.observe(targetNode, config);
});

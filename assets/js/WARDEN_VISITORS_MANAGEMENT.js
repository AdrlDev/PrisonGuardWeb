document.addEventListener('DOMContentLoaded', function () {
    const notification = document.getElementById('notification');

    // Show notification if there's a message
    if (notification.textContent.trim() !== '') {
        notification.classList.add('show');

        // Hide notification after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');

            // Remove notification parameters from URL without reloading
            if (window.history.replaceState && /[?&](action|id)=/.test(window.location.search)) {
                const url = new URL(window.location);
                url.searchParams.delete('action');
                url.searchParams.delete('id');
                window.history.replaceState({}, '', url);
            }
        }, 5000);
    }

    // Prevent multiple clicks on action buttons
    const actionButtons = document.querySelectorAll('.btn-accept, .btn-reject');
    actionButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Processing...';
            this.style.opacity = '0.7';
            this.style.pointerEvents = 'none';

            // Revert after 3 seconds if the page doesn't change
            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.opacity = '1';
                this.style.pointerEvents = 'auto';
            }, 3000);
        });
    });

    // View visitor details functionality
    const viewButtons = document.querySelectorAll('.view-visitor-btn');
    const viewModal = new bootstrap.Modal(document.getElementById('viewVisitorModal'));
    const visitorDetailsContent = document.getElementById('visitorDetailsContent');

    viewButtons.forEach(button => {
        button.addEventListener('click', function () {
            const visitorId = this.getAttribute('data-id');

            // Fetch visitor details via AJAX
            fetch('?ajax=get_visitor_details&id=' + visitorId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const visitor = data.visitor;

                        // Format the visitor details
                        visitorDetailsContent.innerHTML = `
                                    <div class="visitor-detail-row">
                                        <div class="visitor-detail-label">Full Name:</div>
                                        <div class="visitor-detail-value">${visitor.firstName} ${visitor.lastName} ${visitor.middleName || ''}</div>
                                    </div>
                                    <div class="visitor-detail-row">
                                        <div class="visitor-detail-label">Gender:</div>
                                        <div class="visitor-detail-value">${visitor.gender || 'Not specified'}</div>
                                    </div>
                                    <div class="visitor-detail-row">
                                        <div class="visitor-detail-label">Phone Number:</div>
                                        <div class="visitor-detail-value">${visitor.phoneNumber || 'Not provided'}</div>
                                    </div>
                                    <div class="visitor-detail-row">
                                        <div class="visitor-detail-label">Permanent Address:</div>
                                        <div class="visitor-detail-value">${visitor.permanentAddress || 'Not provided'}</div>
                                    </div>
                                    <div class="visitor-detail-row">
                                        <div class="visitor-detail-label">Relationship to Inmate:</div>
                                        <div class="visitor-detail-value">${visitor.relationship || 'Not specified'}</div>
                                    </div>
                                    <div class="visitor-detail-row">
                                        <div class="visitor-detail-label">ID Type:</div>
                                        <div class="visitor-detail-value">${visitor.idType || 'Not specified'}</div>
                                    </div>
                                    <div class="visitor-detail-row">
                                        <div class="visitor-detail-label">ID Number:</div>
                                        <div class="visitor-detail-value">${visitor.idNumber || 'Not provided'}</div>
                                    </div>
                                    <div class="visitor-detail-row">
                                        <div class="visitor-detail-label">Inmate to Visit:</div>
                                        <div class="visitor-detail-value">${visitor.inmate || 'Not specified'}</div>
                                    </div>
                                    <div class="visitor-detail-row">
                                        <div class="visitor-detail-label">Status:</div>
                                        <div class="visitor-detail-value">${visitor.status || 'Unknown'}</div>
                                    </div>
                                    ${visitor.submitted_at ? `
                                    <div class="visitor-detail-row">
                                        <div class="visitor-detail-label">Submitted On:</div>
                                        <div class="visitor-detail-value">${new Date(visitor.submitted_at).toLocaleString()}</div>
                                    </div>
                                    ` : ''}
                                `;

                        // Show the modal
                        viewModal.show();
                    } else {
                        alert('Error loading visitor details: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading visitor details');
                });
        });
    });

    // Edit visitor functionality
    const editButtons = document.querySelectorAll('.edit-visitor-btn');
    const editModal = new bootstrap.Modal(document.getElementById('editVisitorModal'));
    const editVisitorContent = document.getElementById('editVisitorContent');
    const editVisitorForm = document.getElementById('editVisitorForm');

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const visitorId = this.getAttribute('data-id');

            // Fetch visitor details for editing
            fetch('?ajax=get_visitor_details&id=' + visitorId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const visitor = data.visitor;

                        // Create the edit form
                        editVisitorContent.innerHTML = `
                                    <input type="hidden" name="visitor_id" value="${visitor.id}">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" name="firstName" value="${visitor.firstName || ''}" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" name="lastName" value="${visitor.lastName || ''}" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Middle Name</label>
                                            <input type="text" class="form-control" name="middleName" value="${visitor.middleName || ''}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Gender</label>
                                            <select class="form-control" name="gender" required>
                                                <option value="">Select Gender</option>
                                                <option value="Male" ${visitor.gender === 'Male' ? 'selected' : ''}>Male</option>
                                                <option value="Female" ${visitor.gender === 'Female' ? 'selected' : ''}>Female</option>
                                                <option value="Other" ${visitor.gender === 'Other' ? 'selected' : ''}>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" name="phoneNumber" value="${visitor.phoneNumber || ''}" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Permanent Address</label>
                                            <input type="text" class="form-control" name="permanentAddress" value="${visitor.permanentAddress || ''}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Relationship to Inmate</label>
                                            <input type="text" class="form-control" name="relationship" value="${visitor.relationship || ''}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">ID Type</label>
                                            <select class="form-control" name="idType" required>
                                                <option value="">Select ID Type</option>
                                                <option value="National ID" ${visitor.idType === 'National ID' ? 'selected' : ''}>National ID</option>
                                                <option value="Drivers License" ${visitor.idType === 'Drivers License' ? 'selected' : ''}>Drivers License</option>
                                                <option value="Barangay ID" ${visitor.idType === 'Barangay ID' ? 'selected' : ''}>Barangay ID</option>
                                                <option value="PhilHealth" ${visitor.idType === 'PhilHealth' ? 'selected' : ''}>PhilHealth</option>
                                                <option value="Voters" ${visitor.idType === 'Voters' ? 'selected' : ''}>Voters ID</option>
                                                <option value="UMID" ${visitor.idType === 'UMID' ? 'selected' : ''}>UMID</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">ID Number</label>
                                            <input type="text" class="form-control" name="idNumber" value="${visitor.idNumber || ''}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Inmate to Visit</label>
                                            <input type="text" class="form-control" name="inmate" value="${visitor.inmate || ''}" required>
                                        </div>
                                    </div>
                                `;

                        // Show the modal
                        editModal.show();
                    } else {
                        alert('Error loading visitor details: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading visitor details');
                });
        });
    });

    // Delete visitor functionality
    const deleteButtons = document.querySelectorAll('.delete-visitor-btn');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteVisitorModal'));
    const deleteVisitorName = document.getElementById('deleteVisitorName');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    let currentDeleteId = null;

    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            currentDeleteId = button.getAttribute('data-id');
            const visitorName = button.getAttribute('data-name');
            deleteVisitorName.textContent = visitorName;
            deleteModal.show();
        });
    });

    confirmDeleteBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (!currentDeleteId) return;
        fetch(`?action=delete&id=${currentDeleteId}`, { method: 'GET' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = document.querySelector(`[data-row-id="${currentDeleteId}"]`);
                    if (row) row.remove();
                    deleteModal.hide();
                    currentDeleteId = null;
                } else {
                    alert('Failed to delete visitor.');
                }
            })
            .catch(() => {
                alert('Failed to connect to server.');
            });
    });
});

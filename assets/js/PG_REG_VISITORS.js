        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const visitorRows = document.querySelectorAll('.visitor-row');
            const noSearchState = document.getElementById('noSearchState');
            const emptyState = document.getElementById('emptyState');
            const tableContainer = document.getElementById('tableContainer');
            
            // Show/hide initial states
            if (visitorRows.length > 0) {
                tableContainer.style.display = 'block';
                noSearchState.style.display = 'none';
                emptyState.style.display = 'none';
            } else {
                tableContainer.style.display = 'none';
                noSearchState.style.display = 'block';
                emptyState.style.display = 'none';
            }
            
            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchValue = this.value.toLowerCase();
                let hasResults = false;
                
                if (searchValue === '') {
                    // Show all rows if search is empty
                    visitorRows.forEach(row => {
                        row.style.display = '';
                    });
                    
                    if (visitorRows.length > 0) {
                        tableContainer.style.display = 'block';
                        noSearchState.style.display = 'none';
                        emptyState.style.display = 'none';
                    } else {
                        tableContainer.style.display = 'none';
                        noSearchState.style.display = 'block';
                        emptyState.style.display = 'none';
                    }
                    return;
                }
                
                // Filter rows based on search
                visitorRows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();
                    if (rowText.includes(searchValue)) {
                        row.style.display = '';
                        hasResults = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Show appropriate state
                if (hasResults) {
                    tableContainer.style.display = 'block';
                    noSearchState.style.display = 'none';
                    emptyState.style.display = 'none';
                } else {
                    tableContainer.style.display = 'none';
                    noSearchState.style.display = 'none';
                    emptyState.style.display = 'block';
                }
            });
            
            // View visitor details functionality
            const viewButtons = document.querySelectorAll('.view-visitor-btn');
            const viewModal = new bootstrap.Modal(document.getElementById('viewVisitorModal'));
            const visitorDetailsContent = document.getElementById('visitorDetailsContent');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
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
        });
    document.addEventListener('DOMContentLoaded', function() {
        let currentDeleteId = null;

        // Export to Excel functionality
        document.getElementById('exportExcelBtn').addEventListener('click', function() {
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> Exporting...';
            this.disabled = true;

            // Create a form to trigger the download
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'WARDEN_INMATES_DATA.php';
            form.style.display = 'none';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'export';
            form.appendChild(actionInput);

            document.body.appendChild(form);
            form.submit();

            // Remove form after submission
            setTimeout(() => {
                document.body.removeChild(form);
                // Reset button
                this.innerHTML = originalText;
                this.disabled = false;
            }, 1000);
        });

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('inmatesTableBody');
        const clearSearchBtn = document.getElementById('clearSearchBtn');

        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase().trim();
            const rows = tableBody.getElementsByTagName('tr');

            // Show/hide clear button
            clearSearchBtn.style.display = filter ? 'block' : 'none';

            // If search is empty, show all rows
            if (filter === '') {
                for (let i = 0; i < rows.length; i++) {
                    if (!rows[i].querySelector('.no-results-row')) {
                        rows[i].style.display = '';
                    }
                }
                // Remove no-results row if it exists
                const existingNoResults = tableBody.querySelector('.no-results-row');
                if (existingNoResults) {
                    existingNoResults.remove();
                }
                return;
            }

            // Filter rows based on search term
            let visibleCount = 0;
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                
                // Skip if this is the "no data" row or "no results" row
                if (row.querySelector('.empty-state') || row.querySelector('.no-results-row')) {
                    continue;
                }

                const cells = row.getElementsByTagName('td');
                let found = false;

                // Search in: Date Created, Inmate Code, Name, Status
                for (let j = 0; j < 4; j++) { // Only search first 4 columns (exclude action column)
                    if (cells[j]) {
                        const cellText = cells[j].textContent.toLowerCase();
                        if (cellText.includes(filter)) {
                            found = true;
                            break;
                        }
                    }
                }

                // Show/hide row based on search result
                if (found) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }

            // Remove existing no-results row
            const existingNoResults = tableBody.querySelector('.no-results-row');
            if (existingNoResults) {
                existingNoResults.remove();
            }

            // If no results found, show a "no results" message
            if (visibleCount === 0) {
                const noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = `<td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-search me-2"></i>No inmates found matching "${this.value}"
                </td>`;
                tableBody.appendChild(noResultsRow);
            }
        });

        // Clear search functionality
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });

        // Clear search when input is cleared
        searchInput.addEventListener('keydown', function(e) {
            // Clear search on Escape key
            if (e.key === 'Escape') {
                this.value = '';
                this.dispatchEvent(new Event('input'));
                this.blur();
            }
        });

        // View button functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('viewBtn') || e.target.closest('.viewBtn')) {
                const btn = e.target.closest('.viewBtn');
                const id = btn.getAttribute('data-id');
                
                // Fetch inmate data
                fetch('WARDEN_INMATES_DATA.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=view&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    // Populate view modal
                    document.getElementById('viewFirstName').textContent = data.firstName || '';
                    document.getElementById('viewMiddleName').textContent = data.middleName || '';
                    document.getElementById('viewLastName').textContent = data.lastName || '';
                    document.getElementById('viewBirthday').textContent = data.birthday || '';
                    document.getElementById('viewGender').textContent = data.gender || '';
                    document.getElementById('viewAddress').textContent = data.address || '';
                    document.getElementById('viewMaritalStatus').textContent = data.maritalStatus || '';
                    document.getElementById('viewInmateNumber').textContent = data.inmateNumber || '';
                    document.getElementById('viewCrimeCommitted').textContent = data.crimeCommitted || '';
                    document.getElementById('viewSentence').textContent = data.sentence || '';
                    document.getElementById('viewTimeServeStart').textContent = data.timeServeStart || '';
                    document.getElementById('viewTimeServeEnds').textContent = data.timeServeEnds || '';
                    document.getElementById('viewStatus').textContent = data.status || '';
                    document.getElementById('viewDateCreated').textContent = data.dateCreated || '';

                    // Show modal
                    new bootstrap.Modal(document.getElementById('viewModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading inmate data');
                });
            }

            // Edit button functionality
            if (e.target.classList.contains('editBtn') || e.target.closest('.editBtn')) {
                const btn = e.target.closest('.editBtn');
                const id = btn.getAttribute('data-id');
                
                // Fetch inmate data
                fetch('WARDEN_INMATES_DATA.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=view&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    // Populate edit modal
                    document.getElementById('editId').value = data.id;
                    document.getElementById('editFirstName').value = data.firstName || '';
                    document.getElementById('editMiddleName').value = data.middleName || '';
                    document.getElementById('editLastName').value = data.lastName || '';
                    document.getElementById('editInmateNumber').value = data.inmateNumber || '';
                    document.getElementById('editStatus').value = data.status || '';
                    document.getElementById('editServeStart').value = data.timeServeStart || '';
                    document.getElementById('editServeEnds').value = data.timeServeEnds || '';

                    // Show modal
                    new bootstrap.Modal(document.getElementById('editModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading inmate data');
                });
            }

            // Delete button functionality
            if (e.target.classList.contains('deleteBtn') || e.target.closest('.deleteBtn')) {
                const btn = e.target.closest('.deleteBtn');
                const id = btn.getAttribute('data-id');
                currentDeleteId = id;
                
                // Show delete confirmation modal
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            }
        });

        // Edit form submission
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('WARDEN_INMATES_DATA.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const result = data.trim();
                if (result === 'success') {
                    alert('Inmate updated successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                    location.reload(); // Refresh the page to show updated data
                } else if (result.includes('error:')) {
                    alert(result); // Show the specific error message
                } else {
                    alert('Error updating inmate: ' + result);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error updating inmate');
            });
        });

        // Confirm delete functionality
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (currentDeleteId) {
                fetch('WARDEN_INMATES_DATA.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&id=${currentDeleteId}`
                })
                .then(response => response.text())
                .then(data => {
                    const result = data.trim();
                    if (result === 'success') {
                        alert('Inmate deleted successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                        location.reload(); // Refresh the page
                    } else if (result.includes('error:')) {
                        alert(result); // Show the specific error message
                    } else {
                        alert('Error deleting inmate: ' + result);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error deleting inmate');
                });

                currentDeleteId = null;
            }
        });
    });

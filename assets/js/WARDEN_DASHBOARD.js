        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#guardTableBody tr');
            
            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                if (name.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Auto-refresh data every 60 seconds
        setInterval(function() {
            location.reload();
        }, 60000);
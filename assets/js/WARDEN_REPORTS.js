// Current active tab
        let currentTab = 'visitors';

        // Function to switch between tabs
        function switchTab(tabType) {
            const visitorsTab = document.getElementById('visitorsTab');
            const guardsTab = document.getElementById('guardsTab');
            const visitorsTable = document.getElementById('visitorsTable');
            const guardsTable = document.getElementById('guardsTable');
            const tableTitle = document.getElementById('tableTitle');
            const searchInput = document.getElementById('searchInput');

            // Remove active class from all tabs
            visitorsTab.classList.remove('active');
            guardsTab.classList.remove('active');

            // Hide all tables
            visitorsTable.style.display = 'none';
            guardsTable.style.display = 'none';

            if (tabType === 'visitors') {
                // Activate visitors tab
                visitorsTab.classList.add('active');
                visitorsTable.style.display = 'block';
                tableTitle.textContent = 'Visitors Log';
                searchInput.placeholder = 'Search Visitors...';
                currentTab = 'visitors';
            } else {
                // Activate guards tab
                guardsTab.classList.add('active');
                guardsTable.style.display = 'block';
                tableTitle.textContent = 'Prison Guards';
                searchInput.placeholder = 'Search Prison Guards...';
                currentTab = 'guards';
            }
        }

        // Function to handle search input
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            console.log(`Searching ${currentTab}:`, searchTerm);
            
            // Here you would implement the actual search functionality
            // For now, it just logs the search term
        });

        // Function to export to PDF
        function exportToPDF() {
            const currentDate = new Date().toLocaleDateString();
            const reportType = currentTab === 'visitors' ? 'Visitors' : 'Prison Guards';
            
            alert(`Exporting ${reportType} Report to PDF...\nDate: ${currentDate}`);
            
            // Here you would implement the actual PDF export functionality
            console.log(`Exporting ${reportType} report to PDF`);
        }

        // Initialize the interface
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Reports interface initialized');
        });
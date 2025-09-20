// Search functionality
document.getElementById("inmateSearch").addEventListener("input", function () {
    let query = this.value;
    if (query.length > 1) {
        fetch("?search=" + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                let suggestions = document.getElementById("inmateSuggestions");
                suggestions.innerHTML = "";
                if (data.length > 0) {
                    data.forEach(inmate => {
                        let item = document.createElement("button");
                        item.type = "button";
                        item.classList.add("list-group-item", "list-group-item-action");
                        item.textContent = inmate.fullname + " – " + inmate.inmateNumber;
                        item.onclick = () => {
                            document.getElementById("inmateSearch").value = inmate.fullname + " – " + inmate.inmateNumber;
                            suggestions.innerHTML = "";
                        };
                        suggestions.appendChild(item);
                    });
                }
            })
            .catch(err => console.error(err));
    }
});

// Add search functionality for the main search input
document.getElementById("searchInput").addEventListener("input", function () {
    const searchValue = this.value.toLowerCase();
    const statusFilter = document.querySelector('.filter-btn.active-filter').dataset.status;
    const rows = document.querySelectorAll("#visitorsTableBody tr.visitor-row");

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const rowStatus = row.dataset.status;

        // Check if row matches both search and status filter
        const matchesSearch = text.includes(searchValue);
        const matchesStatus = statusFilter === 'all' || rowStatus === statusFilter;

        if (matchesSearch && matchesStatus) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});

// Add filter functionality
document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', function () {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active-filter');
        });
        this.classList.add('active-filter');

        const status = this.dataset.status;
        const searchValue = document.getElementById("searchInput").value.toLowerCase();
        const rows = document.querySelectorAll("#visitorsTableBody tr.visitor-row");

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.dataset.status;

            // Check if row matches both search and status filter
            const matchesSearch = text.includes(searchValue);
            const matchesStatus = status === 'all' || rowStatus === status;

            if (matchesSearch && matchesStatus) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});
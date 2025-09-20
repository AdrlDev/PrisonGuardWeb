<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitors Monitoring system</title>
    <link rel="shortcut icon" href="Occi.png" type="image/jpg">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../../assets/css/warden_reports.css">
    <link rel="stylesheet" href="../../includes/navbar/W_navbar.css" />
    <!-- Fix for Occi.png -->
    <link rel="shortcut icon" href="../../assets/img/Occi.png" type="image/jpg">
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../../includes/navbar/nav.php'; ?>
        <!-- Main Content -->
        <main class="main-content flex-fill">
            <!-- Header -->
              <?php include '../../includes/header/wreports.php'; ?>
            <!-- Reports Content -->
            <div class="reports-container">
                <!-- Search Bar -->
                <div class="search-container">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search Visitors..." id="searchInput">
                </div>
                
                <!-- Report Card -->
                <div class="report-card">
                    <div class="card-header">
                        <div class="tab-container">
                            <button class="tab-button active" onclick="switchTab('visitors')" id="visitorsTab">
                                Visitors Log
                            </button>
                        </div>
                        <button class="export-btn" onclick="exportToPDF()">
                            <i class="bi bi-download"></i>
                            Export as PDF
                        </button>
                    </div>
                    
                    <div class="table-container">
                        <h3 class="table-title" id="tableTitle">Visitors Log</h3>
                        
                        <!-- Visitors Table -->
                        <div id="visitorsTable">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Visitor Name</th>
                                        <th>Inmate to Visit</th>
                                        <th>Relationship to inmate</th>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="7" class="no-data">No visitor data available</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="../assets/js/WARDEN_REPORTS.js"></script>
</body>
</html>
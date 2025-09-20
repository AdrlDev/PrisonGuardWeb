<?php
require_once '../../classes/auth.php';
requireRole('warden');
require_once '../../database/connection.php'; 

function getOnDutyCount() {
    global $con_admin;
    
    // Changed to only count guards who have time_in but no time_out
    $sql = "SELECT COUNT(*) as total FROM users 
            WHERE role = 'prison_guard' 
            AND time_in IS NOT NULL 
            AND (time_out IS NULL OR time_out > NOW())";
    $result = $con_admin->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

// Function to get total guards count
function getTotalGuards() {
    global $con_admin;
    
    $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'prison_guard'";
    $result = $con_admin->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    return 0;
}

// Function to get total inmates count
function getTotalInmates() {
    global $con_admin;
    
    $sql = "SELECT COUNT(*) as total FROM inmates";
    $result = $con_admin->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    return 0;
}

// Function to get total visitors count
function getTotalVisitors() {
    global $con_admin;
    
    $sql = "SELECT COUNT(*) as total FROM visitors";
    $result = $con_admin->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    return 0;
}

// Function to get on-duty guards for the table
function getOnDutyGuards() {
    global $con_admin;
    
    $sql = "SELECT first_name, last_name, time_in, time_out
            FROM users 
            WHERE role = 'prison_guard' 
            ORDER BY time_in DESC";
    
    $result = $con_admin->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $output = '';
        while ($row = $result->fetch_assoc()) {
            // Determine status based on time_in and time_out
            $isOnDuty = ($row['time_in'] !== null && 
                        ($row['time_out'] === null || strtotime($row['time_out']) > time()));
            
            $status_class = $isOnDuty ? 'text-success' : 'text-danger';
            $status_text = $isOnDuty ? 'On Duty' : 'Off Duty';
            
            $output .= "<tr>
                <td>".htmlspecialchars($row['first_name'].' '.$row['last_name'])."</td>
                <td>".($row['time_in'] ? htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['time_in']))) : '-')."</td>
                <td>".($row['time_out'] ? htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['time_out']))) : '-')."</td>
                <td><span class='{$status_class}'>{$status_text}</span></td>
            </tr>";
        }
        return $output;
    }
    return '<tr><td colspan="4" class="text-center">No guards found</td></tr>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitors Monitoring system</title>
    <link rel="shortcut icon" href="../../assets/img/Occi.png" type="image/jpg">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../../assets/css/warden_dashboard.css">
    <link rel="stylesheet" href="../../includes/navbar/W_navbar.css" />
</head>

<body>
    <div class="d-flex">
        <?php include '../../includes/navbar/nav.php'; ?>
    

            <!-- Main Content -->
            <main class="main-content flex-fill">
        <?php include '../../includes/header/w_dashboard_header.php'; ?>

 <!-- Content -->
        <div class="container-fluid p-4">
                <!-- Stats Grid -->
                <div class="row g-4 mb-4 stats-row">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card stats-card blue h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-value"><?php echo getOnDutyCount(); ?></div>
                                    <div class="stats-label">On Duty Now</div>
                                </div>
                                <i class="bi bi-person-check fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card stats-card green h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-value"><?php echo getTotalGuards(); ?></div>
                                    <div class="stats-label">Total Prison Guards</div>
                                </div>
                                <i class="bi bi-shield-check fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card stats-card purple h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-value"><?php echo getTotalInmates(); ?></div>
                                    <div class="stats-label"> Total Inmates</div>
                                </div>
                                <i class="bi bi-people fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card stats-card orange h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-value"><?php echo getTotalVisitors(); ?></div>
                                    <div class="stats-label">Total Visitors</div>
                                </div>
                                <i class="bi bi-bar-chart fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visitor Table -->
                <div class="table-container">
                    <h2 class="table-header">Current Prison Guard</h2>
                    
                    <!-- Search -->
                    <div class="search-container">
                        <div class="position-relative">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" class="form-control search-input" placeholder="Search Prison Guard..." id="searchInput">
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Prison Guard</th>
                                    <th>Time in</th>
                                    <th>Time out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="guardTableBody">
                                <?php echo getOnDutyGuards(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../assets/js/WARDEN_DASHBOARD.js"></script>
    </body>
</html>
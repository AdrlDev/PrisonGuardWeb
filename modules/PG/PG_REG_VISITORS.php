<?php
require_once '../../classes/auth.php';
requireRole('prison_guard');
require_once '../../database/connection.php';

// Get only approved visitors
$approved_visitors = [];

// Get approved visitors
$sql_approved ="SELECT v.*, p.submitted_at as registration_date 
                FROM visitors v 
                LEFT JOIN pending_visitors p ON v.firstName = p.firstName AND v.lastName = p.lastName AND v.inmate = p.inmate
                ORDER BY p.submitted_at DESC";
$result_approved = $con_admin->query($sql_approved);
if ($result_approved) {
    while ($row = $result_approved->fetch_assoc()) {
        $approved_visitors[] = $row;
    }
}

// Handle AJAX request for visitor details
if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_visitor_details' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get visitor details
    $sql = "SELECT * FROM pending_visitors WHERE id = ?";
    $stmt = $con_admin->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $visitor = $result->fetch_assoc();
        echo json_encode(['success' => true, 'visitor' => $visitor]);
    } else {
        // Try to get from visitors table if not found in pending_visitors
        $sql = "SELECT * FROM visitors WHERE id = ?";
        $stmt = $con_admin->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $visitor = $result->fetch_assoc();
            $visitor['status'] = 'approved';
            echo json_encode(['success' => true, 'visitor' => $visitor]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Visitor not found']);
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Visitors Monitoring system</title>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitors Monitoring system</title>
    <link rel="shortcut icon" href="../../assets/img/Occi.png" type="image/jpg">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/PG_REG_VISITORS.css">
    <link rel="stylesheet" href="../../includes/navbar/PG_navbar.css">
    
</head>

    <body>
        <?php include '../../includes/navbar/pg_nav.php'; ?>
            <!-- Main Content -->
            <main class="main-content flex-fill">
            <!-- Header -->
            <?php include '../../includes/header/pg_reg.php'; ?>
            <!-- Content -->
              <!-- View Visitor Modal -->
        <div class="modal fade view-modal" id="viewVisitorModal" tabindex="-1" aria-labelledby="viewVisitorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewVisitorModalLabel">
                            <i class="bi bi-person-badge me-2"></i>Visitor Details
                        </h5>
                    </div>
                    <div class="modal-body" id="visitorDetailsContent">

                        <!-- Visitor details will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
                    <!-- Visitors Log Table -->
                    <div class="table-card">
                        <div class="table-header">
                           
                           
                            
                            <!-- Search Section -->
                            <div class="search-section">
                                <div class="search-container">
                                    <i class="bi bi-search search-icon"></i>
                                    <input type="text" class="search-input" id="searchInput" placeholder="Search by name, inmate, or ID number...">
                                </div>
                            </div>
                        </div>

                        <!-- Table with simplified structure -->
                        <div class="table-container" id="tableContainer">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Visitor Name</th>
                                        <th>Inmate Visited</th>
                                        <th>Relationship</th>
                                        <th>ID Number</th>
                                        <th class="action-cell">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="visitorsTableBody">
                                    <?php if (count($approved_visitors) > 0): ?>
                                        <?php foreach ($approved_visitors as $visitor): ?>
                                        <tr class="visitor-row">
                                            <td><?php echo $visitor['firstName'] . ' ' . $visitor['lastName']; ?></td>
                                            <td><?php echo $visitor['inmate']; ?></td>
                                            <td><?php echo $visitor['relationship']; ?></td>
                                            <td><?php echo $visitor['idNumber']; ?></td>
                                            <td>
                                                <button class="btn btn-view view-visitor-btn" data-id="<?php echo $visitor['id']; ?>">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">No approved visitors found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Default State - No Search -->
                        <div class="no-search-state" id="noSearchState" style="display: <?php echo count($approved_visitors) > 0 ? 'none' : 'block'; ?>;">
                            <i class="bi bi-search search-icon-large"></i>
                            <h4>Search to View Records</h4>
                            <p>Enter a visitor name in the search box above to view visitor records.</p>
                        </div>

                        <!-- Empty Search Results -->
                        <div class="empty-state" id="emptyState" style="display: none;">
                            <i class="bi bi-search empty-icon"></i>
                            <h4>No Results Found</h4>
                            <p>No visitors found matching your search criteria. Try a different search term.</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../../assets/js/PG_REG_VISITORS.js"></script>
    </body>
</html>

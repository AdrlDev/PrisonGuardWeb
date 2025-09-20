<?php
require_once '../../classes/auth.php';
requireRole('warden');
require_once '../../database/connection.php';

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

// Handle form submission for editing visitor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_visitor'])) {
    $id = $_POST['visitor_id'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $middleName = $_POST['middleName'];
    $gender = $_POST['gender'];
    $phoneNumber = $_POST['phoneNumber'];
    $permanentAddress = $_POST['permanentAddress'];
    $relationship = $_POST['relationship'];
    $idType = $_POST['idType'];
    $idNumber = $_POST['idNumber'];
    $inmate = $_POST['inmate'];
    
    // Determine which table to update based on status
    $sql_check = "SELECT status FROM pending_visitors WHERE id = ?";
    $stmt_check = $con_admin->prepare($sql_check);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        // Update pending_visitors table
        $sql = "UPDATE pending_visitors SET 
                firstName = ?, lastName = ?, middleName = ?, gender = ?, 
                phoneNumber = ?, permanentAddress = ?, relationship = ?, 
                idType = ?, idNumber = ?, inmate = ? 
                WHERE id = ?";
    } else {
        // Update visitors table
        $sql = "UPDATE visitors SET 
                firstName = ?, lastName = ?, middleName = ?, gender = ?, 
                phoneNumber = ?, permanentAddress = ?, relationship = ?, 
                idType = ?, idNumber = ?, inmate = ? 
                WHERE id = ?";
    }
    
    $stmt = $con_admin->prepare($sql);
    $stmt->bind_param("ssssssssssi", 
        $firstName, $lastName, $middleName, $gender, 
        $phoneNumber, $permanentAddress, $relationship, 
        $idType, $idNumber, $inmate, $id
    );
    
    if ($stmt->execute()) {
        $notification_message = 'Visitor updated successfully!';
        $notification_type = 'success';
    } else {
        $notification_message = 'Error updating visitor!';
        $notification_type = 'error';
    }
}

// Initialize notification variables
$notification_message = '';
$notification_type = '';

// Handle approval/rejection/delete actions
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $success = false;

    $stmt2 = $con_admin->prepare("DELETE FROM visitors WHERE id = ?");
        if ($stmt2) {
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            if ($stmt2->affected_rows > 0) {
                $success = true;
            } else {
                $success = false;
            }
            $stmt2->close();
        }

    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
    exit;
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'approve') {
        // Get pending visitor data
        $sql = "SELECT * FROM pending_visitors WHERE id = ?";
        $stmt = $con_admin->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $visitor = $result->fetch_assoc();
        
        // Insert into visitors table
        $insert_sql = "INSERT INTO visitors 
                        (firstName, lastName, middleName, gender, phoneNumber, permanentAddress, relationship, idType, idNumber, inmate)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insert_stmt = $con_admin->prepare($insert_sql);
        $insert_stmt->bind_param("ssssssssss", 
            $visitor['firstName'], 
            $visitor['lastName'], 
            $visitor['middleName'], 
            $visitor['gender'], 
            $visitor['phoneNumber'], 
            $visitor['permanentAddress'], 
            $visitor['relationship'], 
            $visitor['idType'], 
            $visitor['idNumber'], 
            $visitor['inmate']
        );
        
        if ($insert_stmt->execute()) {
            // DELETE the visitor from pending_visitors instead of updating status
            $delete_sql = "DELETE FROM pending_visitors WHERE id = ?";
            $delete_stmt = $con_admin->prepare($delete_sql);
            $delete_stmt->bind_param("i", $id);
            $delete_stmt->execute();
            
            $notification_message = 'Visitor approved successfully!';
            $notification_type = 'success';
        }
    } elseif ($action == 'reject') {
        // Update pending visitor status to rejected
        $sql = "UPDATE pending_visitors SET status = 'rejected' WHERE id = ?";
        $stmt = $con_admin->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $notification_message = 'Visitor rejected successfully!';
        $notification_type = 'error';
    } elseif ($action == 'delete') {
        // First try to delete from pending_visitors
        $sql = "DELETE FROM pending_visitors WHERE id = ?";
        $stmt = $con_admin->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $notification_message = 'Visitor deleted successfully!';
            $notification_type = 'success';
        } else {
            // If not found in pending_visitors, try to delete from visitors
            $sql = "DELETE FROM visitors WHERE id = ?";
            $stmt = $con_admin->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $notification_message = 'Visitor deleted successfully!';
                $notification_type = 'success';
            } else {
                $notification_message = 'Error deleting visitor!';
                $notification_type = 'error';
            }
        }
    }
}

// Get visitor statistics
$stats = [];
$sql = "SELECT status, COUNT(*) as count FROM pending_visitors GROUP BY status";
$result = $con_admin->query($sql);
while ($row = $result->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
}

// Get pending visitors
$pending_visitors = [];
$sql = "SELECT * FROM pending_visitors WHERE status = 'pending' ORDER BY submitted_at DESC";
$result = $con_admin->query($sql);
while ($row = $result->fetch_assoc()) {
    $pending_visitors[] = $row;
}

// Get approved visitors
$approved_visitors = [];
$sql = "SELECT v.*, p.submitted_at as registration_date 
        FROM visitors v 
        LEFT JOIN pending_visitors p ON v.firstName = p.firstName AND v.lastName = p.lastName AND v.inmate = p.inmate
        ORDER BY p.submitted_at DESC";
$result = $con_admin->query($sql);
while ($row = $result->fetch_assoc()) {
    $approved_visitors[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitors Monitoring System</title>
    <link rel="shortcut icon" href="Occi.png" type="image/jpg">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="../../assets/css/warden_visitors_management.css">
    <link rel="stylesheet" href="../../includes/navbar/W_navbar.css">
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../../includes/navbar/nav.php'; ?>
        <!-- Main Content -->
        <main class="main-content flex-fill">
            <!-- Header  -->
            <?php include '../../includes/header/wvisitors.php'; ?>

            <!-- Notification Container-->
            <div id="notification" class="notification <?php echo $notification_type; ?>">
                <?php echo $notification_message; ?>
            </div>

            <!-- Main content starts here -->
            <div class="content-container"></div>
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value stat-pending"><?php echo isset($stats['pending']) ? $stats['pending'] : 0; ?>
                    </div>
                    <div class="stat-label">Pending Visitors</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value stat-approved"><?php echo count($approved_visitors); ?></div>
                    <div class="stat-label">Approved Visitors</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value stat-rejected">
                        <?php echo isset($stats['rejected']) ? $stats['rejected'] : 0; ?></div>
                    <div class="stat-label">Rejected Visitors</div>
                </div>
            </div>

            <div class="content-card">
                <!-- Pending Visitors Section -->
                <div class="section-header">
                    <h2 class="section-title">Pending Visitors</h2>
                </div>

                <?php if (count($pending_visitors) > 0): ?>
                <?php foreach ($pending_visitors as $visitor): ?>
                <div class="pending-visitor-card">
                    <div class="visitor-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="visitor-details">
                        <div class="visitor-info-row">
                            <span class="visitor-info-label">Visitor Name:</span>
                            <span
                                class="visitor-info-value"><?php echo $visitor['firstName'] . ' ' . $visitor['lastName']; ?></span>
                        </div>
                        <div class="visitor-info-row">
                            <span class="visitor-info-label">Inmate to Visit:</span>
                            <span class="visitor-info-value"><?php echo $visitor['inmate']; ?></span>
                        </div>
                        <div class="visitor-info-row">
                            <span class="visitor-info-label">Relationship:</span>
                            <span class="visitor-info-value"><?php echo $visitor['relationship']; ?></span>
                        </div>
                        <div class="visitor-info-row">
                            <span class="visitor-info-label">Submitted:</span>
                            <span
                                class="visitor-info-value"><?php echo date('M j, Y g:i A', strtotime($visitor['submitted_at'])); ?></span>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <!-- Pending visitors: View button only -->
                        <button class="btn btn-view btn-icon view-visitor-btn" data-id="<?php echo $visitor['id']; ?>"
                            title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="?action=approve&id=<?php echo $visitor['id']; ?>" class="btn btn-accept"
                            title="Approve">
                            <i class="bi bi-person-check"></i> Approve
                        </a>
                        <a href="?action=reject&id=<?php echo $visitor['id']; ?>" class="btn btn-reject" title="Reject">
                            <i class="bi bi-person-x"></i> Reject
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="alert alert-info">No pending visitor requests</div>
                <?php endif; ?>

                <!-- Registered Visitors Section -->
                <div class="registered-section">
                    <div class="section-header">
                        <h2 class="section-title">Registered Visitors</h2>
                    </div>

                    <div class="registered-table-container">
                        <div class="table-header-row">
                            <div class="table-header-cell">Visitor Name</div>
                            <div class="table-header-cell">Relationship</div>
                            <div class="table-header-cell">Inmate</div>
                            <div class="table-header-cell">Date Registered</div>
                            <div class="table-header-cell">Status</div>
                            <div class="table-header-cell table-action-cell">Actions</div>
                        </div>

                        <?php if (count($approved_visitors) > 0): ?>
                        <?php foreach ($approved_visitors as $visitor): ?>
                        <div class="table-row" data-row-id="<?php echo $visitor['id']; ?>">
                            <div class="table-cell"><?php echo $visitor['firstName'] . ' ' . $visitor['lastName']; ?>
                            </div>
                            <div class="table-cell"><?php echo $visitor['relationship']; ?></div>
                            <div class="table-cell"><?php echo $visitor['inmate']; ?></div>
                            <div class="table-cell">
                                <?php echo isset($visitor['registration_date']) ? date('M j, Y', strtotime($visitor['registration_date'])) : 'N/A'; ?>
                            </div>
                            <div class="table-cell"><span class="badge bg-success">Approved</span></div>
                            <div class="table-cell">
                                <div class="action-buttons">
                                    <!-- Approved visitors: View, Edit, Delete buttons -->
                                    <button class="btn btn-view btn-icon view-visitor-btn"
                                        data-id="<?php echo $visitor['id']; ?>" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-edit btn-icon edit-visitor-btn"
                                        data-id="<?php echo $visitor['id']; ?>" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-delete btn-icon delete-visitor-btn"
                                        data-id="<?php echo $visitor['id']; ?>"
                                        data-id-number="<?php echo $visitor['idNumber']; ?>"
                                        data-name="<?php echo $visitor['firstName'] . ' ' . $visitor['lastName']; ?>"
                                        title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <div class="empty-message">
                            No registered visitors available
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this visitor?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scanner Modal -->
            <div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Visitor Face Scanner</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <video id="cameraFeed" autoplay playsinline class="w-100 rounded"></video>
                            <canvas id="snapshotCanvas" class="d-none"></canvas>
                            <p class="mt-2 text-muted" id="progressText">Scanning face, please wait...</p>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </main>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/WARDEN_VISITORS_MANAGEMENT.js?v=2"></script>
    <script src="../../assets/js/delete_warden_visitor.js?v=7"></script>
</body>

</html>
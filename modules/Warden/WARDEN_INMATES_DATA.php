<?php
require_once '../../classes/auth.php';
requireRole('warden');
require_once '../../database/connection.php'; 

// Handle AJAX requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // 🟢 EXPORT TO EXCEL
    if ($action === 'export') {
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="inmates_data_' . date('Y-m-d_H-i-s') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Get all inmates data
        $sql = "SELECT id, firstName, middleName, lastName, birthday, gender, address, maritalStatus, 
                        inmateNumber, crimeCommitted, timeServeStart, sentence, timeServeEnds, 
                        dateCreated, status 
                FROM inmates 
                ORDER BY dateCreated DESC";
        
        $result = $con_admin->query($sql);

        // Start building Excel content
        echo '<table border="1">';
        echo '<thead>';
        echo '<tr style="background-color: #f8f9fa; font-weight: bold;">';
        echo '<th>ID</th>';
        echo '<th>First Name</th>';
        echo '<th>Middle Name</th>';
        echo '<th>Last Name</th>';
        echo '<th>Birthday</th>';
        echo '<th>Gender</th>';
        echo '<th>Address</th>';
        echo '<th>Marital Status</th>';
        echo '<th>Inmate Number</th>';
        echo '<th>Crime Committed</th>';
        echo '<th>Time Serve Start</th>';
        echo '<th>Sentence</th>';
        echo '<th>Time Serve Ends</th>';
        echo '<th>Date Created</th>';
        echo '<th>Status</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['firstName']) . '</td>';
                echo '<td>' . htmlspecialchars($row['middleName']) . '</td>';
                echo '<td>' . htmlspecialchars($row['lastName']) . '</td>';
                echo '<td>' . htmlspecialchars($row['birthday']) . '</td>';
                echo '<td>' . htmlspecialchars($row['gender']) . '</td>';
                echo '<td>' . htmlspecialchars($row['address']) . '</td>';
                echo '<td>' . htmlspecialchars($row['maritalStatus']) . '</td>';
                echo '<td>' . htmlspecialchars($row['inmateNumber']) . '</td>';
                echo '<td>' . htmlspecialchars($row['crimeCommitted']) . '</td>';
                echo '<td>' . htmlspecialchars($row['timeServeStart']) . '</td>';
                echo '<td>' . htmlspecialchars($row['sentence']) . '</td>';
                echo '<td>' . htmlspecialchars($row['timeServeEnds']) . '</td>';
                echo '<td>' . htmlspecialchars($row['dateCreated']) . '</td>';
                echo '<td>' . htmlspecialchars($row['status']) . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="15">No inmates found</td></tr>';
        }

        echo '</tbody>';
        echo '</table>';
        exit;
    }

    // 🟢 VIEW
    if ($action === 'view') {
        $id = intval($_POST['id']);
        $sql = "SELECT * FROM inmates WHERE id=?";
        $stmt = $con_admin->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    // 🟢 EDIT/UPDATE
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $firstName  = trim($_POST['firstName']);
        $middleName = trim($_POST['middleName']);
        $lastName   = trim($_POST['lastName']);
        $inmateNumber = trim($_POST['inmateNumber']);
        $status     = $_POST['status']; 
        $timeServeStart = $_POST['timeServeStart'];
        $timeServeEnds  = $_POST['timeServeEnds'];

        // Validate required fields
        if (empty($firstName) || empty($lastName) || empty($inmateNumber)) {
            echo "error: Required fields cannot be empty";
            exit;
        }

        // Check if inmate number already exists (excluding current record)
        $checkSql = "SELECT id FROM inmates WHERE inmateNumber = ? AND id != ?";
        $checkStmt = $con_admin->prepare($checkSql);
        $checkStmt->bind_param("si", $inmateNumber, $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            echo "error: Inmate number already exists";
            exit;
        }

        $sql = "UPDATE inmates 
                SET firstName=?, middleName=?, lastName=?, inmateNumber=?, status=?, timeServeStart=?, timeServeEnds=? 
                WHERE id=?";
        $stmt = $con_admin->prepare($sql);
        $stmt->bind_param("sssssssi", $firstName, $middleName, $lastName, $inmateNumber, $status, $timeServeStart, $timeServeEnds, $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "success";
            } else {
                echo "error: No changes made or record not found";
            }
        } else {
            echo "error: Database error - " . $stmt->error;
        }
        exit;
    }

    // 🟢 DELETE
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        
        if ($id <= 0) {
            echo "error: Invalid ID";
            exit;
        }

        // First check if record exists
        $checkSql = "SELECT id FROM inmates WHERE id = ?";
        $checkStmt = $con_admin->prepare($checkSql);
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            echo "error: Record not found";
            exit;
        }

        $sql = "DELETE FROM inmates WHERE id=?";
        $stmt = $con_admin->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "success";
            } else {
                echo "error: No record was deleted";
            }
        } else {
            echo "error: Database error - " . $stmt->error;
        }
        exit;
    }

    // Handle regular form submission for adding new inmates
    if (!isset($_POST['action'])) {
        // Collect form data safely
        $firstName      = $_POST['firstName'] ?? '';
        $lastName       = $_POST['lastName'] ?? '';
        $middleName     = $_POST['middleName'] ?? '';
        $birthday       = $_POST['birthday'] ?? '';
        $gender         = $_POST['gender'] ?? '';
        $address        = $_POST['address'] ?? '';
        $maritalStatus  = $_POST['maritalStatus'] ?? '';
        $inmateNumber   = $_POST['inmateNumber'] ?? '';
        $crimeCommitted = $_POST['crimeCommitted'] ?? '';
        $timeServeStart = $_POST['timeServeStart'] ?? '';
        $sentence       = $_POST['sentence'] ?? '';
        $timeServeEnds  = $_POST['timeServeEnds'] ?? '';

        // Prevent empty inmateNumber
        if (!empty($inmateNumber)) {
            // Use prepared statement for security
            $sql = "INSERT INTO inmates 
                    (firstName, middleName, lastName, birthday, gender, address, maritalStatus, inmateNumber, crimeCommitted, timeServeStart, sentence, timeServeEnds, dateCreated, status) 
                    VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Active')";
            
            $stmt = $con_admin->prepare($sql);
            $stmt->bind_param("ssssssssssss", $firstName, $middleName, $lastName, $birthday, $gender, $address, $maritalStatus, $inmateNumber, $crimeCommitted, $timeServeStart, $sentence, $timeServeEnds);

            if ($stmt->execute()) {
                echo "<script>alert('Inmate saved successfully!'); window.location.href='WARDEN_INMATES_DATA.php';</script>";
            } else {
                echo "Error: " . $con_admin->error;
            }
        } else {
            echo "<script>alert('Inmate Number cannot be empty!');</script>";
        }
    }
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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/winmatedata.css">
    <link rel="stylesheet" href="../../includes/navbar/W_navbar.css" />
   
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../../includes/navbar/nav.php'; ?>
        <!-- Main Content -->
        <main class="main-content flex-fill">
            <!-- Header -->
          <?php include '../../includes/header/winmatesdata.php'; ?>

 <!-- Content -->
            <div class="content-area">
                <div class="inmates-container">
                    <!-- Header with Title and Create Button -->
                    <div class="inmates-header">
                        <h2 class="inmates-title">List of Inmates</h2>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success" id="exportExcelBtn">
                                <i class="bi bi-file-earmark-excel"></i>
                                Export to Excel
                            </button>
                            <button type="button" class="create-btn" data-bs-toggle="modal" data-bs-target="#addInmateModal">
                                <i class="bi bi-plus-lg"></i>
                                Create New
                            </button>
                        </div>
                    </div>

                    <!-- Search Section -->
                    <div class="search-section">
                        <div class="search-container position-relative">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" class="search-input" id="searchInput" placeholder="Search Inmates...">
                            <button type="button" class="btn btn-sm btn-outline-secondary position-absolute" 
                                    id="clearSearchBtn" style="right: 10px; top: 50%; transform: translateY(-50%); display: none;">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="table-container">
                        <table class="table inmates-table mb-0">
                            <thead>
                                <tr>
                                    <th>Date Created</th>
                                    <th>Inmate Code</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="inmatesTableBody">
                                <?php
                                $today = date('Y-m-d');

                                $sql = "SELECT id, dateCreated, inmateNumber, 
                                                CONCAT(lastName, ', ', firstName, ' ', middleName) AS fullName, 
                                                status, timeServeStart, timeServeEnds 
                                        FROM inmates 
                                        ORDER BY dateCreated DESC";

                                $result = $con_admin->query($sql);

                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        // Check serve end date
                                        $status = $row['status']; // default from DB
                                        if (!empty($row['timeServeEnds'])) {
                                            if ($today >= $row['timeServeEnds'] && $status == 'Active') {
                                                $status = 'Released'; // auto-change when time is done
                                            }
                                        }

                                        echo "<tr data-id='{$row['id']}'>
                                        <td>" . htmlspecialchars($row['dateCreated']) . "</td>
                                        <td>" . htmlspecialchars($row['inmateNumber']) . "</td>
                                        <td>" . htmlspecialchars($row['fullName']) . "</td>
                                        <td><span class='badge bg-" . ($status == 'Active' ? 'success' : ($status == 'Released' ? 'secondary' : 'warning')) . "'>" . htmlspecialchars($status) . "</span></td>
                                        <td>
                                            <button class='btn btn-sm btn-primary viewBtn' data-id='{$row['id']}'>
                                                <i class='bi bi-eye'></i> View
                                            </button>
                                            <button class='btn btn-sm btn-warning editBtn' data-id='{$row['id']}'>
                                                <i class='bi bi-pencil'></i> Edit
                                            </button>
                                            <button class='btn btn-sm btn-danger deleteBtn' data-id='{$row['id']}'>
                                                <i class='bi bi-trash'></i> Delete
                                            </button>
                                        </td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='empty-state'>No registered inmates available</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Inmate Modal -->
    <div class="modal fade" id="addInmateModal" tabindex="-1" aria-labelledby="addInmateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="WARDEN_INMATES_DATA.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addInmateModalLabel">Add New Inmate</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body row g-3">
                        <!-- Personal Info -->
                        <div class="col-md-4">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>

                        <div class="col-md-4">
                            <label for="middleName" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middleName" name="middleName">
                        </div>

                        <div class="col-md-4">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>

                        <div class="col-md-4">
                            <label for="birthday" class="form-label">Birthday</label>
                            <input type="date" class="form-control" id="birthday" name="birthday" required>
                        </div>

                        <div class="col-md-4">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">-- Select --</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="maritalStatus" class="form-label">Marital Status</label>
                            <select class="form-select" id="maritalStatus" name="maritalStatus">
                                <option value="">-- Select --</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address">
                        </div>

                        <!-- Inmate Info -->
                        <div class="col-md-4">
                            <label for="inmateNumber" class="form-label">Inmate Number</label>
                            <input type="text" class="form-control" id="inmateNumber" name="inmateNumber" required>
                        </div>

                        <div class="col-md-8">
                            <label for="crimeCommitted" class="form-label">Crime Committed</label>
                            <textarea class="form-control" id="crimeCommitted" name="crimeCommitted" rows="2" required></textarea>
                        </div>

                        <div class="col-md-4">
                            <label for="timeServeStart" class="form-label">Time Serve Start</label>
                            <input type="date" class="form-control" id="timeServeStart" name="timeServeStart" required>
                        </div>

                        <div class="col-md-4">
                            <label for="timeServeEnds" class="form-label">Time Serve Ends</label>
                            <input type="date" class="form-control" id="timeServeEnds" name="timeServeEnds" required>
                        </div>

                        <div class="col-md-4">
                            <label for="sentence" class="form-label">Sentence</label>
                            <input type="text" class="form-control" id="sentence" name="sentence" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Inmate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Inmate Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Inmate Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Personal Information</h6>
                            <table class="table table-borderless">
                                <tr><td><strong>First Name:</strong></td><td id="viewFirstName"></td></tr>
                                <tr><td><strong>Middle Name:</strong></td><td id="viewMiddleName"></td></tr>
                                <tr><td><strong>Last Name:</strong></td><td id="viewLastName"></td></tr>
                                <tr><td><strong>Birthday:</strong></td><td id="viewBirthday"></td></tr>
                                <tr><td><strong>Gender:</strong></td><td id="viewGender"></td></tr>
                                <tr><td><strong>Address:</strong></td><td id="viewAddress"></td></tr>
                                <tr><td><strong>Marital Status:</strong></td><td id="viewMaritalStatus"></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Inmate Information</h6>
                            <table class="table table-borderless">
                                <tr><td><strong>Inmate Number:</strong></td><td id="viewInmateNumber"></td></tr>
                                <tr><td><strong>Crime Committed:</strong></td><td id="viewCrimeCommitted"></td></tr>
                                <tr><td><strong>Sentence:</strong></td><td id="viewSentence"></td></tr>
                                <tr><td><strong>Serve Start:</strong></td><td id="viewTimeServeStart"></td></tr>
                                <tr><td><strong>Serve Ends:</strong></td><td id="viewTimeServeEnds"></td></tr>
                                <tr><td><strong>Status:</strong></td><td id="viewStatus"></td></tr>
                                <tr><td><strong>Date Created:</strong></td><td id="viewDateCreated"></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Inmate Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Inmate</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editId">
                        <input type="hidden" name="action" value="edit">

                        <div class="mb-3">
                            <label>First Name</label>
                            <input type="text" class="form-control" id="editFirstName" name="firstName" required>
                        </div>

                        <div class="mb-3">
                            <label>Middle Name</label>
                            <input type="text" class="form-control" id="editMiddleName" name="middleName">
                        </div>

                        <div class="mb-3">
                            <label>Last Name</label>
                            <input type="text" class="form-control" id="editLastName" name="lastName" required>
                        </div>

                        <div class="mb-3">
                            <label>Inmate Code</label>
                            <input type="text" class="form-control" id="editInmateNumber" name="inmateNumber" required>
                        </div>

                        <div class="mb-3">
                            <label>Status</label>
                            <select class="form-select" id="editStatus" name="status">
                                <option value="Active">Active</option>
                                <option value="Released">Released</option>
                                <option value="Transferred">Transferred</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Serve Start</label>
                            <input type="date" class="form-control" id="editServeStart" name="timeServeStart">
                        </div>

                        <div class="mb-3">
                            <label>Serve Ends</label>
                            <input type="date" class="form-control" id="editServeEnds" name="timeServeEnds">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this inmate record?</p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- XLSX Library for Excel file handling -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="../../assets/js/w_inmates_data.js"></script>
</body>
</html>
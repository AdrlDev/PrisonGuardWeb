<?php
require_once '../../classes/auth.php';
requireRole('prison_guard');
require_once '../../database/connection.php';
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

$all_visitors = [];

$sql_approved = "SELECT v.*, p.submitted_at as registration_date, 'approved' as status 
                 FROM visitors v 
                 LEFT JOIN pending_visitors p ON v.firstName = p.firstName AND v.lastName = p.lastName AND v.inmate = p.inmate
                 ORDER BY p.submitted_at DESC";
$result_approved = $con_admin->query($sql_approved);
if ($result_approved) {
    while ($row = $result_approved->fetch_assoc()) {
        $all_visitors[] = $row;
    }
}
// Get rejected visitors
$sql_rejected = "SELECT *, 'rejected' as status FROM pending_visitors WHERE status = 'rejected' ORDER BY submitted_at DESC";
$result_rejected = $con_admin->query($sql_rejected);
if ($result_rejected) {
    while ($row = $result_rejected->fetch_assoc()) {
        $all_visitors[] = $row;
    }
}

// Get pending visitors
$sql_pending = "SELECT *, 'pending' as status FROM pending_visitors WHERE status = 'pending' ORDER BY submitted_at DESC";
$result_pending = $con_admin->query($sql_pending);
if ($result_pending) {
    while ($row = $result_pending->fetch_assoc()) {
        $all_visitors[] = $row;
    }
}

// Handle search request
if (isset($_GET['search'])) {
  header("Content-Type: application/json");
  $q = $_GET['search'];

  $sql = "SELECT inmateNumber, 
                 CONCAT(firstName, ' ', middleName, ' ', lastName) AS fullname
          FROM inmates
          WHERE firstName LIKE ? 
             OR middleName LIKE ? 
             OR lastName LIKE ? 
             OR inmateNumber LIKE ?
          LIMIT 10";

  $stmt = $con_admin->prepare($sql);
  $like = "%$q%";
  $stmt->bind_param("ssss", $like, $like, $like, $like);
  $stmt->execute();
  $result = $stmt->get_result();

  $inmates = [];
  while ($row = $result->fetch_assoc()) {
    $inmates[] = $row;
  }
  echo json_encode($inmates);
  exit;
}

// Handle form submission for approval
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitForApproval'])) {
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

    // Insert into pending_visitors table for approval
  $sql = "INSERT INTO pending_visitors 
          (firstName, lastName, middleName, gender, phoneNumber, permanentAddress, relationship, idType, idNumber, inmate)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

  $stmt = $con_admin->prepare($sql);
  $stmt->bind_param("ssssssssss", $firstName, $lastName, $middleName, $gender, $phoneNumber, $permanentAddress, $relationship, $idType, $idNumber, $inmate);

  if ($stmt->execute()) {

    // Grab last inserted visitor info
    $last_id = $stmt->insert_id;
    $submitted_visitor = [
        "firstName" => $firstName,
        "lastName" => $lastName,
        "middleName" => $middleName,
        "idType" => $idType,
        "idNumber" => $idNumber
    ];
    $json_data = json_encode($submitted_visitor);

    echo "<script>
        alert('Visitor registration submitted for approval!');
    </script>";
  } else {
    echo "<script>alert('Error: Could not submit for approval');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitors Monitoring system</title>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Visitors Monitoring system</title>
        <link rel="shortcut icon" href="../../assets/img/Occi.png" type="image/png">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

        <!-- Custom CSS -->
        <link rel="stylesheet" href="../../assets/css/PG_INFO.css">
        <link rel="stylesheet" href="../../includes/navbar/PG_navbar.css" />
    </head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../../includes/navbar/pg_nav.php'; ?>
        <!-- Main Content -->
        <main class="main-content flex-fill">
            <!-- Header -->
            <?php include '../../includes/header/pg_info.php'; ?>

            <div class="content-section">
                <!-- Search + Register Button -->
                <div class="search-container">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="position-relative">
                                <i class="bi bi-search position-absolute search-icon"></i>
                                <input type="text" class="form-control search-input ps-5"
                                    placeholder="Search Visitors..." id="searchInput" />
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <!-- ✅ Corrected Button: uses Bootstrap's modal trigger -->
                            <button class="btn btn-register" data-bs-toggle="modal" data-bs-target="#registrationModal">
                                <i class="bi bi-person-plus me-2"></i>Register Visitors
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Status Filter Buttons -->
                <div class="filter-buttons mt-3">
                    <button class="btn btn-outline-primary filter-btn active-filter" data-status="all">
                        All Visitors
                    </button>
                    <button class="btn btn-outline-success filter-btn" data-status="approved">
                        Approved
                    </button>
                    <button class="btn btn-outline-warning filter-btn" data-status="pending">
                        Pending
                    </button>
                    <button class="btn btn-outline-danger filter-btn" data-status="rejected">
                        Rejected
                    </button>
                </div>

                <!-- Table -->
                <div class="table-container mt-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Visitor Name</th>
                                <th>Inmates to Visit</th>
                                <th>Relationship</th>
                                <th>Address</th>
                                <th>Phone Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="visitorsTableBody">
                            <?php if (count($all_visitors) > 0): ?>
                            <?php foreach ($all_visitors as $visitor): ?>
                            <tr class="visitor-row" data-status="<?php echo $visitor['status']; ?>">
                                <td><?php echo $visitor['firstName'] . ' ' . $visitor['lastName']; ?></td>
                                <td><?php echo $visitor['inmate']; ?></td>
                                <td><?php echo $visitor['relationship']; ?></td>
                                <td><?php echo $visitor['permanentAddress']; ?></td>
                                <td><?php echo $visitor['phoneNumber']; ?></td>
                                <td>
                                    <?php if ($visitor['status'] == 'approved'): ?>
                                    <span class="status-badge status-approved">Approved</span>
                                    <?php elseif ($visitor['status'] == 'pending'): ?>
                                    <span class="status-badge status-pending">Pending</span>
                                    <?php elseif ($visitor['status'] == 'rejected'): ?>
                                    <span class="status-badge status-rejected">Rejected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-data-message text-center py-4">No visitors found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- ✅ Registration Modal -->
    <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content registration-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="registrationModalLabel">
                        <i class="bi bi-person-plus me-2"></i>Register New Visitor
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="visitorForm" method="POST">
                    <div class="modal-body">
                        <div class="content-container">
                            <div class="registration-form-card">
                                <div class="row">
                                    <!-- Form -->
                                    <div class="col-md-12">
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">First Name</label>
                                                <input type="text" class="form-control" name="firstName"
                                                    id="firstName" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" class="form-control" name="lastName" id="lastName" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Middle Name</label>
                                                <input type="text" class="form-control" name="middleName"
                                                    id="middleName" />
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Gender</label>
                                                <select class="form-control" name="gender" id="gender" required>
                                                    <option value="">Select Gender</option>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Phone Number</label>
                                                <input type="tel" class="form-control" name="phoneNumber"
                                                    id="phoneNumber" required />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Permanent Address</label>
                                                <input type="text" class="form-control" name="permanentAddress"
                                                    id="permanentAddress" />
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Relationship to Inmate</label>
                                                <input type="text" class="form-control" name="relationship"
                                                    id="relationship" />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">ID Type</label>
                                                <select class="form-control" name="idType" id="idType">
                                                    <option value="">Select ID Type</option>
                                                    <option value="National ID">National ID</option>
                                                    <option value="Drivers License">Drivers License</option>
                                                    <option value="Barangay ID">Barangay ID</option>
                                                    <option value="PhilHealth">PhilHealth</option>
                                                    <option value="Voters">Voters ID</option>
                                                    <option value="UMID">UMID</option>
                                                </select>
                                            </div>
                                            <div class="col-md-5 id-number-container">
                                                <label class="form-label">ID Number</label>
                                                <input type="text" class="form-control" name="idNumber" id="idNumber" />
                                            </div>
                                        </div>


                                        <!-- ✅ Inmate Search -->
                                        <div class="mb-3">
                                            <label class="form-label">Search Inmate</label>
                                            <input type="text" class="form-control" name="inmate" id="inmateSearch"
                                                autocomplete="off" placeholder="Enter name or inmate number..."
                                                required />
                                            <div id="inmateSuggestions" class="list-group"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" id="submitForApproval" class="btn btn-primary">
                                Submit to Warden
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ✅ Face Enrollment Modal -->
    <div class="modal fade" id="faceEnrollModal" tabindex="-1" aria-labelledby="faceEnrollModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="faceEnrollModalLabel">
                        <i class="bi bi-camera-video me-2"></i>Face Enrollment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <video id="enrollCameraFeed" autoplay playsinline
                        style="max-width:100%;border-radius:10px;"></video>
                    <canvas id="enrollSnapshotCanvas" style="display:none;"></canvas>
                    <p id="enrollProgressText" class="mt-3 text-muted">Initializing camera...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Bootstrap Bundle (with JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../../assets/js/enrollFace.js?v=8"></script>
    <script src="../../assets/js/PG_INFO.js?v=2"></script>
</body>

</html>
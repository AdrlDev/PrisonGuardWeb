<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../classes/auth.php';
require_once '../../database/connection.php';
require '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../../classes/AuthenticationFlow.php';
use PHPMailer\PHPMailer\SMTP;
requireRole('warden');

// Check database connection
if (!$con_admin) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if user is logged in and is a warden
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'warden') {
    header('Location: ../../login/login-user.php');
    exit();
}

// Initialize error/success messages
$error = null;
$success = null;

// Handle key generation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_guard_key'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);

    if (empty($email) || empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide valid guard's name and email address.";
    } else {
        try {
            // Start transaction
            $con_admin->begin_transaction();

            // Generate random secure key
            $guardKey = bin2hex(random_bytes(8));

            // Check if email exists in users table
            $checkUserStmt = $con_admin->prepare("SELECT id FROM users WHERE email = ?");
            if (!$checkUserStmt) {
                throw new Exception("Database error: " . $con_admin->error);
            }
            
            $checkUserStmt->bind_param("s", $email);
            if (!$checkUserStmt->execute()) {
                throw new Exception("Failed to check email: " . $checkUserStmt->error);
            }
            
            $result = $checkUserStmt->get_result();
            if ($result->num_rows > 0) {
                $checkUserStmt->close();
                throw new Exception("A user with this email already exists.");
            }
            $checkUserStmt->close();

                // Check for existing unused key
            $checkKeyStmt = $con_admin->prepare("SELECT id FROM registration_keys WHERE email = ? AND role_type = 'prison_guard' AND is_blocked = 0");
            if (!$checkKeyStmt) {
                throw new Exception("Database error: " . $con_admin->error);
            }
            
            if (!$checkKeyStmt->bind_param("s", $email)) {
                throw new Exception("Failed to check existing key: " . $checkKeyStmt->error);
            }            if (!$checkKeyStmt->execute()) {
                throw new Exception("Failed to check existing key: " . $checkKeyStmt->error);
            }
            
            if ($checkKeyStmt->get_result()->num_rows > 0) {
                $checkKeyStmt->close();
                throw new Exception("An unused registration key already exists for this email.");
            }
            $checkKeyStmt->close();

            // Insert new key with expiration
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $insertStmt = $con_admin->prepare("INSERT INTO registration_keys (key_code, email, role_type, created_by, usage_limit, usage_used, is_blocked, expires_at) VALUES (?, ?, 'prison_guard', ?, 1, 0, 0, ?)");
            if (!$insertStmt) {
                throw new Exception("Failed to prepare insert: " . $con_admin->error);
            }
            
            if (!$insertStmt->bind_param("ssis", $guardKey, $email, $_SESSION['id'], $expiresAt)) {
                throw new Exception("Failed to bind parameters: " . $insertStmt->error);
            }
            
            if (!$insertStmt->execute()) {
                throw new Exception("Failed to save key: " . $insertStmt->error);
            }
            
            // Send email
            $mail = new PHPMailer(true);
            try {
                // Server settings
              
                $mail->isSMTP();       // Send using SMTP
                $mail->Host       = 'smtp.gmail.com';  // SMTP server
                $mail->SMTPAuth   = true;   // Enable SMTP authentication
                $mail->Username   = 'catherinemaemauricio.bsit@gmail.com';
                $mail->Password   = 'togd lnqm uyvz trwc';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;      

                try {
                    // Recipients
                    $mail->setFrom('catherinemaemauricio.bsit@gmail.com', 'Prison Management System');
                    $mail->addAddress($email, $name);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Prison Guard Registration Key";
                    $mail->Body = "
                    <html>
                    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <h2>Welcome to Prison Management System</h2>
                        <p>Hello " . htmlspecialchars($name) . ",</p>
                        <p>You have been issued a <strong>Prison Guard registration key</strong>.</p>
                        <p><strong>Your Registration Key:</strong> " . $guardKey . "</p>
                        <p>Please use this key when you register at our system.</p>
                        <p>For security reasons, this key will expire if not used within 24 hours.</p>
                        <p>Best regards,<br>Prison Management System</p>
                    </body>
                    </html>";

                    if (!$mail->send()) {
                        throw new Exception("Failed to send email: " . $mail->ErrorInfo);
                    }

                    $con_admin->commit();
                    $success = "Registration key has been generated and sent to " . htmlspecialchars($email);
                    
                    error_log("Key generated successfully: " . $guardKey . " for email: " . $email);
                } catch (Exception $e) {
                    error_log("Email error: " . $e->getMessage());
                    throw $e;
                }

            } catch (Exception $e) {
                $con_admin->rollback();
                $error = $e->getMessage();
                error_log("Email sending error: " . $e->getMessage());
            }

            
        } catch (Exception $e) {
            $con_admin->rollback();
            error_log("Error in key generation process: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prison Guards Data</title>
    <link rel="shortcut icon" href="../../assets/img/Occi.png" type="image/jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
      <link rel="stylesheet" href="../../assets/css/WARDEN_PG_DATA.css">
      <link rel="stylesheet" href="../../includes/navbar/PG_navbar.css" />
   
</head>

<body>
    <div class="d-flex">
        
        <!-- Sidebar -->
        <?php include '../../includes/navbar/nav.php' ?>

        <!-- Main Content -->
        <main class="main-content flex-fill">
            <!-- Header -->
            <?php include '../../includes/header/wpgdata.php'; ?>

            <!-- Content Area -->   
        <div class="content-area p-4">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="mb-0">List of Prison Guards</h2>
                            <button type="button" class="btn btn-success d-flex align-items-center gap-2" 
                                    data-bs-toggle="modal" data-bs-target="#generateKeyModal">
                            
                                <span>Generate Guard Key</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php elseif (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <!-- Table -->
                <div class="table-container mt-3">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Date Created</th>
                            <th>Guard ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $query = $con_admin->query("SELECT id, created_at, first_name, last_name, middle_name, email, phone_number, role 
                                                    FROM users WHERE role = 'prison_guard' ORDER BY created_at DESC");
                     if ($query && $query->num_rows > 0) {
    while ($row = $query->fetch_assoc()) {
        // Build full name safely
        $fullName = trim($row['first_name'] . ' ' . ($row['middle_name'] ?? '') . ' ' . $row['last_name']);

        // Determine status from available columns (fallback to "Unknown")
        $status = 'Unknown';
        if (isset($row['is_blocked'])) {
            // assuming is_blocked is 0/1
            $status = ($row['is_blocked'] == 1) ? 'Blocked' : 'Active';
        } elseif (isset($row['status'])) {
            // if you have a 'status' text column
            $status = $row['status'] ?: 'Unknown';
        } elseif (isset($row['is_active'])) {
            // if you use is_active 0/1
            $status = ($row['is_active'] == 1) ? 'Active' : 'Inactive';
        }

        // Optional: choose badge class
        $lower = strtolower($status);
        $badgeClass = 'secondary';
        if ($lower === 'active') $badgeClass = 'success';
        elseif ($lower === 'blocked' || $lower === 'inactive' || $lower === 'disabled') $badgeClass = 'danger';

        echo "<tr>
            <td>".htmlspecialchars($row['created_at'] ?? '—')."</td>
            <td>".htmlspecialchars($row['id'])."</td>
            <td>".htmlspecialchars($fullName)."</td>
            <td>".htmlspecialchars($row['email'])."</td>
            <td>".htmlspecialchars($row['phone_number'] ?? '—')."</td>
            <td><span class='badge bg-".htmlspecialchars($badgeClass)."'>".htmlspecialchars($status)."</span></td>
            <td>
                <button class='btn btn-sm btn-primary'><i class='bi bi-eye'></i></button>
                <button class='btn btn-sm btn-warning'><i class='bi bi-pencil'></i></button>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center'>No prison guards found.</td></tr>";
}

                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Generate Key Modal -->
<div class="modal fade" id="generateKeyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Prison Guard Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Guard's Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Guard's Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <input type="hidden" name="generate_guard_key" value="1">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Generate & Send Key</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Form validation
    const form = document.querySelector('#generateKeyModal form');
    form.addEventListener('submit', function(e) {
        const email = this.querySelector('input[type="email"]').value.trim();
        const name = this.querySelector('input[name="name"]').value.trim();
        
        if (!email || !name) {
            e.preventDefault();
            alert('Please fill in all fields');
        }
    });
});
</script>
</body>
</html>

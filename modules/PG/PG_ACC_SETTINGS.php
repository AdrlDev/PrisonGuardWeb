<?php
require_once '../../classes/auth.php';
requireRole('prison_guard');
require_once '../../database/connection.php';

$userId = $_SESSION['id'] ?? null;
$user = null;
$success = "";
$error = "";

// ✅ Grab flash messages from session (for redirect)
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// ✅ Handle update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName   = trim($_POST['firstName']);
    $lastName    = trim($_POST['lastName']);
    $middleName  = trim($_POST['middleName']);
    $gender      = trim($_POST['gender']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $birthday    = trim($_POST['birthday']);
    $age         = trim($_POST['age']);
    $email       = trim($_POST['email']);

    // ✅ Update query (no photo upload)
    $sql = "UPDATE users 
            SET first_name=?, last_name=?, middle_name=?, gender=?, phone_number=?, birthday=?, age=?, email=? 
            WHERE id=?";

    $stmt = $con_admin->prepare($sql);
    $stmt->bind_param("ssssssisi", $firstName, $lastName, $middleName, $gender, $phoneNumber, $birthday, $age, $email, $userId);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: PG_ACC_SETTINGS.php");
        exit;
    } else {
        $_SESSION['error'] = "Error updating profile: " . $stmt->error;
        header("Location: PG_ACC_SETTINGS.php");
        exit;
    }
}

// ✅ Fetch user info to display
if ($userId) {
    $stmt = $con_admin->prepare("SELECT first_name, last_name, middle_name, gender, phone_number, birthday, age, email 
                                 FROM users WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitors Monitoring System</title>
    <link rel="shortcut icon" href="../../assets/img/Occi.png" type="image/jpg">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../../assets/css/PG_ACC_SETTINGS.css">
    <link rel="stylesheet" href="../../includes/navbar/PG_navbar.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../../includes/navbar/pg_nav.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content flex-fill">
            <!-- Header -->
            <?php include '../../includes/header/pg_accsett.php'; ?>

            <!-- Content -->
            <div class="content">
                <div class="settings-card">

                    <!-- ✅ Success/Error Messages -->
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" id="successAlert"><?= htmlspecialchars($success) ?></div>
                        <script>
                            // Fade out success after 3s
                            setTimeout(() => {
                                const alertBox = document.getElementById("successAlert");
                                if (alertBox) {
                                    alertBox.style.transition = "opacity 0.5s ease";
                                    alertBox.style.opacity = "0";
                                    setTimeout(() => alertBox.remove(), 500);
                                }
                            }, 3000);
                        </script>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" id="errorAlert"><?= htmlspecialchars($error) ?></div>
                        <script>
                            // Fade out error after 5s
                            setTimeout(() => {
                                const alertBox = document.getElementById("errorAlert");
                                if (alertBox) {
                                    alertBox.style.transition = "opacity 0.5s ease";
                                    alertBox.style.opacity = "0";
                                    setTimeout(() => alertBox.remove(), 500);
                                }
                            }, 5000);
                        </script>
                    <?php endif; ?>

                    <form id="accountForm" method="POST">
                        <div class="profile-section">
                            <!-- Avatar Section (No upload, just placeholder) -->
                            <div class="avatar-container">
                                <div class="profile-avatar">
                                    <i class="bi bi-person" id="avatarIcon"></i>
                                </div>
                            </div>

                            <!-- Form Section -->
                            <div class="form-section">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-input" id="firstName" name="firstName" 
                                               value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-input" id="lastName" name="lastName" 
                                               value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" class="form-input" id="middleName" name="middleName" 
                                               value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Gender</label>
                                        <select class="form-input" id="gender" name="gender" disabled>
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?= ($user['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                            <option value="Female" <?= ($user['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-input" id="phoneNumber" name="phoneNumber" 
                                               value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Birthday</label>
                                        <input type="date" class="form-input" id="birthday" name="birthday" 
                                               value="<?= htmlspecialchars($user['birthday'] ?? '') ?>" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Age</label>
                                        <input type="number" class="form-input" id="age" name="age" 
                                               value="<?= htmlspecialchars($user['age'] ?? '') ?>" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-input" id="email" name="email" 
                                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Update Button -->
                        <button type="button" class="update-btn" id="updateBtn" onclick="toggleEditMode()">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                        <button type="submit" class="update-btn hidden" id="saveBtn" name="update_profile">
                            <i class="bi bi-save"></i> Save
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Edit/Save Toggle -->
    <script>
    function toggleEditMode() {
        const inputs = document.querySelectorAll('#accountForm .form-input');
        const saveBtn = document.getElementById("saveBtn");
        const editBtn = document.getElementById("updateBtn");

        inputs.forEach(input => input.disabled = !input.disabled);

        editBtn.classList.toggle("hidden");
        saveBtn.classList.toggle("hidden");
    }
    </script>
</body>
</html>

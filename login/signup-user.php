<?php
session_start();
require_once '../database/connection.php';
require_once '../classes/controllerUserData.php';
require_once '../classes/AuthenticationFlow.php';

$error = "";
$success = "";
$errors = array();

// Ensure user came from key verification with all required data
if (!isset($_SESSION['verified_key'], $_SESSION['key_role'], $_SESSION['key_email'])) {
    header('Location: KEY.php');
    exit();
}

// Additional security check for direct access
if (!isset($_SERVER['HTTP_REFERER']) || 
    (strpos($_SERVER['HTTP_REFERER'], 'KEY.php') === false && 
     strpos($_SERVER['HTTP_REFERER'], 'signup-user.php') === false)) {
    // Clear session and redirect if someone tries to access directly
    session_unset();
    session_destroy();
    header('Location: KEY.php');
    exit();
}

// Check if access is allowed through authentication flow
AuthenticationFlow::checkAccess(__FILE__);

$key_code = $_SESSION['verified_key'];
$role_type = $_SESSION['key_role'];
$email = $_SESSION['key_email'];

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    // Get all form fields
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $middleName = trim($_POST['middleName']);
    $gender = trim($_POST['gender']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $birthday = trim($_POST['birthday']);
    $age = trim($_POST['age']);
    $password = trim($_POST['password']);
    $cpassword = trim($_POST['cpassword']);
    
    // Validation
    if (empty($firstName)) {
        $errors[] = "First name is required.";
    }
    if (empty($lastName)) {
        $errors[] = "Last name is required.";
    }
    if (empty($gender)) {
        $errors[] = "Gender is required.";
    }
    if (empty($phoneNumber)) {
        $errors[] = "Phone number is required.";
    } elseif (!preg_match('/^[0-9+\-\s()]+$/', $phoneNumber)) {
        $errors[] = "Phone number contains invalid characters.";
    }
    if (empty($birthday)) {
        $errors[] = "Birthday is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $cpassword) {
        $errors[] = "Passwords do not match.";
    }
    
    // If no validation errors, proceed with registration
    if (empty($errors)) {
        $hashedPass = password_hash($password, PASSWORD_BCRYPT);
        
        // Calculate age if not provided
        if (empty($age) && !empty($birthday)) {
            $birthday_date = new DateTime($birthday);
            $today = new DateTime();
            $age = $today->diff($birthday_date)->y;
        }
        
        // Full name for display
        $name = trim($firstName . ' ' . $middleName . ' ' . $lastName);
        
        // Convert role format to match database
        $db_role = ($role_type === 'warden') ? 'warden' : 'prison_guard';
        
        try {
            // Check if email already exists
            $stmt = $con_admin->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = "Email already registered.";
            } else {
                // Insert user with all required fields
                $stmt = $con_admin->prepare("INSERT INTO users (id, email, first_name, last_name, middle_name, gender, phone_number, birthday, age, password, role, signup_key) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssssssss", $id, $email, $firstName, $lastName, $middleName, $gender, $phoneNumber, $birthday, $age, $hashedPass, $db_role, $key_code);
                
                if ($stmt->execute()) {
                    $success = "Registration successful as " . ucfirst($role_type) . ".";
                    
                    // Mark key as used
                    $updateStmt = $con_admin->prepare("UPDATE registration_keys SET usage_used = usage_used + 1 WHERE key_code = ?");
                    $updateStmt->bind_param("s", $key_code);
                    $updateStmt->execute();
                    $updateStmt->close();
                    
                    // Clear key session data
                    unset($_SESSION['verified_key'], $_SESSION['key_role'], $_SESSION['key_email']);
                    
                    // Set success message for redirect
                    $_SESSION['registration_success'] = true;
                    $_SESSION['success_message'] = "Your account has been created successfully! Please log in.";
                    // Redirect to login page
                    header("Location: login-user.php");
                    exit();
                } else {
                    $errors[] = "Failed to register: " . $stmt->error;
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prison Management System - Registration</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="form-container">
                    <?php if ($success): ?>
                        <div class="success-section">
                            <i class="bi bi-check-circle success-icon"></i>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                            </div>
                            <h4>Account Created Successfully!</h4>
                            <p class="text-muted mb-4">You can now log in to your account.</p>
                            <p class="countdown">Redirecting to login in <span id="countdown">5</span> seconds...</p>
                            <div class="d-flex justify-content-center gap-3 mt-4">
                                <a href="login-user.php" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Go to Login Now
                                </a>
                                
                            </div>
                        </div>
                    <?php else: ?>
                        <form action="signup-user.php" method="POST" autocomplete="off">
                            <h2 class="text-center">Complete Registration</h2>
                            <p class="text-center">Fill in your details to create your account.</p>
                            
                            <div class="key-display">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong><i class="bi bi-key me-2"></i>Key:</strong> 
                                        <code><?= htmlspecialchars($key_code) ?></code>
                                    </div>
                                    <div class="col-md-6">
                                        <strong><i class="bi bi-person-badge me-2"></i>Role:</strong> 
                                        <span class="badge badge-primary"><?= ucfirst($role_type) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if(count($errors) == 1): ?>
                                <div class="alert alert-danger text-center">
                                    <?php echo htmlspecialchars($errors[0]); ?>
                                </div>
                            <?php elseif(count($errors) > 1): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- Personal Information Grid -->
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <input class="form-control" type="email" name="email" placeholder="Email Address (Auto-assigned)" required value="<?php echo htmlspecialchars($email) ?>" readonly>
                                    <small class="form-text text-muted">This email is assigned to your registration key</small>
                                </div>

                                <div class="form-group">
                                    <input class="form-control" type="text" name="firstName" placeholder="First Name" required value="<?php echo htmlspecialchars($firstName ?? '') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <input class="form-control" type="text" name="lastName" placeholder="Last Name" required value="<?php echo htmlspecialchars($lastName ?? '') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <input class="form-control" type="text" name="middleName" placeholder="Middle Name (Optional)" value="<?php echo htmlspecialchars($middleName ?? '') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <select class="form-control" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo (isset($gender) && $gender == 'Male') ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?php echo (isset($gender) && $gender == 'Female') ? 'selected' : '' ?>>Female</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <input class="form-control" type="tel" name="phoneNumber" placeholder="Phone Number" required value="<?php echo htmlspecialchars($phoneNumber ?? '') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <input class="form-control" type="date" name="birthday" placeholder="Birthday" required value="<?php echo htmlspecialchars($birthday ?? '') ?>" onchange="calculateAge()">
                                </div>
                                
                                <div class="form-group">
                                    <input class="form-control" type="number" name="age" id="age" placeholder="Age" min="18" max="100" readonly value="<?php echo htmlspecialchars($age ?? '') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <input class="form-control" type="password" name="password" placeholder="Password (min. 6 characters)" required minlength="6">
                                </div>
                                
                                <div class="form-group">
                                    <input class="form-control" type="password" name="cpassword" placeholder="Confirm Password" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" name="signup" class="btn btn-primary btn-block">
                                    <i class="bi bi-person-plus me-2"></i>Create Account
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <a href="KEY.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Key Verification
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function calculateAge() {
            const birthday = document.querySelector('input[name="birthday"]').value;
            if (birthday) {
                const today = new Date();
                const birthDate = new Date(birthday);
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                document.getElementById('age').value = age;
            }
        }

        // Auto-calculate age when page loads if birthday is already set
        document.addEventListener('DOMContentLoaded', function() {
            const birthday = document.querySelector('input[name="birthday"]');
            if (birthday && birthday.value) {
                calculateAge();
            }
            
            // Auto-redirect after successful registration
            <?php if ($success): ?>
                let countdown = 5;
                const countdownElement = document.getElementById('countdown');
                const timer = setInterval(() => {
                    countdown--;
                    countdownElement.textContent = countdown;
                    if (countdown <= 0) {
                        clearInterval(timer);
                        window.location.href = 'login-user.php';
                    }
                }, 1000);
            <?php endif; ?>
        });
    </script>
</body>
</html>
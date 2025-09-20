<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../database/connection.php';
require_once '../classes/AuthenticationFlow.php';
require_once '../classes/controllerUserData.php';
require_once '../classes/AuthenticationFlow.php';

// ✅ Check if access is allowed
AuthenticationFlow::checkAccess(__FILE__);

// ✅ Redirect if already logged in
if (isset($_SESSION['email']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'warden') {
        header('Location: ../modules/Warden/WARDEN_DASHBOARD.php');
        exit();
    } elseif ($_SESSION['role'] === 'prison_guard') {
        header('Location: ../modules/PG/PG_DASHBOARD.php');
        exit();
    }
}

// Initialize variables
$email = "";
$password = "";
$user = null; // 🔥 prevent undefined variable warnings
$errors = isset($_SESSION['login_errors']) ? $_SESSION['login_errors'] : [];
unset($_SESSION['login_errors']);

// Registration success message
$registration_success = false;
$success_message = "";

if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']) {
    $registration_success = true;
    $success_message = $_SESSION['success_message'] ?? "Registration successful!";
    unset($_SESSION['registration_success'], $_SESSION['success_message']);
}

// ✅ Handle login form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        try {
            $stmt = $con_admin->prepare("SELECT id, first_name, last_name, middle_name, email, password, role 
                                         FROM users 
                                         WHERE email = ?");
            if (!$stmt) {
                $errors[] = "Database error. Please try again later.";
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows === 1) {
                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['password'])) {
                        // ✅ Set all session variables
                        $_SESSION['id'] = $user['id'];
                        $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['last_name'] = $user['last_name'];
                        $_SESSION['middle_name'] = $user['middle_name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = strtolower($user['role']); // normalize role
                        $_SESSION['users_id'] = $user['id'];
                        $_SESSION['logged_in'] = true;

                        // ✅ Redirect to correct dashboard
                        if ($_SESSION['role'] === 'warden') {
                            header('Location: ../modules/Warden/WARDEN_DASHBOARD.php');
                            exit();
                        } elseif ($_SESSION['role'] === 'prison_guard') {
                            header('Location: ../modules/PG/PG_DASHBOARD.php');
                            exit();
                        } else {
                            $errors[] = "Unknown user role. Please contact administrator.";
                        }
                    } else {
                        $errors[] = "Invalid email or password.";
                    }
                } else {
                    $errors[] = "Invalid email or password.";
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $errors[] = "An error occurred during login. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prison Management System - Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form login-form">
                <!-- Logo -->
                <div class="logo text-center mb-3">
                    <img src="../assets/img/Occi.png" alt="OCCI Logo" style="max-width:120px;">
                </div>

                <!-- ✅ Success Message -->
                <?php if ($registration_success): ?>
                    <div class="alert alert-success text-center alert-dismissible fade show">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Success!</strong><br>
                        <?= htmlspecialchars($success_message) ?>
                        <br><small class="mt-2 d-block">You can now log in with your credentials.</small>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- ✅ Login Form -->
                <form action="login-user.php" method="POST" autocomplete="off">
                    <h2 class="text-center">Login Form</h2>
                    <p class="text-center">Login with your email and password.</p>

                    <?php if (count($errors) > 0): ?>
                        <div class="alert alert-danger text-center">
                            <?php foreach($errors as $error): ?>
                                <?= htmlspecialchars($error) ?><br>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <input type="email" name="email" class="form-control" 
                               placeholder="Email Address" required 
                               value="<?= htmlspecialchars($email); ?>">
                    </div>

                    <div class="form-group position-relative">
                        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Password" required>
                        <i class="bi bi-eye-slash" id="togglePassword"></i>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-0">Don't have an account? 
                        <a href="KEY.php" class="register-link">Register here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show/hide password
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#passwordInput');
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        }
    </script>
</body>
</html>

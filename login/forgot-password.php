<?php
session_start();
require_once '../database/connection.php';
require_once '../classes/controllerUserData.php';

$errors = [];
$email = "";
$info = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check-email'])) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } else {
        try {
            // Check if email exists in users table
            $stmt = $con_admin->prepare("SELECT id, name, email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Generate reset code
                $code = mt_rand(100000, 999999);
                
                // Store reset code in database (create table if needed)
                $stmt = $con_admin->prepare("INSERT INTO password_resets (email, code, expires_at, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW()) ON DUPLICATE KEY UPDATE code = VALUES(code), expires_at = VALUES(expires_at), created_at = VALUES(created_at)");
                $stmt->bind_param("si", $email, $code);
                
                if ($stmt->execute()) {
                    // Store email in session for next step
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['info'] = "Reset code sent! Check your email for the 6-digit code.";
                    
                    // In a real application, send email here
                    // For development, log the code
                    error_log("Password reset code for {$email}: {$code}");
                    
                    // Redirect to reset code page
                    header('Location: reset-code.php');
                    exit;
                } else {
                    $errors[] = "Failed to generate reset code. Please try again.";
                }
            } else {
                // Don't reveal if email exists or not for security
                $_SESSION['reset_email'] = $email;
                $_SESSION['info'] = "If this email is registered, you will receive a reset code.";
                header('Location: reset-code.php');
                exit;
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prison Management System - Forgot Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .back-to-login {
            color: #6665ee;
            text-decoration: none;
            font-size: 14px;
        }
        .back-to-login:hover {
            color: #5757d1;
            text-decoration: underline;
        }
        .form-info {
            background: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 5px 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form">
                <!-- Logo -->
                <div class="logo">
                    <img src="../assets/img/Occi.png" alt="OCCI Logo">
                </div>
                
                <form action="forgot-password.php" method="POST" autocomplete="off">
                    <h2 class="text-center">Forgot Password</h2>
                    <p class="text-center">Enter your email address to reset your password</p>
                    
                    <div class="form-info">
                        <small>
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>How it works:</strong><br>
                            1. Enter your registered email address<br>
                            2. You'll receive a 6-digit verification code<br>
                            3. Enter the code to create a new password
                        </small>
                    </div>
                    
                    <?php if (count($errors) > 0): ?>
                        <div class="alert alert-danger text-center">
                            <?php foreach($errors as $error): ?>
                                <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?><br>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($info)): ?>
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($info) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            </div>
                            <input class="form-control" type="email" name="email" placeholder="Enter your email address" required value="<?= htmlspecialchars($email) ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="check-email" class="btn btn-primary btn-block">
                            <i class="bi bi-send me-2"></i>Send Reset Code
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <a href="login-user.php" class="back-to-login">
                        <i class="bi bi-arrow-left me-1"></i>Back to Login
                    </a>
                    <span class="mx-2">|</span>
                    <a href="KEY.php" class="back-to-login">
                        <i class="bi bi-person-plus me-1"></i>Register
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
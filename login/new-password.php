<?php 
session_start();
require_once "../database/connection.php";

$errors = [];
$email = $_SESSION['reset_email'] ?? '';

// Redirect if no email in session
if (empty($email)) {
    header('Location: forgot-password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check-reset-otp'])) {
    $otp = trim($_POST['otp']);
    
    if (empty($otp)) {
        $errors[] = "Please enter the verification code.";
    } elseif (!is_numeric($otp) || strlen($otp) !== 6) {
        $errors[] = "Please enter a valid 6-digit code.";
    } else {
        try {
            // Check if code is valid and not expired
            $stmt = $con_admin->prepare("SELECT email FROM password_resets WHERE email = ? AND code = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param("si", $email, $otp);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                // Valid code found
                $_SESSION['reset_code_verified'] = true;
                $_SESSION['reset_email'] = $email;
                $_SESSION['info'] = "Code verified successfully. Create your new password.";
                
                // Delete used code
                $deleteStmt = $con_admin->prepare("DELETE FROM password_resets WHERE email = ?");
                $deleteStmt->bind_param("s", $email);
                $deleteStmt->execute();
                $deleteStmt->close();
                
                header('Location: new-password.php');
                exit;
            } else {
                $errors[] = "Invalid or expired verification code.";
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Reset code verification error: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prison Management System - Code Verification</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .code-input {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .resend-link {
            color: #6665ee;
            text-decoration: none;
            font-size: 14px;
        }
        .resend-link:hover {
            color: #5757d1;
            text-decoration: underline;
        }
        .email-display {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .countdown {
            font-size: 14px;
            color: #dc3545;
            font-weight: bold;
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
                
                <form action="reset-code.php" method="POST" autocomplete="off">
                    <h2 class="text-center">Code Verification</h2>
                    <p class="text-center">Enter the 6-digit code sent to your email</p>
                    
                    <div class="email-display">
                        <small>
                            <i class="bi bi-envelope-check me-2"></i>
                            <strong>Code sent to:</strong> <?= htmlspecialchars($email) ?>
                        </small>
                    </div>
                    
                    <?php if (isset($_SESSION['info'])): ?>
                        <div class="alert alert-success text-center">
                            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_SESSION['info']) ?>
                        </div>
                        <?php unset($_SESSION['info']); ?>
                    <?php endif; ?>
                    
                    <?php if (count($errors) > 0): ?>
                        <div class="alert alert-danger text-center">
                            <?php foreach($errors as $error): ?>
                                <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?><br>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <input class="form-control code-input" type="text" name="otp" placeholder="000000" required maxlength="6" pattern="[0-9]{6}" title="Please enter a 6-digit number">
                        <small class="form-text text-muted text-center">
                            Enter the 6-digit code from your email
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="check-reset-otp" class="btn btn-primary btn-block">
                            <i class="bi bi-check-circle me-2"></i>Verify Code
                        </button>
                    </div>
                </form>
                
                <div class="text-center">
                    <p class="mb-2">
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>Code expires in <span class="countdown" id="countdown">60:00</span>
                        </small>
                    </p>
                    
                    <p class="mb-0">
                        Didn't receive the code? 
                        <a href="forgot-password.php" class="resend-link">
                            <i class="bi bi-arrow-clockwise me-1"></i>Request New Code
                        </a>
                    </p>
                    
                    <div class="mt-3">
                        <a href="login-user.php" class="resend-link">
                            <i class="bi bi-arrow-left me-1"></i>Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-format input to numbers only
        document.querySelector('input[name="otp"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Countdown timer (60 minutes)
        let timeLeft = 60 * 60; // 60 minutes in seconds
        const countdownElement = document.getElementById('countdown');
        
        function updateCountdown() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                countdownElement.textContent = 'EXPIRED';
                countdownElement.style.color = '#dc3545';
                // Optionally disable the form or redirect
                return;
            }
            
            timeLeft--;
        }
        
        // Update countdown every second
        updateCountdown();
        setInterval(updateCountdown, 1000);
        
        // Auto-submit when 6 digits entered
        document.querySelector('input[name="otp"]').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                setTimeout(() => {
                    this.closest('form').submit();
                }, 100);
            }
        });
    </script>
</body>
</html>
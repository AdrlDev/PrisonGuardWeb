<?php
session_start();
include '../database/connection.php';

$error = "";
$success = "";

// Handle key verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_key'])) {
    $key_code = trim($_POST['key_code']);
    
    // Log the attempt
    error_log("Attempting to verify key: " . $key_code);

    // Validate key format
    if (strlen($key_code) !== 16 || !ctype_xdigit($key_code)) {
        $error = "❌ Invalid key format. Please check your key and try again.";
    } else {
        $stmt = $con_admin->prepare("SELECT * FROM registration_keys WHERE key_code = ?");
        if ($stmt) {
            $stmt->bind_param("s", $key_code);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result && $result->num_rows === 1) {
                    $key = $result->fetch_assoc();

                    // Check validity
                    if ($key['is_blocked']) {
                        $error = "❌ This key has been blocked.";
                    } elseif (!empty($key['expires_at']) && strtotime($key['expires_at']) < time()) {
                        $error = "❌ This key has expired.";
                    } elseif ($key['usage_used'] >= $key['usage_limit']) {
                        $error = "❌ This key is already used.";
                    } else {
                        // ✅ Key is valid
                        $_SESSION['verified_key'] = $key_code;
                        $_SESSION['key_role'] = $key['role_type'];
                        $_SESSION['key_email'] = $key['email'];

                        // 🔥 Increment usage count
                        $update = $con_admin->prepare("UPDATE registration_keys SET usage_used = usage_used + 1 WHERE id = ?");
                        $update->bind_param("i", $key['id']);
                        $update->execute();
                        $update->close();

                        error_log("Key verification successful. Role: " . $key['role_type']);

                        header("Location: signup-user.php");
                        exit();
                    }
                } else {
                    $error = "❌ Invalid key. Please check your key and try again.";
                }
            } else {
                $error = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Database error: " . $con_admin->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Key Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('../assets/img/magbay.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: inherit;
            filter: blur(8px);
            z-index: -1;
        }
        .container {
            width: 100%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.97);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-weight: 500;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .alert-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-left: 4px solid #a71e2a;
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
            transform: translateY(-1px);
        }
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border: none;
            padding: 12px;
            font-weight: 500;
            width: 100%;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <h3 class="text-center mb-4">Key Verification</h3>
        
        <div class="welcome-text text-center mb-4">
            <p class="mb-2">Please enter your verification key below to proceed.</p>
            <small class="text-muted">(This key should have been provided by your administrator)</small>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="verify_key" value="1">
            <div class="mb-3">
                <input type="text" 
                       name="key_code" 
                       class="form-control" 
                       placeholder="Enter Registration Key" 
                       required
                       autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary">
                Verify Key
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="login-user.php" class="text-decoration-none text-muted">Back to Login</a>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alertBox = document.querySelector('.alert');
            if (alertBox) {
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 300);
            }
        }, 5000);
    </script>
</body>
</html>

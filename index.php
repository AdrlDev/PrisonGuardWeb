<?php
session_start();
require_once 'classes/AuthenticationFlow.php';

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['email']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'warden') {
        header('Location: modules/Warden/WARDEN_DASHBOARD.php');
        exit();
    } else if ($_SESSION['role'] == 'prison_guard') {
        header('Location: modules/PG/PG_DASHBOARD.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Magbay Jail System</title>
    <link rel="shortcut icon" href="assets/img/Occi.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <div class="bg-blur"></div>z
    <div class="welcome-card">
        <img src="assets/img/Occi.png" alt="OCCI Logo" class="logo-img">
        <div class="welcome-title">Welcome to Magbay Jail System</div>
        <div class="welcome-desc">Manage visitors, inmates, and staff efficiently and securely.</div>
        <div class="d-flex justify-content-center btn-group">
            <a href="login/login-user.php" class="btn btn-primary px-4">Log In</a>
            <a href="login/KEY.php" class="btn btn-outline-primary px-4">Register</a>
        </div>
    </div>
</body>
</html>
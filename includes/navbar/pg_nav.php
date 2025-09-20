<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                <img src="../../assets/img/Occi.png" alt="OCCI Logo" class="img-fluid">
                </div>
            </div>

        <div class="nav-section">
            <h3 class="nav-title">Applications</h3>
            <div class="nav flex-column">
            <a href="/Capstone/modules/PG/PG_DASHBOARD.php" class="nav-link <?php if($current_page == 'PG_DASHBOARD.php') echo 'active'; ?>">
                <i class="bi bi-grid nav-icon"></i>
                Dashboard
            </a>
        <a href="/Capstone/modules/PG/PG_INFO.php" class="nav-link <?php if($current_page == 'PG_INFO.php') echo 'active'; ?>">
                <i class="bi bi-person-vcard"></i>
                Visitors Information
            </a>
            </div>
        </div>

        <div class="nav-section">
            <h3 class="nav-title">Others</h3>
            <div class="nav flex-column">
            <a href="/Capstone/modules/PG/PG_REG_VISITORS.php" class="nav-link <?php if($current_page == 'PG_REG_VISITORS.php') echo 'active'; ?>">
                <i class="bi bi-calendar nav-icon"></i>
                Registered Visitors
            </a>
            <a href="/Capstone/modules/PG/PG_ACC_SETTINGS.php" class="nav-link <?php if($current_page == 'PG_ACC_SETTINGS.php') echo 'active'; ?>">
                <i class="bi bi-gear"></i>
                Account Settings
            </a>
            </div>
        </div>

        <div class="sidebar-footer">
            <a href="/Capstone/login/logout-user.php" class="nav-link text-center">
            <i class="bi bi-box-arrow-right nav-icon"></i>
            Log out
            </a>
        </div>
        </nav>
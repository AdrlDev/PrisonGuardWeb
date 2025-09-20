<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
        <img src="../../assets/img/Occi.png" alt="OCCI Logo" class="img-fluid">
        </div>
    </div>

    <div class="nav-section">
        <h3 class="nav-title">Applications</h3>
        <div class="nav flex-column">
            <a href="../../modules/Warden/WARDEN_DASHBOARD.php" class="nav-link <?php if($current_page == 'WARDEN_DASHBOARD.php') echo 'active'; ?>">
                <i class="bi bi-grid nav-icon"></i>
                Dashboard
            </a>
            <a href="../../modules/Warden/WARDEN_VISITORS_MANAGEMENT.php" class="nav-link <?php if($current_page == 'WARDEN_VISITORS_MANAGEMENT.php') echo 'active'; ?>">
                <i class="bi bi-person"></i>
                Visitor's Management
            </a>
            <a href="../../modules/Warden/WARDEN_INMATES_DATA.php" class="nav-link <?php if($current_page == 'WARDEN_INMATES_DATA.php') echo 'active'; ?>">
                <i class="bi bi-person-vcard"></i>
                Manage Inmates Data
            </a>
            <a href="../../modules/Warden/WARDEN_PG_DATA.php" class="nav-link <?php if($current_page == 'WARDEN_PG_DATA.php') echo 'active'; ?>">
                <i class="bi bi-person-vcard"></i>
                Manage PG Data
            </a>
        </div>
    </div>

    <div class="nav-section">
        <h3 class="nav-title">Others</h3>
        <div class="nav flex-column">
            <a href="../../modules/Warden/WARDEN_REPORTS.php" class="nav-link <?php if($current_page == 'WARDEN_REPORTS.php') echo 'active'; ?>">
                <i class="bi bi-folder"></i>
                Reports
            </a>
            <a href="../../modules/Warden/WARDEN_ACC_SETTINGS.php" class="nav-link <?php if($current_page == 'WARDEN_ACC_SETTINGS.php') echo 'active'; ?>">
                <i class="bi bi-gear"></i>
                Account Settings
            </a>
        </div>
    </div>

    <div class="sidebar-footer">
        <a href="../../login/logout-user.php" class="nav-link text-center">
            <i class="bi bi-box-arrow-right nav-icon"></i>
            Log out
        </a>
    </div>
</nav>
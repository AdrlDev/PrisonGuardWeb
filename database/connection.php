<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable error reporting

try {
    $con_admin = new mysqli("localhost", "root", "", "admin_system"); 
    $dev_conn = new mysqli("localhost", "root", "", "developer_system");
} catch (mysqli_sql_exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check admin system connection
if ($con_admin->connect_error) {
    die("Admin Connection failed: " . $con_admin->connect_error);
}

// Check developer system connection
if ($dev_conn->connect_error) {
    die("Developer Connection failed: " . $dev_conn->connect_error);
}
?>

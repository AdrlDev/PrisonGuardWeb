<?php
require_once '../../classes/auth.php';
requireRole('prison_guard');
require_once '../../database/connection.php'; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitors Monitoring system</title>
    <link rel="shortcut icon" href="Occi.png" type="image/jpg">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/PG_DASHBOARD.css">
    <link rel="stylesheet" href="../../includes/navbar/PG_navbar.css" />
    <link rel="shortcut icon" href="../../assets/img/Occi.png" type="image/jpg">
</head>


<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../../includes/navbar/pg_nav.php'; ?>

        <!-- Main Content -->
        <main class="main-content flex-fill">
            <!-- Header -->
            <?php include '../../includes/header/pg_dash.php'; ?>

            <!-- Content -->
            <div class="container-fluid p-4">
                <!-- Stats Grid -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card stats-card blue h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-value" id="today-visitor">0</div>
                                    <div class="stats-label">Today's Visitors</div>
                                </div>
                                <i class="bi bi-person fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card stats-card green h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-value" id="this-week-visitor">0</div>
                                    <div class="stats-label">This Week</div>
                                </div>
                                <i class="bi bi-calendar-week fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card stats-card purple h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-value" id="this-month-visitor">0</div>
                                    <div class="stats-label">This Month</div>
                                </div>
                                <i class="bi bi-calendar-week fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card stats-card orange h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stats-value" id="overall">0</div>
                                    <div class="stats-label">Overall</div>
                                </div>
                                <i class="bi bi-bar-chart fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visitor Table -->
                <div class="table-container">
                    <h2 class="table-header">Current Visitors</h2>
                    <div class="d-flex justify-content-between align-items-center px-4 py-2">
                        <button id="scanVisitorBtn" class="btn btn-primary">
                            <i class="bi bi-camera me-2"></i> Scan Visitor
                        </button>
                        <button id="selectDateBtn" class="btn btn-primary">
                            <i class="bi bi-calendar me-2"></i> Select Date
                        </button>
                        <!-- Hidden inputs for Date & Time -->
                        <input type="date" id="dateInput" class="d-none">
                        <input type="time" id="timeInput" class="d-none">
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Visitor Name</th>
                                    <th>Id Number</th>
                                    <th>Inmate to Visit</th>
                                    <th>Relationship</th>
                                    <th>Date</th>
                                    <th>Time in</th>
                                    <th>Time out</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="no-visitors text-center">
                                        No Visitors
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center my-3" id="pagination"></div>
                    </div>

                    <!-- Scanner Modal -->
                    <div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Visitor Face Scanner</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <video id="cameraFeed" autoplay playsinline class="w-100 rounded"></video>
                                    <canvas id="snapshotCanvas" class="d-none"></canvas>
                                    <p class="mt-2 text-muted" id="progressText">Scanning face, please wait...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- Close table-container -->

                <!-- Date Picker Modal -->
                <div class="modal fade" id="datePickerModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Select Date</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <input type="date" id="modalDatePicker" class="form-control">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" id="applyDateBtn">Apply</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- Close container-fluid -->
        </main>
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script src="../../assets/js/PG_DASHBOARD.js?v=4"></script>
        <script src="../../assets/js/videoCamera.js?v=13"></script>
</body>

</html>
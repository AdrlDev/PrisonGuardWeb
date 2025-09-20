<?php
header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include '../database/connection.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['visitorsFullName']) || !isset($data['visitorsIdNumber']) || !isset($data['status'])
        || !isset($data['inmateToVisit']) || !isset($data['relationshipToInmate'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing required fields"
        ]);
        exit;
    }

    $visitorsFullName = $data['visitorsFullName'];
    $visitorsIdNumber = $data['visitorsIdNumber'];
    $inmateToVisit = $data['inmateToVisit'];
    $relationshipToInmate = $data['relationshipToInmate'];
    $status = strtoupper($data['status']);
    $now = date("Y-m-d H:i:s");
    $today = date("Y-m-d");

    // Check if already has IN today
    if ($status === "IN") {
        $stmt = $con_admin->prepare("SELECT * FROM visitors_log WHERE visitorsIdNumber = ? AND status = 'IN' AND DATE(timeIn) = ?");
        $stmt->bind_param("ss", $visitorsIdNumber, $today);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Visitor already checked IN today"]);
            exit;
        }

        // Insert IN log
        $insert = $con_admin->prepare("INSERT INTO visitors_log (visitorsFullName, visitorsIdNumber, inmateToVisit, relationshipToInmate, timeIn, status) VALUES (?, ?, ?, ?, ?, 'IN')");
        $insert->bind_param("sssss", $visitorsFullName, $visitorsIdNumber, $inmateToVisit, $relationshipToInmate, $now);
        $insert->execute();
        echo json_encode(["status" => "ok", "action" => "IN", "message" => "Visitor checked IN"]);
        exit;
    }

    // Check if already has OUT today
    if ($status === "OUT") {
        $stmt = $con_admin->prepare("SELECT * FROM visitors_log WHERE visitorsIdNumber = ? AND status = 'OUT' AND DATE(timeOut) = ?");
        $stmt->bind_param("ss", $visitorsIdNumber, $today);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Visitor already checked OUT today"]);
            exit;
        }

        // Insert OUT log
        $insert = $con_admin->prepare("INSERT INTO visitors_log (visitorsFullName, visitorsIdNumber, inmateToVisit, relationshipToInmate, timeOut, status) VALUES (?, ?, ?, ?, ?, 'OUT')");
        $insert->bind_param("sssss", $visitorsFullName, $visitorsIdNumber, $inmateToVisit, $relationshipToInmate, $now);
        $insert->execute();
        echo json_encode(["status" => "ok", "action" => "OUT", "message" => "Visitor checked OUT"]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Invalid status"]);
    exit;
}

// 📌 Fetch stats
if ($method === "GET" && isset($_GET['stats'])) {
    $today = date("Y-m-d");
    $weekStart = date("Y-m-d", strtotime("last sunday"));
    $monthStart = date("Y-m-01");

    // Count today's visits (all IN logs)
    $todayCount = $con_admin->query("SELECT COUNT(*) AS cnt 
                                        FROM visitors_log 
                                        WHERE status='IN' AND DATE(timeIn) = '$today'")
                                ->fetch_assoc()['cnt'];

    // Count this week's visits
    $weekCount = $con_admin->query("SELECT COUNT(*) AS cnt 
                                        FROM visitors_log 
                                        WHERE status='IN' AND DATE(timeIn) >= '$weekStart'")
                            ->fetch_assoc()['cnt'];

    // Count this month's visits
    $monthCount = $con_admin->query("SELECT COUNT(*) AS cnt 
                                        FROM visitors_log 
                                        WHERE status='IN' AND DATE(timeIn) >= '$monthStart'")
                                ->fetch_assoc()['cnt'];

    // Count overall visits
    $overallCount = $con_admin->query("SELECT COUNT(*) AS cnt 
                                        FROM visitors_log 
                                        WHERE status='IN'")
                                ->fetch_assoc()['cnt'];

    echo json_encode([
        "status" => "ok",
        "today" => $todayCount,
        "week" => $weekCount,
        "month" => $monthCount,
        "overall" => $overallCount
    ]);
    exit;
}

// 📌 Retrieve all logs with pagination
if ($method === "GET" && !isset($_GET['stats'])) {
    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // 📌 If no date passed, default to today
    $filterDate = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");

    // Total count
    $totalRes = $con_admin->query("SELECT COUNT(*) as total FROM visitors_log WHERE DATE(timeIn) = '$filterDate' OR DATE(timeOut) = '$filterDate ORDER BY id DESC LIMIT $limit OFFSET $offset'");
    $total = $totalRes->fetch_assoc()['total'];

    // Fetch logs with limit
    $sql = "SELECT * FROM visitors_log WHERE DATE(timeIn) = '$filterDate' OR DATE(timeOut) = '$filterDate' ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $result = $con_admin->query($sql);

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    echo json_encode([
        "status" => "ok",
        "logs" => $logs,
        "total" => $total,
        "page" => $page,
        "limit" => $limit,
        "totalPages" => ceil($total / $limit)
    ]);
    exit;
}



echo json_encode(["status" => "error", "message" => "Unsupported request method"]);
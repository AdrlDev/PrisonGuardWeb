<?php
header('Content-Type: application/json');
include '../database/connection.php';

// Get JSON POST body
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['idNumber'])) {
    echo json_encode(["status" => "error", "message" => "No idNumber provided"]);
    exit;
}

$idNumber = $data['idNumber'];

// Use prepared statement
$stmt = $con_admin->prepare("SELECT * FROM visitors WHERE idNumber = ?");
$stmt->bind_param("s", $idNumber);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $visitor = $result->fetch_assoc();
    echo json_encode([
        "status" => "ok",
        "visitor" => $visitor
    ]);
} else {
    echo json_encode(["status" => "not_found"]);
}

$stmt->close();
$con_admin->close();
?>
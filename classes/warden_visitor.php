<?php
header("Content-Type: application/json");
require_once __DIR__ . "../../database/connection.php"; // fixed path

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'No ID provided']);
        exit;
    }

    try {
        $stmt = $con_admin->prepare("DELETE FROM visitors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Visitor deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Visitor not found!']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
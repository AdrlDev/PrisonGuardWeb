<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// ✅ Use absolute path to avoid "file not found" issues
require_once __DIR__ . "/api_client.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit;
    }

    $api = new FaceScanAPI();

    // Handle cancel action
    if (isset($data["action"]) && $data["action"] === "cancel") {
        try {
            $response = $api->cancelEnroll();
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        exit;
    }

    $fullName = $data["name"] ?? null;
    $idNumber = $data["id_number"] ?? null;
    $images = $data["images_base64"] ?? null;

    if (!$fullName || !$idNumber) {
        echo json_encode(["error" => "Missing name or ID number"]);
        exit;
    }

    if (!$images || !is_array($images) || count($images) === 0) {
        echo json_encode(["error" => "No images provided for enrollment"]);
        exit;
    }

    try {
        $response = $api->enroll($fullName, $idNumber, $images);

        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
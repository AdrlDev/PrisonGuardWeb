<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
header("Content-Type: application/json");

// ✅ Use absolute path to avoid "file not found" issues
require_once __DIR__ . "/api_client.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // 🔹 Read raw JSON body
        $input = json_decode(file_get_contents("php://input"), true);

        $images = null;
        if (isset($input["images_base64"])) {
            $images = $input["images_base64"];
        }

        $api = new FaceScanAPI();
        
        // 🔹 Check if request is to cancel scan
        if (isset($input["action"]) && $input["action"] === "cancel") {
            $response = $api->cancelScan();
        } else {
            // Otherwise, perform normal scan
            $images = $input["images_base64"] ?? null;
            $response = $api->scan($images);
        }

        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
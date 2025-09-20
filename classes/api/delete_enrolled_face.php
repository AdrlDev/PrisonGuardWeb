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
        $id_number = null;
        if (isset($input["images_base64"]) && isset($input["id_number"])) {
            $images = $input["images_base64"];
            $id_number = $input["id_number"];
        }

        $api = new FaceScanAPI();

        // Delete face by scanning images
        $response = $api->deleteFace($images, $id_number);

        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
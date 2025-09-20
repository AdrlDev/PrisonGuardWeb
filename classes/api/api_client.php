<?php
class FaceScanAPI {
    private $baseUrl = "http://72.60.193.190"; // FastAPI server

    // 🔹 Generic request method
    private function request($endpoint, $method = "POST", $data = null) {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ["error" => $error];
        }

        curl_close($ch);

        // Decode safely
        $decoded = json_decode($response, true);
        return $decoded !== null ? $decoded : ["raw" => $response];
    }

    // ✅ Enroll a user
    public function enroll($name, $id_number, $images_base64) {
        return $this->request("/api/enroll", "POST", [
            "name" => $name,
            "id_number" => $id_number,
            "images_base64" => $images_base64
        ]);
    }

    // ✅ Scan a user (with images)
    public function scan($images = null) {
        $payload = [];
        if ($images !== null) {
            $payload["images_base64"] = $images;
        }
        return $this->request("/api/scan", "POST", $payload);
    }

    // ✅ Cancel current scan
    public function cancelScan() {
        return $this->request("/api/cancel-scan", "POST");
    }

    // ✅ Cancel current enrollment
    public function cancelEnroll() {
        return $this->request("/api/cancel-enroll", "POST");
    }

    // ✅ Delete enrolled face by scanning
    public function deleteFace($images_base64, $id_number) {
        return $this->request("/api/delete-face", "POST", [
            "images_base64" => $images_base64,
            "id_number" => $id_number
        ]);
    }
}
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';

// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;
        
        if (!$id) {
            jsonResponse(400, false, "Work experience ID is required");
            exit();
        }
        
        $sql = "DELETE FROM work_experience WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            jsonResponse(200, true, "Work experience deleted successfully");
        } else {
            jsonResponse(404, false, "Work experience not found");
        }
        exit();
        
    } catch(PDOException $e) {
        jsonResponse(500, false, "Database error", null, $e->getMessage());
        exit();
    }
} else {
    jsonResponse(405, false, "Method Not Allowed");
    exit();
}
?>
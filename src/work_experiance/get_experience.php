<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';

// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $resume_id = $_GET['resume_id'] ?? null;
        
        if (!$resume_id) {
            jsonResponse(400, false, "resume_id query parameter is required");
            exit();
        }
        
        $sql = "SELECT id, resume_id, company, position, start_date, end_date, description, created_at, updated_at FROM work_experience WHERE resume_id = :rid ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':rid' => $resume_id]);
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse(200, true, "Work experience entries retrieved", $data);
        exit();
        
    } catch (PDOException $e) {
        jsonResponse(500, false, "Database error", null, $e->getMessage());
        exit();
    }
} else {
    jsonResponse(405, false, "Method Not Allowed");
    exit();
}
?>
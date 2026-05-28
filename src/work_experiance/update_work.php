<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';

// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $id = $data['id'] ?? null;
        $company = $data['company'] ?? null;
        $position = $data['position'] ?? null;
        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        $description = $data['description'] ?? null;
        
        if (!$id) {
            jsonResponse(400, false, "Work experience ID is required");
            exit();
        }
        
        if (!$company || !$position) {
            jsonResponse(400, false, "Company and position are required");
            exit();
        }
        
        $sql = "UPDATE work_experience 
                SET company = :comp, position = :pos, start_date = :start, end_date = :end, description = :desc, updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            ':id'    => $id,
            ':comp'  => $company,
            ':pos'   => $position,
            ':start' => $start_date,
            ':end'   => $end_date,
            ':desc'  => $description
        ]);
        
        if ($success && $stmt->rowCount() > 0) {
            jsonResponse(200, true, "Work experience updated successfully");
        } else {
            jsonResponse(404, false, "Work experience not found or no changes made");
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
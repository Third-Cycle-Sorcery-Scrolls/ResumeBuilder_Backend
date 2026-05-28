<?php
require_once __DIR__ . "/../../../config/db.php";
require_once __DIR__ . "/../../../helpers/response.php";
require_once __DIR__ . "/../../../helpers/auth.php";

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    authenticateAdmin();
} catch (Exception $e) {
    jsonResponse(401, false, "Unauthorized", null, "Admin authentication required");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    jsonResponse(405, false, "Method not allowed");
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$resumeId = $input['resume_id'] ?? $input['id'] ?? null;

if (!$resumeId) {
    jsonResponse(400, false, "resume_id is required");
    exit();
}

$stmt = $conn->prepare("DELETE FROM resumes WHERE id = :id");
$stmt->execute([':id' => $resumeId]);

if ($stmt->rowCount() > 0) {
    jsonResponse(200, true, "Resume deleted successfully");
} else {
    jsonResponse(404, false, "Resume not found");
}

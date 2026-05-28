<?php
require_once __DIR__ . "/../../../config/db.php";
require_once __DIR__ . "/../../../helpers/response.php";
require_once __DIR__ . "/../../../helpers/auth.php";

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    jsonResponse(405, false, "Method not allowed");
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['user_id'] ?? null;

if (!$userId) {
    jsonResponse(400, false, "user_id is required");
    exit();
}

if ((int)$userId === 1) {
    jsonResponse(400, false, "Cannot deny the primary admin account");
    exit();
}

$stmt = $conn->prepare("UPDATE users SET role = 'denied' WHERE id = :id AND role <> 'admin'");
$stmt->execute([':id' => $userId]);

if ($stmt->rowCount() > 0) {
    jsonResponse(200, true, "User access denied successfully");
} else {
    jsonResponse(404, false, "User not found or already restricted");
}

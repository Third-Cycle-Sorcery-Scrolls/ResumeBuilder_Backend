<?php
require_once __DIR__  . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/debug.php';

// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rawInput = file_get_contents("php://input");

    // Debug
    error_log($rawInput);

    $data = json_decode($rawInput, true);
    if (!$data) {
        jsonResponse(400, false, "Invalid JSON", null, "Request body is not valid JSON");
    }

    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    if (!$email || !$password) {
        jsonResponse(400, false, "Email and password are required!", null, "Missing fields");
    }

    $stmt = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        jsonResponse(400, false, "Invalid credentials!", null, "Wrong email or password");
    }

    debug("User logged in: " . $user['email'], $user);

    jsonResponse(200, true, "Login successful", [
        "userId" => $user['id'],
        "email" => $user['email'],
        "role" => $user['role'] ?? 'user'
    ]);

} else {
    jsonResponse(404, false, "Route not found", null, "Invalid endpoint");
}
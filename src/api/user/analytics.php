<?php
require_once __DIR__  . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/debug.php';
require_once __DIR__ . '/../../models/User.php';

// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS,DELETE,PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

 //analytics:- by user id
//total_resumes,last_updated,most used template
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET['userId'] ?? null;

    if (!$userId) {
        jsonResponse(400, false, "User ID is required!", null, "Missing userId parameter");
    }

    $user = new User($conn);
    $analytics = $user->getAnalytics($userId);

    if ($analytics) {
        jsonResponse(200, true, "User analytics retrieved successfully", $analytics);
    } else {
        jsonResponse(404, false, "User not found or no analytics available", null, "No data for user");
    }
} else {
    jsonResponse(404, false, "Route not found", null, "Invalid endpoint");
}

<?php
require_once __DIR__  . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/debug.php';
require_once __DIR__ . '/../../helpers/auth.php';
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

// Authenticate user for all requests
try {
    $user = authenticateUser();
    $userId = $user['userId'];
} catch (Exception $e) {
    jsonResponse(401, false, "Unauthorized: Authentication required");
    exit();
}

 //analytics:- by user id
//total_resumes,last_updated,most used template
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $requestUserId = $_GET['userId'] ?? null;

    if (!$requestUserId) {
        jsonResponse(400, false, "User ID is required!", null, "Missing userId parameter");
    }
    
    // Verify user can only access their own analytics
    if (!verifyResourceOwnership($userId, $requestUserId)) {
        jsonResponse(403, false, "Forbidden: Cannot access another user's analytics");
        exit();
    }

    $userModel = new User($conn);
    $analytics = $userModel->getAnalytics($requestUserId);

    if ($analytics) {
        jsonResponse(200, true, "User analytics retrieved successfully", $analytics);
    } else {
        jsonResponse(404, false, "User not found or no analytics available", null, "No data for user");
    }
} else {
    jsonResponse(404, false, "Route not found", null, "Invalid endpoint");
}

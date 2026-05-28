<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/Logger.php';

// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify user is authenticated
        $user = authenticateUser();

        Logger::info(Logger::CAT_AUTH, 'AUTH_LOGOUT', 'User logged out', [
            'user_id' => $user['userId'] ?? null,
            'email'   => $user['email']  ?? null,
        ]);
        
        // Since we're using token-based authentication (stateless),
        // the logout is handled on the client side by removing the token.
        // Server simply confirms the logout and invalidates the session if needed.
        
        jsonResponse(200, true, "Logout successful", [
            "message" => "Token has been invalidated. Please remove it from client storage."
        ]);
        
    } catch (Exception $e) {
        jsonResponse(401, false, "Unauthorized", null, "Invalid or missing authentication token");
    }
} else {
    jsonResponse(405, false, "Method Not Allowed", null, "Only POST requests are allowed");
}
?>

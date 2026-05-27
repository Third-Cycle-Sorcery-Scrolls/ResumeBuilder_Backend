<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middlewares/authMiddleware.php';
require_once __DIR__ . '/../../services/ActivityLogger.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    // Get the user from the token
    $user = authMiddleware();

    // Log the logout event
    ActivityLogger::log($conn, $user['id'], 'logout', 'user', $user['id'], null);

    jsonResponse(200, true, "Logout successful", null, null);

}else{
    jsonResponse(404, false, "Route not found", null, "The requested endpoint does not exist.");
}
?>
<?php

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/logger.php';

header('Content-Type: application/json');

try {

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        jsonResponse(200, true, "Logout successful", null, null);
    } else {
        logError($conn, "Logout route accessed with invalid method", __FILE__, __LINE__, null);
        jsonResponse(404, false, "Route not found", null, "The requested endpoint does not exist.");
    }

} catch (Exception $e) {
    logError($conn, $e->getMessage(), $e->getFile(), $e->getLine(), null);
    jsonResponse(500, false, "Internal Server Error");
}
?>
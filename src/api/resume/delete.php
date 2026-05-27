<?php

require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/logger.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        logError($conn, "Resume delete route accessed with invalid method", __FILE__, __LINE__, null);
        jsonResponse(404, false, 'Route not found', null, 'The requested endpoint does not exist.');
    }
    
    $id = $_GET['id'] ?? null;

    // Validation
    if (!$id) {

        logError(
            $conn,
            "Resume deletion failed: missing resume id",
            __FILE__,
            __LINE__,
            null
        );

        jsonResponse(400, false, "Resume ID is required");
    }

    $resume = new Resume($conn);

    $result = $resume->delete($id);

    if (!$result) {

        logError($conn, "Resume deletion failed for resume_id=$id", __FILE__, __LINE__, $userId ?? null);

        jsonResponse(500, false, "Could not delete resume");
    }

    jsonResponse(200, true, "Resume deleted");

} catch (Exception $e) {

    logError(
        $conn,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        null
    );

    jsonResponse(500, false, "Internal Server Error");
}
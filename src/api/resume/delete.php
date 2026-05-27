<?php

require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";
require_once '../../helpers/response.php';
require_once '../../helpers/logger.php';
require_once __DIR__ . '/../../services/ActivityLogger.php';

try {

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

        logError(
            $conn,
            "Resume deletion failed for resume_id=$id",
            __FILE__,
            __LINE__,
            null
        );

        jsonResponse(500, false, "Could not delete resume");
    }
        ActivityLogger::log($conn, null, 'resume_delete', 'resume', $id, null);
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
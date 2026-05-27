<?php

require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";
require_once '../../helpers/response.php';
require_once '../../helpers/logger.php';
require_once __DIR__ . '/../../services/ActivityLogger.php';

try {

    $id = $_GET['id'] ?? null;

    $data = json_decode(file_get_contents("php://input"), true);

    $title = $data['title'] ?? null;
    $template = $data['template'] ?? null;

    // Basic validation
    if (!$id) {

        logError(
            $conn,
            "Resume update failed: missing resume id",
            __FILE__,
            __LINE__,
            null
        );

        jsonResponse(400, false, "Resume ID is required");
    }

    if (!$title && !$template) {

        logError(
            $conn,
            "Resume update failed: no fields provided for resume_id=$id",
            __FILE__,
            __LINE__,
            null
        );

        jsonResponse(400, false, "At least one field is required");
    }

    $resume = new Resume($conn);

    $result = $resume->update($id, $title, $template);

    if (!$result) {

        logError(
            $conn,
            "Resume update failed for resume_id=$id",
            __FILE__,
            __LINE__,
            null
        );

        jsonResponse(500, false, "Could not update resume");
    }
    ActivityLogger::log($conn, null, 'resume_update', 'resume', $id, ['title' => $title, 'template' => $template]);

    jsonResponse(200, true, "Resume updated");

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
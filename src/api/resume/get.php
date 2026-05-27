<?php

require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";
require_once __DIR__.'/../../helpers/response.php';
require_once __DIR__.'/../../helpers/logger.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        logError($conn, "Resume get route accessed with invalid method", __FILE__, __LINE__, null);
        jsonResponse(404, false, 'Route not found', null, 'The requested endpoint does not exist.');
    }

    $id = $_GET['id'] ?? null;

    if (!$id) {
        logError($conn, "Resume fetch failed: missing resume_id", __FILE__, __LINE__, null);
        jsonResponse(400, false, "ID required");
    }

    $resume = new Resume($conn);
    $data = $resume->getById($id);

    if (!$data) {
        logError($conn, "Resume fetch failed: no resume found for resume_id=$id", __FILE__, __LINE__, null);
        jsonResponse(404, false, "Resume not found");
    }

    jsonResponse(200, true, "Resume fetched", $data);

} catch (Exception $e) {
    logError($conn, $e->getMessage(), $e->getFile(), $e->getLine(), null);
    jsonResponse(500, false, "Internal Server Error");
}
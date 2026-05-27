<?php

require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";
require_once __DIR__.'/../../helpers/response.php';
require_once __DIR__.'/../../helpers/logger.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        logError($conn, "Resume list route accessed with invalid method", __FILE__, __LINE__, null);
        jsonResponse(404, false, 'Route not found', null, 'The requested endpoint does not exist.');
    }

    $resume = new Resume($conn);
    $data = $resume->getAll();

    if ($data === false) {
        logError($conn, "Resume list fetch failed: getAll() returned false", __FILE__, __LINE__, null);
        jsonResponse(500, false, "Could not fetch resumes");
    }

    jsonResponse(200, true, "Resumes fetched", $data);

} catch (Exception $e) {
    logError($conn, $e->getMessage(), $e->getFile(), $e->getLine(), null);
    jsonResponse(500, false, "Internal Server Error");
}
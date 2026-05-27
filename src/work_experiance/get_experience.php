<?php

// Include the central DB connection, response helper and logger
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/logger.php';

// Tells the browser/Postman to expect JSON
header('Content-Type: application/json');

try {

    // Check if the request is a GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        logError($conn, "Get-experience route accessed with invalid method", __FILE__, __LINE__, null);
        jsonResponse(404, false, 'Route not found', null, 'The requested endpoint does not exist.');
    }

    // 1. Get resume_id from query params
    $resume_id = $_GET['resume_id'] ?? null;

    // Validation
    if (!$resume_id) {
        logError($conn, "Get-experience failed: missing resume_id", __FILE__, __LINE__, null);
        jsonResponse(400, false, "Resume ID is required!");
    }

    // 2. Simple Select Query
    $sql  = "SELECT * FROM work_experience WHERE resume_id = :rid";
    $stmt = $conn->prepare($sql);

    // 3. Execute
    $stmt->execute([':rid' => $resume_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Handle empty result
    if ($data === false) {
        logError($conn, "Get-experience failed: fetchAll() returned false for resume_id=$resume_id", __FILE__, __LINE__, null);
        jsonResponse(500, false, "Could not fetch work experience.");
    }

    // 5. Success Response
    jsonResponse(200, true, "Work experience fetched.", [
        "count"        => count($data),
        "work_history" => $data
    ]);

} catch (Exception $e) {
    // Error Response
    logError($conn, $e->getMessage(), $e->getFile(), $e->getLine(), null);
    jsonResponse(500, false, "Internal Server Error");
}
?>
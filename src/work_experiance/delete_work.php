<?php

// Include the central DB connection, response helper and logger
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/logger.php';

header('Content-Type: application/json');

try {

    // Check if the request is a POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logError($conn, "Delete-work route accessed with invalid method", __FILE__, __LINE__, null);
        jsonResponse(404, false, 'Route not found', null, 'The requested endpoint does not exist.');
    }

    // Collect data
    $id = $_POST['id'] ?? null;

    // Validation
    if (!$id) {
        logError($conn, "Delete-work failed: missing work experience id", __FILE__, __LINE__, null);
        jsonResponse(400, false, "ID is required!");
    }

    // Prepare SQL
    $sql  = "DELETE FROM work_experience WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // Execute
    $stmt->execute([':id' => $id]);

    // rowCount() tells us if something actually disappeared
    $count = $stmt->rowCount();

    if ($count > 0) {
        jsonResponse(200, true, "Successfully deleted $count row(s).");
    } else {
        // Success call but 0 rows deleted — ID likely doesn't exist
        logError($conn, "Delete-work: no rows deleted for work_experience id=$id — record may not exist", __FILE__, __LINE__, null);
        jsonResponse(404, false, "No record found with ID $id in work_experience.");
    }

} catch (Exception $e) {
    // Database error response
    logError($conn, $e->getMessage(), $e->getFile(), $e->getLine(), null);
    jsonResponse(500, false, "Internal Server Error");
}
?>
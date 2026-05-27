<?php

// Include the central DB connection, response helper and logger
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/logger.php';

header('Content-Type: application/json');

try {

    // Check if the request is a POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logError($conn, "Update-work route accessed with invalid method", __FILE__, __LINE__, null);
        jsonResponse(404, false, 'Route not found', null, 'The requested endpoint does not exist.');
    }

    // 1. We need the ID of the row to update
    $id      = $_POST['id'] ?? null;
    $company = $_POST['company_name'] ?? null;
    $title   = $_POST['job_title'] ?? null;
    $desc    = $_POST['description'] ?? '';

    // Validation
    if (!$id) {
        logError($conn, "Update-work failed: missing work experience id", __FILE__, __LINE__, null);
        jsonResponse(400, false, "ID is required for update!");
    }

    if (!$company && !$title) {
        logError($conn, "Update-work failed: no fields provided for work_experience id=$id", __FILE__, __LINE__, null);
        jsonResponse(400, false, "At least company name or job title is required!");
    }

    // 2. Prepare the Update SQL
    $sql = "UPDATE work_experience 
            SET company_name = :comp, job_title = :title, description = :desc 
            WHERE id = :id";

    $stmt = $conn->prepare($sql);

    // Execute
    $stmt->execute([
        ':id'   => $id,
        ':comp' => $company,
        ':title' => $title,
        ':desc'  => $desc
    ]);

    // Check if the update actually changed anything
    if (!$stmt->rowCount()) {
        logError($conn, "Update-work: no rows updated for work_experience id=$id — record may not exist", __FILE__, __LINE__, null);
        jsonResponse(404, false, "No record found with ID $id in work_experience.");
    }

    jsonResponse(200, true, "Work experience updated!");

} catch (Exception $e) {
    // Database error response
    logError($conn, $e->getMessage(), $e->getFile(), $e->getLine(), null);
    jsonResponse(500, false, "Internal Server Error");
}
?>
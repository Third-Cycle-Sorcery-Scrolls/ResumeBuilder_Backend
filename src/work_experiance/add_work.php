<?php

// Include the central DB connection, response helper and logger
require_once __DIR__ . '/../config/db.php'; 
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/logger.php';

try {
    // Check if the request is a POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logError($conn, "Add-work route accessed with invalid method", __FILE__, __LINE__, null);
        jsonResponse(404, false, 'Route not found', null, 'The requested endpoint does not exist.');
    }

    // Collect data
    $resume_id = $_POST['resume_id'] ?? null;
    $company   = $_POST['company_name'] ?? null;
    $title     = $_POST['job_title'] ?? null;
    $start     = $_POST['start_date'] ?? null;
    $end       = $_POST['end_date'] ?? null;
    $desc      = $_POST['description'] ?? '';

    // Validation using response helper
    if (!$resume_id) {
        logError($conn, "Add-work failed: missing resume_id", __FILE__, __LINE__, null);
        jsonResponse(400, false, "Resume ID is required!");
    }

    if (!$company || !$title) {
        logError($conn, "Add-work failed: missing company or title for resume_id=$resume_id", __FILE__, __LINE__, null);
        jsonResponse(400, false, "Company and Title are required!");
    }

    if (!$start) {
        logError($conn, "Add-work failed: missing start_date for resume_id=$resume_id", __FILE__, __LINE__, null);
        jsonResponse(400, false, "Start date is required!");
    }

    // Prepare SQL
    $sql = "INSERT INTO work_experience (resume_id, company_name, job_title, start_date, end_date, description) 
            VALUES (:rid, :comp, :title, :start, :end, :desc)";
    
    $stmt = $conn->prepare($sql);
    
    // Execute
    $stmt->execute([
        ':rid'   => $resume_id,
        ':comp'  => $company,
        ':title' => $title,
        ':start' => $start,
        ':end'   => $end,
        ':desc'  => $desc
    ]);

    // Check if the insert was successful
    if (!$stmt->rowCount()) {
        logError($conn, "Add-work failed: could not insert work experience for resume_id=$resume_id", __FILE__, __LINE__, null);
        jsonResponse(500, false, "Could not save work experience");
    }

    // Success Response
    jsonResponse(201, true, "Work experience saved successfully!", [
        "inserted_id" => $conn->lastInsertId()
    ]);

} catch (Exception $e) {
    // Database error response
    logError($conn, $e->getMessage(), $e->getFile(), $e->getLine(), null);
    jsonResponse(500, false, "Internal Server Error");
}
?>
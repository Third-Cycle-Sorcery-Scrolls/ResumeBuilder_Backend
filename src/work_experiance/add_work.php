<?php
// 1. Include the central DB connection and the response helper

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config/db.php'; 
// echo "";
// exit;
require_once __DIR__ . '../helpers/response.php';

try {
    // Check if the request is a POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // 2. Collect data (Using your $pdo from db.php)
        $resume_id = 1; 
        $company = $_POST['company_name'] ?? '';
        $title = $_POST['job_title'] ?? '';
        $start = $_POST['start_date'] ?? '';
        $end = $_POST['end_date'] ?? null; 
        $desc = $_POST['description'] ?? '';

        // 3. Validation using your helper
        if (empty($company) || empty($title)) {
            // Arguments: HTTP Code, Success, Message
            jsonResponse(400, false, "Company and Title are required!");
        }

        // 4. Prepare SQL
        $sql = "INSERT INTO work_experience (resume_id, company_name, job_title, start_date, end_date, description) 
                VALUES (:rid, :comp, :title, :start, :end, :desc)";
        
        $stmt = $pdo->prepare($sql);
        
        // 5. Execute
        $stmt->execute([
            ':rid'   => $resume_id,
            ':comp'  => $company,
            ':title' => $title,
            ':start' => $start,
            ':end'   => $end,
            ':desc'  => $desc
        ]);

        // 6. Success Response
        jsonResponse(201, true, "Work experience saved successfully!", [
            "inserted_id" => $pdo->lastInsertId()
        ]);
        
    } else {
        // Handle non-POST requests
        jsonResponse(405, false, "Method Not Allowed");
    }

} catch(PDOException $e) {
    // Database error response
    jsonResponse(500, false, "Database error", null, $e->getMessage());
}
?>
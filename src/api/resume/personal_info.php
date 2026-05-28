<?php
// personal_info.php
require_once __DIR__.'/../../models/PersonalInfo.php';
require_once __DIR__."/../../config/db.php";
require_once __DIR__.'/../../helpers/response.php';
require_once __DIR__.'/../../helpers/auth.php';
require_once __DIR__.'/../../models/Resume.php';
// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
// personal_info Table:
// - id (INT, PRIMARY KEY, AUTO_INCREMENT)
// - resume_id (INT, FOREIGN KEY referencing Resumes(id))
// - full_name (VARCHAR(255))
// - email (VARCHAR(255))
// - phone (VARCHAR(255))
// - address (VARCHAR(255))
// - created_at (TIMESTAMP)
// - updated_at (TIMESTAMP)
// - photo_url (VARCHAR(255)) (optional, URL or path to the uploaded profile picture)
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Authenticate user for all requests
try {
    $user = authenticateUser();
    $userId = $user['userId'];
} catch (Exception $e) {
    jsonResponse(401, false, "Unauthorized: Authentication required");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $resume_id = $_GET['resume_id'] ?? null;

    if (!$resume_id) {
        jsonResponse(400, false, "Resume ID required");
    }
    $personalInfo = new PersonalInfo($conn);
    $data = $personalInfo->getByResumeId($resume_id);
    if (!$data) {
        jsonResponse(404, false, "Personal info not found");
    }
    jsonResponse(200, true, "Personal info fetched", $data);
        
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $resume_id = $data['resume_id'] ?? null;
    $full_name = $data['full_name'] ?? null;
    $email = $data['email'] ?? null;
    $phone = $data['phone'] ?? null;
    $address = $data['address'] ?? null;
    $photo_url = $data['photo_url'] ?? null;

    if (!$resume_id || !$full_name || !$email || !$phone || !$address) {
        jsonResponse(400, false, "All fields except photo_url are required");
    }

    $personalInfo = new PersonalInfo($conn);
    if ($personalInfo->createOrUpdate($resume_id, $full_name, $email, $phone, $address, $photo_url)) {
        jsonResponse(200, true, "Personal info saved");
    } else {
        jsonResponse(500, false, "Failed to save personal info");
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $resume_id = $_GET['resume_id'] ?? null;
    if (!$resume_id) {
        jsonResponse(400, false, "Resume ID required");
    }
    $personalInfo = new PersonalInfo($conn);
    if ($personalInfo->deleteByResumeId($resume_id)) {
        jsonResponse(200, true, "Personal info deleted");
    } else {
        jsonResponse(500, false, "Failed to delete personal info");
    }
}
elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $resume_id = $data['resume_id'] ?? null;
    $full_name = $data['full_name'] ?? null;
    $email = $data['email'] ?? null;
    $phone = $data['phone'] ?? null;
    $address = $data['address'] ?? null;
    $photo_url = $data['photo_url'] ?? null;

    if (!$resume_id || !$full_name || !$email || !$phone || !$address) {
        jsonResponse(400, false, "All fields except photo_url are required");
    }

    $personalInfo = new PersonalInfo($conn);
    if ($personalInfo->createOrUpdate($resume_id, $full_name, $email, $phone, $address, $photo_url)) {
        jsonResponse(200, true, "Personal info updated");
    } else {
        jsonResponse(500, false, "Failed to update personal info");
    }
} else {
    jsonResponse(405, false, "Method Not Allowed");
}
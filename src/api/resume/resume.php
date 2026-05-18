<?php

require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";
require_once '../../helpers/debug.php';
require '../../helpers/response.php';
// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");


if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// var_dump($_SERVER['REQUEST_METHOD']);
// die();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    debug("POST request in /api/resume/resume.php", $_POST);
    $data = json_decode(file_get_contents("php://input"), true);
    
    $title = $data['title'] ?? null;
    $template = $data['template'] ?? null;
    $user_id = $data['user_id'] ?? null;
    
    if (!$title || !$template || !$user_id) {
        jsonResponse(400, false, "All fields are required");
    }
    // echo "creating template";
    
    $resume = new Resume($conn);
    $id = $resume->create($user_id, $title, $template);
    
    jsonResponse(201, true, "Resume created", ["id" => $id]);
}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['user_id']) && isset($_GET['resume_id'])){ 
    debug("GET request in /api/resume/resume.php", $_GET);
    $user = $_GET['user_id'];
    $resume_id = $_GET['resume_id'];

    $resume = new Resume($conn);

    $resume = $resume->getById($resume_id);

    jsonResponse(200, true, "Resume fetched successfully", $resume);
}elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['user_id'])) {
    debug("GET request in /api/resume/resume.php by user ID: " . $_GET['user_id'], $_GET);
       $user = $_GET['user_id'];
        $resume = new Resume($conn);

        $resume = $resume->getAllResumesByUserIdWithMetaData($user);

        jsonResponse(200, true, "Resumes fetched successfully", $resume);
}  elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $id = $data['resume_id'] ?? null;
    debug("DELETE request in /api/resume/resume.php by ID: " . $id, $_DELETE);

    // var_dump("Deleting resume with ID: " . $id);

    if (!$id) {
        jsonResponse(400, false, "ID required");
    }

    $resume = new Resume($conn);
    if ($resume->delete($id)) {
        jsonResponse(200, true, "Resume deleted");
    } else {
        jsonResponse(500, false, "Failed to delete resume");
    }
}elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    debug("PUT request in /api/resume/resume.php for resume ID: " . ($data['resume_id'] ?? null), $_PUT);
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['resume_id'] ?? null;
    $title = $data['title'] ?? null;
    $template = $data['template'] ?? null;

    if (!$id || !$title || !$template) {
        jsonResponse(400, false, "All fields are required");
    }

    $resume = new Resume($conn);
    if ($resume->update($id, $title, $template)) {
        jsonResponse(200, true, "Resume updated");
    } else {
        jsonResponse(500, false, "Failed to update resume");
    }
} else {
    jsonResponse(405, false, "Method Not Allowed");
}
 

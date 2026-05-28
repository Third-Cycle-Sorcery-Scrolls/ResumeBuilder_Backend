<?php

require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";
require_once '../../helpers/debug.php';
require_once '../../helpers/response.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/Logger.php';
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

// Authenticate user for all requests except OPTIONS
try {
    $user = authenticateUser();
    $userId = $user['userId'];
} catch (Exception $e) {
    jsonResponse(401, false, "Unauthorized: Authentication required");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    debug("POST request in /api/resume/resume.php", $_POST);
    $data = json_decode(file_get_contents("php://input"), true);
    
    $title = $data['title'] ?? null;
    $template = $data['template'] ?? null;
    $user_id = $data['user_id'] ?? null;
    
    if (!$title || !$template || !$user_id) {
        jsonResponse(400, false, "All fields are required");
    }
    
    // Verify user owns the resource
    if (!verifyResourceOwnership($userId, $user_id)) {
        jsonResponse(403, false, "Forbidden: Cannot create resume for another user");
        exit();
    }
    // echo "creating template";
    
    $resume = new Resume($conn);
    $id = $resume->create($user_id, $title, $template);
    
    Logger::info(Logger::CAT_RESUME, 'RESUME_CREATE', 'Resume created', [
        'user_id'      => $userId,
        'resume_id'    => $id,
        'resume_title' => $title,
        'template'     => $template,
    ]);

    jsonResponse(201, true, "Resume created", ["id" => $id]);
}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['user_id']) && isset($_GET['resume_id'])){ 
    debug("GET request in /api/resume/resume.php", $_GET);
    $requestUserId = $_GET['user_id'];
    $resume_id = $_GET['resume_id'];
    
    // Verify user can only access their own resumes
    if (!verifyResourceOwnership($userId, $requestUserId)) {
        jsonResponse(403, false, "Forbidden: Cannot access another user's resume");
        exit();
    }

    $resume = new Resume($conn);
    $resume = $resume->getById($resume_id);

    jsonResponse(200, true, "Resume fetched successfully", $resume);
}elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['user_id'])) {
    debug("GET request in /api/resume/resume.php by user ID: " . $_GET['user_id'], $_GET);
    $requestUserId = $_GET['user_id'];
    
    // Verify user can only access their own resumes
    if (!verifyResourceOwnership($userId, $requestUserId)) {
        jsonResponse(403, false, "Forbidden: Cannot access another user's resumes");
        exit();
    }
    
    $resume = new Resume($conn);
    $resume = $resume->getAllResumesByUserIdWithMetaData($requestUserId);

    jsonResponse(200, true, "Resumes fetched successfully", $resume);
}  elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $id = $data['resume_id'] ?? null;
    debug("DELETE request in /api/resume/resume.php by ID: " . $id, $_DELETE);

    // var_dump("Deleting resume with ID: " . $id);

    if (!$id) {
        jsonResponse(400, false, "ID required");
    }
    
    // Verify user owns the resume before deleting
    $stmt = $conn->prepare("SELECT user_id FROM resumes WHERE id = ?");
    $stmt->execute([$id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume || !verifyResourceOwnership($userId, $resume['user_id'])) {
        jsonResponse(403, false, "Forbidden: Cannot delete another user's resume");
        exit();
    }

    $resumeModel = new Resume($conn);
    if ($resumeModel->delete($id)) {
        Logger::info(Logger::CAT_RESUME, 'RESUME_DELETE', 'Resume deleted', [
            'user_id'   => $userId,
            'resume_id' => $id,
        ]);
        jsonResponse(200, true, "Resume deleted");
    } else {
        jsonResponse(500, false, "Failed to delete resume");
    }
}elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['resume_id'] ?? null;
    $title = $data['title'] ?? null;
    $template = $data['template'] ?? null;

    if (!$id || !$title || !$template) {
        jsonResponse(400, false, "All fields are required");
    }
    
    // Verify user owns the resume before updating
    $stmt = $conn->prepare("SELECT user_id FROM resumes WHERE id = ?");
    $stmt->execute([$id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume || !verifyResourceOwnership($userId, $resume['user_id'])) {
        jsonResponse(403, false, "Forbidden: Cannot update another user's resume");
        exit();
    }

    $resume = new Resume($conn);
    if ($resume->update($id, $title, $template)) {
        Logger::info(Logger::CAT_RESUME, 'RESUME_UPDATE', 'Resume updated', [
            'user_id'      => $userId,
            'resume_id'    => $id,
            'resume_title' => $title,
            'template'     => $template,
        ]);
        jsonResponse(200, true, "Resume updated");
    } else {
        jsonResponse(500, false, "Failed to update resume");
    }
} else {
    jsonResponse(405, false, "Method Not Allowed");
}
 

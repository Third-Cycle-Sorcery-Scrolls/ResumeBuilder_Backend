<?php
require_once __DIR__.'/../../models/Project.php';
require_once __DIR__."/../../config/db.php";
require_once __DIR__.'/../../helpers/response.php';
require_once __DIR__.'/../../helpers/auth.php';
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

// Authenticate user for all requests
try {
    $user = authenticateUser();
    $userId = $user['userId'];
} catch (Exception $e) {
    jsonResponse(401, false, "Unauthorized: Authentication required");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $resume_id = $_GET['resume_id'] ?? null;
    if (!$resume_id) {
        jsonResponse(400, false, "Resume ID required");
    }
    
    // Verify user owns the resume
    $stmt = $conn->prepare("SELECT user_id FROM resumes WHERE id = ?");
    $stmt->execute([$resume_id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume || !verifyResourceOwnership($userId, $resume['user_id'])) {
        jsonResponse(403, false, "Forbidden: Cannot access another user's resume data");
        exit();
    }
    
    $project = new Project($conn);
    $data = $project->getByResumeId($resume_id);
    jsonResponse(200, true, "Projects fetched", $data);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $resume_id = $data['resume_id'] ?? null;
    if (!$resume_id) {
        jsonResponse(400, false, "Resume ID required");
    }
    
    // Verify user owns the resume
    $stmt = $conn->prepare("SELECT user_id FROM resumes WHERE id = ?");
    $stmt->execute([$resume_id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume || !verifyResourceOwnership($userId, $resume['user_id'])) {
        jsonResponse(403, false, "Forbidden: Cannot modify another user's resume data");
        exit();
    }
    
    if (!isset($data['projects']) || !is_array($data['projects'])) {
        jsonResponse(400, false, "Projects array required");
    }
    $projects = $data['projects'];
    foreach ($projects as $proj) {
        $title = $proj['title'] ?? null;
        $description = $proj['description'] ?? null;
        $link = $proj['link'] ?? null;

        if (!$title || !$description) {
            jsonResponse(400, false, "Each project must have title and description");
        }

        $project = new Project($conn);
        $project->create($resume_id, $title, $description, $link);
    }
    jsonResponse(201, true, "Projects saved");

} elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['resume_id'] ?? null;
    if (!$id) {
        jsonResponse(400, false, "Resume ID required");
    }
    if (!isset($data['projects']) || !is_array($data['projects'])) {
        jsonResponse(400, false, "Projects array required");
    }
    $projects = $data['projects'];
    foreach ($projects as $proj) {
        $proj_id = $proj['id'] ?? null;
        $title = $proj['title'] ?? null;
        $description = $proj['description'] ?? null;
        $link = $proj['link'] ?? null;

        if (!$title || !$description) {
            jsonResponse(400, false, "Each project must have title and description");
        }
        $project = new Project($conn);
        if ($proj_id) {
            $project->update($proj_id, $title, $description, $link);
        } else {
            $project->create($id, $title, $description, $link);
        }
    }
    jsonResponse(200, true, "Projects updated");
    
} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = $_DELETE['id'] ?? null;

    if (!$id) {
        jsonResponse(400, false, "ID is required");
    }

    $project = new Project($conn);
    if ($project->delete($id)) {
        jsonResponse(200, true, "Project deleted");
    } else {
        jsonResponse(500, false, "Failed to delete project");
    }
} else {
    jsonResponse(405, false, "Method Not Allowed");
}
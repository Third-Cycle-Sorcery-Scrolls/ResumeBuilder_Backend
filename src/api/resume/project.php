<?php
require_once __DIR__.'/../../models/Project.php';
require_once __DIR__."/../../config/db.php";

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

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $resume_id = $_GET['resume_id'] ?? null;
    if (!$resume_id) {
        jsonResponse(400, false, "Resume ID required");
    }
    $project = new Project($conn);
    $data = $project->getByResumeId($resume_id);
    jsonResponse(200, true, "Projects fetched", $data);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     projects
// : 
// [{id: "proj_1", title: "LocalMed",…}, {id: "proj_2", title: "Task Manager API",…}]
// 0
// : 
// {id: "proj_1", title: "LocalMed",…}
// 1
// : 
// {id: "proj_2", title: "Task Manager API",…}
// resume_id
// : 
// "65"
    $data = json_decode(file_get_contents("php://input"), true);
    $resume_id = $data['resume_id'] ?? null;
    if (!$resume_id) {
        jsonResponse(400, false, "Resume ID required");
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
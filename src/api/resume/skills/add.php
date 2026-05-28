<?php
require_once "../../../config/db.php";
require_once "../../../models/Skill.php";
require_once "../../../helpers/response.php";
require_once "../../../helpers/debug.php";
require_once "../../../helpers/auth.php";

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    debug("POST request in /api/resume/skills/add.php", $_POST);
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);
    
    $resume_id = $data['resume_id'] ?? null;
    $skills = $data['skills'] ?? null;
    
    if (!$resume_id) {
        jsonResponse(400, false, "Missing required fields: resume_id");
        exit();
    }
    
    // Verify user owns the resume
    $stmt = $conn->prepare("SELECT user_id FROM resumes WHERE id = ?");
    $stmt->execute([$resume_id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume || !verifyResourceOwnership($userId, $resume['user_id'])) {
        jsonResponse(403, false, "Forbidden: Cannot modify another user's resume data");
        exit();
    }
    
    if (!$skills) {
        jsonResponse(400, false, "Skills data is required");
        exit();
    }
    $stmt = "INSERT INTO skills (resume_id, skill_name,proficiency) VALUES (:rid, :skill, :proficiency)";
    $stmt = $conn->prepare($stmt);
    foreach ($skills as $skill) {
        $success = $stmt->execute([
            ':rid' => $resume_id,
            ':skill' => $skill['skill_name'] ?? null,
            ':proficiency' => $skill['proficiency'] ?? null
        ]);
    
        if (!$success) {
            jsonResponse(500, false, "Failed to insert skill");
            exit();
        }
    }
    jsonResponse(201, true, "Skills added successfully");

} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // skil update
    $data = json_decode(file_get_contents("php://input"), true);
    $resume_id = $data['resume_id'] ?? null;
    $skills = $data['skills'] ?? null;
    debug("PUT request in /api/resume/skills/add.php for resume ID: " . $resume_id, $skills);
    if (!$resume_id) {
        jsonResponse(400, false, "Missing required fields: resume_id");
    }

    // Delete existing skills for the resume
    $deleteStmt = $conn->prepare("DELETE FROM skills WHERE resume_id = :rid");
    $deleteStmt->execute([':rid' => $resume_id]);
    // Insert new skills
    $insertStmt = $conn->prepare("INSERT INTO skills (resume_id, skill_name, proficiency) VALUES (:rid, :skill, :proficiency)");
    $failure = 0;
    foreach ($skills as $skill) {
        //null name check
        if ($skill['name'] === null) {
            continue; // Skip this skill if name is null
        } 
        

        $success = $insertStmt->execute([
            ':resume_id' => $resume_id,
            ':skill_name' => $skill['name'] ?? null,
            ':proficiency' => $skill['proficiency'] ?? null
        ]);
        if (!$success) {
            $failure++;
        }
    }
    if ($failure == count($skills) and $failure > 0) {
        jsonResponse(500, false, "Failed to update skills");
    } else {

    jsonResponse(200, true, "Skills updated successfully");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    debug("DELETE request in /api/resume/skills/add.php", $_DELETE);
    // skill delete
    parse_str(file_get_contents("php://input"), $_DELETE);
    $resume_id = $_DELETE['resume_id'] ?? null;
    if (!$resume_id) {
        jsonResponse(400, false, "Resume ID is required");
    }
    $deleteStmt = $conn->prepare("DELETE FROM skills WHERE resume_id = :rid");
    if ($deleteStmt->execute([':rid' => $resume_id])) {
        jsonResponse(200, true, "Skills deleted successfully");
    } else {
        jsonResponse(500, false, "Failed to delete skills");
    }
} else {
    jsonResponse(405, false, "Method not allowed");
}

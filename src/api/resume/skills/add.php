<?php

define('BASE_PATH', dirname(__DIR__, 3));

require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/models/Skill.php';
require_once BASE_PATH . '/helpers/response.php';
require_once BASE_PATH . '/helpers/logger.php';
require_once BASE_PATH . '/middlewares/authMiddleware.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(405, false, "Method not allowed");
    }

    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        jsonResponse(400, false, "Invalid JSON input");
    }

    $resume_id = $data['resume_id'] ?? null;
    $skill_name = $data['skill_name'] ?? null;
    $proficiency = $data['proficiency'] ?? null;

    if (!isset($resume_id, $skill_name, $proficiency) ||
        trim($skill_name) === "" ||
        trim($proficiency) === "") {
        jsonResponse(400, false, "All fields are required");
    }

    // Ownership check (future-ready)
    $user = authMiddleware();
    $user_id = $user['id'];
    logError($conn, "DEBUG: resume_id=$resume_id, user_id=$user_id");

    $stmt = $conn->prepare("SELECT id FROM resumes WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        ':id' => $resume_id,
        ':user_id' => $user_id
    ]);

    if (!$stmt->fetch()) {

        logError($conn, "Unauthorized access attempt: user=$user_id, resume_id=$resume_id");

        jsonResponse(403, false, "You do not have permission to modify this resume");
    }

    // Add skill
    $skill = new Skill($conn);
    if ($skill->add($resume_id, $skill_name, $proficiency)) {
        jsonResponse(201, true, "Skill added successfully");
    } else {

        logError($conn, "Skill insert failed (unknown reason)");

        jsonResponse(500, false, "Could not add skill");
    }

} catch (Exception $e) {

    logError($conn, $e->getMessage(), $e->getFile(), $e->getLine(), $user_id ?? null);

    jsonResponse(500, false, "Internal Server Error");
}


<?php

define('BASE_PATH', dirname(__DIR__, 3));

require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/models/Skill.php';
require_once BASE_PATH . '/helpers/response.php';
require_once BASE_PATH . '/helpers/logger.php';
require_once BASE_PATH . '/middlewares/authMiddleware.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");

try {

    //Method check
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        jsonResponse(404, false, "Method not allowed");
    }

    //Get skill ID
    $id = $_GET['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        logError($conn, "Skill update failed: missing or invalid skill_id", __FILE__, __LINE__, null);
        jsonResponse(400, false, "Valid skill ID is required");
    }

    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        logError($conn, "Skill update failed: invalid JSON input", __FILE__, __LINE__, null);
        jsonResponse(400, false, "Invalid JSON");
    }

    $skill_name = $data['skill_name'] ?? null;
    $proficiency = $data['proficiency'] ?? null;

    // Validate input
    if (!isset($skill_name, $proficiency) ||
        trim($skill_name) === "" ||
        trim($proficiency) === "") {
        logError($conn, "Skill update failed: missing fields for skill_id=$id", __FILE__, __LINE__, null);
        jsonResponse(400, false, "All fields are required");
    }

    $user = authMiddleware();
    $user_id = $user['id'];

    // Ownership check (structure ready)
    $stmt = $conn->prepare("
        SELECT s.id 
        FROM skills s
        JOIN resumes r ON s.resume_id = r.id
        WHERE s.id = :skill_id AND r.user_id = :user_id
    ");

    $stmt->execute([
        ':skill_id' => $id,
        ':user_id' => $user_id
    ]);

    if (!$stmt->fetch()) {

        logError($conn, "Unauthorized update attempt for skill_id=$id by user_id=$user_id", __FILE__, __LINE__, $user_id);

        jsonResponse(403, false, "You do not have permission to update this skill");
    }

    // Update skill
    $skill = new Skill($conn);

    if ($skill->update($id, $skill_name, $proficiency)) {

        jsonResponse(200, true, "Skill updated successfully");

    } else {

        logError($conn, "Skill update failed for skill_id=$id, user_id=$user_id", __FILE__, __LINE__, $user_id);

        jsonResponse(500, false, "Could not update skill");
    }
    
} catch (Exception $e) {
    
    logError(
        $conn,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $user_id ?? null
    );

    jsonResponse(500, false, "Internal Server Error");
}
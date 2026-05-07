<?php

define('BASE_PATH', dirname(__DIR__, 3));

require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/models/Skill.php';
require_once BASE_PATH . '/helpers/response.php';
require_once BASE_PATH . '/helpers/logger.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");

// TEMP user function (for future auth)
function getCurrentUserId() {
    return 1; // TODO: replace with real authentication
}

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    jsonResponse(405, false, "Method not allowed");
}

// Get skill ID
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    jsonResponse(400, false, "Valid skill ID is required");
}

// Get current user (temp)
$user_id = getCurrentUserId();

// Ownership check (IMPORTANT)
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

    logError(
        $conn,
        "Unauthorized delete attempt for skill_id=$id by user_id=$user_id"
    );

    jsonResponse(403, false, "You do not have permission to delete this skill");
}

// Delete skill
$skill = new Skill($conn);

try {

    if ($skill->delete($id)) {

        jsonResponse(200, true, "Skill deleted successfully");

    } else {

        logError(
            $conn,
            "Skill delete failed for skill_id=$id"
        );

        jsonResponse(500, false, "Could not delete skill");
    }

} catch (Exception $e) {

    logError(
        $conn,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $user_id
    );

    jsonResponse(500, false, "Internal Server Error");
}
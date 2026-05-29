<?php
require_once "../../../config/db.php";
require_once "../../../models/Skill.php";
require_once "../../../helpers/response.php";
require_once "../../../helpers/debug.php";
require_once "../../../helpers/auth.php";
require_once "../../../helpers/Logger.php";          // Your Logger
// error_handler.php is already registered globally via db.php — do not include again

// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Authenticate user for all requests
try {
    $user = authenticateUser();
    $userId = $user['userId'];
} catch (Exception $e) {
    // Log failed auth attempts
    Logger::warning(Logger::CAT_AUTH, 'AUTH_FAILED', 'Unauthenticated request to skills endpoint', [
        'user_id' => null,
    ]);
    jsonResponse(401, false, "Unauthorized: Authentication required");
    exit();
}

// Instantiate your Skill model — single place, used by all methods below
$skillModel = new Skill($conn);

// ─── POST — Add skills ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    debug("POST request in /api/resume/skills/add.php", $_POST);

    $data      = json_decode(file_get_contents("php://input"), true);
    $resume_id = $data['resume_id'] ?? null;
    $skills    = $data['skills']    ?? null;

    if (!$resume_id) {
        logValidationFailure("Missing resume_id on skill POST", ['user_id' => $userId]);
        jsonResponse(400, false, "Missing required fields: resume_id");
        exit();
    }

    // Verify the authenticated user owns this resume (unchanged logic)
    $stmt = $conn->prepare("SELECT user_id FROM resumes WHERE id = ?");
    $stmt->execute([$resume_id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resume || !verifyResourceOwnership($userId, $resume['user_id'])) {
        Logger::warning(Logger::CAT_AUTH, 'UNAUTHORIZED_RESUME_ACCESS', 'User tried to add skills to another user\'s resume', [
            'user_id'   => $userId,
            'resume_id' => $resume_id,
        ]);
        jsonResponse(403, false, "Forbidden: Cannot modify another user's resume data");
        exit();
    }

    if (!$skills) {
        logValidationFailure("Skills data missing on POST", ['user_id' => $userId, 'resume_id' => $resume_id]);
        jsonResponse(400, false, "Skills data is required");
        exit();
    }

    // Use Skill model's add() instead of raw inline SQL
    $failed = 0;
    foreach ($skills as $skill) {
        try {
            $success = $skillModel->add(
                $resume_id,
                $skill['skill_name'] ?? null,
                $skill['proficiency'] ?? null
            );
            if (!$success) {
                $failed++;
            }
        } catch (Exception $e) {
            logDbFailure($e, ['user_id' => $userId, 'resume_id' => $resume_id]);
            jsonResponse(500, false, "Failed to insert skill");
            exit();
        }
    }

    if ($failed > 0) {
        Logger::error(Logger::CAT_RESUME, 'SKILL_INSERT_PARTIAL_FAIL', 'One or more skills failed to insert', [
            'user_id'   => $userId,
            'resume_id' => $resume_id,
            'failed'    => $failed,
        ]);
        jsonResponse(500, false, "Failed to insert skill");
        exit();
    }

    Logger::info(Logger::CAT_RESUME, 'SKILL_ADDED', 'Skills added successfully', [
        'user_id'   => $userId,
        'resume_id' => $resume_id,
        'count'     => count($skills),
    ]);
    jsonResponse(201, true, "Skills added successfully");

// ─── PUT — Replace all skills for a resume ────────────────────────────────────
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    $data      = json_decode(file_get_contents("php://input"), true);
    $resume_id = $data['resume_id'] ?? null;
    $skills    = $data['skills']    ?? null;

    debug("PUT request in /api/resume/skills/add.php for resume ID: " . $resume_id, $skills);

    if (!$resume_id) {
        logValidationFailure("Missing resume_id on skill PUT", ['user_id' => $userId]);
        jsonResponse(400, false, "Missing required fields: resume_id");
        exit();
    }

    // Delete existing skills for the resume (same strategy as before)
    try {
        $deleteStmt = $conn->prepare("DELETE FROM skills WHERE resume_id = :rid");
        $deleteStmt->execute([':rid' => $resume_id]);
    } catch (Exception $e) {
        logDbFailure($e, ['user_id' => $userId, 'resume_id' => $resume_id, 'action' => 'DELETE_BEFORE_UPDATE']);
        jsonResponse(500, false, "Failed to update skills");
        exit();
    }

    // Insert updated skills via Skill model's add()
    // Bug fix: the original PUT used wrong keys (':resume_id', ':skill_name') in execute()
    // which silently failed. Skill::add() uses the correct named params internally.
    $failure = 0;
    foreach ($skills as $skill) {
        // Keep the original null-name guard
        if (($skill['name'] ?? null) === null) {
            continue;
        }

        try {
            $success = $skillModel->add(
                $resume_id,
                $skill['name'],
                $skill['proficiency'] ?? null
            );
            if (!$success) {
                $failure++;
            }
        } catch (Exception $e) {
            logDbFailure($e, ['user_id' => $userId, 'resume_id' => $resume_id]);
            $failure++;
        }
    }

    if ($failure === count($skills) && $failure > 0) {
        Logger::error(Logger::CAT_RESUME, 'SKILL_UPDATE_FAILED', 'All skill inserts failed during PUT', [
            'user_id'   => $userId,
            'resume_id' => $resume_id,
            'failed'    => $failure,
        ]);
        jsonResponse(500, false, "Failed to update skills");
    } else {
        Logger::info(Logger::CAT_RESUME, 'SKILL_UPDATED', 'Skills updated successfully', [
            'user_id'   => $userId,
            'resume_id' => $resume_id,
            'inserted'  => count($skills) - $failure,
            'failed'    => $failure,
        ]);
        jsonResponse(200, true, "Skills updated successfully");
    }

// ─── DELETE — Remove all skills for a resume ──────────────────────────────────
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    debug("DELETE request in /api/resume/skills/add.php", []);

    parse_str(file_get_contents("php://input"), $_DELETE);
    $resume_id = $_DELETE['resume_id'] ?? null;

    if (!$resume_id) {
        logValidationFailure("Missing resume_id on skill DELETE", ['user_id' => $userId]);
        jsonResponse(400, false, "Resume ID is required");
        exit();
    }

    // Skill::delete() targets a single skill by its own ID.
    // The endpoint deletes ALL skills for a resume — that bulk operation
    // belongs here, not in the model. We keep the direct query intentionally.
    try {
        $deleteStmt = $conn->prepare("DELETE FROM skills WHERE resume_id = :rid");
        if ($deleteStmt->execute([':rid' => $resume_id])) {
            Logger::info(Logger::CAT_RESUME, 'SKILL_DELETED', 'All skills deleted for resume', [
                'user_id'   => $userId,
                'resume_id' => $resume_id,
            ]);
            jsonResponse(200, true, "Skills deleted successfully");
        } else {
            Logger::error(Logger::CAT_RESUME, 'SKILL_DELETE_FAILED', 'Bulk skill delete returned false', [
                'user_id'   => $userId,
                'resume_id' => $resume_id,
            ]);
            jsonResponse(500, false, "Failed to delete skills");
        }
    } catch (Exception $e) {
        logDbFailure($e, ['user_id' => $userId, 'resume_id' => $resume_id]);
        jsonResponse(500, false, "Failed to delete skills");
    }

} else {
    jsonResponse(405, false, "Method not allowed");
}
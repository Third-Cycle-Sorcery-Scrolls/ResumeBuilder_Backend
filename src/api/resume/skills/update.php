<?php

require_once "../../../config/db.php";
require_once "../../../models/Skill.php";
require_once "../../../helpers/response.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");

// TEMP user function (for future auth)
function getCurrentUserId() {
    return 1; // TODO: replace with real authentication
}

//Method check
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonResponse(405, false, "Method not allowed");
}

//Get skill ID
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    jsonResponse(400, false, "Valid skill ID is required");
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    jsonResponse(400, false, "Invalid JSON");
}

$skill_name = $data['skill_name'] ?? null;
$proficiency = $data['proficiency'] ?? null;

// Validate input
if (!isset($skill_name, $proficiency) ||
    trim($skill_name) === "" ||
    trim($proficiency) === "") {
    jsonResponse(400, false, "All fields are required");
}

// Get current user (temp)
$user_id = getCurrentUserId();

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
    jsonResponse(403, false, "You do not have permission to update this skill");
}

// Update skill
$skill = new Skill($conn);

if ($skill->update($id, $skill_name, $proficiency)) {
    jsonResponse(200, true, "Skill updated successfully");
} else {
    jsonResponse(500, false, "Could not update skill");
}
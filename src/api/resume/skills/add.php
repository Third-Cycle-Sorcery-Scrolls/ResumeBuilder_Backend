<?php
require_once "../../config/db.php";
require_once "../../models/Skill.php";
require_once "../../helpers/response.php";

// Set CORS headers for React frontend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, false, "Method not allowed");
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

$resume_id = $data['resume_id'] ?? null;
$skill_name = $data['skill_name'] ?? null;
$proficiency = $data['proficiency'] ?? null;

if (!$resume_id || !$skill_name || !$proficiency) {
    jsonResponse(400, false, "Missing required fields: resume_id, skill_name, or proficiency");
}

$skill = new Skill($conn);

if ($skill->add($resume_id, $skill_name, $proficiency)) {
    jsonResponse(201, true, "Skill added successfully");
} else {
    jsonResponse(500, false, "Internal server error: Could not add skill");
}


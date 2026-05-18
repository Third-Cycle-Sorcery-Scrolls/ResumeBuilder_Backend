<?php


require_once __DIR__.'/../../models/Resume.php';
require_once __DIR__."/../../config/db.php";
require_once __DIR__.'/../../models/Resume.php';


require '../../helpers/response.php';
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
/*
flow:-
  - user sends a GET request to this endpoint with user_id as query parameter
  - we fetch all resumes associated with that user_id
  - we attach all the related information like education, experience, projects, skills, personal info to each resume
  - we send the response back to the user with an array of resumes, each resume contains all the related information
*/

if($_SERVER['REQUEST_METHOD'] == "GET" and isset($_GET['user_id'])){
   $user = $_GET['user_id'];

   $resume = new Resume($conn);

   $resume = $resume->getAllResumeByUserId($user);

   jsonResponse(200, true, "Resumes fetched successfully", $resume);

}


// $id = $_GET['id'] ?? null;

// if (!$id) {
//     jsonResponse(400, false, "ID required");
// }

// $resume = new Resume($conn);
// $data = $resume->getById($id);

// if (!$data) {
//     jsonResponse(404, false, "Resume not found");
// }

// jsonResponse(200, true, "Resume fetched", $data);